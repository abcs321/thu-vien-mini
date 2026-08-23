<?php

session_start();


// =====================================================
// 1. KẾT NỐI CSDL
// =====================================================

require_once __DIR__ . '/database.php';


// =====================================================
// 2. HÀM CHỐNG XSS
// =====================================================

function e($value)
{
    return htmlspecialchars(
        $value ?? '',
        ENT_QUOTES,
        'UTF-8'
    );
}


// =====================================================
// 3. SỐ LIỆU THẬT — LẤY TỪ CSDL (bảng sach, doc_gia)
// =====================================================

// Tổng số lượng sách vật lý trong kho (cộng dồn cột so_luong)
$tongSoSach = (int)$conn
    ->query("SELECT COALESCE(SUM(so_luong), 0) FROM sach")
    ->fetchColumn();

// Tổng số thành viên đang có trong bảng doc_gia
$soThanhVien = (int)$conn
    ->query("SELECT COUNT(*) FROM doc_gia")
    ->fetchColumn();

// Tổng lượt mượn cộng dồn từ trước tới nay (cột luot_muon có sẵn trong bảng sach)
$tongLuotMuon = (int)$conn
    ->query("SELECT COALESCE(SUM(luot_muon), 0) FROM sach")
    ->fetchColumn();


// =====================================================
// 4. SỐ LIỆU MẪU (CHƯA CÓ BẢNG TRONG CSDL)
// =====================================================
// CSDL nhóm trưởng gửi KHÔNG có bảng lưu phiếu mượn/trả sách,
// nên các số dưới đây là DỮ LIỆU MẪU (cố định), chưa lấy được
// từ CSDL thật. Khi nào nhóm trưởng bổ sung 1 bảng kiểu
// "phieu_muon" (id, id_doc_gia, id_sach, ngay_muon, ngay_tra,
// trang_thai...) thì chỉ cần thay các dòng có đánh dấu
// "// MẪU" bên dưới bằng câu truy vấn SELECT thật.

$soSachMuonHomNay = 100; // MẪU
$coSachQuaHan = true;     // MẪU

$hoatDongGanDay = [       // MẪU
    [
        'code' => 'M01',
        'name' => 'Lê Đình Nam',
        'detail' => 'OPM tập 1 - 15:03',
        'status' => 'TRẢ SÁCH',
        'status_class' => 'done',
    ],
    [
        'code' => 'M02',
        'name' => 'Đinh Hào Kiệt',
        'detail' => 'Harry Potter và hòn đá phù thủy - 14:05',
        'status' => 'QUÁ HẠN',
        'status_class' => 'late',
    ],
];

$soSachMat = 0;    // MẪU
$soSachHong = 0;   // MẪU

?>
<!DOCTYPE html>

<html lang="vi">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Trang chủ - Quản lý thư viện</title>


<style>

/* ==================================================
   RESET
================================================== */

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}


/* ==================================================
   BODY
================================================== */

body {
    padding-top: 18px;
    padding-bottom: 30px;
    min-height: 100vh;
    background-color: #17181a;
    background-image:
        linear-gradient(
            rgba(10, 10, 12, 0.78),
            rgba(10, 10, 12, 0.78)
        ),
        url("bg-thuvien.jpg");
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: fixed;
}

/* ==================================================
   KHUNG CHÍNH
================================================== */

.page {
    width: 720px;
    min-height: 715px;
    margin: 46px auto;
    background: #e3e3e3;
    overflow: hidden;
}


/* ==================================================
   HEADER (menu chính của cả web)
================================================== */

.site-header {
    width: 100%;
    background: #ffffff;
    border-bottom: 3px solid #ef5350;
}

.site-header-inner {
    width: 1470px;
    max-width: calc(100% - 60px);
    height: 78px;
    margin: 0 auto;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 22px;
}

.brand {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
}

.brand-mark {
    width: 43px;
    height: 43px;
    border-radius: 50%;
    background: #ef5350;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
}

.brand-mark svg {
    width: 22px;
    height: 22px;
}

.brand-name {
    font-size: 25px;
    font-weight: 800;
    letter-spacing: .5px;
    white-space: nowrap;
}

.main-nav {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 18px;
    flex: 1;
}

.main-nav a {
    color: #333;
    text-decoration: none;
    font-size: 16px;
    font-weight: 700;
    white-space: nowrap;
}

.main-nav a:hover,
.main-nav a.active {
    color: #ef5350;
}

@media (max-width: 760px) {
    .site-header-inner {
        height: auto;
        min-height: 64px;
        padding: 10px 15px;
        flex-wrap: wrap;
    }

    .main-nav {
        width: 100%;
        justify-content: center;
        flex-wrap: wrap;
        gap: 12px;
    }
}


/* ==================================================
   PAGE NAV (TRANG CHỦ / SÁCH / THÀNH VIÊN)
================================================== */

.page-nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #ffffff;
    padding: 14px 30px;
}

.page-nav a {
    text-decoration: none;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.4px;
    color: #222;
    text-transform: uppercase;
}

.page-nav a:hover {
    color: #ef5350;
}

.page-nav a.active {
    color: #ef5350;
}


/* ==================================================
   BANNER + THẺ THỐNG KÊ
================================================== */

.dashboard-banner {
    padding: 34px 24px 46px;
    background-image:
        linear-gradient(
            rgba(10, 10, 12, 0.55),
            rgba(10, 10, 12, 0.55)
        ),
        url("bg-thuvien.jpg");
    background-size: cover;
    background-position: center;
}

.stat-row {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
}

.stat-card {
    flex: 1;
    min-width: 150px;
    background: #ffffff;
    border-radius: 6px;
    padding: 16px 12px;
    text-align: center;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
}

.stat-card.highlight {
    border: 2px solid #2f6fed;
    box-shadow: 0 0 0 3px rgba(47, 111, 237, 0.18);
}

.stat-label {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.4px;
    text-transform: uppercase;
    color: #333;
    margin-bottom: 8px;
}

.stat-number {
    font-size: 22px;
    font-weight: 800;
    color: #111;
    margin-bottom: 4px;
}

.stat-sub {
    font-size: 11px;
    color: #888;
}

.stat-sub.alert {
    color: #ef5350;
    font-weight: 700;
}


/* ==================================================
   HOẠT ĐỘNG GẦN ĐÂY + THAO TÁC NHANH
================================================== */

.dashboard-content {
    display: flex;
    gap: 16px;
    padding: 18px;
    align-items: flex-start;
}

.activity-box {
    flex: 1.5;
    background: #ffffff;
    border-radius: 6px;
    padding: 16px;
}

.activity-title {
    color: #ef5350;
    font-weight: 700;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    margin-bottom: 12px;
}

.activity-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 0;
    border-bottom: 1px solid #eee;
    font-size: 12px;
}

.activity-row:last-child {
    border-bottom: none;
}

.badge-code {
    background: #333333;
    color: #4caf50;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 4px;
    flex-shrink: 0;
}

.activity-name {
    font-weight: 700;
    white-space: nowrap;
    flex-shrink: 0;
}

.activity-detail {
    flex: 1;
    color: #555;
}

.badge-status {
    font-size: 10px;
    font-weight: 700;
    padding: 5px 10px;
    border-radius: 4px;
    color: #ffffff;
    white-space: nowrap;
    text-transform: uppercase;
}

.badge-status.done {
    background: #43a047;
}

.badge-status.late {
    background: #ef5350;
}

.quick-actions {
    flex: 1;
    background: #ffffff;
    border-radius: 6px;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.quick-actions a {
    display: block;
    text-align: center;
    background: #d9d9d9;
    color: #222;
    text-decoration: none;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    padding: 12px;
    border-radius: 4px;
}

.quick-actions a:hover {
    background: #c7c7c7;
}


/* ==================================================
   THỐNG KÊ CUỐI TRANG
================================================== */

.bottom-stats {
    padding: 6px 18px 26px;
    text-align: center;
}

.bottom-stats-labels,
.bottom-stats-values {
    display: flex;
    justify-content: center;
    gap: 60px;
    flex-wrap: wrap;
}

.bottom-stats-labels {
    font-weight: 700;
    font-size: 14px;
    text-transform: uppercase;
    margin-bottom: 14px;
}

.bottom-stats-values {
    margin-bottom: 4px;
}

.bottom-stat-box {
    background: #d9d9d9;
    width: 100px;
    height: 84px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    font-weight: 800;
    border-radius: 4px;
}


/* ==================================================
   RESPONSIVE
================================================== */

@media (max-width: 760px) {
    .page {
        width: calc(100% - 30px);
        margin: 20px auto;
    }

    .stat-row {
        flex-direction: column;
    }

    .dashboard-content {
        flex-direction: column;
    }

    .bottom-stats-labels,
    .bottom-stats-values {
        gap: 24px;
    }
}

</style>

</head>

<body>

<header class="site-header">

    <div class="site-header-inner">

        <div class="brand">

            <span class="brand-mark">
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <circle cx="10.5" cy="10.5" r="6.5"/>
                    <line
                        x1="15.3"
                        y1="15.3"
                        x2="20.5"
                        y2="20.5"
                    />
                </svg>
            </span>

            <span class="brand-name">
                THƯ VIỆN
            </span>

        </div>

        <nav class="main-nav">

            <a href="trangchu.php" class="active">
                TRANG CHỦ
            </a>

            <a href="#">
                VỀ CHÚNG TÔI
            </a>

            <a href="danh-sach-sach.php">
                DANH SÁCH SÁCH
            </a>

            <a href="#">
                PHIẾU MƯỢN
            </a>

            <a href="#">
                KHÁM PHÁ
            </a>

            <a href="#">
                LIÊN LẠC
            </a>

        </nav>

    </div>

</header>


<div class="page">


    <!-- ==================================================
         MENU PHỤ TRONG TRANG
    ================================================== -->

    <nav class="page-nav">

        <a href="trangchu.php" class="active">
            Trang chủ
        </a>

        <a href="danh-sach-sach.php">
            Sách
        </a>

        <a href="thanhvien.php">
            Thành viên
        </a>

    </nav>


    <!-- ==================================================
         BANNER + 3 THẺ THỐNG KÊ CHÍNH
    ================================================== -->

    <div class="dashboard-banner">

        <div class="stat-row">

            <div class="stat-card">
                <div class="stat-label">Tổng số sách</div>
                <div class="stat-number">
                    <?= e(number_format($tongSoSach)) ?>
                </div>
                <div class="stat-sub">
                    Tổng số lượng trong kho
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Số thành viên</div>
                <div class="stat-number">
                    <?= e(number_format($soThanhVien)) ?>
                </div>
                <div class="stat-sub">
                    Đang hoạt động
                </div>
            </div>

            <div class="stat-card highlight">
                <div class="stat-label">Số sách mượn</div>
                <div class="stat-number">
                    <?= e($soSachMuonHomNay) ?> trong hôm nay
                </div>
                <?php if ($coSachQuaHan): ?>
                    <div class="stat-sub alert">
                        có sách quá hạn trả
                    </div>
                <?php endif; ?>
            </div>

        </div>

    </div>


    <!-- ==================================================
         HOẠT ĐỘNG GẦN ĐÂY + THAO TÁC NHANH
    ================================================== -->

    <div class="dashboard-content">

        <div class="activity-box">

            <div class="activity-title">
                Hoạt động gần đây
            </div>

            <?php foreach ($hoatDongGanDay as $row): ?>

                <div class="activity-row">

                    <span class="badge-code">
                        <?= e($row['code']) ?>
                    </span>

                    <span class="activity-name">
                        <?= e($row['name']) ?>
                    </span>

                    <span class="activity-detail">
                        <?= e($row['detail']) ?>
                    </span>

                    <span class="badge-status <?= e($row['status_class']) ?>">
                        <?= e($row['status']) ?>
                    </span>

                </div>

            <?php endforeach; ?>

        </div>

        <div class="quick-actions">

            <a href="#">Điều chỉnh sách</a>
            <a href="#">Danh mục sách</a>
            <a href="thanhvien.php">Điều chỉnh người dùng</a>

        </div>

    </div>


    <!-- ==================================================
         THỐNG KÊ CUỐI TRANG
    ================================================== -->

    <div class="bottom-stats">

        <div class="bottom-stats-labels">
            <span>Số lượt mượn</span>
            <span>Số sách mất</span>
            <span>Số sách hỏng</span>
        </div>

        <div class="bottom-stats-values">

            <div class="bottom-stat-box">
                <?= e(number_format($tongLuotMuon)) ?>
            </div>

            <div class="bottom-stat-box">
                <?= e($soSachMat) ?>
            </div>

            <div class="bottom-stat-box">
                <?= e($soSachHong) ?>
            </div>

        </div>

    </div>


</div>

</body>

</html>
