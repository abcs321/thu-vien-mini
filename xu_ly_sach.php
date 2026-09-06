<?php
/**
 * xu_ly_sach.php
 * Nhận request AJAX (FormData) từ modal Thêm/Sửa sách trong discover.php.
 * action = "add"  -> thêm sách mới
 * action = "edit" -> cập nhật sách theo id_sach
 *
 * Trả về JSON: {"success": true/false, "message": "..."}
 */

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. CHỈ CHO PHÉP ADMIN
// TODO: đổi điều kiện này cho khớp với hệ thống đăng nhập thực tế của nhóm bạn
$isAdmin = isset($_SESSION['vai_tro']) && $_SESSION['vai_tro'] === 'admin';
if (!$isAdmin) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền thực hiện thao tác này.']);
    exit;
}

// 2. KẾT NỐI CSDL
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "thu_vien_mini";

$conn = @new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Lỗi kết nối CSDL: ' . $conn->connect_error]);
    exit;
}
$conn->set_charset("utf8mb4");

// 3. LẤY DỮ LIỆU TỪ FORM
$action        = $_POST['action'] ?? 'add';
$id_sach       = isset($_POST['id_sach']) ? (int)$_POST['id_sach'] : 0;
$ten_sach      = trim($_POST['ten_sach'] ?? '');
$id_genre      = !empty($_POST['id_genre']) ? (int)$_POST['id_genre'] : null;
$id_tac_gia_raw = $_POST['id_tac_gia'] ?? '';
$tac_gia_moi   = trim($_POST['tac_gia_moi'] ?? '');
$tinh_trang    = $_POST['tinh_trang'] ?? 'Có sẵn';
$sach_vat_ly   = $_POST['sach_vat_ly'] ?? 'Còn sách';
$so_luong_con_lai = isset($_POST['so_luong_con_lai']) ? max(0, (int)$_POST['so_luong_con_lai']) : 0;
$so_luot_muon  = isset($_POST['so_luot_muon']) ? (int)$_POST['so_luot_muon'] : 0;
$phim_ct       = $_POST['phim_chuyen_the'] ?? 'Không';
$anh_bia_cu    = $_POST['anh_bia_hien_tai'] ?? '';

if ($action !== 'delete' && $ten_sach === '') {
    echo json_encode(['success' => false, 'message' => 'Tên sách không được để trống.']);
    exit;
}

// 3b. XỬ LÝ "+ THÊM TÁC GIẢ MỚI"
// Nếu admin chọn thêm tác giả mới ngay trong form, kiểm tra xem tên đó
// đã tồn tại trong bảng tac_gia chưa (tránh trùng lặp); nếu chưa có thì tạo mới.
if ($id_tac_gia_raw === '__new__') {
    if ($tac_gia_moi === '') {
        echo json_encode(['success' => false, 'message' => 'Vui lòng nhập tên tác giả mới.']);
        exit;
    }

    $stmtFind = $conn->prepare("SELECT id_tac_gia FROM tac_gia WHERE ten_tac_gia = ? LIMIT 1");
    $stmtFind->bind_param("s", $tac_gia_moi);
    $stmtFind->execute();
    $stmtFind->bind_result($foundId);

    if ($stmtFind->fetch()) {
        $id_tac_gia = $foundId;
        $stmtFind->close();
    } else {
        $stmtFind->close();
        $stmtInsertTG = $conn->prepare("INSERT INTO tac_gia (ten_tac_gia) VALUES (?)");
        $stmtInsertTG->bind_param("s", $tac_gia_moi);
        if (!$stmtInsertTG->execute()) {
            echo json_encode(['success' => false, 'message' => 'Không thể tạo tác giả mới: ' . $stmtInsertTG->error]);
            exit;
        }
        $id_tac_gia = $stmtInsertTG->insert_id;
        $stmtInsertTG->close();
    }
} else {
    $id_tac_gia = !empty($id_tac_gia_raw) ? (int)$id_tac_gia_raw : null;
}

// 4. XỬ LÝ UPLOAD ẢNH BÌA (nếu admin có chọn file mới)
$anh_bia = $anh_bia_cu; // mặc định giữ ảnh cũ (khi sửa mà không đổi ảnh)

if (isset($_FILES['anh_bia_file']) && $_FILES['anh_bia_file']['error'] === UPLOAD_ERR_OK) {
    $allowedExt = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $tmpName = $_FILES['anh_bia_file']['tmp_name'];
    $originalName = $_FILES['anh_bia_file']['name'];
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    if (!in_array($ext, $allowedExt)) {
        echo json_encode(['success' => false, 'message' => 'Định dạng ảnh không được hỗ trợ. Chỉ nhận JPG, PNG, WEBP, GIF.']);
        exit;
    }

    $uploadDir = __DIR__ . '/uploads/covers/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $newFileName = 'bia_' . uniqid() . '.' . $ext;
    $destPath = $uploadDir . $newFileName;

    if (!move_uploaded_file($tmpName, $destPath)) {
        echo json_encode(['success' => false, 'message' => 'Không thể lưu ảnh bìa lên máy chủ.']);
        exit;
    }

    // Đường dẫn tương đối để lưu vào CSDL và dùng trong thẻ <img>
    $anh_bia = 'uploads/covers/' . $newFileName;
}

// 5. THÊM MỚI
if ($action === 'add') {
    $stmt = $conn->prepare(
        "INSERT INTO sach (ten_sach, id_genre, id_tac_gia, anh_bia, tinh_trang, sach_vat_ly, so_luong_con_lai, so_luot_muon, phim_chuyen_the)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param(
        "siisssiis",
        $ten_sach, $id_genre, $id_tac_gia, $anh_bia, $tinh_trang, $sach_vat_ly, $so_luong_con_lai, $so_luot_muon, $phim_ct
    );
    // Thứ tự tham số: s(ten_sach) i(id_genre) i(id_tac_gia) s(anh_bia) s(tinh_trang) s(sach_vat_ly) i(so_luong_con_lai) i(so_luot_muon) s(phim_ct)

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Đã thêm sách mới.', 'id_sach' => $stmt->insert_id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi khi thêm sách: ' . $stmt->error]);
    }
    $stmt->close();

// 6. CẬP NHẬT (SỬA)
} elseif ($action === 'edit') {
    if ($id_sach <= 0) {
        echo json_encode(['success' => false, 'message' => 'Thiếu id_sach để cập nhật.']);
        exit;
    }

    $stmt = $conn->prepare(
        "UPDATE sach SET
            ten_sach = ?,
            id_genre = ?,
            id_tac_gia = ?,
            anh_bia = ?,
            tinh_trang = ?,
            sach_vat_ly = ?,
            so_luong_con_lai = ?,
            phim_chuyen_the = ?,
            so_luot_muon = ?
         WHERE id_sach = ?"
    );
    $stmt->bind_param(
        "siisssisii",
        $ten_sach, $id_genre, $id_tac_gia, $anh_bia, $tinh_trang, $sach_vat_ly, $so_luong_con_lai, $phim_ct, $so_luot_muon, $id_sach
    );

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Đã cập nhật sách.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi khi cập nhật sách: ' . $stmt->error]);
    }
    $stmt->close();

// 7. XÓA SÁCH
} elseif ($action === 'delete') {
    if ($id_sach <= 0) {
        echo json_encode(['success' => false, 'message' => 'Thiếu id_sach để xóa.']);
        exit;
    }

    // Lấy đường dẫn ảnh bìa hiện tại để xóa file khỏi server sau khi xóa bản ghi (nếu có)
    $anh_bia_dang_xoa = null;
    $stmtGet = $conn->prepare("SELECT anh_bia FROM sach WHERE id_sach = ?");
    $stmtGet->bind_param("i", $id_sach);
    $stmtGet->execute();
    $stmtGet->bind_result($anh_bia_dang_xoa);
    $stmtGet->fetch();
    $stmtGet->close();

    // Lưu ý: bảng phieu_muon có khóa ngoại ON DELETE CASCADE tới sach,
    // nên xóa sách ở đây sẽ xóa luôn mọi phiếu mượn/trả liên quan tới sách đó.
    $stmt = $conn->prepare("DELETE FROM sach WHERE id_sach = ?");
    $stmt->bind_param("i", $id_sach);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            // Xóa file ảnh bìa vật lý trên server (nếu là ảnh do người dùng upload, không phải link ngoài)
            if ($anh_bia_dang_xoa && strpos($anh_bia_dang_xoa, 'uploads/covers/') === 0) {
                $duongDanFile = __DIR__ . '/' . $anh_bia_dang_xoa;
                if (is_file($duongDanFile)) {
                    @unlink($duongDanFile);
                }
            }
            echo json_encode(['success' => true, 'message' => 'Đã xóa sách.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy sách để xóa (có thể đã bị xóa trước đó).']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi khi xóa sách: ' . $stmt->error]);
    }
    $stmt->close();

} else {
    echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ.']);
}

$conn->close();
