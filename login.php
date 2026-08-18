<?php

// ==========================
// KHỞI TẠO DỮ LIỆU
// ==========================

$username = "";

$errors = [];
$success = "";


// ==========================
// XỬ LÝ FORM
// ==========================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Lấy dữ liệu
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";


    // ==========================
    // VALIDATE TÊN ĐĂNG NHẬP
    // ==========================

    if ($username === "") {

        $errors["username"] =
            "Vui lòng nhập tên đăng nhập.";

    } elseif (mb_strlen($username) < 4) {

        $errors["username"] =
            "Tên đăng nhập phải có ít nhất 4 ký tự.";

    } elseif (mb_strlen($username) > 30) {

        $errors["username"] =
            "Tên đăng nhập không được quá 30 ký tự.";

    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {

        $errors["username"] =
            "Tên đăng nhập chỉ được chứa chữ cái, số và dấu gạch dưới.";
    }


    // ==========================
    // VALIDATE MẬT KHẨU
    // ==========================

    if ($password === "") {

        $errors["password"] =
            "Vui lòng nhập mật khẩu.";

    } elseif (strlen($password) < 6) {

        $errors["password"] =
            "Mật khẩu phải có ít nhất 6 ký tự.";

    } elseif (strlen($password) > 50) {

        $errors["password"] =
            "Mật khẩu không được quá 50 ký tự.";
    }


    // ==========================
    // NẾU KHÔNG CÓ LỖI
    // ==========================

    if (empty($errors)) {

        /*
         * Hiện tại chưa kết nối MySQL.
         * Sau này sẽ kiểm tra:
         * username + password trong database.
         */

        $success = "Thông tin đăng nhập hợp lệ.";

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

    <title>Đăng nhập - Thư viện</title>

    <link rel="stylesheet" href="style.css">

</head>


<body>


<!-- ==========================
     HEADER
     ========================== -->

<header class="site-header">

    <div class="site-header-inner">


        <!-- LOGO -->

        <div class="brand">

            <span class="brand-mark">

                <svg
                    viewBox="0 0 24 24"
                    width="22"
                    height="22"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >

                    <circle
                        cx="10.5"
                        cy="10.5"
                        r="6.5"
                    />

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


        <!-- MENU -->

        <nav class="main-nav">

            <a href="index.php">
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


        <!-- NÚT ĐĂNG NHẬP -->

        <a href="login.php" class="btn-login">

            <svg
                viewBox="0 0 24 24"
                width="15"
                height="15"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
            >

                <circle
                    cx="12"
                    cy="8"
                    r="3.4"
                />

                <path
                    d="M4.5 20c1.4-3.6 4.4-5.6 7.5-5.6s6.1 2 7.5 5.6"
                />

            </svg>

            Đăng nhập

        </a>

    </div>

</header>


<!-- ==========================
     TRANG ĐĂNG NHẬP
     ========================== -->

<main class="login-page">

    <div class="login-box">


        <h1>
            ĐĂNG NHẬP
        </h1>


        <p class="login-subtitle">
            Đăng nhập để sử dụng thư viện
        </p>


        <!-- THÔNG BÁO -->

        <?php if ($success !== ""): ?>

            <div class="success-message">
                <?= htmlspecialchars($success) ?>
            </div>

        <?php endif; ?>


        <!-- FORM -->

        <form
            method="POST"
            action=""
        >


            <!-- =====================
                 TÊN ĐĂNG NHẬP
                 ===================== -->

            <div class="form-group">

                <label for="username">
                    Tên đăng nhập
                </label>

                <input
                    type="text"
                    id="username"
                    name="username"
                    value="<?= htmlspecialchars($username) ?>"
                    placeholder="Nhập tên đăng nhập"
                >

                <?php if (isset($errors["username"])): ?>

                    <div class="error-message">

                        <?= htmlspecialchars(
                            $errors["username"]
                        ) ?>

                    </div>

                <?php endif; ?>

            </div>


            <!-- =====================
                 MẬT KHẨU
                 ===================== -->

            <div class="form-group">

                <label for="password">
                    Mật khẩu
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Nhập mật khẩu"
                >

                <?php if (isset($errors["password"])): ?>

                    <div class="error-message">

                        <?= htmlspecialchars(
                            $errors["password"]
                        ) ?>

                    </div>

                <?php endif; ?>

            </div>


            <!-- =====================
                 GHI NHỚ
                 ===================== -->

            <div class="login-options">

                <label>

                    <input
                        type="checkbox"
                        name="remember"
                    >

                    Ghi nhớ đăng nhập

                </label>


                <a href="#">
                    Quên mật khẩu?
                </a>

            </div>


            <!-- =====================
                 NÚT ĐĂNG NHẬP
                 ===================== -->

            <button
                type="submit"
                class="form-btn"
            >
                ĐĂNG NHẬP
            </button>


        </form>


        <!-- =====================
             ĐĂNG KÝ
             ===================== -->

        <p class="register-link">

            Chưa có tài khoản?

            <a href="register.php">
                Đăng ký
            </a>

        </p>


    </div>

</main>


</body>

</html>