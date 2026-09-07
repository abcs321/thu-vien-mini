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

// Nạp dữ liệu mục (thể loại/tag) đang chỉnh sửa (nếu có id trên URL)
// LƯU Ý: $edit_id giờ là id_genre (khớp với danh-sach-sach.php dùng id_genre làm id mục),
// không còn là id_category của bảng categories cũ nữa.
$existing_genre  = null;
$existing_covers = [];

if ($edit_id) {
    $stmt = $pdo->prepare('SELECT id_genre, ten_genre FROM genres WHERE id_genre = ?');
    $stmt->execute([$edit_id]);
    $existing_genre = $stmt->fetch();

    if ($existing_genre) {
        $coverStmt = $pdo->prepare(
            'SELECT id_sach, anh_bia, ten_sach
             FROM sach
             WHERE id_genre = ?
             ORDER BY ngay_them DESC'
        );
        $coverStmt->execute([$edit_id]);
        $existing_covers = $coverStmt->fetchAll();
    }
}

// Xử lý xoá cả mục (genre) — kèm xoá luôn sách và ảnh bìa bên trong mục đó
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'xoa_muc' && $edit_id && $existing_genre) {
    $pdo->beginTransaction();
    try {
        // Xoá file ảnh bìa của từng sách trong mục trước khi xoá dữ liệu
        foreach ($existing_covers as $c) {
            if ($c['anh_bia'] && str_starts_with($c['anh_bia'], 'images/')) {
                $old_path = __DIR__ . '/' . $c['anh_bia'];
                if (is_file($old_path)) {
                    unlink($old_path);
                }
            }
        }

        $delSach = $pdo->prepare('DELETE FROM sach WHERE id_genre = ?');
        $delSach->execute([$edit_id]);

        $delGenre = $pdo->prepare('DELETE FROM genres WHERE id_genre = ?');
        $delGenre->execute([$edit_id]);

        $pdo->commit();
        header('Location: danh-sach-sach.php');
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $errors[] = 'Không xoá được mục: ' . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') !== 'xoa_muc') {
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
            if ($edit_id && $existing_genre) {
                // Cập nhật tên mục (genre) đã có
                $upd = $pdo->prepare('UPDATE genres SET ten_genre = ? WHERE id_genre = ?');
                $upd->execute([$ten_muc, $edit_id]);
                $id_genre = $edit_id;
            } else {
                // Tạo mục (genre) mới
                $insGenre = $pdo->prepare('INSERT INTO genres (ten_genre) VALUES (?)');
                $insGenre->execute([$ten_muc]);
                $id_genre = $pdo->lastInsertId();
            }

            // Cập nhật / xoá các bìa đã có ngay trên form
            if ($edit_id && $existing_genre && $existing_covers) {
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

    .btn-danger {
        display: inline-block;
        background: #c0392b;
        color: #fff;
        font-weight: 700;
        border: none;
        padding: 8px 18px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 0.85rem;
    }
    .btn-danger:hover { background: #e74c3c; }
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
            <input type="text" name="ten_muc" value="<?= esc($existing_genre['ten_genre'] ?? '') ?>" required>
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

    <?php if ($edit_id && $existing_genre): ?>
        <form method="post" onsubmit="return confirm('Xoá mục &quot;<?= esc(addslashes($existing_genre['ten_genre'])) ?>&quot; và toàn bộ sách bên trong? Hành động này không thể hoàn tác.');" style="margin-top: 12px;">
            <input type="hidden" name="action" value="xoa_muc">
            <button type="submit" class="btn-danger">Xoá mục này</button>
        </form>
    <?php endif; ?>
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