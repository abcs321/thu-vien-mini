<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/database.php';

$pageTitle = 'Liên lạc';
$activeKey = 'contact';

$thong_bao = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $ho = trim($_POST['ho'] ?? '');
    $ten = trim($_POST['ten'] ?? '');
    $so_dien_thoai = trim($_POST['so_dien_thoai'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $noi_dung = trim($_POST['noi_dung'] ?? '');

    if (
        $ho === '' ||
        $ten === '' ||
        $so_dien_thoai === '' ||
        $email === '' ||
        $noi_dung === ''
    ) {

        $thong_bao = 'Vui lòng nhập đầy đủ thông tin.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $thong_bao = 'Địa chỉ email không hợp lệ.';

    } else {

        $sql = "
            INSERT INTO lien_lac
            (
                ho,
                ten,
                so_dien_thoai,
                email,
                noi_dung
            )
            VALUES
            (
                :ho,
                :ten,
                :so_dien_thoai,
                :email,
                :noi_dung
            )
        ";

        $stmt = $conn->prepare($sql);

        $stmt->execute([
            ':ho' => $ho,
            ':ten' => $ten,
            ':so_dien_thoai' => $so_dien_thoai,
            ':email' => $email,
            ':noi_dung' => $noi_dung
        ]);

        $thong_bao = 'Gửi góp ý thành công!';

    }
}

?>

<!DOCTYPE html>
<html lang="vi">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Liên lạc - Thư viện</title>

    <link rel="stylesheet" href="style.css">

    <style>

.contact-message {
    margin-bottom: 15px;
    padding: 12px 15px;

    background: #f1f1f1;

    color: #333;

    font-size: 16px;

    border-left: 4px solid #ff4b4b;
}
      /* =====================================================
   RESET
===================================================== */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html,
body {
    margin: 0 !important;
    padding: 0 !important;
}

body {
    font-family: Arial, Helvetica, sans-serif;
    background: #ffffff;
    color: #333333;
}


/* =====================================================
   TRANG LIÊN LẠC
===================================================== */

.contact-page {
    width: 100%;
    min-height: 100vh;
}


/* =====================================================
   ẢNH HERO
===================================================== */

.hero {
    width: 100%;
    height: 273px;

    background-image:
        linear-gradient(
            rgba(0, 0, 0, 0.62),
            rgba(0, 0, 0, 0.62)
        ),
        url("images/library1.jpg");

    background-size: cover;
    background-position: center;

    display: flex;
    justify-content: center;
    align-items: center;
}

.hero h1 {
    color: #ff4b4b;

    font-size: 48px;
    font-weight: 400;

    margin: 0;
}


/* =====================================================
   KHUNG LIÊN HỆ
===================================================== */

.contact-box {
    width: 96%;
    max-width: none;

    margin: -28px auto 0;

    position: relative;
    z-index: 5;

    background: #ffffff;

    border-radius: 14px 14px 0 0;

    padding: 28px 40px 35px;

    box-shadow:
        0 2px 10px rgba(0, 0, 0, 0.12);
}


/* =====================================================
   DÒNG GIỚI THIỆU
===================================================== */

.contact-description {
    font-size: 20px;

    color: #333333;

    margin-bottom: 18px;
}


/* =====================================================
   2 CỘT
===================================================== */

.contact-content {
    display: grid;

    grid-template-columns: 1.25fr 1fr;

    gap: 40px;
}


/* =====================================================
   FORM
===================================================== */

.form-row {
    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 35px;

    margin-bottom: 18px;
}

.form-group {
    width: 100%;
}

.form-group label {
    display: block;

    font-size: 18px;

    color: #333333;

    margin-bottom: 8px;
}

.form-group input {
    width: 100%;
    height: 45px;

    border: none;

    background: #e9e9e9;

    padding: 8px 12px;

    font-family: Arial, sans-serif;

    font-size: 18px;

    outline: none;
}

.form-group input:focus {
    outline: 2px solid #ff4b4b;
}


/* =====================================================
   Ô GÓP Ý
===================================================== */

.message {
    margin-top: 8px;
}

.message textarea {
    width: 100%;
    height: 150px;

    resize: none;

    border: none;

    background: #e9e9e9;

    padding: 12px;

    font-family: Arial, sans-serif;

    font-size: 18px;

    outline: none;
}

.message textarea:focus {
    outline: 2px solid #ff4b4b;
}

.message textarea::placeholder {
    color: #999999;

    font-size: 17px;
}


/* =====================================================
   NÚT GỬI
===================================================== */

.submit-area {
    text-align: center;

    margin-top: 18px;
}

.submit-btn {
    width: 70px;
    height: 32px;

    border: none;

    border-radius: 7px;

    background: #ff4b4b;

    color: #ffffff;

    font-family: Arial, sans-serif;

    font-size: 14px;

    cursor: pointer;
}

.submit-btn:hover {
    background: #e63c3c;
}


/* =====================================================
   THÔNG TIN LIÊN HỆ
===================================================== */

.contact-info {
    background: #e9e9e9;

    min-height: 245px;

    padding: 25px 22px;
}

.contact-info h2 {
    font-size: 27px;

    font-weight: 400;

    color: #222222;

    margin-bottom: 22px;
}

.info-item {
    display: flex;

    align-items: center;

    margin-bottom: 20px;

    font-size: 18px;

    color: #222222;
}

.info-icon {
    width: 28px;

    color: #ff4b4b;

    font-size: 18px;

    font-weight: bold;

    margin-right: 5px;
}



        /* =====================================================
           FOOTER
        ===================================================== */

        .footer-black {
            width: 100%;

            background: #0c0c0c;

            color: #ffffff;
        }


        
    </style>

</head>


<body>


<?php
/* =====================================================
   HEADER CỦA PROJECT (dùng chung từ includes.php)
===================================================== */

require_once __DIR__ . '/includes.php'; // $nav, render_header()...
render_header($nav, $activeKey);
?>


<!-- =====================================================
     NỘI DUNG TRANG LIÊN LẠC
===================================================== -->

<div class="contact-page">


    <!-- =================================================
         ẢNH + TIÊU ĐỀ
    ================================================== -->

    <section class="hero">

        <h1>
            Liên lạc
        </h1>

    </section>


    <!-- =================================================
         KHUNG LIÊN HỆ
    ================================================== -->

    <section class="contact-box">


        <!-- Dòng giới thiệu -->

        <p class="contact-description">
            Bạn có phản hồi hoặc góp ý? Hãy gửi đến chúng tôi!
        </p>


        <div class="contact-content">
<?php if ($thong_bao !== ''): ?>

    <div class="contact-message">
        <?= htmlspecialchars($thong_bao) ?>
    </div>

<?php endif; ?>

            <!-- =========================================
                 FORM
            ========================================== -->

            <form
                class="contact-form"
                method="POST"
                action=""
            >


                <!-- Họ + Tên -->

                <div class="form-row">


                    <div class="form-group">

                        <label for="ho">
                            Họ
                        </label>

                        <input
                            type="text"
                            id="ho"
                            name="ho"
                        >

                    </div>


                    <div class="form-group">

                        <label for="ten">
                            Tên
                        </label>

                        <input
                            type="text"
                            id="ten"
                            name="ten"
                        >

                    </div>


                </div>


                <!-- Số điện thoại + Email -->

                <div class="form-row">


                    <div class="form-group">

                        <label for="so_dien_thoai">
                            Số điện thoại
                        </label>

                        <input
                            type="text"
                            id="so_dien_thoai"
                            name="so_dien_thoai"
                        >

                    </div>


                    <div class="form-group">

                        <label for="email">
                            Địa chỉ email
                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                        >

                    </div>


                </div>


                <!-- Nội dung -->

                <div class="form-group message">

                    <label for="noi_dung">
                        Nội dung
                    </label>

                    <textarea
                        id="noi_dung"
                        name="noi_dung"
                        placeholder="Nhập góp ý hoặc phản hồi của bạn tại đây..."
                    ></textarea>

                </div>


                <!-- Nút gửi -->

                <div class="submit-area">

                    <button
                        type="submit"
                        class="submit-btn"
                    >
                        Gửi
                    </button>

                </div>


            </form>


            <!-- =========================================
                 THÔNG TIN LIÊN HỆ
            ========================================== -->

            <div class="contact-info">


                <h2>
                    Liên lạc với chúng tôi
                </h2>


                <div class="info-item">

                    <span class="info-icon">
                        ☎
                    </span>

                    <span>
                        0985792118
                    </span>

                </div>


                <div class="info-item">

                    <span class="info-icon">
                        ✉
                    </span>

                    <span>
                        thanhbinh06@gmail.com
                    </span>

                </div>


                <div class="info-item">

                    <span class="info-icon">
                        ♥
                    </span>

                    <span>
                        Trần Phú, Hà Đông, Hà Nội
                    </span>

                </div>


            </div>


        </div>


    </section>


</div>


<?php
/* =====================================================
   FOOTER CỦA PROJECT (dùng chung từ includes.php)
===================================================== */

echo '<div class="footer-black">';
render_footer($footer);
echo '</div>';
?>


</body>

</html>