<?php

/* =========================================================
   1. KẾT NỐI DATABASE
========================================================= */

require_once __DIR__ . '/config/database.php';


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

/* Giá trị giữ lại trên form */
$tai_khoan = '';
$mat_khau = '';

$ho_ten = '';

$id_sach = '';
$so_luong = 1;

$ngay_muon = '';
$ngay_hen_tra = '';

$trang_thai = 'Đang mượn';


/* =========================================================
   3. XỬ LÝ KHI BẤM "TẠO PHIẾU MƯỢN"
========================================================= */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['submit_borrow'])
) {

    /* -----------------------------------------
       Lấy dữ liệu từ form
    ----------------------------------------- */

    $tai_khoan = trim($_POST['tai_khoan'] ?? '');
    $mat_khau = trim($_POST['mat_khau'] ?? '');

    $ho_ten = trim($_POST['ho_ten'] ?? '');

    $id_sach = (int)($_POST['id_sach'] ?? 0);
    $so_luong = (int)($_POST['so_luong'] ?? 0);

    $ngay_muon = $_POST['ngay_muon'] ?? '';
    $ngay_hen_tra = $_POST['ngay_hen_tra'] ?? '';

    $trang_thai = $_POST['trang_thai'] ?? 'Đang mượn';


    /* =====================================================
       3.1. KIỂM TRA DỮ LIỆU BẮT BUỘC
    ===================================================== */

    if ($tai_khoan === '') {

        $message = 'Vui lòng nhập tài khoản.';
        $messageType = 'error';

    } elseif ($mat_khau === '') {

        $message = 'Vui lòng nhập mật khẩu.';
        $messageType = 'error';

    } elseif ($id_sach <= 0) {

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
               3.2. KIỂM TRA TÀI KHOẢN ĐỘC GIẢ

               LƯU Ý: không so sánh mật khẩu ngay trong SQL vì
               bảng doc_gia dùng mat_khau VARCHAR(255) - có thể
               đang lưu dạng ĐÃ BĂM (password_hash/bcrypt) hoặc
               PLAIN TEXT tuỳ dữ liệu thực tế. Lấy ra theo tài
               khoản trước, rồi xác thực bằng PHP để tự nhận diện
               đúng cả 2 trường hợp mà không cần sửa lại về sau.
            ================================================= */

            $sql = "
                SELECT
                    id,
                    ho_ten,
                    ten_dang_nhap,
                    mat_khau
                FROM doc_gia
                WHERE ten_dang_nhap = :tai_khoan
                LIMIT 1
            ";

            $stmt = $conn->prepare($sql);

            $stmt->execute([
                ':tai_khoan' => $tai_khoan
            ]);

            $docGia = $stmt->fetch();


            /* ---------------------------------------------
               Xác thực mật khẩu (tự nhận diện hash / plain text)
            --------------------------------------------- */

            $matKhauDung = false;

            if ($docGia) {

                $matKhauLuu = $docGia['mat_khau'];
                $thongTinHash = password_get_info($matKhauLuu);

                if ($thongTinHash['algo'] !== null) {

                    /* mat_khau trong DB là chuỗi đã băm */
                    $matKhauDung = password_verify($mat_khau, $matKhauLuu);

                } else {

                    /* mat_khau trong DB đang là plain text */
                    $matKhauDung = hash_equals($matKhauLuu, $mat_khau);
                }
            }


            /* ---------------------------------------------
               Không tìm thấy tài khoản hoặc sai mật khẩu
            --------------------------------------------- */

            if (!$docGia || !$matKhauDung) {

                $message = 'Tài khoản hoặc mật khẩu không đúng.';
                $messageType = 'error';

            } else {

                /* =============================================
                   3.3. KIỂM TRA SÁCH
                ============================================= */

                $sql = "
                    SELECT
                        id,
                        ten_sach,
                        so_luong
                    FROM sach
                    WHERE id = :id_sach
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

                elseif ((int)$sach['so_luong'] < $so_luong) {

                    $message =
                        'Sách "' .
                        $sach['ten_sach'] .
                        '" chỉ còn ' .
                        (int)$sach['so_luong'] .
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
                             - doc_gia_id      (không phải id_doc_gia)
                             - ngay_tra_du_kien (không phải ngay_hen_tra)
                             - id_sach, so_luong là 2 cột mới thêm
                               (xem file fix_bang_phieu_muon.sql)
                        ------------------------------------- */

                        $sql = "
                            INSERT INTO phieu_muon
                            (
                                doc_gia_id,
                                id_sach,
                                so_luong,
                                ngay_muon,
                                ngay_tra_du_kien,
                                trang_thai,
                                ghi_chu
                            )
                            VALUES
                            (
                                :doc_gia_id,
                                :id_sach,
                                :so_luong,
                                :ngay_muon,
                                :ngay_tra_du_kien,
                                :trang_thai,
                                :ghi_chu
                            )
                        ";

                        $stmt = $conn->prepare($sql);

                        $stmt->execute([
                            ':doc_gia_id' => $docGia['id'],
                            ':id_sach' => $id_sach,
                            ':so_luong' => $so_luong,
                            ':ngay_muon' => $ngay_muon,
                            ':ngay_tra_du_kien' => $ngay_hen_tra,
                            ':trang_thai' => $trang_thai,
                            ':ghi_chu' => ''
                        ]);


                        /* -------------------------------------
                           CẬP NHẬT SỐ LƯỢNG SÁCH
                        ------------------------------------- */

                        $sql = "
                            UPDATE sach
                            SET so_luong = so_luong - :so_luong
                            WHERE id = :id_sach
                              AND so_luong >= :so_luong
                        ";

                        $stmt = $conn->prepare($sql);

                        $stmt->execute([
                            ':so_luong' => $so_luong,
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
                        $ngay_muon = '';
                        $ngay_hen_tra = '';
                        $trang_thai = 'Đang mượn';

                    } catch (Throwable $e) {

                        /* Nếu đang transaction thì rollback */
                        if ($conn->inTransaction()) {
                            $conn->rollBack();
                        }

                        /* Ghi log chi tiết lỗi phía server, không hiện cho người dùng */
                        error_log('[phieu_muon] Tạo phiếu mượn thất bại: ' . $e->getMessage());

                        $message = 'Không thể tạo phiếu mượn. Vui lòng thử lại sau.';
                        $messageType = 'error';
                    }
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
            id,
            ten_sach,
            so_luong
        FROM sach
        WHERE so_luong > 0
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
           BUTTON
        ===================================================== */

        .button-area {
            display: flex;

            justify-content: flex-end;

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
             TÀI KHOẢN + MẬT KHẨU
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
                    placeholder="Nhập tài khoản"
                    required
                >

            </div>


            <div class="form-group password">

                <label for="mat_khau">
                    MẬT KHẨU
                </label>

                <input
                    type="password"
                    id="mat_khau"
                    name="mat_khau"
                    placeholder="Nhập mật khẩu"
                    required
                >

            </div>

        </div>


        <!-- =================================================
             HỌ TÊN + NGÀY SINH
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
                    placeholder="Nhập họ và tên"
                >

            </div>



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
                            <?= (int)$book['so_luong'] ?>
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

    </script>


</body>

</html>