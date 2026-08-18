<?php
// Trang phiếu mượn
?>

<!DOCTYPE html>
<html lang="vi">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Phiếu mượn</title>

    <style>
        /* =========================
   RESET
========================= */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}


/* =========================
   BODY
========================= */

body {
    font-family: Arial, sans-serif;

    background: white;

    color: #222;
}


/* =========================
   THANH MENU
========================= */

.navbar {
    width: 100%;
    height: 55px;

    background: #21152d;

    display: flex;
    align-items: center;

    padding: 0 3%;
}


/* LOGO */

.logo {
    width: 250px;

    color: white;

    font-size: 18px;

    font-weight: bold;
}


/* MENU */

.menu {
    display: flex;

    align-items: center;

    gap: 65px;

    flex: 1;
}

.menu a {
    color: white;

    text-decoration: none;

    font-size: 13px;

    transition: 0.2s;
}

.menu a:hover {
    color: #ff3b3b;
}


/* NÚT ĐĂNG NHẬP */

.login {
    width: 115px;
    height: 38px;

    background: #ff3b3b;

    color: white;

    border: none;

    font-size: 11px;

    cursor: pointer;
}


/* =========================
   BANNER
========================= */

.banner {
    width: 100%;

    height: 190px;

    position: relative;

    background-image: url("https://images.unsplash.com/photo-1507842217343-583bb7270b66");

    background-size: cover;

    background-position: center 45%;

    display: flex;

    justify-content: center;

    align-items: center;
}


/* LỚP TỐI */

.banner-overlay {
    position: absolute;

    top: 0;
    left: 0;

    width: 100%;
    height: 100%;

    background: rgba(0, 0, 0, 0.65);
}


/* TIÊU ĐỀ */

.banner h1 {
    position: relative;

    z-index: 2;

    color: red;

    font-size: 28px;

    font-weight: normal;
}


/* =========================
   KHUNG FORM
========================= */

.form-container {
    width: calc(100% - 24px);

    margin: 12px auto;

    padding: 30px 35px;

    background: #e1e1e1;

    min-height: 550px;
}


/* =========================
   HỌ TÊN + NGÀY SINH
========================= */

.row {
    display: flex;

    gap: 30px;

    width: 100%;
}


.form-group {
    margin-bottom: 24px;
}


/* HỌ TÊN */

.name {
    flex: 3;
}


/* NGÀY SINH */

.birthday {
    flex: 2;
}


/* =========================
   LABEL
========================= */

.form-group label {
    display: block;

    margin-bottom: 9px;

    font-size: 14px;

    font-weight: bold;
}


/* =========================
   INPUT
========================= */

.form-group input {
    width: 100%;

    height: 48px;

    padding: 10px 14px;

    border: 1px solid #bbb;

    background: white;

    font-size: 14px;

    outline: none;
}

.form-group input:focus {
    border-color: #777;
}


/* =========================
   ĐỊA CHỈ
========================= */

.address {
    display: flex;

    gap: 30px;
}

.address input {
    height: 48px;

    padding: 10px 14px;

    border: 1px solid #bbb;

    background: white;

    font-size: 14px;
}


/* THÀNH PHỐ */

.address input:nth-child(1) {
    width: 20%;
}


/* XÃ */

.address input:nth-child(2) {
    width: 25%;
}


/* ĐỊA CHỈ CHI TIẾT */

.address input:nth-child(3) {
    width: 55%;
}


/* =========================
   ĐƯỜNG KẺ
========================= */

hr {
    border: none;

    border-top: 1px solid #aaa;

    margin: 15px 0 28px;
}


/* =========================
   THANH TOÁN
========================= */

.payment {
    position: relative;
}


/* TIÊU ĐỀ */

.payment h2 {
    font-size: 15px;

    margin-bottom: 15px;
}


/* SỐ THẺ */

.card-number {
    width: 100%;

    height: 48px;

    padding: 10px 14px;

    border: 1px solid #bbb;

    background: white;

    font-size: 14px;

    outline: none;
}


/* =========================
   THÔNG TIN THẺ
========================= */

.card-info {
    display: flex;

    gap: 30px;

    margin-top: 25px;
}

.card-info input {
    width: 33.33%;

    height: 48px;

    padding: 10px 14px;

    border: 1px solid #bbb;

    background: white;

    font-size: 14px;

    outline: none;
}


/* =========================
   QR
========================= */

.qr-area {
    text-align: center;

    margin-top: 28px;
}

.qr-area p {
    font-size: 14px;

    font-weight: bold;

    margin-bottom: 8px;
}

.qr-area img {
    width: 150px;

    height: 150px;
}


/* =========================
   NÚT PHIẾU MƯỢN
========================= */

.borrow-button {
    position: absolute;

    right: 0;

    bottom: 0;

    width: 125px;

    height: 40px;

    background: #ff3b3b;

    color: white;

    border: none;

    font-size: 12px;

    cursor: pointer;
}

.borrow-button:hover {
    background: #d90000;
}


/* =========================
   RESPONSIVE
========================= */

@media (max-width: 900px) {

    .menu {
        gap: 20px;
    }

    .menu a {
        font-size: 10px;
    }

    .logo {
        width: 150px;
    }

}


@media (max-width: 600px) {

    .navbar {
        height: auto;

        padding: 15px;

        flex-direction: column;

        gap: 15px;
    }

    .logo {
        width: auto;
    }

    .menu {
        flex-wrap: wrap;

        justify-content: center;

        gap: 15px;
    }

    .row {
        flex-direction: column;

        gap: 0;
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

    .card-info {
        flex-direction: column;

        gap: 10px;
    }

    .card-info input {
        width: 100%;
    }

    .form-container {
        width: calc(100% - 20px);

        padding: 20px;
    }
}

:root {
    --font-display: 'Roboto', Arial, Helvetica, sans-serif;
    --font-body: 'Roboto', Arial, sans-serif;
    --color-accent: #fa4b3e;
    --color-accent-dark: #e03a2d;
}

* { box-sizing: border-box; }
body {
    margin: 0;
    padding: 0;
    background: #e9ebee;
    font-family: 'Roboto', Arial, sans-serif;
}
.page-body {
    padding: 0;
}

/* ================= 0. Header ================= */
.site-header { background: #fff; border-bottom: 3px solid #fa4b3e; }
.site-header-inner {
    max-width: 1200px; margin: 0 auto;
    display: flex; align-items: center; gap: 28px;
    padding: 14px 16px;
}
.brand { display: flex; align-items: center; gap: 8px; margin-right: auto; }
.brand-mark {
    width: 34px; height: 34px; border-radius: 50%;
    background: #fa4b3e; color: #fff;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.brand-name {
    font-family: 'Roboto', Arial, sans-serif; font-weight: 900;
    font-size: 18px; letter-spacing: 0.4px; color: #10121a;
}
.main-nav { display: flex; align-items: center; gap: 22px; flex-wrap: wrap; }
.main-nav a {
    text-decoration: none; color: #10121a; font-size: 13px;
    font-weight: 700; letter-spacing: 0.3px; white-space: nowrap;
}
.main-nav a.active { color: #fa4b3e; }
.main-nav a:hover { color: #fa4b3e; }
.btn-login {
    display: inline-flex; align-items: center; gap: 8px;
    background: #fa4b3e; color: #fff; text-decoration: none;
    font-size: 13px; font-weight: 700; padding: 10px 18px;
    border-radius: 6px; white-space: nowrap; flex-shrink: 0;
}
.btn-login:hover { background: #e03a2d; }

@media (max-width: 900px) {
    .site-header-inner { flex-wrap: wrap; }
    .main-nav { order: 3; width: 100%; gap: 16px; justify-content: center; padding-top: 6px; }
}

.hero-section { position: relative; overflow: hidden; min-height: 560px; display: flex; align-items: center; }
.hero-bg {
    position: absolute; inset: 0;
    background: linear-gradient(160deg, #2a1d12 0%, #1a1410 55%, #0c0a08 100%);
    background-size: cover; background-position: center;
}
.hero-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(180deg, rgba(6,6,8,0.55) 0%, rgba(6,6,8,0.55) 60%, rgba(6,6,8,0.9) 100%);
}
.hero-content {
    position: relative; z-index: 2;
    max-width: 780px; margin: 0 auto; padding: 60px 24px 90px;
    text-align: center; color: #fff;
}
.hero-heading {
    font-size: clamp(20px, 2.9vw, 30px); font-weight: 700;
    line-height: 1.5; margin: 0 0 32px;
}
.btn-cta {
    display: inline-block; background: #fff; color: #fa4b3e;
    border: 2px solid #fa4b3e; text-decoration: none;
    font-weight: 700; font-size: 14px;
    padding: 13px 34px; border-radius: 30px; letter-spacing: 0.3px;
}
.btn-cta:hover { background: #fa4b3e; color: #fff; }

.search-bar {
    margin-top: 44px; background: #fff; border-radius: 10px;
    box-shadow: 0 12px 30px rgba(0,0,0,0.35);
    display: flex; align-items: stretch; overflow: hidden;
    max-width: 640px; margin-left: auto; margin-right: auto;
}
.search-field {
    flex: 1; display: flex; align-items: center; gap: 8px;
    padding: 16px 18px; min-width: 0;
}
.search-field label { color: #8a8f9c; font-size: 13px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.search-field svg { color: #8a8f9c; flex-shrink: 0; }
.search-field-date { flex: 1.1; }
.search-divider { width: 1px; background: #e6e6e6; margin: 12px 0; }
.btn-search {
    border: none; background: #fa4b3e; color: #fff;
    font-weight: 700; font-size: 14px; padding: 0 26px;
    cursor: pointer; flex-shrink: 0;
}
.btn-search:hover { background: #e03a2d; }

@media (max-width: 640px) {
    .hero-content { padding: 40px 18px 60px; }
    .search-bar { flex-direction: column; border-radius: 14px; max-width: 100%; }
    .search-divider { display: none; }
    .search-field { border-bottom: 1px solid #eee; padding: 14px 18px; }
    .btn-search { padding: 16px; }
}

.section-header {
    background: #fff;
    text-align: center;
    padding: 22px 20px;
    border-bottom: 2px solid #0a0a0a;
}
.section-header h1 {
    margin: 0;
    font-family: 'Roboto', Arial, Helvetica, sans-serif;
    font-weight: 900;
    font-size: clamp(20px, 3.6vw, 36px);
    color: #fa4b3e;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

.btn-more {
    display: inline-block;
    padding: 10px 26px;
    background: #fa4b3e;
    color: #fff;
    font-weight: 700;
    font-size: 13px;
    text-decoration: none;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    letter-spacing: 0.3px;
}
.btn-more:hover { background: #e03a2d; }

/* ================= 1. Thẻ sách nổi bật (Harry Potter) ================= */
.book-showcase {
    max-width: 100%;
    margin: 0;
    background: #fff;
    border: none;
    overflow: hidden;
}
.showcase-body { display: flex; flex-wrap: wrap; min-height: 520px; }
.showcase-cover {
    flex: 1 1 300px;
    max-width: 400px;
    position: relative;
    overflow: hidden;
    background: #10121a;
}
.showcase-cover img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    display: block;
    min-height: 380px;
}
.showcase-info {
    flex: 1 1 600px;
    position: relative;
    color: #fff;
    padding: 40px 44px;
    overflow: hidden;
    background: #10121a;
}
.info-bg {
    position: absolute;
    inset: 0;
    display: flex;
    z-index: 0;
}
.info-bg .bg-item {
    flex: 1;
    background-size: cover;
    background-position: center;
}
.info-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(120deg, rgba(8,10,16,0.94) 0%, rgba(8,10,16,0.8) 55%, rgba(8,10,16,0.94) 100%);
    z-index: 1;
}
.info-content { position: relative; z-index: 2; }
.info-content h2 {
    margin: 0 0 26px; font-size: clamp(19px, 2.4vw, 25px);
    letter-spacing: 0.5px; text-transform: uppercase; font-weight: 800;
}
.info-row { display: flex; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 20px; font-size: 16px; }
.info-row .label { color: #e2e5ec; min-width: 150px; flex-shrink: 0; }
.info-row .value { color: #fff; }

.tag {
    display: inline-block; padding: 8px 16px; border-radius: 4px;
    font-size: 12.5px; font-weight: 700; letter-spacing: 0.5px;
    text-transform: uppercase; color: #fff; white-space: nowrap;
}
.tag-navy   { background: #2a3348; border: 1px solid rgba(255,255,255,0.08); }
.tag-green  { background: #1f7a4d; }
.tag-blue   { background: #1c6fa5; }
.tag-teal   { background: #125a63; }
.tag-red    { background: #7a2b2b; }
.tag-brown  { background: #8a5a2c; }
.tag-amber  { background: #b4791f; }
.tag-purple { background: #5b4b8a; }
.tag-gray   { background: #3a3f4b; }

@media (max-width: 640px) {
    .showcase-cover { max-width: 100%; }
    .showcase-cover img { min-height: 260px; }
    .showcase-info { padding: 28px 24px; }
    .info-row .label { min-width: 100%; }
}

/* ================= 2. Carousel "Sắp ra mắt" ================= */
.carousel-section {
    max-width: 100%;
    margin: 0;
    background: #fff;
    border: none;
    overflow: hidden;
}
.carousel-track-wrap { display: flex; background: #000; }
.carousel-track {
    flex: 1;
    display: flex;
    height: 420px;
    background: #000;
}
.carousel-track img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    object-position: center;
    display: block;
}
.carousel-item {
    flex: 1;
    height: 100%;
    background-size: contain;
    background-position: center;
    background-repeat: no-repeat;
}
.carousel-dots {
    width: 46px; background: #52565c;
    display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 16px;
}
.carousel-dots span { width: 9px; height: 9px; border-radius: 50%; background: #0c0c0c; display: block; }
.carousel-footer { background: #0c0c0c; padding: 14px; text-align: center; }

@media (max-width: 640px) {
    .carousel-track { height: 220px; }
}

/* ================= 3. Lưới sách ================= */
.grid-section {
    max-width: 100%;
    margin: 0;
    background: #fff;
    border: none;
    overflow: hidden;
}
.grid-body {
    display: flex; flex-wrap: wrap; gap: 14px;
    padding: 16px; background: #ececec;
}
.grid-item { flex: 1 1 200px; display: flex; flex-direction: column; align-items: center; }
.grid-item img { width: 100%; aspect-ratio: 3 / 4; object-fit: cover; display: block; }
.grid-item .btn-more { margin-top: 12px; }

/* ================= 4. Footer ================= */
.site-footer { background: #0c0c0c; color: #cfd2d8; margin-top: 8px; }
.site-footer-inner {
    max-width: 1030px; margin: 0 auto; padding: 30px 20px;
    display: flex; flex-wrap: wrap; justify-content: space-between;
    align-items: flex-start; gap: 24px;
}
.site-footer .brand-name { color: #fff; }
.footer-brand { display: flex; flex-direction: column; gap: 12px; }
.footer-line { display: flex; align-items: center; gap: 10px; font-size: 13.5px; color: #b7bac2; }
.footer-line svg { color: #fa4b3e; flex-shrink: 0; }
.footer-social { display: flex; flex-direction: column; align-items: flex-end; gap: 12px; }
.footer-social-label { font-size: 13px; color: #9a9da5; }
.footer-social-icons { display: flex; gap: 10px; }
.footer-social-icons a {
    width: 34px; height: 34px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    background: #1c1c1c; color: #fff; text-decoration: none;
}
.footer-social-icons a:hover { background: #fa4b3e; }

@media (max-width: 640px) {
    .site-footer-inner { flex-direction: column; align-items: flex-start; }
    .footer-social { align-items: flex-start; }
}

/* ================= 5. Trang "Danh sách sách" =================
   (nối thêm từ bản refactor trước — cần cho danh-sach-sach.php) */
.catalog-hero {
    position: relative; overflow: hidden;
    min-height: 300px; display: flex; flex-direction: column; justify-content: flex-end;
}
.catalog-hero-bg {
    position: absolute; inset: 0;
    background: linear-gradient(160deg, #2a2118 0%, #171310 55%, #0a0908 100%);
    background-size: cover; background-position: center;
}
.catalog-hero-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(180deg, rgba(6,6,8,0.55) 0%, rgba(6,6,8,0.35) 45%, rgba(6,6,8,0.92) 100%);
}
.catalog-hero-content {
    position: relative; z-index: 2;
    padding: 56px 24px 40px;
}
.breadcrumb {
    margin: 0; font-family: var(--font-display); font-weight: 800;
    font-size: clamp(20px, 3.4vw, 32px); letter-spacing: 0.3px;
}
.breadcrumb .crumb-active { color: var(--color-accent); }
.breadcrumb .crumb { color: #fff; }
.breadcrumb .crumb-sep { color: #fff; margin: 0 10px; font-weight: 500; }

.quick-search {
    position: relative; z-index: 2;
    display: flex; align-items: center; justify-content: space-between; gap: 16px;
    flex-wrap: wrap;
    padding: 18px 24px; background: rgba(8,8,10,0.55);
}
.quick-search-text { color: #fff; font-size: 14px; }
.quick-search-text strong { font-weight: 700; margin-right: 6px; }
.quick-search-text span { color: #c9ccd3; }
.quick-search .btn-search { border-radius: 4px; padding: 12px 30px; flex-shrink: 0; }

@media (max-width: 640px) {
    .catalog-hero-content { padding: 40px 18px 24px; }
    .quick-search { padding: 16px 18px; }
}

.catalog-body { background: #101116; padding: 28px 16px 44px; }

.sort-bar { max-width: 1030px; margin: 0 auto 30px; }
.sort-label {
    display: block; color: #cfd2d8; font-size: 14px; margin-bottom: 12px;
}
.sort-tabs {
    display: flex; flex-wrap: wrap; gap: 6px;
    background: #fff; border-radius: 0; padding: 6px;
}
.sort-tab {
    border: none; background: transparent; color: #4a4d55;
    font-family: var(--font-body); font-weight: 700; font-size: 13px;
    padding: 10px 18px; border-radius: 0; cursor: pointer;
}
.sort-tab.active { background: var(--color-accent); color: #fff; }
.sort-tab:not(.active):hover { background: #f1f1f1; }

.book-category { max-width: 100%; margin: 0 0 34px; }
.category-label {
    display: inline-block; background: #f2994a; color: #1a1206;
    font-family: var(--font-body); font-weight: 800; font-size: 13px;
    text-transform: uppercase; letter-spacing: 0.4px;
    padding: 10px 22px; border-radius: 0; margin-bottom: 14px;
}
.category-track-wrap { position: relative; background: #000; border-radius: 0; overflow: hidden; }
.category-track {
    display: flex; gap: 0;
    overflow-x: auto; scroll-snap-type: x mandatory;
    scrollbar-width: none;
    -ms-overflow-style: none; 
}
.category-track::-webkit-scrollbar {
    display: none;
}
.category-track img {
    flex: 0 0 38%; width: 38%; aspect-ratio: 3 / 4;
    object-fit: cover; display: block; scroll-snap-align: start;
}
.category-dots {
    position: absolute; top: 0; right: 0; bottom: 0;
    width: 56px;
    background: linear-gradient(to right, rgba(0,0,0,0) 0%, rgba(0,0,0,0.6) 55%);
    display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px;
    z-index: 2;
    pointer-events: none; /* để không chặn thao tác cuộn/click lên ảnh bên dưới */
}
.category-dots span {
    width: 7px; height: 7px; border-radius: 50%;
    background: #fff;
    display: block;
}

@media (max-width: 640px) {
    .category-track img { flex: 0 0 50%; width: 50%; }
}
/* =========================
   FOOTER PHIEU MUON
========================= */

.footer-black {
    width: 100%;
    background-color: #000 !important;
    margin-top: 50px;
}

.footer-black .site-footer {
    width: 100%;
    background-color: #000 !important;
    color: #fff !important;
    margin: 0 !important;
}

.footer-black .site-footer-inner {
    background-color: #000 !important;
}

.footer-black .site-footer .brand-name {
    color: #fff !important;
}

.footer-black .site-footer .footer-line {
    color: #fff !important;
}

.footer-black .site-footer .footer-social-label {
    color: #fff !important;
}

.footer-black .site-footer .footer-social-icons a {
    background-color: #fa4b3e !important;
    color: #fff !important;
}
    </style>

</head>

<body>


<!-- =====================================================
     THANH MENU
====================================================== -->

<nav class="navbar">

    <div class="logo">
        📚 <span>THƯ VIỆN</span>
    </div>


    <div class="menu">

        <a href="index.php">
            TRANG CHỦ
        </a>

        <a href="gioithieu.php">
            GIỚI THIỆU
        </a>

        <a href="sach.php">
            SÁCH
        </a>

        <a href="#">
            THỂ LOẠI
        </a>

        <a href="lienhe.php">
            LIÊN HỆ
        </a>

    </div>


    <button class="login">
        ĐĂNG NHẬP
    </button>

</nav>



<!-- =====================================================
     BANNER
====================================================== -->

<section class="banner">

    <div class="banner-overlay"></div>

    <h1>
        Phiếu mượn
    </h1>

</section>



<!-- =====================================================
     FORM
====================================================== -->

<main class="form-container">


    <!-- HỌ TÊN + NGÀY SINH -->

    <div class="row">


        <div class="form-group name">

            <label>
                HỌ VÀ TÊN
            </label>

            <input type="text">

        </div>



        <div class="form-group birthday">

            <label>
                NGÀY SINH
            </label>

            <input
                type="text"
                placeholder="dd/mm/yyyy"
            >

        </div>


    </div>



    <!-- EMAIL -->

    <div class="form-group">

        <label>
            EMAIL
        </label>

        <input type="email">

    </div>



    <!-- NỘI DUNG -->

    <div class="form-group">

        <label>
            NỘI DUNG
        </label>


        <div class="address">

            <input
                type="text"
                placeholder="Thành phố"
            >

            <input
                type="text"
                placeholder="Xã"
            >

            <input
                type="text"
                placeholder="Địa chỉ chi tiết"
            >

        </div>

    </div>



    <!-- ĐƯỜNG KẺ -->

    <hr>



    <!-- =====================================================
         THANH TOÁN
    ====================================================== -->

    <section class="payment">

        <h2>
            Thông tin thanh toán
        </h2>



        <!-- SỐ THẺ -->

        <input
            class="card-number"
            type="text"
            placeholder="Số thẻ tín dụng"
        >



        <!-- MÃ THẺ -->

        <div class="card-info">

            <input
                type="text"
                placeholder="Mã số thẻ"
            >

            <input
                type="text"
                placeholder="mã CVV"
            >

            <input
                type="text"
                placeholder="Hết hạn vào"
            >

        </div>



        <!-- QR -->

        <div class="qr-area">

            <p>
                Thanh toán qua QR
            </p>


            <img
                src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=ThanhToanThuVien"
                alt="QR thanh toán"
            >

        </div>



        <!-- NÚT -->

        <button class="borrow-button">
            PHIẾU MƯỢN
        </button>


    </section>


</main>



<!-- =====================================================
     FOOTER
====================================================== -->

<div class="footer-black">
    <?php include 'footer.php'; ?>
</div>


</body>

</html>