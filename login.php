<?php

session_start();

require_once "db.php";
require_once "includes.php";

// ==========================
// KHỞI TẠO DỮ LIỆU
// ==========================

$username = "";

$errors = [];
$success = "";

// Đã đăng nhập từ trước (session cũ), không phải vừa submit form
$isLoggedIn = isset($_SESSION["ten_tai_khoan"]);


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
    // NẾU KHÔNG CÓ LỖI ĐỊNH DẠNG -> KIỂM TRA TRONG DATABASE
    // ==========================

    if (empty($errors)) {

        $stmt = $pdo->prepare(
            "SELECT id_doc_gia, ten_tai_khoan, mat_khau
             FROM doc_gia
             WHERE ten_tai_khoan = :username
             LIMIT 1"
        );

        $stmt->execute(["username" => $username]);

        $user = $stmt->fetch();


        if (!$user || !password_verify($password, $user["mat_khau"])) {

            $errors["login"] =
                "Tên đăng nhập hoặc mật khẩu không đúng.";

        } else {

            // ==========================
            // ĐĂNG NHẬP THÀNH CÔNG -> TẠO SESSION
            // ==========================
            // Phân biệt admin / user thường qua tên tài khoản:
            // tài khoản có ten_tai_khoan = 'admin' sẽ là admin.

            $vai_tro = ($user["ten_tai_khoan"] === "admin") ? "admin" : "user";

            $_SESSION["id_doc_gia"]   = $user["id_doc_gia"];
            $_SESSION["ten_tai_khoan"] = $user["ten_tai_khoan"];
            $_SESSION["vai_tro"]      = $vai_tro;

            $success = "Đăng nhập thành công! Xin chào " .
                htmlspecialchars($user["ten_tai_khoan"]) .
                ($vai_tro === "admin" ? " (admin)." : ".");
        }
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

<?php render_header($nav); ?>


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

            <!-- Vừa submit form đăng nhập thành công -->

            <div class="success-message">
                <?= $success ?>

                <br>

                <a href="index.php">Vào trang chủ</a>
            </div>

        <?php elseif ($isLoggedIn): ?>

            <!-- Đã có session đăng nhập từ trước, không hiện lại form -->

            <div class="success-message">
                Bạn đã đăng nhập với tài khoản
                <?= htmlspecialchars($_SESSION["ten_tai_khoan"]) ?>
                <?= ($_SESSION["vai_tro"] === "admin") ? " (admin)" : "" ?>.

                <br>

                <a href="index.php">Vào trang chủ</a>
                &nbsp;·&nbsp;
                <a href="logout.php">Đăng xuất</a>
            </div>

        <?php else: ?>

            <!-- Chưa đăng nhập -> hiện form như bình thường -->

            <?php if (isset($errors["login"])): ?>

                <div class="error-message">
                    <?= htmlspecialchars($errors["login"]) ?>
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

        <?php endif; ?>


    </div>

</main>


</body>

</html>
