<?php

session_start();

require __DIR__ . '/includes.php'; // $nav, $footer, esc(), render_tag(), render_header(), render_footer()...
require __DIR__ . '/db.php';       // $pdo
require __DIR__ . '/queries.php';  // fetch_book_by_id(), fetch_genres(), get_or_create_author(), update_book()

// Chỉ admin mới được vào trang này
$isAdmin = (($_SESSION['vai_tro'] ?? '') === 'admin');

if (!$isAdmin) {
    header('Location: login.php');
    exit;
}

$id   = (int) ($_GET['id'] ?? 0);
$book = $id > 0 ? fetch_book_by_id($pdo, $id) : null;

$errors  = [];
$success = false;

if ($book && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $ten_sach        = trim($_POST['ten_sach'] ?? '');
    $id_genre        = ($_POST['id_genre'] ?? '') !== '' ? (int) $_POST['id_genre'] : null;
    $ten_tac_gia     = trim($_POST['ten_tac_gia'] ?? '');
    $tinh_trang      = $_POST['tinh_trang'] ?? '';
    $sach_vat_ly     = $_POST['sach_vat_ly'] ?? '';
    $phim_chuyen_the = $_POST['phim_chuyen_the'] ?? '';

    if ($ten_sach === '') {
        $errors[] = 'Vui lòng nhập tên sách.';
    }
    if (!in_array($tinh_trang, ['Có sẵn', 'Đang được mượn', 'Ngừng phát hành'], true)) {
        $errors[] = 'Vui lòng chọn tình trạng hợp lệ.';
    }
    if (!in_array($sach_vat_ly, ['Còn sách', 'Hết sách'], true)) {
        $errors[] = 'Vui lòng chọn tình trạng sách vật lý hợp lệ.';
    }
    if (!in_array($phim_chuyen_the, ['Có', 'Không'], true)) {
        $errors[] = 'Vui lòng chọn tình trạng phim chuyển thể hợp lệ.';
    }

    // Ảnh bìa: null nghĩa là giữ nguyên ảnh cũ (không upload ảnh mới)
    $anh_bia_moi = null;

    if (!empty($_FILES['anh_bia_moi']['name']) && $_FILES['anh_bia_moi']['error'] === UPLOAD_ERR_OK) {
        $ext         = strtolower(pathinfo($_FILES['anh_bia_moi']['name'], PATHINFO_EXTENSION));
        $allowedExt  = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($ext, $allowedExt, true)) {
            $errors[] = 'Ảnh bìa phải có định dạng jpg, jpeg, png hoặc webp.';
        } else {
            $uploadDir = __DIR__ . '/images';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $newName = 'sach_' . $id . '_' . time() . '.' . $ext;
            $dest    = $uploadDir . '/' . $newName;

            if (move_uploaded_file($_FILES['anh_bia_moi']['tmp_name'], $dest)) {
                $anh_bia_moi = 'images/' . $newName;
            } else {
                $errors[] = 'Không upload được ảnh bìa, vui lòng thử lại.';
            }
        }
    }

    if (empty($errors)) {
        $id_tac_gia = get_or_create_author($pdo, $ten_tac_gia);

        update_book($pdo, $id, [
            'ten_sach'        => $ten_sach,
            'id_genre'        => $id_genre,
            'id_tac_gia'      => $id_tac_gia,
            'tinh_trang'      => $tinh_trang,
            'sach_vat_ly'     => $sach_vat_ly,
            'phim_chuyen_the' => $phim_chuyen_the,
            'anh_bia'         => $anh_bia_moi,
        ]);

        $success = true;
        $book    = fetch_book_by_id($pdo, $id); // tải lại dữ liệu mới nhất để hiển thị
    }
}

$the_loai_list         = fetch_genres($pdo);
$debug_mode             = 'form';
$debug_label            = 'chỉnh sửa sách "' . ($book['ten_sach'] ?? '') . '"';
$debug_show_add_button  = false;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Chỉnh sửa sách - Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>

<?php render_header($nav, ''); ?>

<div class="page-body" style="padding: 24px;">

    <h1 style="margin-bottom: 16px;">Chỉnh sửa sách</h1>

    <?php if (!$book): ?>

        <p>Không tìm thấy sách cần chỉnh sửa.</p>
        <a href="index.php" class="btn-more">Về trang chủ</a>

    <?php else: ?>

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

    <?php endif; ?>

</div>

<?php render_footer($footer); ?>

</body>
</html>
