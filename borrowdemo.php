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


/* =========================================================
   5. XỬ LÝ TẠO PHIẾU MƯỢN
========================================================= */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['submit_borrow'])
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
   6. LẤY DANH SÁCH SÁCH CÒN TRONG KHO
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
                >
                    TẠO PHIẾU MƯỢN
                </button>


                <?php if (
                    $phieuVuaTaoId !== null
                    && $phieuVuaTaoId > 0
                ): ?>


                    <a
                        href="xuat_phieu.php?id=<?= (int)$phieuVuaTaoId ?>"
                        target="_blank"
                        class="view-receipt-button"
                    >
                        XEM PHIẾU ĐÃ XUẤT
                    </a>


                <?php else: ?>


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


    </script>


</body>

</html>