<?php
// Trang giới thiệu thư viện

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/includes.php'; // $nav, $footer, esc(), render_header(), render_footer()
?>

<!DOCTYPE html>
<html lang="vi">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Thư Viện - Trang Chủ</title>

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">

    <style>

        /* =====================================================
           1. RESET
        ===================================================== */

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #ffffff;
            color: #333333;
            line-height: 1.6;
        }


        /* Header/nav/logo/nút đăng nhập giờ dùng chung style.css, không định nghĩa lại ở đây
           (trước đây .brand-name bị đặt font Georgia/serif riêng, làm logo khác các trang khác) */


        /* =====================================================
           6. PHẦN HERO
        ===================================================== */

        .hero {
            width: 100%;

            max-width: none;

            min-height: 430px;

            margin: 35px 0;

            border: none;

            background-image:
                linear-gradient(
                    rgba(0, 0, 0, 0.68),
                    rgba(0, 0, 0, 0.68)
                ),
                url("images/l1.jpg");

            background-size: cover;

            background-position: center;

            display: flex;

            align-items: center;

            padding: 55px 8%;
        }


        .hero-content {
            width: 100%;
            max-width: 1000px;

            margin: 0 auto;

            text-align: center;
        }


        .breadcrumb {
            font-size: 18px;

            margin-bottom: 25px;

            color: #eeeeee;
        }


        .breadcrumb span {
            color: #fa4b3e;
        }


        .hero-content h1 {
            font-size: 38px;

            margin-bottom: 15px;

            font-weight: 800;

            color: #ffffff;
        }


        .hero-content h2 {
            font-size: 26px;

            margin-bottom: 15px;

            color: #eeeeee;
        }


        .hero-content p {
            font-size: 17px;

            line-height: 1.8;

            color: #eeeeee;
        }


        /* =====================================================
           7. TIÊU ĐỀ MỤC TIÊU
        ===================================================== */

        .section-title {
            width: 100%;
            max-width: none;

            margin: 70px 0 45px;

            padding: 0;

            text-align: center;

            font-size: 26px;

            color: #333333;
        }


        .section-title::after {
            content: "";

            display: block;

            width: 60px;
            height: 3px;

            background: #fa4b3e;

            margin: 10px auto 0;
        }


        /* =====================================================
           8. KHU VỰC NỘI DUNG
        ===================================================== */

        .content {
            width: 90%;

            max-width: 1000px;

            margin: 0 auto;
        }


        /* =====================================================
           9. TỪNG MỤC
        ===================================================== */

        .item {
            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 60px;

            margin-bottom: 80px;

            min-height: 240px;
        }


        /* Mục chẵn đảo ngược vị trí */

        .item:nth-child(even) {
            flex-direction: row-reverse;
        }


        /* =====================================================
           10. PHẦN CHỮ
        ===================================================== */

        .item-text {
            width: 50%;
        }


        .item-text h3 {
            color: #333333;

            font-size: 20px;

            margin-bottom: 14px;
        }


        .item-text p {
            color: #333333;

            font-size: 15px;

            line-height: 1.8;
        }


        /* =====================================================
           11. PHẦN ẢNH
        ===================================================== */

        .item-image {
            width: 50%;
        }


        .item-image img {
            width: 100%;

            height: 240px;

            object-fit: cover;

            display: block;

            border-radius: 3px;

            transition: 0.3s;
        }


        .item-image img:hover {
            transform: scale(1.02);
        }


        /* Footer giờ dùng chung style.css (trước đây phần này đặt sai tên class
           so với những gì render_footer() thực sự xuất ra, và một số class trùng
           tên với style.css gây xung đột màu sắc/bố cục) */


        /* =====================================================
           13. RESPONSIVE
        ===================================================== */
        /* Responsive cho header/nav/logo giờ nằm trong style.css dùng chung */

        @media (max-width: 700px) {

            .hero {
                width: 94%;

                min-height: 400px;

                padding: 30px;
            }


            .hero-content h1 {
                font-size: 28px;
            }


            .hero-content h2 {
                font-size: 22px;
            }


            .hero-content p {
                font-size: 15px;
            }


            .item,
            .item:nth-child(even) {
                flex-direction: column;

                gap: 25px;

                margin-bottom: 60px;
            }


            .item-text,
            .item-image {
                width: 100%;
            }


            .item-image img {
                height: 220px;
            }

        }

    </style>

</head>


<body>


<!-- =====================================================
     HEADER (dùng chung từ header.php)
====================================================== -->

<?php render_header($nav, 'about'); ?>



<!-- =====================================================
     HERO - VỀ CHÚNG TÔI
====================================================== -->

<section class="hero">

    <div class="hero-content">


        <div class="breadcrumb">

            <span>Về chúng tôi</span>
            / Trang chủ

        </div>


        <h1>
            Sứ mệnh
        </h1>


        <p>
            Thư viện Online hướng đến việc đưa tri thức đến
            gần hơn với mọi người, tạo điều kiện thuận lợi
            cho việc tự học, nghiên cứu và phát triển kiến
            thức mọi lúc, mọi nơi.
        </p>


    </div>

</section>



<!-- =====================================================
     MỤC TIÊU
====================================================== -->

<h2 class="section-title">

    <span>Mục tiêu</span>

</h2>



<!-- =====================================================
     NỘI DUNG MỤC TIÊU
====================================================== -->

<section class="content">


    <!-- ================= MỤC 1 ================= -->

    <div class="item">


        <div class="item-text">

            <h3>
                Xây dựng kho tài liệu trực tuyến đa dạng
            </h3>

            <p>
                Xây dựng một kho tài liệu trực tuyến đa dạng,
                phong phú và dễ dàng tìm kiếm, giúp người dùng
                nhanh chóng tiếp cận những tài liệu phù hợp
                với nhu cầu học tập và nghiên cứu.
            </p>

        </div>


        <div class="item-image">

            <img
                src="images/l2.jpg"
                alt="Kho tài liệu thư viện"
            >

        </div>


    </div>



    <!-- ================= MỤC 2 ================= -->

    <div class="item">


        <div class="item-text">

            <h3>
                Hỗ trợ người dùng tìm kiếm tài liệu
            </h3>

            <p>
                Hỗ trợ người dùng tìm kiếm và tiếp cận nguồn
                tài liệu một cách nhanh chóng, thuận tiện
                và hiệu quả.
            </p>

        </div>


        <div class="item-image">

            <img
                src="images/l3.jpg"
                alt="Người dùng tìm kiếm tài liệu"
            >

        </div>


    </div>



    <!-- ================= MỤC 3 ================= -->

    <div class="item">


        <div class="item-text">

            <h3>
                Tạo môi trường học tập thuận tiện
            </h3>

            <p>
                Tạo môi trường học tập hiện đại, thuận tiện
                và phù hợp với nhu cầu của sinh viên, học sinh
                cũng như những người có nhu cầu đọc sách.
            </p>

        </div>


        <div class="item-image">

            <img
                src="images/l4.jpg"
                alt="Môi trường học tập"
            >

        </div>


    </div>



    <!-- ================= MỤC 4 ================= -->

    <div class="item">


        <div class="item-text">

            <h3>
                Ứng dụng công nghệ vào quản lý thư viện
            </h3>

            <p>
                Ứng dụng công nghệ thông tin vào việc quản lý,
                tổ chức và khai thác tài liệu thư viện, giúp
                nâng cao hiệu quả hoạt động của hệ thống.
            </p>

        </div>


        <div class="item-image">

            <img
                src="images/l5.jpg"
                alt="Công nghệ thư viện"
            >

        </div>


    </div>



    <!-- ================= MỤC 5 ================= -->

    <div class="item">


        <div class="item-text">

            <h3>
                Không ngừng cải tiến trải nghiệm người dùng
            </h3>

            <p>
                Không ngừng cải tiến hệ thống và giao diện
                nhằm mang lại trải nghiệm tốt hơn, giúp người
                dùng dễ dàng sử dụng và khai thác nguồn tài
                nguyên của thư viện.
            </p>

        </div>


        <div class="item-image">

            <img
                src="images/l6.jpg"
                alt="Thư viện hiện đại"
            >

        </div>


    </div>


</section>



<!-- =====================================================
     FOOTER (dùng chung từ footer.php)
====================================================== -->

<?php render_footer($footer); ?>


</body>

</html>