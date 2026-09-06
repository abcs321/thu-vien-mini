<?php

/* =========================================================
   1. KẾT NỐI DATABASE + FILE DÙNG CHUNG
========================================================= */

require_once __DIR__ . '/database/database.php';
require_once __DIR__ . '/includes.php';


/* =========================================================
   2. CẤU HÌNH TRANG
========================================================= */

$pageTitle = 'Phiếu mượn';
$activeKey = 'borrow';


$message = '';
$messageType = '';


/* =========================================================
   3. GIÁ TRỊ GIỮ LẠI TRÊN FORM
========================================================= */

$tai_khoan = '';
$mat_khau = '';
$ho_ten = '';

$id_sach = '';
$so_luong = 1;

$ngay_muon = '';
$ngay_hen_tra = '';

$trang_thai = 'Đang mượn';


/* =========================================================
   4. XỬ LÝ KHI BẤM "TẠO PHIẾU MƯỢN"
========================================================= */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['submit_borrow'])
) {

    /* =====================================================
       4.1. LẤY DỮ LIỆU TỪ FORM
    ===================================================== */

    $tai_khoan = trim($_POST['tai_khoan'] ?? '');
    $mat_khau = trim($_POST['mat_khau'] ?? '');

    $ho_ten = trim($_POST['ho_ten'] ?? '');

    $id_sach = (int)($_POST['id_sach'] ?? 0);
    $so_luong = (int)($_POST['so_luong'] ?? 0);

    $ngay_muon = $_POST['ngay_muon'] ?? '';
    $ngay_hen_tra = $_POST['ngay_hen_tra'] ?? '';

    $trang_thai = $_POST['trang_thai'] ?? 'Đang mượn';


    /* =====================================================
       4.2. KIỂM TRA DỮ LIỆU BẮT BUỘC
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
               4.3. KIỂM TRA TÀI KHOẢN ĐỘC GIẢ
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


            /* =================================================
               4.3.1. XÁC THỰC MẬT KHẨU
            ================================================= */

            $matKhauDung = false;

            if ($docGia) {

                $matKhauLuu = $docGia['mat_khau'];

                $thongTinHash = password_get_info($matKhauLuu);

                if ($thongTinHash['algo'] !== null) {

                    /* Mật khẩu trong DB đã được băm */

                    $matKhauDung = password_verify(
                        $mat_khau,
                        $matKhauLuu
                    );

                } else {

                    /* Mật khẩu trong DB đang là plain text */

                    $matKhauDung = hash_equals(
                        $matKhauLuu,
                        $mat_khau
                    );
                }
            }


            /* =================================================
               4.3.2. TÀI KHOẢN HOẶC MẬT KHẨU KHÔNG ĐÚNG
            ================================================= */

            if (!$docGia || !$matKhauDung) {

                $message = 'Tài khoản hoặc mật khẩu không đúng.';
                $messageType = 'error';

            } else {

                /* =================================================
                   4.4. KIỂM TRA SÁCH
                ================================================= */

                $sql = "
                    SELECT
                        id,
                        ten_sach,
                        so_luong,
                        luot_muon
                    FROM sach
                    WHERE id = :id_sach
                    LIMIT 1
                ";

                $stmt = $conn->prepare($sql);

                $stmt->execute([
                    ':id_sach' => $id_sach
                ]);

                $sach = $stmt->fetch();


                /* =================================================
                   4.4.1. KHÔNG TÌM THẤY SÁCH
                ================================================= */

                if (!$sach) {

                    $message = 'Không tìm thấy sách.';
                    $messageType = 'error';

                }

                /* =================================================
                   4.4.2. KHÔNG ĐỦ SỐ LƯỢNG
                ================================================= */

                elseif ((int)$sach['so_luong'] < $so_luong) {

                    $message =
                        'Sách "' .
                        $sach['ten_sach'] .
                        '" chỉ còn ' .
                        (int)$sach['so_luong'] .
                        ' quyển.';

                    $messageType = 'error';

                }

                /* =================================================
                   4.4.3. ĐỦ ĐIỀU KIỆN -> TẠO PHIẾU
                ================================================= */

                else {

                    try {

                        /* =================================================
                           BẮT ĐẦU TRANSACTION
                        ================================================= */

                        $conn->beginTransaction();


                        /* =================================================
                           4.4.3.1. THÊM PHIẾU MƯỢN
                        ================================================= */

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


                        /* =================================================
                           4.4.3.2. TĂNG LƯỢT MƯỢN

                           Mỗi lần tạo phiếu thành công:
                           luot_muon + 1

                           Ví dụ:

                           0 -> 1
                           1 -> 2
                           2 -> 3
                        ================================================= */

                        $sql = "
                            UPDATE sach
                            SET luot_muon = COALESCE(luot_muon, 0) + 1
                            WHERE id = :id_sach
                        ";

                        $stmt = $conn->prepare($sql);

                        $stmt->execute([
                            ':id_sach' => $id_sach
                        ]);


                        /* =================================================
                           KIỂM TRA UPDATE LƯỢT MƯỢN
                        ================================================= */

                        if ($stmt->rowCount() <= 0) {

                            throw new Exception(
                                'Không thể cập nhật lượt mượn của sách.'
                            );
                        }


                        /* =================================================
                           4.4.3.3. TRỪ SỐ LƯỢNG SÁCH
                        ================================================= */

                        $sql = "
                            UPDATE sach
                            SET so_luong = so_luong - :so_luong_tru
                            WHERE id = :id_sach
                              AND so_luong >= :so_luong_kiem_tra
                        ";

                        $stmt = $conn->prepare($sql);

                        $stmt->execute([
                            ':so_luong_tru' => $so_luong,
                            ':id_sach' => $id_sach,
                            ':so_luong_kiem_tra' => $so_luong
                        ]);


                        /* =================================================
                           KIỂM TRA UPDATE SỐ LƯỢNG
                        ================================================= */

                        if ($stmt->rowCount() <= 0) {

                            throw new Exception(
                                'Không thể cập nhật số lượng sách.'
                            );
                        }


                        /* =================================================
                           4.4.3.4. HOÀN TẤT TRANSACTION
                        ================================================= */

                        $conn->commit();


                        /* =================================================
                           4.4.3.5. THÔNG BÁO THÀNH CÔNG
                        ================================================= */

                        $message =
                            'Tạo phiếu mượn thành công cho độc giả "' .
                            $docGia['ho_ten'] .
                            '". Lượt mượn của sách đã tăng 1.';

                        $messageType = 'success';


                        /* =================================================
                           RESET FORM
                        ================================================= */

                        $id_sach = '';
                        $so_luong = 1;
                        $ngay_muon = '';
                        $ngay_hen_tra = '';
                        $trang_thai = 'Đang mượn';


                    } catch (Throwable $e) {

                        /* =================================================
                           CÓ LỖI -> ROLLBACK
                        ================================================= */

                        if ($conn->inTransaction()) {
                            $conn->rollBack();
                        }


                        /* =================================================
                           GHI LOG LỖI
                        ================================================= */

                        error_log(
                            '[phieu_muon] Tạo phiếu mượn thất bại: ' .
                            $e->getMessage()
                        );


                        $message =
                            'Không thể tạo phiếu mượn. Vui lòng thử lại sau.';

                        $messageType = 'error';
                    }
                }
            }

        } catch (PDOException $e) {

            error_log(
                '[phieu_muon] Lỗi CSDL: ' .
                $e->getMessage()
            );

            $message =
                'Có lỗi khi xử lý dữ liệu. Vui lòng thử lại sau.';

            $messageType = 'error';
        }
    }
}


/* =========================================================
   5. LẤY DANH SÁCH SÁCH CÒN TRONG KHO
========================================================= */

try {

    $sql = "
       SELECT
    id,
    ten_sach,
    so_luong,
    luot_muon
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
            'Không thể lấy danh sách sách.';

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
        <?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>
    </title>

    <!-- CSS CHUNG CỦA WEBSITE -->
    <link rel="stylesheet" href="style.css">


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


        .password {
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
           RESPONSIVE
        ===================================================== */

        @media (max-width: 900px) {

            .row {
                flex-direction: column;

                gap: 0;
            }


            .name,
            .password {
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

        }

    </style>

</head>


<body>


    <!-- =====================================================
         HEADER DÙNG CHUNG VỚI INDEX.PHP
    ====================================================== -->

    <?php render_header($nav, 'borrow'); ?>


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
                class="message <?= htmlspecialchars(
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
                    value="<?= htmlspecialchars(
                        $tai_khoan,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
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
                    value="<?= htmlspecialchars(
                        $ho_ten,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                    placeholder="Nhập họ và tên"
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
            (string)$id_sach ===
            (string)$book['id']
        ) ? 'selected' : '' ?>
    >
        <?= htmlspecialchars(
            $book['ten_sach'],
            ENT_QUOTES,
            'UTF-8'
        ) ?>

        - Còn <?= (int)$book['so_luong'] ?> quyển
        - <?= (int)$book['luot_muon'] ?> lượt mượn
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
                        <?= $trang_thai === 'Đang mượn'
                            ? 'selected'
                            : ''
                        ?>
                    >
                        Đang mượn
                    </option>


                    <option
                        value="Đã trả"
                        <?= $trang_thai === 'Đã trả'
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

            </div>

        </section>

    </form>


    <!-- =====================================================
         FOOTER DÙNG CHUNG VỚI INDEX.PHP
    ====================================================== -->

    <?php render_footer($footer); ?>


    <!-- =====================================================
         JAVASCRIPT
    ====================================================== -->

    <script>

        /* -----------------------------------------------------
           Không cho ngày hẹn trả nhỏ hơn ngày mượn
        ----------------------------------------------------- */

        const ngayMuon =
            document.getElementById('ngay_muon');

        const ngayHenTra =
            document.getElementById('ngay_hen_tra');


        if (ngayMuon && ngayHenTra) {

            ngayMuon.addEventListener(
                'change',
                function () {

                    ngayHenTra.min = this.value;

                    if (
                        ngayHenTra.value &&
                        ngayHenTra.value < this.value
                    ) {

                        ngayHenTra.value = '';

                    }

                }
            );

        }


        /* -----------------------------------------------------
           Khi chọn sách, số lượng mặc định là 1
        ----------------------------------------------------- */

        const selectSach =
            document.getElementById('id_sach');

        const soLuong =
            document.getElementById('so_luong');


        if (selectSach && soLuong) {

            selectSach.addEventListener(
                'change',
                function () {

                    if (this.value !== '') {

                        soLuong.value = 1;

                    }

                }
            );

        }

    </script>


</body>

</html>