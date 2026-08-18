<?php
// ===============================
// KHỞI TẠO DỮ LIỆU TRANG CHỦ
// ===============================

$pageTitle = "Trang chủ - Thư viện";

// ===============================
// DỮ LIỆU THỐNG KÊ
// ===============================

$totalBooks = 20000;
$totalMembers = 1000;
$totalBorrowedBooks = 100;

// Thống kê cuối trang
$totalBorrowTimes = 14;
$totalLostBooks = 0;
$totalDamagedBooks = 0;


// ===============================
// DỮ LIỆU HOẠT ĐỘNG GẦN ĐÂY
// ===============================

$activities = [
    [
        "code" => "M01",
        "name" => "Lê Đình Nam",
        "book" => "OPM tập 1",
        "time" => "15:03",
        "status" => "TRẢ SÁCH",
        "statusClass" => "returned"
    ],

    [
        "code" => "M02",
        "name" => "Đinh Hào Kiệt",
        "book" => "Harry Potter và hòn đá phù thủy",
        "time" => "14:05",
        "status" => "QUÁ HẠN",
        "statusClass" => "overdue"
    ]
];


// ===============================
// HÀM BẢO VỆ DỮ LIỆU KHI HIỂN THỊ
// ===============================

function e($data)
{
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title><?= e($pageTitle) ?></title>

    <link rel="stylesheet" href="thuvien.css">

</head>

<body>

    <!-- ================= HEADER ================= -->

    <header class="site-header">

        <div class="site-header-inner">

            <!-- LOGO -->

            <div class="brand">

                <span class="brand-mark">

                    <!-- Icon kính lúp -->

                    <svg viewBox="0 0 24 24"
                         width="22"
                         height="22"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="2">

                        <circle cx="10.5"
                                cy="10.5"
                                r="6.5"/>

                        <line x1="15.3"
                              y1="15.3"
                              x2="20.5"
                              y2="20.5"/>

                    </svg>

                </span>

                <span class="brand-name">
                    THƯ VIỆN
                </span>

            </div>


            <!-- MENU -->

            <nav class="main-nav">

                <a href="trangchu.php"
                   class="active">
                    TRANG CHỦ
                </a>

                <a href="#">
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


            <!-- ĐĂNG NHẬP -->

            <a href="dangnhap.php"
               class="btn-login">

                <svg viewBox="0 0 24 24"
                     width="15"
                     height="15"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="2">

                    <circle cx="12"
                            cy="8"
                            r="3.4"/>

                    <path d="M4.5 20c1.4-3.6 4.4-5.6 7.5-5.6s6.1 2 7.5 5.6"/>

                </svg>

                Đăng nhập

            </a>

        </div>

    </header>


    <!-- ================= NỘI DUNG TRANG CHỦ ================= -->

    <main class="container">


        <!-- ================= KHU VỰC THỐNG KÊ ================= -->

        <section class="hero">

            <div class="stat-container">


                <!-- TỔNG SỐ SÁCH -->

                <div class="stat-box">

                    <div class="stat-title">
                        TỔNG SỐ SÁCH
                    </div>

                    <div class="stat-number">
                        <?= number_format($totalBooks) ?>
                    </div>

                    <div class="stat-note">
                        Chưa nhập thêm
                    </div>

                </div>


                <!-- SỐ THÀNH VIÊN -->

                <div class="stat-box">

                    <div class="stat-title">
                        SỐ THÀNH VIÊN
                    </div>

                    <div class="stat-number">
                        <?= number_format($totalMembers) ?>
                    </div>

                    <div class="stat-note">
                        +12 thành viên mới
                    </div>

                </div>


                <!-- SỐ SÁCH MƯỢN -->

                <div class="stat-box">

                    <div class="stat-title">
                        SỐ SÁCH MƯỢN
                    </div>

                    <div class="stat-number">
                        <?= number_format($totalBorrowedBooks) ?>
                    </div>

                    <div class="stat-note warning">
                        Có sách quá hạn trả
                    </div>

                </div>

            </div>

        </section>


        <!-- ================= HOẠT ĐỘNG ================= -->

        <section class="content-area">


            <!-- HOẠT ĐỘNG GẦN ĐÂY -->

            <div class="recent">

                <h3>
                    HOẠT ĐỘNG GẦN ĐÂY
                </h3>


                <?php foreach ($activities as $activity): ?>

                    <div class="activity">

                        <span class="code">
                            <?= e($activity["code"]) ?>
                        </span>

                        <span class="name">
                            <?= e($activity["name"]) ?>
                        </span>

                        <span class="book">
                            <?= e($activity["book"]) ?>
                            -
                            <?= e($activity["time"]) ?>
                        </span>

                        <span class="status <?= e($activity["statusClass"]) ?>">
                            <?= e($activity["status"]) ?>
                        </span>

                    </div>

                <?php endforeach; ?>

            </div>


            <!-- NÚT CHỨC NĂNG -->

            <div class="actions">

                <button>
                    ĐIỀU CHỈNH SÁCH
                </button>

                <button>
                    DANH MỤC SÁCH
                </button>

                <button>
                    TƯƠNG TÁC ADMIN
                </button>

            </div>

        </section>


        <!-- ================= THỐNG KÊ CUỐI ================= -->

        <section class="bottom-stat">


            <!-- SỐ LƯỢT MƯỢN -->

            <div class="bottom-item">

                <h3>
                    SỐ LƯỢT MƯỢN
                </h3>

                <div class="bottom-number">
                    <?= number_format($totalBorrowTimes) ?>
                </div>

            </div>


            <!-- SỐ SÁCH MẤT -->

            <div class="bottom-item">

                <h3>
                    SỐ SÁCH MẤT
                </h3>

                <div class="bottom-number">
                    <?= number_format($totalLostBooks) ?>
                </div>

            </div>


            <!-- SỐ SÁCH HỎNG -->

            <div class="bottom-item">

                <h3>
                    SỐ SÁCH HỎNG
                </h3>

                <div class="bottom-number">
                    <?= number_format($totalDamagedBooks) ?>
                </div>

            </div>

        </section>

    </main>

</body>

</html>
