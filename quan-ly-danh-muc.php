<?php

require __DIR__ . '/includes.php';
require_once __DIR__ . '/db.php'; // db.php có sẵn trong project (login.php cũng dùng file này), cung cấp $pdo

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$is_admin = isset($_SESSION['vai_tro']) && $_SESSION['vai_tro'] === 'admin';
if (!$is_admin) {
    http_response_code(403);
    die('Bạn không có quyền truy cập trang này.');
}

$edit_id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$errors  = [];

// Nạp dữ liệu mục đang chỉnh sửa (nếu có id trên URL)
$existing_category = null;
$existing_covers   = [];

if ($edit_id) {
    $stmt = $pdo->prepare('SELECT id_category, ten_category FROM categories WHERE id_category = ?');
    $stmt->execute([$edit_id]);
    $existing_category = $stmt->fetch();

    if ($existing_category) {
        $coverStmt = $pdo->prepare(
            'SELECT s.id_sach, s.anh_bia, s.ten_sach
             FROM sach s
             INNER JOIN genres g ON s.id_genre = g.id_genre
             WHERE g.id_category = ?
             ORDER BY s.ngay_them DESC'
        );
        $coverStmt->execute([$edit_id]);
        $existing_covers = $coverStmt->fetchAll();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten_muc = trim($_POST['ten_muc'] ?? '');
    if ($ten_muc === '') {
        $errors[] = 'Vui lòng nhập tên mục.';
    }

    $upload_dir = __DIR__ . '/images/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    if (empty($errors)) {
        $pdo->beginTransaction();
        try {
            if ($edit_id && $existing_category) {
                // Cập nhật tên mục đã có
                $upd = $pdo->prepare('UPDATE categories SET ten_category = ? WHERE id_category = ?');
                $upd->execute([$ten_muc, $edit_id]);
                $id_category = $edit_id;

                // Lấy 1 thể loại sẵn có của mục này để gắn sách mới vào,
                // nếu chưa có thể loại nào thì tạo mới
                $genreStmt = $pdo->prepare('SELECT id_genre FROM genres WHERE id_category = ? LIMIT 1');
                $genreStmt->execute([$id_category]);
                $genre = $genreStmt->fetch();

                if ($genre) {
                    $id_genre = $genre['id_genre'];
                } else {
                    $insGenre = $pdo->prepare('INSERT INTO genres (ten_genre, id_category) VALUES (?, ?)');
                    $insGenre->execute([$ten_muc, $id_category]);
                    $id_genre = $pdo->lastInsertId();
                }
            } else {
                // Tạo mục mới
                $insCat = $pdo->prepare('INSERT INTO categories (ten_category) VALUES (?)');
                $insCat->execute([$ten_muc]);
                $id_category = $pdo->lastInsertId();

                // Tạo 1 thể loại mặc định cùng tên để có thể gắn sách vào ngay
                $insGenre = $pdo->prepare('INSERT INTO genres (ten_genre, id_category) VALUES (?, ?)');
                $insGenre->execute([$ten_muc, $id_category]);
                $id_genre = $pdo->lastInsertId();
            }

            // Cập nhật / xoá các bìa đã có ngay trên form
            if ($edit_id && $existing_category && $existing_covers) {
                foreach ($existing_covers as $c) {
                    $id_sach = (int) $c['id_sach'];

                    // Tick "Xoá bìa này" -> xoá sách khỏi CSDL và xoá luôn file ảnh cũ (nếu có)
                    if (!empty($_POST['edit_xoa'][$id_sach])) {
                        $del = $pdo->prepare('DELETE FROM sach WHERE id_sach = ?');
                        $del->execute([$id_sach]);

                        if ($c['anh_bia'] && str_starts_with($c['anh_bia'], 'images/')) {
                            $old_path = __DIR__ . '/' . $c['anh_bia'];
                            if (is_file($old_path)) {
                                unlink($old_path);
                            }
                        }
                        continue;
                    }

                    // Bỏ trống ô tên -> giữ nguyên tên cũ thay vì xoá mất
                    $ten_sach_moi = trim($_POST['edit_ten_sach'][$id_sach] ?? '');
                    if ($ten_sach_moi === '') {
                        $ten_sach_moi = $c['ten_sach'];
                    }

                    $anh_bia_moi = null;
                    if (
                        isset($_FILES['edit_anh_bia']['tmp_name'][$id_sach]) &&
                        $_FILES['edit_anh_bia']['tmp_name'][$id_sach] !== '' &&
                        $_FILES['edit_anh_bia']['error'][$id_sach] === UPLOAD_ERR_OK
                    ) {
                        $ext = strtolower(pathinfo($_FILES['edit_anh_bia']['name'][$id_sach], PATHINFO_EXTENSION));
                        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                        if (in_array($ext, $allowed, true)) {
                            $filename = 'sach-' . uniqid() . '.' . $ext;
                            move_uploaded_file($_FILES['edit_anh_bia']['tmp_name'][$id_sach], $upload_dir . $filename);
                            $anh_bia_moi = 'images/' . $filename;

                            // Xoá ảnh bìa cũ để không rác thư mục images/
                            if ($c['anh_bia'] && str_starts_with($c['anh_bia'], 'images/')) {
                                $old_path = __DIR__ . '/' . $c['anh_bia'];
                                if (is_file($old_path)) {
                                    unlink($old_path);
                                }
                            }
                        }
                    }

                    if ($anh_bia_moi !== null) {
                        $updSach = $pdo->prepare('UPDATE sach SET ten_sach = ?, anh_bia = ? WHERE id_sach = ?');
                        $updSach->execute([$ten_sach_moi, $anh_bia_moi, $id_sach]);
                    } else {
                        $updSach = $pdo->prepare('UPDATE sach SET ten_sach = ? WHERE id_sach = ?');
                        $updSach->execute([$ten_sach_moi, $id_sach]);
                    }
                }
            }

            // Xử lý từng ảnh bìa được chọn trong form
            $names = $_POST['ten_sach'] ?? [];
            $files = $_FILES['anh_bia'] ?? null;

            if ($files) {
                foreach ($files['tmp_name'] as $i => $tmp_name) {
                    if ($tmp_name === '' || $files['error'][$i] !== UPLOAD_ERR_OK) {
                        continue; // ô này chưa chọn ảnh -> bỏ qua bình thường
                    }

                    $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
                    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                    if (!in_array($ext, $allowed, true)) {
                        continue; // định dạng không hỗ trợ
                    }

                    // Có ảnh hợp lệ -> luôn lưu, kể cả khi chưa nhập tên sách
                    $ten_sach = trim($names[$i] ?? '');
                    if ($ten_sach === '') {
                        $ten_sach = pathinfo($files['name'][$i], PATHINFO_FILENAME);
                    }

                    $filename = 'sach-' . uniqid() . '.' . $ext;
                    move_uploaded_file($tmp_name, $upload_dir . $filename);

                    $insSach = $pdo->prepare('INSERT INTO sach (ten_sach, id_genre, anh_bia) VALUES (?, ?, ?)');
                    $insSach->execute([$ten_sach, $id_genre, 'images/' . $filename]);
                }
            }

            $pdo->commit();
            header('Location: danh-sach-sach.php');
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Có lỗi xảy ra: ' . $e->getMessage();
        }
    }
}

$page_title = $edit_id ? 'Chỉnh sửa mục danh sách sách' : 'Thêm mục trong danh sách sách';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= esc($page_title) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    body {
        background: #1c1c1c;
        color: #fff;
        font-family: 'Be Vietnam Pro', sans-serif;
        margin: 0;
        padding: 32px 24px;
    }
    .debug-screen { max-width: 900px; margin: 0 auto; }
    h1 { font-size: 1.15rem; margin-bottom: 20px; }

    .form-errors {
        background: #3a1414;
        border: 1px solid #a33;
        padding: 10px 14px;
        margin-bottom: 16px;
        border-radius: 4px;
    }
    .form-errors p { margin: 4px 0; font-size: 0.9rem; }

    .ten-muc-box {
        display: inline-block;
        background: #ff8c1a;
        color: #1c1c1c;
        font-weight: 700;
        padding: 10px 24px;
        border-radius: 4px;
        margin-bottom: 24px;
    }
    .ten-muc-box span {
        display: block;
        font-size: 0.7rem;
        letter-spacing: 0.05em;
        margin-bottom: 4px;
    }
    .ten-muc-box input {
        border: none;
        background: transparent;
        font-size: 1rem;
        font-weight: 700;
        color: #1c1c1c;
        width: 100%;
        outline: none;
    }

    .section-note { color: #ccc; margin: 24px 0 10px; font-size: 0.9rem; }

    .cover-track {
        display: flex;
        gap: 12px;
        overflow-x: auto;
        padding-bottom: 8px;
    }
    .cover-slot { flex: 0 0 170px; }
    .cover-picker {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 170px;
        height: 240px;
        background: #000;
        border: 1px solid #333;
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }
    .cover-picker input[type="file"] {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
    }
    .cover-picker-text {
        color: #fff;
        font-weight: 600;
        text-align: center;
        padding: 0 12px;
        pointer-events: none;
    }
    .cover-preview {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
        pointer-events: none;
    }
    .cover-title-input {
        width: 100%;
        box-sizing: border-box;
        margin-top: 8px;
        padding: 6px 8px;
        border: 1px solid #333;
        background: #111;
        color: #fff;
        border-radius: 3px;
    }

    .cover-picker-existing { position: relative; }
    .cover-hover-label {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(0, 0, 0, 0.55);
        color: #fff;
        font-weight: 600;
        font-size: 0.8rem;
        text-align: center;
        padding: 0 10px;
        opacity: 0;
        transition: opacity .15s ease;
        pointer-events: none;
    }
    .cover-picker-existing:hover .cover-hover-label { opacity: 1; }

    .cover-delete {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-top: 6px;
        font-size: 0.75rem;
        color: #e88;
        cursor: pointer;
    }
    .cover-delete input { accent-color: #e33; }

    .btn-add-slot, .btn-submit, .btn-back {
        display: inline-block;
        margin-top: 20px;
        background: #ff8c1a;
        color: #1c1c1c;
        font-weight: 700;
        border: none;
        padding: 10px 22px;
        border-radius: 4px;
        cursor: pointer;
        text-decoration: none;
        font-size: 0.9rem;
    }
    .btn-add-slot { background: transparent; color: #ff8c1a; border: 1px solid #ff8c1a; margin-right: 12px; }
    .btn-back { display: block; margin-top: 28px; background: transparent; color: #ff8c1a; padding-left: 0; }
</style>
</head>
<body>
<div class="debug-screen">
    <h1><?= esc($page_title) ?></h1>

    <?php if ($errors): ?>
        <div class="form-errors">
            <?php foreach ($errors as $err): ?><p><?= esc($err) ?></p><?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <label class="ten-muc-box">
            <span>TÊN MỤC</span>
            <input type="text" name="ten_muc" value="<?= esc($existing_category['ten_category'] ?? '') ?>" required>
        </label>

        <?php if ($existing_covers): ?>
            <p class="section-note">Bìa hiện có trong mục (bấm vào ảnh để đổi ảnh khác, hoặc tick để xoá):</p>
            <div class="cover-track">
                <?php foreach ($existing_covers as $c): ?>
                    <div class="cover-slot">
                        <label class="cover-picker cover-picker-existing">
                            <input type="file" name="edit_anh_bia[<?= (int) $c['id_sach'] ?>]" accept="image/*" onchange="previewCover(this)">
                            <span class="cover-picker-text" style="display:none">chọn ảnh bìa</span>
                            <img class="cover-preview" src="<?= esc($c['anh_bia'] ?: 'images/no-cover.jpg') ?>" alt="<?= esc($c['ten_sach']) ?>" onerror="this.style.display='none'; this.previousElementSibling.style.display='block';">
                            <span class="cover-hover-label">Bấm để đổi ảnh</span>
                        </label>
                        <input type="text" name="edit_ten_sach[<?= (int) $c['id_sach'] ?>]" value="<?= esc($c['ten_sach']) ?>" class="cover-title-input">
                        <label class="cover-delete">
                            <input type="checkbox" name="edit_xoa[<?= (int) $c['id_sach'] ?>]" value="1">
                            Xoá bìa này
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <p class="section-note">Thêm bìa sách mới:</p>
        <div class="cover-track" id="coverTrack">
            <div class="cover-slot">
                <label class="cover-picker">
                    <input type="file" name="anh_bia[]" accept="image/*" onchange="previewCover(this)">
                    <span class="cover-picker-text">chọn ảnh bìa</span>
                    <img class="cover-preview" style="display:none">
                </label>
                <input type="text" name="ten_sach[]" placeholder="Tên sách" class="cover-title-input">
            </div>
        </div>

        <div>
            <button type="button" class="btn-add-slot" onclick="addCoverSlot()">+ Thêm ô ảnh bìa</button>
            <button type="submit" class="btn-submit"><?= $edit_id ? 'Lưu thay đổi' : 'Thêm mục mới' ?></button>
        </div>
    </form>

    <a class="btn-back" href="danh-sach-sach.php">&larr; Quay lại danh sách sách</a>
</div>

<script>
function previewCover(input) {
    const wrapper = input.closest('.cover-picker');
    const img = wrapper.querySelector('.cover-preview');
    const text = wrapper.querySelector('.cover-picker-text');
    if (input.files && input.files[0]) {
        img.src = URL.createObjectURL(input.files[0]);
        img.style.display = 'block';
        text.style.display = 'none';
    }
}

function addCoverSlot() {
    const track = document.getElementById('coverTrack');
    const slot = document.createElement('div');
    slot.className = 'cover-slot';
    slot.innerHTML = `
        <label class="cover-picker">
            <input type="file" name="anh_bia[]" accept="image/*" onchange="previewCover(this)">
            <span class="cover-picker-text">chọn ảnh bìa</span>
            <img class="cover-preview" style="display:none">
        </label>
        <input type="text" name="ten_sach[]" placeholder="Tên sách" class="cover-title-input">
    `;
    track.appendChild(slot);
}
</script>
</body>
</html>
