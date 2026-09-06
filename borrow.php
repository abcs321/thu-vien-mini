<?php

/* =========================================================
   0. SESSION
========================================================= */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* =========================================================
   1. KẾT NỐI DATABASE
========================================================= */
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/includes.php';

/* =========================================================
   2. CẤU HÌNH TRANG
========================================================= */
$pageTitle = 'Phiếu mượn';
$activeKey = 'borrow';

$nav = [
    'logo' => 'THƯ VIỆN',
    'links' => [
        ['label' => 'TRANG CHỦ', 'href' => 'index.php', 'key' => 'home'],
        ['label' => 'VỀ CHÚNG TÔI', 'href' => 'aboutus.php', 'key' => 'about'],
        ['label' => 'DANH SÁCH SÁCH', 'href' => 'danh-sach-sach.php', 'key' => 'books'],
        ['label' => 'PHIẾU MƯỢN', 'href' => 'borrow.php', 'key' => 'borrow'],
        ['label' => 'KHÁM PHÁ', 'href' => 'discover.php', 'key' => 'explore'],
        ['label' => 'LIÊN LẠC', 'href' => 'contact.php', 'key' => 'contact']
    ],
    'login' => 'Đăng nhập'
];

$message = '';
$messageType = '';
$phieuVuaTaoId = null;

// Chỉ admin mới được thao tác với THÀNH TIỀN, TRẢ PHIẾU, XEM PHIẾU ĐÃ XUẤT — độc giả thường chỉ tạo phiếu mượn
$isAdmin = (($_SESSION['vai_tro'] ?? '') === 'admin');

// Dữ liệu phiếu vừa tạo thành công, dùng để tự động xuất file .txt
$phieuVuaTaoTenSach = '';
$phieuVuaTaoSoLuong = 0;
$phieuVuaTaoNgayMuon = '';
$phieuVuaTaoNgayHenTra = '';
$phieuVuaTaoTrangThai = '';
$phieuVuaTaoNgayTraThucTe = '';

/* =========================================================
   3. TỰ ĐỘNG LẤY TÀI KHOẢN ĐANG ĐĂNG NHẬP
========================================================= */
$id_doc_gia = (int)($_SESSION['id_doc_gia'] ?? 0);
$docGia = null;

if ($id_doc_gia > 0) {

    try {

        $stmt = $conn->prepare("
            SELECT
                id_doc_gia,
                ho_ten,
                ten_tai_khoan
            FROM doc_gia
            WHERE id_doc_gia = :id_doc_gia
            LIMIT 1
        ");

        $stmt->execute([
            ':id_doc_gia' => $id_doc_gia
        ]);

        $docGia = $stmt->fetch();

        if (!$docGia) {

            $message = 'Không tìm thấy thông tin tài khoản.';
            $messageType = 'error';

        }

    } catch (PDOException $e) {

        error_log(
            '[phieu_muon] Lỗi lấy thông tin độc giả: '
            . $e->getMessage()
        );

        $message = 'Không thể lấy thông tin tài khoản.';
        $messageType = 'error';

    }

} else {

    $message = 'Bạn cần đăng nhập trước khi lập phiếu mượn.';
    $messageType = 'error';

}

/* =========================================================
   4. GIÁ TRỊ FORM
========================================================= */
$ho_ten = $docGia['ho_ten'] ?? '';
$tai_khoan = $docGia['ten_tai_khoan'] ?? '';

// Nếu người dùng được điều hướng từ discover.php (bấm "Tìm hiểu thêm"),
// sách sẽ được truyền qua URL dạng borrow.php?id_sach=123 và ta điền
// sẵn vào ô "CHỌN SÁCH" bên dưới.
$id_sach = isset($_GET['id_sach']) ? (int)$_GET['id_sach'] : '';
$so_luong = 1;

$ngay_muon = date('Y-m-d');
$ngay_hen_tra = date('Y-m-d', strtotime($ngay_muon . ' +14 days'));

// Giá mượn sách: 2.000 VNĐ / ngày
$don_gia_moi_ngay = 2000;

$trang_thai = 'Đang mượn';

/* ID phiếu mượn đang được chọn để trả (đọc từ file phiếu_tra.txt,
   xem phần JS "TRẢ PHIẾU BẰNG FILE .TXT" bên dưới) */
$id_phieu_tra = (int)($_POST['id_phieu_tra'] ?? 0);


/* =========================================================
   5. XỬ LÝ KHI BẤM "XÁC NHẬN TRẢ" (TRẢ PHIẾU)
========================================================= */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['submit_borrow'])
    && $id_phieu_tra > 0
) {

    if (!$isAdmin) {

        $message = 'Chỉ admin mới được thực hiện trả phiếu.';
        $messageType = 'error';

    } elseif ($id_doc_gia <= 0 || !$docGia) {

        $message = 'Bạn cần đăng nhập trước khi trả phiếu.';
        $messageType = 'error';

    } else {

        try {

            $conn->beginTransaction();

            $stmtReturn = $conn->prepare("
                SELECT
                    pm.id_phieu_muon,
                    pm.id_sach,
                    pm.so_luong,
                    pm.trang_thai,
                    pm.ngay_muon,
                    pm.ngay_tra_du_kien,
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
                ':id_doc_gia' => $id_doc_gia
            ]);

            $phieuTra = $stmtReturn->fetch();

            if (!$phieuTra) {

                throw new Exception(
                    'Không tìm thấy phiếu mượn hoặc phiếu này không thuộc tài khoản đang đăng nhập.'
                );

            }

            if ($phieuTra['trang_thai'] === 'Đã trả') {

                throw new Exception(
                    'Phiếu #' . $id_phieu_tra . ' đã được trả trước đó.'
                );

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
                ':id_doc_gia' => $id_doc_gia
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

            /* =========================================
               LƯU DỮ LIỆU PHIẾU VỪA TRẢ
               (dùng để xuất file .txt bên dưới,
               phải lưu TRƯỚC khi reset $id_phieu_tra)
            ========================================= */

            $phieuVuaTaoId = $id_phieu_tra;
            $phieuVuaTaoTenSach = $phieuTra['ten_sach'];
            $phieuVuaTaoSoLuong = $phieuTra['so_luong'];
            $phieuVuaTaoNgayMuon = $phieuTra['ngay_muon'];
            $phieuVuaTaoNgayHenTra = $phieuTra['ngay_tra_du_kien'];
            $phieuVuaTaoTrangThai = 'Đã trả';
            $phieuVuaTaoNgayTraThucTe = date('Y-m-d');

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

}


/* =========================================================
   6. XỬ LÝ TẠO PHIẾU MƯỢN
========================================================= */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['submit_borrow'])
    && $id_phieu_tra <= 0
) {

    if ($id_doc_gia <= 0 || !$docGia) {

        $message = 'Bạn cần đăng nhập trước khi lập phiếu mượn.';
        $messageType = 'error';

    } else {

        /* -------------------------------------------------
           LẤY DỮ LIỆU FORM
        ------------------------------------------------- */

        $id_sach = (int)($_POST['id_sach'] ?? 0);

        $so_luong = (int)($_POST['so_luong'] ?? 0);

        $ngay_muon = $_POST['ngay_muon'] ?? '';

        $ngay_hen_tra = $_POST['ngay_hen_tra'] ?? '';

        $trang_thai = $_POST['trang_thai'] ?? 'Đang mượn';


        /* -------------------------------------------------
           KIỂM TRA DỮ LIỆU
        ------------------------------------------------- */

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

            $message =
                'Ngày hẹn trả phải sau hoặc bằng ngày mượn.';

            $messageType = 'error';

        } elseif (
            !in_array(
                $trang_thai,
                ['Đang mượn', 'Đã trả'],
                true
            )
        ) {

            $trang_thai = 'Đang mượn';

        }


        /* -------------------------------------------------
           NẾU KHÔNG CÓ LỖI → XỬ LÝ DATABASE
        ------------------------------------------------- */

        if ($message === '') {

            try {

                /* =========================================
                   KIỂM TRA SÁCH
                ========================================= */

                $stmt = $conn->prepare("
                    SELECT
                        id_sach,
                        ten_sach,
                        so_luong_con_lai
                    FROM sach
                    WHERE id_sach = :id_sach
                    LIMIT 1
                ");

                $stmt->execute([
                    ':id_sach' => $id_sach
                ]);

                $sach = $stmt->fetch();


                /* =========================================
                   KHÔNG TÌM THẤY SÁCH
                ========================================= */

                if (!$sach) {

                    $message = 'Không tìm thấy sách.';
                    $messageType = 'error';

                }

                /* =========================================
                   KHÔNG ĐỦ SỐ LƯỢNG
                ========================================= */

                elseif (
                    (int)$sach['so_luong_con_lai']
                    < $so_luong
                ) {

                    $message =
                        'Sách "'
                        . $sach['ten_sach']
                        . '" chỉ còn '
                        . (int)$sach['so_luong_con_lai']
                        . ' quyển.';

                    $messageType = 'error';

                }

                /* =========================================
                   ĐỦ SÁCH → TẠO PHIẾU
                ========================================= */

                else {

                    $conn->beginTransaction();


                    /* =====================================
                       1. TẠO PHIẾU MƯỢN
                    ===================================== */

                    $stmt = $conn->prepare("
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
                    ");

                    $stmt->execute([

                        ':id_doc_gia' =>
                            $id_doc_gia,

                        ':id_sach' =>
                            $id_sach,

                        ':so_luong' =>
                            $so_luong,

                        ':ngay_muon' =>
                            $ngay_muon,

                        ':ngay_tra_du_kien' =>
                            $ngay_hen_tra,

                        ':trang_thai' =>
                            $trang_thai

                    ]);


                    /* =====================================
                       2. LẤY ID PHIẾU VỪA TẠO
                    ===================================== */

                    $phieuVuaTaoId =
                        (int)$conn->lastInsertId();


                    /* =====================================
                       3. TRỪ SỐ LƯỢNG SÁCH
                    ===================================== */

                    $stmt = $conn->prepare("
                        UPDATE sach

                        SET
                            so_luong_con_lai =
                                so_luong_con_lai
                                - :so_luong_tru,

                            so_luot_muon =
                                so_luot_muon
                                + :so_luong_luot,

                            sach_vat_ly =
                                CASE

                                    WHEN
                                        so_luong_con_lai
                                        - :so_luong_case <= 0

                                    THEN 'Hết sách'

                                    ELSE 'Còn sách'

                                END

                        WHERE
                            id_sach = :id_sach

                            AND so_luong_con_lai
                                >= :so_luong_where
                    ");

                    $stmt->execute([

                        ':so_luong_tru' =>
                            $so_luong,

                        ':so_luong_luot' =>
                            $so_luong,

                        ':so_luong_case' =>
                            $so_luong,

                        ':id_sach' =>
                            $id_sach,

                        ':so_luong_where' =>
                            $so_luong

                    ]);


                    /* =====================================
                       KIỂM TRA UPDATE
                    ===================================== */

                    if ($stmt->rowCount() <= 0) {

                        throw new Exception(
                            'Không thể cập nhật số lượng sách.'
                        );

                    }


                    /* =====================================
                       4. HOÀN TẤT TRANSACTION
                    ===================================== */

                    $conn->commit();


                    /* =====================================
                       5. THÔNG BÁO THÀNH CÔNG
                    ===================================== */

                    $message =
                        'Tạo phiếu mượn thành công cho độc giả "'
                        . $docGia['ho_ten']
                        . '".';

                    $messageType = 'success';


                    /* =====================================
                       5.1. LƯU DỮ LIỆU PHIẾU VỪA TẠO
                       (dùng để xuất file .txt bên dưới,
                       phải lưu TRƯỚC khi reset form)
                    ===================================== */

                    $phieuVuaTaoTenSach = $sach['ten_sach'];
                    $phieuVuaTaoSoLuong = $so_luong;
                    $phieuVuaTaoNgayMuon = $ngay_muon;
                    $phieuVuaTaoNgayHenTra = $ngay_hen_tra;
                    $phieuVuaTaoTrangThai = $trang_thai;


                    /* =====================================
                       6. RESET FORM
                    ===================================== */

                    $id_sach = '';

                    $so_luong = 1;

                    $ngay_muon = date('Y-m-d');

                    $ngay_hen_tra = '';

                    $trang_thai = 'Đang mượn';

                }

            } catch (Throwable $e) {

                /* -----------------------------------------
                   ROLLBACK NẾU CÓ LỖI
                ----------------------------------------- */

                if ($conn->inTransaction()) {

                    $conn->rollBack();

                }

                $phieuVuaTaoId = null;


                /* -----------------------------------------
                   GHI LOG LỖI
                ----------------------------------------- */

                error_log(
                    '[phieu_muon] Tạo phiếu mượn thất bại: '
                    . $e->getMessage()
                );


                /* -----------------------------------------
                   HIỂN THỊ LỖI
                ----------------------------------------- */

                $message =
                    'Không thể tạo phiếu mượn. '
                    . 'Vui lòng kiểm tra database và thử lại.';

                $messageType = 'error';

            }

        }

    }

}


/* =========================================================
   7. LẤY DANH SÁCH SÁCH CÒN TRONG KHO
========================================================= */

try {

    $stmt = $conn->query("
        SELECT
            id_sach AS id,
            ten_sach,
            so_luong_con_lai

        FROM sach

        WHERE so_luong_con_lai > 0

        ORDER BY ten_sach ASC
    ");

    $books = $stmt->fetchAll();

} catch (PDOException $e) {

    $books = [];

    if ($message === '') {

        $message =
            'Không thể lấy danh sách sách: '
            . $e->getMessage();

        $messageType = 'error';

    }

}

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
        <?= htmlspecialchars(
            $pageTitle,
            ENT_QUOTES,
            'UTF-8'
        ) ?>
    </title>

    <!-- Font hỗ trợ đầy đủ dấu tiếng Việt ở mọi độ đậm, tránh lỗi
         trình duyệt tự "giả đậm" (synthetic bold) làm nhòe/lặp nét chữ
         khi dùng font-weight 800/900 với Arial. -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet"
    >


    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }


        body {
            margin: 0;
            padding: 0;

            background: #e9ebee;

            color: #222;

            font-family:
                'Be Vietnam Pro',
                Arial,
                Helvetica,
                sans-serif;
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
                url(
                    "https://images.unsplash.com/photo-1507842217343-583bb7270b66"
                );

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

    render_header(
        $nav,
        $activeKey
    );

    ?>


    <!-- =====================================================
         BANNER
    ====================================================== -->

    <section class="banner">

        <div class="banner-overlay"></div>

        <h1>

            <?= htmlspecialchars(
                $pageTitle,
                ENT_QUOTES,
                'UTF-8'
            ) ?>

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

            <div
                class="message
                <?= htmlspecialchars(
                    $messageType,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
            >

                <?= htmlspecialchars(
                    $message,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>

            </div>

        <?php endif; ?>


        <!-- =================================================
             THÔNG TIN TÀI KHOẢN
        ================================================== -->

        <div class="row">


            <div class="form-group name">

                <label>
                    TÀI KHOẢN
                </label>

                <input
                    type="text"
                    value="<?= htmlspecialchars(
                        $tai_khoan,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                    readonly
                >

            </div>


            <div class="form-group name">

                <label>
                    HỌ VÀ TÊN
                </label>

                <input
                    type="text"
                    value="<?= htmlspecialchars(
                        $ho_ten,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
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
                            <?= (
                                (string)$id_sach
                                ===
                                (string)$book['id']
                            )
                                ? 'selected'
                                : ''
                            ?>
                        >

                            <?= htmlspecialchars(
                                $book['ten_sach'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                            - Còn

                            <?= (int)$book[
                                'so_luong_con_lai'
                            ] ?>

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
                        value="<?= htmlspecialchars(
                            $ngay_muon,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
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
                        value="<?= htmlspecialchars(
                            $ngay_hen_tra,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        required
                    >

                </div>


            </div>


            <!-- =============================================
                 THÀNH TIỀN (TỰ ĐỘNG TÍNH)
            ============================================== -->

            <div class="form-group">

                <label for="thanh_tien">
                    THÀNH TIỀN (<?= number_format($don_gia_moi_ngay, 0, ',', '.') ?> VNĐ / ngày / quyển)
                </label>

                <input
                    type="text"
                    id="thanh_tien"
                    name="thanh_tien"
                    value=""
                    readonly
                    <?= $isAdmin ? '' : 'disabled title="Chỉ admin mới thao tác được mục này"' ?>
                >

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
                        <?= (
                            $trang_thai
                            ===
                            'Đang mượn'
                        )
                            ? 'selected'
                            : ''
                        ?>
                    >
                        Đang mượn
                    </option>


                    <option
                        value="Đã trả"
                        <?= (
                            $trang_thai
                            ===
                            'Đã trả'
                        )
                            ? 'selected'
                            : ''
                        ?>
                    >
                        Đã trả
                    </option>


                </select>

            </div>


            <!-- =============================================
                 NÚT TẠO PHIẾU
            ============================================== -->

            <div class="button-area">


                <button
                    type="submit"
                    name="submit_borrow"
                    class="borrow-button"
                    id="btnTaoPhieu"
                >
                    TẠO PHIẾU MƯỢN
                </button>


                <?php if ($isAdmin): ?>
                    <button
                        type="button"
                        class="return-button"
                        id="btnTraPhieu"
                        onclick="document.getElementById('filePhieuTra').click();"
                    >
                        TRẢ PHIẾU
                    </button>
                <?php endif; ?>

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


                <?php if (
                    $isAdmin
                    && $phieuVuaTaoId !== null
                    && $phieuVuaTaoId > 0
                ): ?>


                    <a
                        href="xuat_phieu.php?id=<?= (int)$phieuVuaTaoId ?>"
                        target="_blank"
                        class="view-receipt-button"
                    >
                        XEM PHIẾU ĐÃ XUẤT
                    </a>


                <?php elseif ($isAdmin): ?>


                    <span
                        class="view-receipt-button disabled"
                    >
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

        if (
            file_exists(
                __DIR__ . '/footer.php'
            )
        ) {

            include __DIR__ . '/footer.php';

        }

        ?>

    </div>


    <!-- =====================================================
         JAVASCRIPT
    ====================================================== -->

    <script>


        /* -----------------------------------------------
           NGÀY HẸN TRẢ KHÔNG ĐƯỢC NHỎ HƠN NGÀY MƯỢN
        ------------------------------------------------ */

        const ngayMuon =
            document.getElementById(
                'ngay_muon'
            );


        const ngayHenTra =
            document.getElementById(
                'ngay_hen_tra'
            );


        if (
            ngayMuon
            &&
            ngayHenTra
        ) {


            ngayHenTra.min =
                ngayMuon.value;


            ngayMuon.addEventListener(
                'change',
                function () {


                    ngayHenTra.min =
                        this.value;


                    /* -------------------------------------
                       MẶC ĐỊNH NGÀY HẸN TRẢ = NGÀY MƯỢN + 14 NGÀY
                    ------------------------------------- */

                    if (this.value) {

                        const ngayMuonDate =
                            new Date(this.value);

                        ngayMuonDate.setDate(
                            ngayMuonDate.getDate() + 14
                        );

                        const yyyy =
                            ngayMuonDate.getFullYear();

                        const mm = String(
                            ngayMuonDate.getMonth() + 1
                        ).padStart(2, '0');

                        const dd = String(
                            ngayMuonDate.getDate()
                        ).padStart(2, '0');

                        ngayHenTra.value =
                            yyyy + '-' + mm + '-' + dd;

                    } else if (
                        ngayHenTra.value
                        &&
                        ngayHenTra.value
                        <
                        this.value
                    ) {

                        ngayHenTra.value =
                            '';

                    }


                    capNhatThanhTien();

                }
            );

        }


        /* -----------------------------------------------
           TỰ ĐỘNG TÍNH THÀNH TIỀN
           (2.000 VNĐ / ngày / quyển sách)
        ------------------------------------------------ */

        const DON_GIA_MOI_NGAY = <?= (int)$don_gia_moi_ngay ?>;

        const soLuongInput =
            document.getElementById('so_luong');

        const thanhTienInput =
            document.getElementById('thanh_tien');

        function capNhatThanhTien() {

            if (
                !ngayMuon
                ||
                !ngayHenTra
                ||
                !thanhTienInput
            ) {
                return;
            }

            const ngayMuonVal = ngayMuon.value;
            const ngayHenTraVal = ngayHenTra.value;
            const soLuongVal = soLuongInput
                ? (parseInt(soLuongInput.value, 10) || 0)
                : 0;

            if (!ngayMuonVal || !ngayHenTraVal || soLuongVal <= 0) {
                thanhTienInput.value = '';
                return;
            }

            const d1 = new Date(ngayMuonVal);
            const d2 = new Date(ngayHenTraVal);

            const soNgay = Math.round(
                (d2 - d1) / (1000 * 60 * 60 * 24)
            );

            if (soNgay <= 0) {
                thanhTienInput.value = '';
                return;
            }

            const tongTien =
                soNgay * soLuongVal * DON_GIA_MOI_NGAY;

            thanhTienInput.value =
                tongTien.toLocaleString('vi-VN') + ' VNĐ';

        }

        if (ngayMuon) {
            ngayMuon.addEventListener('change', capNhatThanhTien);
        }

        if (ngayHenTra) {
            ngayHenTra.addEventListener('change', capNhatThanhTien);
        }

        if (soLuongInput) {
            soLuongInput.addEventListener('input', capNhatThanhTien);
        }

        // Tính ngay khi tải trang (đề phòng đã có sẵn giá trị ngày/số lượng)
        capNhatThanhTien();

        /* -----------------------------------------------
           CHỌN SÁCH → SỐ LƯỢNG MẶC ĐỊNH = 1
        ------------------------------------------------ */

        const selectSach =
            document.getElementById(
                'id_sach'
            );


        const soLuong =
            document.getElementById(
                'so_luong'
            );


        if (
            selectSach
            &&
            soLuong
        ) {


            selectSach.addEventListener(
                'change',
                function () {


                    if (
                        this.value !== ''
                    ) {

                        soLuong.value =
                            1;

                    }

                    capNhatThanhTien();

                }
            );

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

                // Gộp mọi khoảng trắng/xuống dòng liên tiếp thành 1 dấu cách,
                // vì text trong <option> của select "CHỌN SÁCH" có nhiều
                // xuống dòng do cách viết PHP nhiều dòng.
                function chuanHoaTen(s) {
                    return (s || '').replace(/\s+/g, ' ').trim();
                }

                const tenSachChuan = chuanHoaTen(tenSach);
                let found = false;

                if (selectSach) {
                    for (const option of selectSach.options) {
                        let tenOption = chuanHoaTen(option.textContent);

                        // Cắt bỏ phần hậu tố hiển thị số lượng còn lại,
                        // đúng với định dạng render trong select "CHỌN SÁCH":
                        // "Tên sách - Còn N quyển"
                        tenOption = tenOption
                            .replace(/\s*-\s*Còn\s+\d+\s+quyển\s*$/i, '')
                            // giữ lại cho tương thích định dạng cũ (nếu có nơi khác dùng)
                            .replace(/\s*\(còn\s*\d+\)\s*$/i, '')
                            .replace(/\s*\(Hết sách\)\s*$/i, '')
                            .trim();

                        if (tenOption === tenSachChuan) {
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


    <?php if (
        $phieuVuaTaoId !== null
        && $phieuVuaTaoId > 0
        && $messageType === 'success'
    ):

        /* =====================================================
           NỘI DUNG FILE .TXT CỦA PHIẾU VỪA TẠO / VỪA TRẢ
           (cùng định dạng "Nhãn: giá trị" mà JS đọc lại khi
           trả phiếu — xem hàm layGiaTri() ở trên)
        ===================================================== */
        $noiDungPhieuTxt =
            "Mã phiếu: #{$phieuVuaTaoId}\n"
            . "Họ tên: {$ho_ten}\n"
            . "Tên sách: {$phieuVuaTaoTenSach}\n"
            . "Số lượng: {$phieuVuaTaoSoLuong}\n"
            . "Ngày mượn: " . date('d/m/Y', strtotime($phieuVuaTaoNgayMuon)) . "\n"
            . "Ngày hẹn trả: " . date('d/m/Y', strtotime($phieuVuaTaoNgayHenTra)) . "\n"
            . "Trạng thái: {$phieuVuaTaoTrangThai}\n";

        // Nếu vừa trả phiếu (có ngày trả thực tế) thì ghi thêm dòng này.
        if (!empty($phieuVuaTaoNgayTraThucTe)) {

            $noiDungPhieuTxt .=
                "Ngày trả thực tế: "
                . date('d/m/Y', strtotime($phieuVuaTaoNgayTraThucTe))
                . "\n";

        }

        // Trình duyệt gốc Chromium (Chrome, Edge...) sẽ tự tạo thư mục con
        // "phiếu xuất" bên trong thư mục Downloads mặc định khi thấy dấu "/"
        // trong tên file tải về. Firefox/Safari sẽ bỏ qua phần thư mục và
        // chỉ lưu file vào Downloads như bình thường.
        $hauToTenFile = ($phieuVuaTaoTrangThai === 'Đã trả') ? '_da_tra' : '';
        $tenFileTaiVe = 'phiếu xuất/phieu_muon_' . (int)$phieuVuaTaoId . $hauToTenFile . '.txt';
    ?>

        <script>
            (function () {

                const noiDungPhieu =
                    <?= json_encode($noiDungPhieuTxt, JSON_UNESCAPED_UNICODE) ?>;

                const blob = new Blob(
                    [noiDungPhieu],
                    { type: 'text/plain;charset=utf-8' }
                );

                const url = URL.createObjectURL(blob);

                const a = document.createElement('a');
                a.href = url;
                a.download = <?= json_encode($tenFileTaiVe, JSON_UNESCAPED_UNICODE) ?>;

                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);

                setTimeout(function () {
                    URL.revokeObjectURL(url);
                }, 5000);

            })();
        </script>

    <?php endif; ?>


</body>

</html>