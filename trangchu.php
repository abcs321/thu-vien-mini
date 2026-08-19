<?php

// ============================================================
// PHẦN PHP XỬ LÝ DỮ LIỆU
// ============================================================

session_start();


// ------------------------------------------------------------
// HÀM BẢO VỆ DỮ LIỆU KHI HIỂN THỊ
// ------------------------------------------------------------

function e($value)
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}


// ------------------------------------------------------------
// KHỞI TẠO DỮ LIỆU THƯ VIỆN (DEMO)
// ------------------------------------------------------------

if (!isset($_SESSION["library"])) {

    $_SESSION["library"] = [

        "totalBooks" => 20000,

        "totalMembers" => 1000,

        "totalBorrowedBooks" => 100,

        "totalBorrowTimes" => 14,

        "totalLostBooks" => 0,

        "totalDamagedBooks" => 0,

        "activities" => [

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

        ]

    ];
}


// ------------------------------------------------------------
// LẤY DỮ LIỆU RA BIẾN
// ------------------------------------------------------------

$totalBooks         = $_SESSION["library"]["totalBooks"];
$totalMembers        = $_SESSION["library"]["totalMembers"];
$totalBorrowedBooks  = $_SESSION["library"]["totalBorrowedBooks"];
$totalBorrowTimes    = $_SESSION["library"]["totalBorrowTimes"];
$totalLostBooks      = $_SESSION["library"]["totalLostBooks"];
$totalDamagedBooks   = $_SESSION["library"]["totalDamagedBooks"];
$activities          = $_SESSION["library"]["activities"];


// ------------------------------------------------------------
// (Ô XỬ LÝ FORM SẼ ĐƯỢC HOÀN THIỆN Ở BƯỚC LÀM CHỨC NĂNG)
// Hiện tại chỉ giữ khung để không phá giao diện.
// ------------------------------------------------------------

$message     = "";
$messageType = "";


// ------------------------------------------------------------
// TIÊU ĐỀ TRANG
// ------------------------------------------------------------

$pageTitle = "Trang chủ - Thư viện";

?>
<!DOCTYPE html>
<html lang="vi">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= e($pageTitle) ?></title>

    <link rel="stylesheet" href="thuvien.css">


    <!-- =====================================================
         CSS RIÊNG CHO TRANG QUẢN TRỊ (TRANGCHU.PHP)
         Chỉ bổ sung phần khác biệt so với thuvien.css dùng chung,
         không sửa thuvien.css để không ảnh hưởng các trang khác.
         ===================================================== -->

    <style>

        body {
            padding-top: 18px;
        }


        /* NHÃN "giao diện admin/thủ thư" */

        .admin-label {
            width: 700px;
            max-width: 95%;
            margin: 0 auto 10px;
            font-size: 13px;
            font-weight: 600;
            color: #eee;
        }


        /* HEADER RIÊNG CHO TRANG QUẢN TRỊ (rút gọn menu) */

        .admin-header {
            width: 700px;
            max-width: 95%;
            margin: 0 auto;
            background-color: white;
        }

        .admin-nav {
            display: flex;
            align-items: center;
            gap: 34px;
            height: 60px;
            padding: 0 20px;
        }

        .admin-nav a {
            text-decoration: none;
            font-size: 13px;
            letter-spacing: 0.3px;
            color: #999;
            font-weight: 600;
        }

        .admin-nav a.active {
            color: #111;
        }


        /* THÔNG BÁO KẾT QUẢ XỬ LÝ PHP (dùng khi làm chức năng) */

        .php-message {
            margin: 0 0 15px;
            padding: 12px 15px;
            border-radius: 6px;
            font-size: 13px;
        }

        .php-message.success {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .php-message.error {
            background-color: #fdecea;
            color: #c0392b;
        }


        /* Ô SỐ SÁCH MƯỢN CÓ CHÚ THÍCH NHỎ ĐI KÈM SỐ */

        .stat-sub {
            font-size: 11px;
            font-weight: normal;
            color: #999;
        }


        /* FORM ĐIỀU CHỈNH (ẩn - hiện khi bấm nút, sẽ nối chức năng sau) */

        .adjust-form {
            display: none;
            background-color: white;
            padding: 18px;
            margin: 0 10px 20px;
            box-shadow: 0 1px 6px rgba(0, 0, 0, 0.2);
        }

        .adjust-form h3 {
            font-size: 13px;
            margin-bottom: 12px;
        }

        .adjust-form input {
            width: 100%;
            padding: 9px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-bottom: 10px;
            box-sizing: border-box;
        }

        .adjust-form button {
            border: none;
            background-color: #168bea;
            color: white;
            padding: 9px 16px;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
        }

    </style>

</head>

<body>


    <!-- NHÃN VAI TRÒ TRANG -->

    <p class="admin-label">
        giao diện admin/thủ thư
    </p>


    <!-- =========================
         HEADER RÚT GỌN (QUẢN TRỊ)
    ========================== -->

    <header class="admin-header">

        <nav class="admin-nav">

            <a href="trangchu.php" class="active">
                TRANG CHỦ
            </a>

            <a href="sach.php">
                SÁCH
            </a>

            <a href="#">
                THÀNH VIÊN
            </a>

        </nav>

    </header>


    <!-- =========================
         NỘI DUNG CHÍNH
    ========================== -->

    <main class="container">


        <?php if ($message !== ""): ?>

            <div class="php-message <?= e($messageType) ?>">
                <?= e($message) ?>
            </div>

        <?php endif; ?>


        <!-- KHU VỰC THỐNG KÊ -->

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
                        <span class="stat-sub">trong hôm nay</span>
                    </div>

                    <div class="stat-note warning">
                        có sách quá hạn trả
                    </div>

                </div>


            </div>

        </section>


        <!-- HOẠT ĐỘNG + NÚT CHỨC NĂNG -->

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
                            <?= e($activity["book"]) ?> - <?= e($activity["time"]) ?>
                        </span>

                        <span class="status <?= e($activity["statusClass"]) ?>">
                            <?= e($activity["status"]) ?>
                        </span>

                    </div>

                <?php endforeach; ?>

            </div>


            <!-- NÚT CHỨC NĂNG (nối logic ở bước sau) -->

            <div class="actions">

                <button
                    type="button"
                    onclick="document.getElementById('adjustForm').style.display='block';"
                >
                    ĐIỀU CHỈNH SÁCH
                </button>

                <button type="button">
                    DANH MỤC SÁCH
                </button>

                <button type="button">
                    TƯƠNG TÁC ADMIN
                </button>

            </div>

        </section>


        <!-- FORM ĐIỀU CHỈNH TỔNG SỐ SÁCH (ẩn mặc định) -->

        <section id="adjustForm" class="adjust-form">

            <h3>
                ĐIỀU CHỈNH TỔNG SỐ SÁCH
            </h3>

            <form method="POST">

                <input type="hidden" name="action" value="update_books">

                <input
                    type="number"
                    name="totalBooks"
                    min="0"
                    value="<?= e($totalBooks) ?>"
                    required
                >

                <button type="submit">
                    LƯU THAY ĐỔI
                </button>

            </form>

        </section>


        <!-- THỐNG KÊ CUỐI -->

        <section class="bottom-stat">

            <div class="bottom-item">

                <h3>
                    SỐ LƯỢT MƯỢN
                </h3>

                <div class="bottom-number">
                    <?= number_format($totalBorrowTimes) ?>
                </div>

            </div>


            <div class="bottom-item">

                <h3>
                    SỐ SÁCH MẤT
                </h3>

                <div class="bottom-number">
                    <?= number_format($totalLostBooks) ?>
                </div>

            </div>


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
