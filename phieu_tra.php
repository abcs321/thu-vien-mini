<?php

/* =========================================================
   0. SESSION (phải gọi TRƯỚC bất kỳ HTML/output nào, nếu không
      sẽ lỗi "Session cannot be started after headers have
      already been sent" khi include header.php ở phía dưới)
========================================================= */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* =========================================================
   1. KẾT NỐI DATABASE
========================================================= */

require_once __DIR__ . '/database.php';

/* =========================================================
   1.1. LẤY TÀI KHOẢN ĐANG ĐĂNG NHẬP
========================================================= */

if (empty($_SESSION['ten_tai_khoan'])) {
    header('Location: login.php');
    exit;
}

$tai_khoan = $_SESSION['ten_tai_khoan'];

$stmtUser = $conn->prepare("
    SELECT id_doc_gia, ho_ten, ten_tai_khoan
    FROM doc_gia
    WHERE ten_tai_khoan = :tai_khoan
    LIMIT 1
");
$stmtUser->execute([':tai_khoan' => $tai_khoan]);
$docGiaDangNhap = $stmtUser->fetch();

if (!$docGiaDangNhap) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

$ho_ten = $docGiaDangNhap['ho_ten'];


/* =========================================================
   2. CẤU HÌNH TRANG
========================================================= */

$pageTitle = 'Phiếu mượn';
$activeKey = 'borrow';
/*
 * QUAN TRỌNG: cấu trúc $nav dưới đây phải khớp CHÍNH XÁC với những gì
 * header.php thật (hàm render_header) mong đợi:
 *   - 'logo'  : chuỗi
 *   - 'links' : mảng các phần tử ['label' => .., 'href' => .., 'key' => ..]
 *   - 'login' : chuỗi (chỉ là nhãn hiển thị, không phải mảng/url)
 * Sai một khóa (vd 'items' thay vì 'links', 'url' thay vì 'href')
 * sẽ khiến header.php báo lỗi "Undefined array key" / TypeError trong esc().
 */
$nav = [
    'logo' => 'THƯ VIỆN',

    'links' => [
        [
            'label' => 'TRANG CHỦ',
            'href' => 'index.php',
            'key' => 'home'
        ],
        [
            'label' => 'VỀ CHÚNG TÔI',
            'href' => 've-chung-toi.php',
            'key' => 'about'
        ],
        [
            'label' => 'DANH SÁCH SÁCH',
            'href' => 'danh-sach-sach.php',
            'key' => 'books'
        ],
        [
            'label' => 'PHIẾU MƯỢN',
            'href' => 'phieu_muon.php',
            'key' => 'borrow'
        ],
        [
            'label' => 'KHÁM PHÁ',
            'href' => 'discover.php',
            'key' => 'explore'
        ],
        [
            'label' => 'LIÊN LẠC',
            'href' => 'contact.php',
            'key' => 'contact'
        ]
    ],

    'login' => 'Đăng nhập'
];
$message = '';
$messageType = '';

/* ID phiếu mượn vừa tạo thành công trong lần submit này
   (dùng để hiện nút "Xem phiếu đã xuất") */
$phieuVuaTaoId = null;

/* ID phiếu đang được chọn để trả */
$id_phieu_tra = (int)($_POST['id_phieu_tra'] ?? 0);

/* =========================================================
   2.1. XỬ LÝ KHI BẤM "XÁC NHẬN TRẢ"
========================================================= */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['submit_borrow'])
    && (int)($_POST['id_phieu_tra'] ?? 0) > 0
) {
    try {
        if ($id_phieu_tra <= 0) {
            throw new Exception('Vui lòng chọn một file phiếu mượn cũ trước khi trả.');
        }

        $conn->beginTransaction();

        $stmtReturn = $conn->prepare("
            SELECT
                pm.id_phieu_muon,
                pm.id_sach,
                pm.so_luong,
                pm.trang_thai,
                s.ten_sach
            FROM phieu_muon pm
            JOIN sach s ON s.id_sach = pm.id_sach
            WHERE pm.id_phieu_muon = :id_phieu_muon
              AND pm.id_doc_gia = :id_doc_gia
            LIMIT 1
            FOR UPDATE
        ");
        $stmtReturn->execute([
            ':id_phieu_muon' => $id_phieu_tra,
            ':id_doc_gia' => $docGiaDangNhap['id_doc_gia']
        ]);
        $phieuTra = $stmtReturn->fetch();

        if (!$phieuTra) {
            throw new Exception('Không tìm thấy phiếu mượn hoặc phiếu này không thuộc tài khoản đang đăng nhập.');
        }

        if ($phieuTra['trang_thai'] === 'Đã trả') {
            throw new Exception('Phiếu #' . $id_phieu_tra . ' đã được trả trước đó.');
        }

        $stmtUpdatePhieu = $conn->prepare("
            UPDATE phieu_muon
            SET trang_thai = 'Đã trả',
                ngay_tra_thuc_te = CURDATE()
            WHERE id_phieu_muon = :id_phieu_muon
              AND id_doc_gia = :id_doc_gia
        ");
        $stmtUpdatePhieu->execute([
            ':id_phieu_muon' => $id_phieu_tra,
            ':id_doc_gia' => $docGiaDangNhap['id_doc_gia']
        ]);

        $stmtUpdateSach = $conn->prepare("
            UPDATE sach
            SET so_luong_con_lai = COALESCE(so_luong_con_lai, 0) + :so_luong,
                sach_vat_ly = 'Còn sách'
            WHERE id_sach = :id_sach
        ");
        $stmtUpdateSach->execute([
            ':so_luong' => (int)$phieuTra['so_luong'],
            ':id_sach' => (int)$phieuTra['id_sach']
        ]);

        if ($stmtUpdateSach->rowCount() <= 0) {
            throw new Exception('Không thể cập nhật lại số lượng sách.');
        }

        $conn->commit();

        $message = 'Đã trả phiếu #' . $id_phieu_tra . ' - ' . $phieuTra['ten_sach'] . ' thành công.';
        $messageType = 'success';
        $id_phieu_tra = 0;

    } catch (Throwable $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log('[phieu_muon] Trả phiếu thất bại: ' . $e->getMessage());
        $message = $e->getMessage();
        $messageType = 'error';
    }
}

/* Giá trị giữ lại trên form */
$tai_khoan = $_SESSION['ten_tai_khoan'];
$ho_ten = $docGiaDangNhap['ho_ten'];

$id_sach = '';
$so_luong = 1;

$ngay_muon = '';
$ngay_hen_tra = '';

if ($ngay_muon === '') { $ngay_muon = date('Y-m-d'); }

$trang_thai = 'Đang mượn';


/* =========================================================
   3. XỬ LÝ KHI BẤM "TẠO PHIẾU MƯỢN"
========================================================= */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['submit_borrow'])
    && (int)($_POST['id_phieu_tra'] ?? 0) <= 0
) {

    /* -----------------------------------------
       Lấy dữ liệu từ form
    ----------------------------------------- */

    // Không nhận tài khoản/mật khẩu/họ tên từ form.
    // Tất cả lấy từ tài khoản đã đăng nhập.
    $tai_khoan = $_SESSION['ten_tai_khoan'];
    $ho_ten = $docGiaDangNhap['ho_ten'];

    $id_sach = (int)($_POST['id_sach'] ?? 0);
    $so_luong = (int)($_POST['so_luong'] ?? 0);

    $ngay_muon = $_POST['ngay_muon'] ?? '';
    $ngay_hen_tra = $_POST['ngay_hen_tra'] ?? '';

    $trang_thai = $_POST['trang_thai'] ?? 'Đang mượn';


    /* =====================================================
       3.1. KIỂM TRA DỮ LIỆU BẮT BUỘC
    ===================================================== */

    if ($id_sach <= 0) {

        $message = 'Vui lòng chọn sách.';
        $messageType = 'error';

    } elseif ($so_luong <= 0) {

        $message = 'Số lượng mượn phải lớn hơn 0.';
        $messageType = 'error';

    } elseif ($ngay_muon === '') {

        $message = 'Vui lòng chọn ngày mượn.';
        $messageType = 'error';

    } elseif ($ngay_hen_tra === '') {

        $message = 'Vui lòng chọn ngày hẹn trả.';
        $messageType = 'error';

    } elseif ($ngay_hen_tra < $ngay_muon) {

        $message = 'Ngày hẹn trả phải sau hoặc bằng ngày mượn.';
        $messageType = 'error';

    } else {

        try {

            /* =================================================
               3.2. DÙNG THÔNG TIN TÀI KHOẢN ĐANG ĐĂNG NHẬP
            ================================================= */

            $docGia = [
                'id' => $docGiaDangNhap['id_doc_gia'],
                'ho_ten' => $docGiaDangNhap['ho_ten'],
                'ten_dang_nhap' => $docGiaDangNhap['ten_tai_khoan']
            ];

                /* =============================================
                   3.3. KIỂM TRA SÁCH
                ============================================= */

                $sql = "
                    SELECT
                        id_sach AS id,
                        ten_sach,
                        so_luong_con_lai
                    FROM sach
                    WHERE id_sach = :id_sach
                    LIMIT 1
                ";

                $stmt = $conn->prepare($sql);

                $stmt->execute([
                    ':id_sach' => $id_sach
                ]);

                $sach = $stmt->fetch();


                /* ---------------------------------------------
                   Không tìm thấy sách
                --------------------------------------------- */

                if (!$sach) {

                    $message = 'Không tìm thấy sách.';
                    $messageType = 'error';

                }

                /* ---------------------------------------------
                   Không đủ số lượng
                --------------------------------------------- */

                elseif ((int)$sach['so_luong_con_lai'] < $so_luong) {

                    $message =
                        'Sách "' .
                        $sach['ten_sach'] .
                        '" chỉ còn ' .
                        (int)$sach['so_luong_con_lai'] .
                        ' quyển.';

                    $messageType = 'error';

                }

                /* =============================================
                   3.4. TẠO PHIẾU MƯỢN
                ============================================= */

                else {

                    try {

                        /* Bắt đầu transaction */
                        $conn->beginTransaction();


                        /* -------------------------------------
                           INSERT PHIẾU MƯỢN

                           LƯU Ý: tên cột phải khớp đúng bảng thật:
                             - id_doc_gia       (không phải doc_gia_id)
                             - ngay_tra_du_kien (không phải ngay_hen_tra)
                             - bảng phieu_muon thật không có cột ghi_chu
                        ------------------------------------- */

                        $sql = "
                            INSERT INTO phieu_muon
                            (
                                id_doc_gia,
                                id_sach,
                                so_luong,
                                ngay_muon,
                                ngay_tra_du_kien,
                                trang_thai
                            )
                            VALUES
                            (
                                :id_doc_gia,
                                :id_sach,
                                :so_luong,
                                :ngay_muon,
                                :ngay_tra_du_kien,
                                :trang_thai
                            )
                        ";

                        $stmt = $conn->prepare($sql);

                        $stmt->execute([
                            ':id_doc_gia' => $docGia['id'],
                            ':id_sach' => $id_sach,
                            ':so_luong' => $so_luong,
                            ':ngay_muon' => $ngay_muon,
                            ':ngay_tra_du_kien' => $ngay_hen_tra,
                            ':trang_thai' => $trang_thai
                        ]);


                        /* Lấy id phiếu vừa tạo NGAY sau khi INSERT,
                           trước khi chạy thêm câu lệnh nào khác */
                        $phieuVuaTaoId = (int)$conn->lastInsertId();


                        /* -------------------------------------
                           CẬP NHẬT SỐ LƯỢNG SÁCH
                        ------------------------------------- */

                        $sql = "
                            UPDATE sach
                            SET so_luong_con_lai = so_luong_con_lai - :so_luong_tru,
                                so_luot_muon = so_luot_muon + :so_luong_luot,
                                sach_vat_ly = CASE
                                    WHEN so_luong_con_lai - :so_luong_check <= 0 THEN 'Hết sách'
                                    ELSE 'Còn sách'
                                END
                            WHERE id_sach = :id_sach
                              AND so_luong_con_lai >= :so_luong_check
                        ";

                        $stmt = $conn->prepare($sql);

                        $stmt->execute([
                            ':so_luong_tru' => $so_luong,
                            ':so_luong_luot' => $so_luong,
                            ':so_luong_check' => $so_luong,
                            ':id_sach' => $id_sach
                        ]);


                        /* -------------------------------------
                           Kiểm tra UPDATE có thành công không
                        ------------------------------------- */

                        if ($stmt->rowCount() <= 0) {

                            throw new Exception(
                                'Không thể cập nhật số lượng sách.'
                            );
                        }


                        /* Hoàn tất */
                        $conn->commit();


                        /* -------------------------------------
                           THÔNG BÁO THÀNH CÔNG
                        ------------------------------------- */

                        $message =
                            'Tạo phiếu mượn thành công cho độc giả "' .
                            $docGia['ho_ten'] .
                            '".';

                        $messageType = 'success';


                        /* -------------------------------------
                           Reset một số dữ liệu form
                        ------------------------------------- */

                        $id_sach = '';
                        $so_luong = 1;
                        $ngay_muon = date('Y-m-d');
                        $ngay_hen_tra = '';
                        $trang_thai = 'Đang mượn';

                    } catch (Throwable $e) {

                        /* Nếu đang transaction thì rollback */
                        if ($conn->inTransaction()) {
                            $conn->rollBack();
                        }

                        /* Phiếu đã rollback -> không còn tồn tại,
                           không hiện nút xem phiếu cho id này nữa */
                        $phieuVuaTaoId = null;

                        /* Ghi log chi tiết lỗi phía server, không hiện cho người dùng */
                        error_log('[phieu_muon] Tạo phiếu mượn thất bại: ' . $e->getMessage());

                        $message = 'Không thể tạo phiếu mượn. Vui lòng thử lại sau.';
                        $messageType = 'error';
                    }
                }

        } catch (PDOException $e) {

            error_log('[phieu_muon] Lỗi CSDL: ' . $e->getMessage());

            $message = 'Có lỗi khi xử lý dữ liệu. Vui lòng thử lại sau.';
            $messageType = 'error';
        }
    }
}


/* =========================================================
   4. LẤY DANH SÁCH SÁCH CÒN TRONG KHO
========================================================= */

try {

    $sql = "
        SELECT
            id_sach AS id,
            ten_sach,
            so_luong_con_lai
        FROM sach
        ORDER BY ten_sach ASC
    ";

    $stmt = $conn->query($sql);

    $books = $stmt->fetchAll();

} catch (PDOException $e) {

    $books = [];

    if ($message === '') {

        $message =
            'Không thể lấy danh sách sách: ' .
            $e->getMessage();

        $messageType = 'error';
    }
}


/* =========================================================
   4.1. DANH SÁCH PHIẾU CŨ CỦA TÀI KHOẢN ĐANG ĐĂNG NHẬP
========================================================= */
$phieuCuList = [];
try {
    $stmtPhieuCu = $conn->prepare("
        SELECT
            pm.id_phieu_muon,
            pm.id_sach,
            pm.so_luong,
            pm.ngay_muon,
            pm.ngay_tra_du_kien,
            pm.ngay_tra_thuc_te,
            pm.trang_thai,
            s.ten_sach
        FROM phieu_muon pm
        JOIN sach s ON s.id_sach = pm.id_sach
        WHERE pm.id_doc_gia = :id_doc_gia
        ORDER BY pm.id_phieu_muon DESC
    ");
    $stmtPhieuCu->execute([
        ':id_doc_gia' => $docGiaDangNhap['id_doc_gia']
    ]);
    $phieuCuList = $stmtPhieuCu->fetchAll();
} catch (PDOException $e) {
    error_log('[phieu_muon] Không lấy được danh sách phiếu cũ: ' . $e->getMessage());
}


/* =========================================================
   5. MENU

   $nav đã được khai báo đúng cấu trúc mà header.php thật cần
   (logo / links / login) ở mục 2 ngay từ đầu, nên dùng thẳng
   $nav cho cả render_header() lẫn header dự phòng bên dưới,
   không cần tạo biến trung gian nữa.
========================================================= */

?>

<!DOCTYPE html>

<html lang="vi">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>
    </title>


    <style>

        /* =====================================================
           RESET
        ===================================================== */

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }


        /* =====================================================
           BODY
        ===================================================== */

        body {
            margin: 0;
            padding: 0;
            background: #e9ebee;
            color: #222;
            font-family: Arial, Helvetica, sans-serif;
        }


        /* =====================================================
           HEADER
        ===================================================== */

        .site-header {
            background: #fff;
            border-bottom: 3px solid #fa4b3e;
        }

        .site-header-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            gap: 28px;
            padding: 14px 16px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-right: auto;
        }

        .brand-mark {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: #fa4b3e;
            color: #fff;

            display: flex;
            align-items: center;
            justify-content: center;

            flex-shrink: 0;
            font-size: 16px;
        }

        .brand-name {
            font-weight: 900;
            font-size: 18px;
            color: #10121a;
        }

        .main-nav {
            display: flex;
            align-items: center;
            gap: 22px;
            flex-wrap: wrap;
        }

        .main-nav a {
            text-decoration: none;
            color: #10121a;
            font-size: 13px;
            font-weight: 700;
            white-space: nowrap;
        }

        .main-nav a.active {
            color: #fa4b3e;
        }

        .main-nav a:hover {
            color: #fa4b3e;
        }

        .btn-login {
            display: inline-flex;
            align-items: center;
            gap: 8px;

            background: #fa4b3e;
            color: #fff;

            text-decoration: none;

            font-size: 13px;
            font-weight: 700;

            padding: 10px 18px;
            border-radius: 6px;

            white-space: nowrap;
        }

        .btn-login:hover {
            background: #e03a2d;
        }


        /* =====================================================
           BANNER
        ===================================================== */

        .banner {
            width: 100%;
            height: 190px;

            position: relative;

            background-image:
                url("https://images.unsplash.com/photo-1507842217343-583bb7270b66");

            background-size: cover;
            background-position: center 45%;

            display: flex;
            align-items: center;
            justify-content: center;
        }

        .banner-overlay {
            position: absolute;
            inset: 0;

            background: rgba(0, 0, 0, 0.65);
        }

        .banner h1 {
            position: relative;
            z-index: 2;

            color: #fa4b3e;

            font-size: 30px;
            font-weight: 800;

            text-transform: uppercase;
        }


        /* =====================================================
           FORM CONTAINER
        ===================================================== */

        .form-container {
            width: calc(100% - 24px);

            margin: 12px auto;

            padding: 30px 35px;

            background: #e1e1e1;

            min-height: 500px;
        }


        /* =====================================================
           THÔNG BÁO
        ===================================================== */

        .message {
            width: 100%;

            padding: 14px 18px;

            margin-bottom: 25px;

            font-size: 14px;
            font-weight: 600;

            border-radius: 4px;
        }

        .message.success {
            background: #d9f5df;
            color: #176b2c;
            border: 1px solid #9ed7aa;
        }

        .message.error {
            background: #ffe0e0;
            color: #a40000;
            border: 1px solid #ffaaaa;
        }


        /* =====================================================
           ROW
        ===================================================== */

        .row {
            display: flex;
            gap: 30px;
            width: 100%;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .name {
            flex: 3;
        }

        .birthday {
            flex: 2;
        }


        /* =====================================================
           LABEL
        ===================================================== */

        .form-group label,
        .date-box label {
            display: block;

            margin-bottom: 9px;

            font-size: 14px;
            font-weight: bold;
        }


        /* =====================================================
           INPUT
        ===================================================== */

        .form-group input,
        .form-group select,
        .date-box input {

            width: 100%;

            height: 48px;

            padding: 10px 14px;

            border: 1px solid #bbb;

            background: #fff;

            font-size: 14px;

            outline: none;
        }

        .form-group input:focus,
        .form-group select:focus,
        .date-box input:focus {

            border-color: #fa4b3e;
        }


        /* =====================================================
           ADDRESS
        ===================================================== */

        .address {
            display: flex;
            gap: 30px;
        }

        .address input {
            height: 48px;

            padding: 10px 14px;

            border: 1px solid #bbb;

            background: #fff;

            font-size: 14px;

            outline: none;
        }

        .address input:nth-child(1) {
            width: 20%;
        }

        .address input:nth-child(2) {
            width: 25%;
        }

        .address input:nth-child(3) {
            width: 55%;
        }


        /* =====================================================
           ĐƯỜNG KẺ
        ===================================================== */

        hr {
            border: none;

            border-top: 1px solid #aaa;

            margin: 15px 0 28px;
        }


        /* =====================================================
           THÔNG TIN MƯỢN
        ===================================================== */

        .payment {
            position: relative;
        }

        .payment h2 {
            font-size: 15px;

            margin-bottom: 18px;
        }


        /* =====================================================
           SELECT SÁCH
        ===================================================== */

        .book-select {
            width: 100%;

            height: 48px;

            padding: 0 14px;

            border: 1px solid #bbb;

            background: #fff;

            font-size: 14px;

            outline: none;
        }


        /* =====================================================
           NGÀY MƯỢN / NGÀY TRẢ
        ===================================================== */

        .date-row {
            display: flex;

            gap: 30px;

            width: 100%;

            margin-bottom: 24px;
        }

        .date-box {
            width: 50%;
        }

        .date-box input {
            width: 100%;
        }


        /* =====================================================
           CHỌN PHIẾU TRẢ
        ===================================================== */
        .return-box {
            margin: 26px 0;
            padding: 18px;
            border: 1px solid #bbb;
            background: #fafafa;
        }

        .return-box h3 {
            margin: 0 0 12px;
            font-size: 15px;
        }

        .return-file-name {
            margin-top: 10px;
            font-size: 13px;
            color: #555;
        }

        .return-button {
            width: 150px;
            height: 42px;
            padding: 0;
            background: #ff3b3b;
            color: white;
            border: none;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
        }

        .return-button:hover {
            background: #d90000;
        }

        .return-button:disabled {
            opacity: .5;
            cursor: not-allowed;
        }

        .return-confirm-mode {
            font-weight: bold;
        }


        /* =====================================================
           BUTTON
        ===================================================== */

        .button-area {
            display: flex;

            justify-content: flex-end;

            gap: 10px;

            margin-top: 10px;
        }

        .borrow-button {
            width: 150px;

            height: 42px;

            background: #ff3b3b;

            color: white;

            border: none;

            font-size: 12px;

            font-weight: bold;

            cursor: pointer;
        }

        .borrow-button:hover {
            background: #d90000;
        }

        .view-receipt-button {
            width: 170px;

            height: 42px;

            background: #ffffff;

            color: #333;

            border: 2px solid #333;

            font-size: 12px;

            font-weight: bold;

            cursor: pointer;

            display: flex;

            align-items: center;

            justify-content: center;

            text-decoration: none;
        }

        .view-receipt-button:hover {
            background: #333;

            color: #fff;
        }

        .view-receipt-button.disabled {
            border-color: #bbb;

            color: #bbb;

            cursor: not-allowed;

            pointer-events: none;
        }


        /* =====================================================
           FOOTER
        ===================================================== */

        .footer-black {
            background: #0c0c0c;

            color: #fff;

            margin-top: 8px;
        }


        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 900px) {

            .site-header-inner {
                flex-wrap: wrap;
            }

            .main-nav {
                order: 3;

                width: 100%;

                gap: 16px;

                justify-content: center;

                padding-top: 6px;
            }

            .row {
                flex-direction: column;

                gap: 0;
            }

            .name,
            .birthday {
                width: 100%;
            }

            .date-row {
                flex-direction: column;

                gap: 0;
            }

            .date-box {
                width: 100%;
            }

        }


        @media (max-width: 600px) {

            .banner {
                height: 150px;
            }

            .banner h1 {
                font-size: 24px;
            }

            .form-container {
                width: calc(100% - 20px);

                padding: 20px;
            }

            .address {
                flex-direction: column;

                gap: 10px;
            }

            .address input:nth-child(1),
            .address input:nth-child(2),
            .address input:nth-child(3) {
                width: 100%;
            }

            .main-nav {
                gap: 12px;
            }

        }

    </style>

</head>


<body>


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <?php

    /*
     * Nếu header.php của bạn có sẵn hàm render_header()
     * thì dùng header cũ của bạn.
     */

    if (file_exists(__DIR__ . '/header.php')) {

        include __DIR__ . '/header.php';

        if (function_exists('render_header')) {

            render_header($nav, $activeKey);

        }

    } else {

    ?>

        <header class="site-header">

            <div class="site-header-inner">

                <div class="brand">

                    <div class="brand-mark">
                        🔍
                    </div>

                    <div class="brand-name">
                        <?= htmlspecialchars($nav['logo'], ENT_QUOTES, 'UTF-8') ?>
                    </div>

                </div>


                <nav class="main-nav">

                    <?php foreach ($nav['links'] as $link): ?>

                        <a
                            href="<?= htmlspecialchars($link['href'], ENT_QUOTES, 'UTF-8') ?>"
                            class="<?= $link['key'] === $activeKey ? 'active' : '' ?>"
                        >
                            <?= htmlspecialchars($link['label'], ENT_QUOTES, 'UTF-8') ?>
                        </a>

                    <?php endforeach; ?>

                </nav>


                <a href="#" class="btn-login">
                    👤 <?= htmlspecialchars($nav['login'], ENT_QUOTES, 'UTF-8') ?>
                </a>

            </div>

        </header>

    <?php

    }

    ?>


    <!-- =====================================================
         BANNER
    ====================================================== -->

    <section class="banner">

        <div class="banner-overlay"></div>

        <h1>
            <?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>
        </h1>

    </section>


    <!-- =====================================================
         FORM PHIẾU MƯỢN
    ====================================================== -->

    <form
        method="POST"
        action=""
        class="form-container"
    >


        <!-- =================================================
             THÔNG BÁO
        ================================================== -->

        <?php if ($message !== ''): ?>

            <div class="message <?= htmlspecialchars($messageType, ENT_QUOTES, 'UTF-8') ?>">

                <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>

            </div>

        <?php endif; ?>


        <!-- =================================================
             TÀI KHOẢN ĐANG ĐĂNG NHẬP
        ================================================== -->

        <div class="row">

            <div class="form-group name">

                <label for="tai_khoan">
                    TÀI KHOẢN
                </label>
                <input
                    type="text"
                    id="tai_khoan"
                    name="tai_khoan"
                    value="<?= htmlspecialchars($tai_khoan, ENT_QUOTES, 'UTF-8') ?>"
                    readonly
                >

            </div>
        </div>

        <!-- =================================================
             HỌ TÊN
        ================================================== -->

        <div class="row">

            <div class="form-group name">

                <label for="ho_ten">
                    HỌ VÀ TÊN
                </label>
                <input
                    type="text"
                    id="ho_ten"
                    name="ho_ten"
                    value="<?= htmlspecialchars($ho_ten, ENT_QUOTES, 'UTF-8') ?>"
                    readonly
                >

            </div>



        </div>



    

        <!-- =================================================
             ĐƯỜNG KẺ
        ================================================== -->

        <hr>


        <!-- =================================================
             THÔNG TIN MƯỢN SÁCH
        ================================================== -->

        <section class="payment">

            <h2>
                THÔNG TIN MƯỢN SÁCH
            </h2>


            <!-- =============================================
                 CHỌN SÁCH
            ============================================== -->

            <div class="form-group">

                <label for="id_sach">
                    CHỌN SÁCH
                </label>


                <select
                    id="id_sach"
                    name="id_sach"
                    class="book-select"
                    required
                >

                    <option value="">
                        -- Chọn sách --
                    </option>


                    <?php foreach ($books as $book): ?>

                        <option
                            value="<?= (int)$book['id'] ?>"
                            <?= ((string)$id_sach === (string)$book['id']) ? 'selected' : '' ?>
                        >

                            <?= htmlspecialchars(
                                $book['ten_sach'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                            - Còn
                            <?= (int)$book['so_luong_con_lai'] ?>
                            quyển

                        </option>

                    <?php endforeach; ?>


                </select>

            </div>


            <!-- =============================================
                 SỐ LƯỢNG
            ============================================== -->

            <div class="form-group">

                <label for="so_luong">
                    SỐ LƯỢNG
                </label>


                <input
                    type="number"
                    id="so_luong"
                    name="so_luong"
                    min="1"
                    value="<?= (int)$so_luong ?>"
                    required
                >

            </div>


            <!-- =============================================
                 NGÀY MƯỢN + NGÀY HẸN TRẢ
            ============================================== -->

            <div class="date-row">


                <div class="date-box">

                    <label for="ngay_muon">
                        NGÀY MƯỢN
                    </label>

                    <input
                        type="date"
                        id="ngay_muon"
                        name="ngay_muon"
                        value="<?= htmlspecialchars($ngay_muon, ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >

                </div>


                <div class="date-box">

                    <label for="ngay_hen_tra">
                        NGÀY HẸN TRẢ
                    </label>

                    <input
                        type="date"
                        id="ngay_hen_tra"
                        name="ngay_hen_tra"
                        value="<?= htmlspecialchars($ngay_hen_tra, ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >

                </div>


            </div>


            <!-- =============================================
                 TRẠNG THÁI
            ============================================== -->

            <div class="form-group">

                <label for="trang_thai">
                    TRẠNG THÁI
                </label>


                <select
                    id="trang_thai"
                    name="trang_thai"
                    class="book-select"
                >

                    <option
                        value="Đang mượn"
                        <?= $trang_thai === 'Đang mượn' ? 'selected' : '' ?>
                    >
                        Đang mượn
                    </option>


                    <option
                        value="Đã trả"
                        <?= $trang_thai === 'Đã trả' ? 'selected' : '' ?>
                    >
                        Đã trả
                    </option>

                </select>

            </div>


            <!-- =============================================
                 CÁC NÚT CHỨC NĂNG
                 TRẢ PHIẾU dùng để chọn file TXT.
                 Sau khi chọn file, nút TẠO PHIẾU MƯỢN sẽ đổi thành
                 XÁC NHẬN TRẢ và dùng chính nút đó để xác nhận.
            ============================================== -->
            <div class="button-area" id="buttonArea">

                <button
                    type="submit"
                    name="submit_borrow"
                    class="borrow-button"
                    id="btnTaoPhieu"
                >
                    TẠO PHIẾU MƯỢN
                </button>

                <button
                    type="button"
                    class="return-button"
                    id="btnTraPhieu"
                    onclick="document.getElementById('filePhieuTra').click();"
                >
                    TRẢ PHIẾU
                </button>

                <input
                    type="file"
                    id="filePhieuTra"
                    accept=".txt,text/plain"
                    style="display:none;"
                >

                <input
                    type="hidden"
                    name="id_phieu_tra"
                    id="id_phieu_tra"
                    value=""
                >

                <?php if ($phieuVuaTaoId !== null && $phieuVuaTaoId > 0): ?>

                    <a
                        href="xuat_phieu.php?id=<?= (int)$phieuVuaTaoId ?>"
                        target="_blank"
                        class="view-receipt-button"
                    >
                        XEM PHIẾU ĐÃ XUẤT
                    </a>

                <?php else: ?>

                    <span class="view-receipt-button disabled">
                        XEM PHIẾU ĐÃ XUẤT
                    </span>

                <?php endif; ?>

            </div>


        </section>


    </form>


    <!-- =====================================================
         FOOTER
    ====================================================== -->

    <div class="footer-black">

        <?php

        if (file_exists(__DIR__ . '/footer.php')) {

            include __DIR__ . '/footer.php';

        }

        ?>

    </div>


    <!-- =====================================================
         JAVASCRIPT
    ====================================================== -->

    <script>

        /*
         * Không cho ngày hẹn trả nhỏ hơn ngày mượn
         */

        const ngayMuon =
            document.getElementById('ngay_muon');

        const ngayHenTra =
            document.getElementById('ngay_hen_tra');


        if (ngayMuon && ngayHenTra) {

            ngayMuon.addEventListener('change', function () {

                ngayHenTra.min = this.value;

                if (
                    ngayHenTra.value &&
                    ngayHenTra.value < this.value
                ) {

                    ngayHenTra.value = '';

                }

            });

        }


        /*
         * Khi chọn sách, số lượng mặc định là 1
         */

        const selectSach =
            document.getElementById('id_sach');

        const soLuong =
            document.getElementById('so_luong');


        if (selectSach && soLuong) {

            selectSach.addEventListener('change', function () {

                if (this.value !== '') {

                    soLuong.value = 1;

                }

            });

        }


        /* =====================================================
           TRẢ PHIẾU BẰNG FILE .TXT
           - Nút TRẢ PHIẾU mở trình chọn file
           - Đọc Mã phiếu từ file TXT
           - Lấy lại dữ liệu từ xuat_phieu.php
           - Tự điền form
           - Dùng chính nút TẠO PHIẾU MƯỢN để xác nhận trả
        ===================================================== */
        const filePhieuTra = document.getElementById('filePhieuTra');
        const idPhieuTraInput = document.getElementById('id_phieu_tra');
        const tenFilePhieu = document.getElementById('tenFilePhieu');
        const btnTaoPhieu = document.getElementById('btnTaoPhieu');

        function layGiaTri(text, nhan) {
            const regex = new RegExp('^\\s*' + nhan + '\\s*:\\s*(.*)$', 'mi');
            const match = text.match(regex);
            return match ? match[1].trim() : '';
        }

        function chuyenNgayVN(value) {
            const m = value.match(/(\d{1,2})\/(\d{1,2})\/(\d{4})/);
            if (!m) return '';
            return m[3] + '-' + String(m[2]).padStart(2, '0') + '-' + String(m[1]).padStart(2, '0');
        }

        async function docFilePhieuTra(file) {
            if (!file) return;

            if (tenFilePhieu) tenFilePhieu.textContent = 'Đang đọc: ' + file.name;
            idPhieuTraInput.value = '';

            try {
                const text = await file.text();
                const maPhieuText = layGiaTri(text, 'Mã phiếu').replace('#', '').trim();
                const maPhieu = parseInt(maPhieuText, 10);

                if (!maPhieu || maPhieu <= 0) {
                    throw new Error('Không tìm thấy Mã phiếu trong file TXT.');
                }

                const response = await fetch('xuat_phieu.php?id=' + encodeURIComponent(maPhieu), {
                    cache: 'no-store'
                });

                if (!response.ok) {
                    throw new Error('Không thể đọc phiếu #' + maPhieu + ' từ hệ thống.');
                }

                const serverText = await response.text();
                const maServer = parseInt(
                    layGiaTri(serverText, 'Mã phiếu').replace('#', '').trim(),
                    10
                );

                if (!maServer || maServer !== maPhieu) {
                    throw new Error('Mã phiếu #' + maPhieu + ' không tồn tại trong hệ thống.');
                }

                const tenSach = layGiaTri(serverText, 'Tên sách');
                const soLuongText = layGiaTri(serverText, 'Số lượng');
                const ngayMuonText = layGiaTri(serverText, 'Ngày mượn');
                const ngayHenTraText = layGiaTri(serverText, 'Ngày hẹn trả');
                const trangThai = layGiaTri(serverText, 'Trạng thái');

                const selectSach = document.getElementById('id_sach');
                let found = false;

                if (selectSach) {
                    for (const option of selectSach.options) {
                        const tenOption = option.textContent
                            .trim()
                            .replace(/\s*\(còn \d+\)$/, '')
                            .replace(/\s*\(Hết sách\)$/, '');

                        if (tenOption === tenSach.trim()) {
                            selectSach.value = option.value;
                            found = true;
                            break;
                        }
                    }
                }

                if (!found) {
                    throw new Error('Không tìm thấy sách "' + tenSach + '" trong danh sách sách.');
                }

                document.getElementById('so_luong').value =
                    parseInt(soLuongText, 10) || 1;

                document.getElementById('ngay_muon').value =
                    chuyenNgayVN(ngayMuonText);

                document.getElementById('ngay_hen_tra').value =
                    chuyenNgayVN(ngayHenTraText);

                document.getElementById('trang_thai').value =
                    trangThai || 'Đang mượn';

                idPhieuTraInput.value = maPhieu;

                // Dùng chính nút TẠO PHIẾU MƯỢN để xác nhận trả.
                btnTaoPhieu.textContent = 'XÁC NHẬN TRẢ';
                btnTaoPhieu.classList.add('return-confirm-mode');

                if (tenFilePhieu) tenFilePhieu.textContent =
                    'Đã chọn: ' + file.name + ' — Phiếu #' + maPhieu + ' — ' + tenSach;

                if (trangThai === 'Đã trả') {
                    if (tenFilePhieu) tenFilePhieu.textContent += ' — PHIẾU ĐÃ TRẢ';
                    btnTaoPhieu.disabled = true;
                    alert('Phiếu #' + maPhieu + ' đã được trả trước đó.');
                } else {
                    btnTaoPhieu.disabled = false;
                    alert('Đã đọc phiếu #' + maPhieu + '. Bấm XÁC NHẬN TRẢ để hoàn tất.');
                }

                if (typeof capNhatThanhTien === 'function') {
                    capNhatThanhTien();
                }

            } catch (error) {
                console.error(error);
                idPhieuTraInput.value = '';
                btnTaoPhieu.disabled = false;
                btnTaoPhieu.textContent = 'TẠO PHIẾU MƯỢN';
                btnTaoPhieu.classList.remove('return-confirm-mode');
                if (tenFilePhieu) tenFilePhieu.textContent = 'Lỗi: ' + error.message;
                alert(error.message);
            }
        }

        if (filePhieuTra) {
            filePhieuTra.addEventListener('change', function () {
                docFilePhieuTra(this.files[0]);
            });
        }

        // Nếu đang ở chế độ trả thì hỏi lại trước khi gửi POST.
        if (btnTaoPhieu) {
            btnTaoPhieu.addEventListener('click', function (event) {
                const id = parseInt(idPhieuTraInput.value, 10);

                if (id > 0) {
                    const ok = confirm(
                        'Xác nhận trả phiếu #' + id + '?\n\n' +
                        'Hệ thống sẽ chuyển trạng thái thành Đã trả, ghi ngày trả thực tế và cộng lại số lượng sách.'
                    );

                    if (!ok) {
                        event.preventDefault();
                    }
                }
            });
        }

    </script>


</body>

</html>