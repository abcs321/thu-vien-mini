<?php

session_start();

require_once "db.php";


// ==========================
// KHỞI TẠO DỮ LIỆU
// ==========================

$username = "";
$email = "";
$password = "";
$confirm_password = "";

$errors = [];
$success = "";


// ==========================
// XỬ LÝ FORM
// ==========================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Lấy dữ liệu và loại bỏ khoảng trắng đầu/cuối
    $username = trim($_POST["username"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";


    // ==========================
    // VALIDATE TÊN ĐĂNG NHẬP
    // ==========================

    if ($username === "") {

        $errors["username"] = "Vui lòng nhập tên đăng nhập.";

    } elseif (mb_strlen($username) < 4) {

        $errors["username"] = "Tên đăng nhập phải có ít nhất 4 ký tự.";

    } elseif (mb_strlen($username) > 30) {

        $errors["username"] = "Tên đăng nhập không được quá 30 ký tự.";

    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {

        $errors["username"] =
            "Tên đăng nhập chỉ được chứa chữ cái, số và dấu gạch dưới.";

    } else {

        // Kiểm tra trùng tên đăng nhập trong database
        $stmt = $pdo->prepare(
            "SELECT id_doc_gia FROM doc_gia WHERE ten_tai_khoan = :username LIMIT 1"
        );
        $stmt->execute(["username" => $username]);

        if ($stmt->fetch()) {
            $errors["username"] = "Tên đăng nhập này đã được sử dụng.";
        }
    }


    // ==========================
    // VALIDATE EMAIL
    // ==========================

    if ($email === "") {

        $errors["email"] = "Vui lòng nhập email.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $errors["email"] = "Email không đúng định dạng.";

    } else {

        // Kiểm tra trùng email trong database
        $stmt = $pdo->prepare(
            "SELECT id_doc_gia FROM doc_gia WHERE email = :email LIMIT 1"
        );
        $stmt->execute(["email" => $email]);

        if ($stmt->fetch()) {
            $errors["email"] = "Email này đã được đăng ký.";
        }
    }


    // ==========================
    // VALIDATE MẬT KHẨU
    // ==========================

    if ($password === "") {

        $errors["password"] = "Vui lòng nhập mật khẩu.";

    } elseif (strlen($password) < 6) {

        $errors["password"] =
            "Mật khẩu phải có ít nhất 6 ký tự.";

    } elseif (strlen($password) > 50) {

        $errors["password"] =
            "Mật khẩu không được quá 50 ký tự.";
    }


    // ==========================
    // VALIDATE NHẬP LẠI MẬT KHẨU
    // ==========================

    if ($confirm_password === "") {

        $errors["confirm_password"] =
            "Vui lòng nhập lại mật khẩu.";

    } elseif ($password !== $confirm_password) {

        $errors["confirm_password"] =
            "Mật khẩu nhập lại không khớp.";
    }


    // ==========================
    // NẾU KHÔNG CÓ LỖI -> LƯU VÀO DATABASE
    // ==========================

    if (empty($errors)) {

        // Ghi chú: form này chỉ thu thập username/email/password.
        // Họ tên, ngày sinh, địa chỉ sẽ được bổ sung sau ở bước
        // "hoàn thiện hồ sơ" (theo form đầy đủ đã thiết kế trước đó),
        // nên ho_ten/ngay_sinh/... để NULL ở bước đăng ký nhanh này.

        $mat_khau_hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare(
            "INSERT INTO doc_gia (ten_tai_khoan, email, mat_khau)
             VALUES (:username, :email, :mat_khau)"
        );

        $stmt->execute([
            "username" => $username,
            "email"    => $email,
            "mat_khau" => $mat_khau_hash,
        ]);

        $success = "Đăng ký thành công!";

        // Không giữ mật khẩu sau khi đăng ký thành công
        $password = "";
        $confirm_password = "";
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

    <title>Đăng ký - Thư viện</title>

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

        <?php if (isset($_SESSION["ten_tai_khoan"])): ?>

            <span class="btn-login">
                Xin chào, <?= htmlspecialchars($_SESSION["ten_tai_khoan"]) ?>
                <?= ($_SESSION["vai_tro"] === "admin") ? " (admin)" : "" ?>
                &nbsp;|&nbsp;
                <a href="logout.php">Đăng xuất</a>
            </span>

        <?php else: ?>

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

        <?php endif; ?>

    </div>

</header>


<!-- ==========================
     TRANG ĐĂNG KÝ
     ========================== -->

<main class="register-page">

    <div class="register-box">

        <h1>ĐĂNG KÝ</h1>

        <p class="register-subtitle">
            Tạo tài khoản mới để sử dụng thư viện
        </p>


        <!-- THÔNG BÁO THÀNH CÔNG -->

        <?php if ($success !== ""): ?>

            <div class="success-message">
                <?= htmlspecialchars($success) ?>

                <br>

                <a href="login.php">Đăng nhập ngay</a>
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
                        <?= htmlspecialchars($errors["username"]) ?>
                    </div>

                <?php endif; ?>

            </div>


            <!-- =====================
                 EMAIL
                 ===================== -->

            <div class="form-group">

                <label for="email">
                    Email
                </label>

                <input
                    type="text"
                    id="email"
                    name="email"
                    value="<?= htmlspecialchars($email) ?>"
                    placeholder="Nhập email"
                >

                <?php if (isset($errors["email"])): ?>

                    <div class="error-message">
                        <?= htmlspecialchars($errors["email"]) ?>
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
                        <?= htmlspecialchars($errors["password"]) ?>
                    </div>

                <?php endif; ?>

            </div>


            <!-- =====================
                 NHẬP LẠI MẬT KHẨU
                 ===================== -->

            <div class="form-group">

                <label for="confirm_password">
                    Nhập lại mật khẩu
                </label>

                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    placeholder="Nhập lại mật khẩu"
                >

                <?php if (isset($errors["confirm_password"])): ?>

                    <div class="error-message">
                        <?= htmlspecialchars($errors["confirm_password"]) ?>
                    </div>

                <?php endif; ?>

            </div>


            <!-- =====================
                 ĐIỀU KHOẢN
                 ===================== -->

            <div class="register-options">

                <label>

                    <input
                        type="checkbox"
                        name="terms"
                        required
                    >

                    Tôi đồng ý với điều khoản sử dụng

                </label>

            </div>


            <!-- =====================
                 NÚT ĐĂNG KÝ
                 ===================== -->

            <button
                type="submit"
                class="form-btn"
            >
                ĐĂNG KÝ
            </button>


        </form>


        <!-- =====================
             LINK ĐĂNG NHẬP
             ===================== -->

        <p class="login-link">

            Đã có tài khoản?

            <a href="login.php">
                Đăng nhập
            </a>

        </p>

    </div>

</main>


</body>

</html>
