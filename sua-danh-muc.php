<?php

session_start();

require __DIR__ . '/includes.php'; // $nav, $footer, esc(), render_tag(), render_header(), render_footer()...
require __DIR__ . '/db.php';       // $pdo
require __DIR__ . '/queries.php';  // category_khoa(), fetch_section_by_key(), fetch_section_images(),
                                    // fetch_all_books_brief(), save_carousel_section()

// Chỉ admin mới được vào trang này
$isAdmin = (($_SESSION['vai_tro'] ?? '') === 'admin');

if (!$isAdmin) {
    header('Location: login.php');
    exit;
}

// Tên thể loại lấy từ query string, vd: sua-danh-muc.php?the_loai=Thể thao
$the_loai = trim($_GET['the_loai'] ?? '');
if ($the_loai === '') {
    $the_loai = 'Thể thao'; // mặc định nếu không truyền the_loai trên URL
}

// Khối lưới theo thể loại dùng chung cơ chế lưu trữ với carousel "SẮP RA MẮT"
// (bảng trang_chu_muc / trang_chu_muc_anh), chỉ khác ở "khoa" được sinh ra từ tên thể loại.
$khoa = category_khoa($the_loai);

$section        = fetch_section_by_key($pdo, $khoa);
$existingTitle  = $section['tieu_de'] ?? ('SÁCH ' . mb_strtoupper($the_loai));
$existingCovers = $section ? fetch_section_images($pdo, $section['id_muc']) : [];
$allBooks       = fetch_all_books_brief($pdo); // dùng cho <select> chọn sách liên kết với từng ảnh

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $tieu_de = trim($_POST['tieu_de_muc'] ?? '');
    if ($tieu_de === '') {
        $errors[] = 'Vui lòng nhập tên mục.';
    }

    $anhBiaHienTai = $_POST['anh_bia_hien_tai'] ?? [];
    $idSachLienKet = $_POST['id_sach_lien_ket'] ?? [];
    $files         = $_FILES['anh_bia_moi'] ?? null;
    $finalItems    = [];

    $uploadDir  = __DIR__ . '/images';
    $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $slotCount = $files ? count($files['name']) : count($anhBiaHienTai);

    for ($i = 0; $i < $slotCount; $i++) {
        $existing   = $anhBiaHienTai[$i] ?? '';
        $idSach     = isset($idSachLienKet[$i]) && $idSachLienKet[$i] !== '' ? (int) $idSachLienKet[$i] : null;
        $hasNewFile = $files && !empty($files['name'][$i]) && $files['error'][$i] === UPLOAD_ERR_OK;

        if ($hasNewFile) {
            $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowedExt, true)) {
                $errors[] = 'Ảnh bìa #' . ($i + 1) . ' phải có định dạng jpg, jpeg, png hoặc webp.';
                continue;
            }

            $baseName = pathinfo($files['name'][$i], PATHINFO_FILENAME);
            $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $baseName);
            if ($safeName === '') {
                $safeName = 'anh_bia';
            }

            $newName = $safeName . '.' . $ext;
            $dest    = $uploadDir . '/' . $newName;

            // Nếu trùng tên với 1 file khác đã tồn tại (không phải chính ảnh cũ của ô này) thì thêm hậu tố để tránh ghi đè nhầm
            if (is_file($dest) && $dest !== $uploadDir . '/' . basename($existing)) {
                $newName = $safeName . '_' . time() . '.' . $ext;
                $dest    = $uploadDir . '/' . $newName;
            }

            if (move_uploaded_file($files['tmp_name'][$i], $dest)) {
                $finalItems[] = ['anh_bia' => 'images/' . $newName, 'id_sach' => $idSach];
            } else {
                $errors[] = 'Không upload được ảnh bìa #' . ($i + 1) . '.';
            }
        } elseif ($existing !== '') {
            $finalItems[] = ['anh_bia' => $existing, 'id_sach' => $idSach];
        }
        // Ô trống (không chọn ảnh mới, cũng không có ảnh cũ) -> bỏ qua
    }

    if (empty($errors)) {
        save_carousel_section($pdo, $khoa, $tieu_de, $finalItems);

        $success        = true;
        $section        = fetch_section_by_key($pdo, $khoa);
        $existingTitle  = $section['tieu_de'];
        $existingCovers = fetch_section_images($pdo, $section['id_muc']);
    }
}

$debug_mode             = 'carousel';
$debug_label             = $existingTitle;
$debug_existing_covers   = $existingCovers;
$debug_all_books         = $allBooks;
$debug_show_add_button   = true;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Chỉnh sửa danh mục - Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>

<?php render_header($nav, ''); ?>

<div class="page-body" style="padding: 24px;">

    <h1 style="margin-bottom: 8px;">
        Chỉnh sửa danh mục: <?= htmlspecialchars($existingTitle) ?>
    </h1>
    <p style="margin-bottom: 16px; color: #777;">
        Thể loại: <?= htmlspecialchars($the_loai) ?> — chọn sách sẽ hiển thị trong khối này ở trang chủ.
    </p>

    <?php if ($success): ?>
        <div class="success-message">Đã lưu thay đổi thành công.</div>
    <?php endif; ?>

    <?php foreach ($errors as $err): ?>
        <div class="error-message"><?= htmlspecialchars($err) ?></div>
    <?php endforeach; ?>

    <form method="POST" action="" enctype="multipart/form-data">
        <?php include __DIR__ . '/admin_debug_panel.php'; ?>
    </form>

    <a href="index.php" class="btn-more">Quay lại trang chủ</a>

</div>

<?php render_footer($footer); ?>

</body>
</html>
