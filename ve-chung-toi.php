<?php
// Trang giới thiệu thư viện
?>

<!DOCTYPE html>
<html lang="vi">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Thư Viện - Trang Chủ</title>

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


        /* =====================================================
           2. HEADER
        ===================================================== */

        .site-header {
            background: #ffffff;
            border-bottom: 2px solid #fa4b3e;
            width: 100%;
        }

        .site-header-inner {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;

            height: 90px;

            display: flex;
            align-items: center;

            gap: 30px;
        }


        /* =====================================================
           3. LOGO
        ===================================================== */

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;

            margin-right: auto;

            text-decoration: none;
        }

        .brand-mark {
            width: 48px;
            height: 48px;

            border-radius: 50%;

            background: #fa4b3e;
            color: white;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 22px;

            flex-shrink: 0;
        }

        .brand-name {
            font-family: Georgia, "Times New Roman", serif;

            font-weight: 900;
            font-size: 28px;

            letter-spacing: 0.5px;

            color: #333333;
        }


        /* =====================================================
           4. MENU
        ===================================================== */

        .main-nav {
            display: flex;
            align-items: center;

            gap: 28px;
        }

        .main-nav a {
            text-decoration: none;

            color: #333333;

            font-size: 13px;
            font-weight: 700;

            white-space: nowrap;

            transition: 0.2s;
        }

        .main-nav a.active {
            color: #fa4b3e;
        }


        /* =====================================================
           5. ĐĂNG NHẬP
        ===================================================== */

        .btn-login {
            display: inline-flex;
            align-items: center;
            justify-content: center;

            gap: 8px;

            background: #fa4b3e;
            color: #ffffff;

            text-decoration: none;

            font-size: 13px;
            font-weight: 700;

            padding: 12px 20px;

            border-radius: 7px;

            white-space: nowrap;

            flex-shrink: 0;

            transition: 0.2s;
        }

        .btn-login:hover {
            background: #e03a2d;
        }


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
                url("images/library1.jpg");

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


        /* =====================================================
           12. FOOTER
        ===================================================== */

        footer {
            margin-top: 50px;

            padding: 30px;

            background: #12111f;

            border-top: 1px solid #eeeeee;

            text-align: center;

            color: #eeeeee;

            font-size: 13px;
        }


        /* =====================================================
           13. RESPONSIVE
        ===================================================== */

        @media (max-width: 1000px) {

            .site-header-inner {
                height: auto;

                padding: 15px;

                flex-wrap: wrap;

                gap: 15px;
            }


            .brand {
                margin-right: auto;
            }


            .main-nav {
                order: 3;

                width: 100%;

                justify-content: center;

                flex-wrap: wrap;

                gap: 15px;
            }

        }


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


            .brand-name {
                font-size: 18px;
            }


            .btn-login {
                padding: 9px 12px;
            }

        }

    </style>

</head>


<body>


<!-- =====================================================
     HEADER
====================================================== -->

<header class="site-header">

    <div class="site-header-inner">


        <!-- LOGO -->

        <a href="index.php" class="brand">

            <div class="brand-mark">
                📚
            </div>

            <span class="brand-name">
                THƯ VIỆN
            </span>

        </a>


        <!-- MENU -->

        <nav class="main-nav">

            <a href="index.php">
                TRANG CHỦ
            </a>

            <a href="gioithieu.php" class="active">
                VỀ CHÚNG TÔI
            </a>

            <a href="sach.php">
                DANH SÁCH SÁCH
            </a>

            <a href="phieumuon.php">
                PHIẾU MƯỢN
            </a>

            <a href="#">
                KHÁM PHÁ
            </a>

            <a href="lienhe.php">
                LIÊN LẠC
            </a>

        </nav>


        <!-- NÚT ĐĂNG NHẬP -->

        <a href="#" class="btn-login">
            👤 Đăng nhập
        </a>


    </div>

</header>



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
                src="images/library2.jpg"
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
                src="images/library3.jpg"
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
                src="images/library4.jpg"
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
                src="images/library5.jpg"
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
                src="images/library1.jpg"
                alt="Thư viện hiện đại"
            >

        </div>


    </div>


</section>



<!-- =====================================================
     FOOTER
====================================================== -->

<?php include 'footer.php'; ?>


</body>

</html>