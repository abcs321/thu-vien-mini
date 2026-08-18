<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Đăng nhập - Thư viện</title>

    <link rel="stylesheet" href="style.css">
</head>

<body>

    <!-- FORM ĐĂNG NHẬP -->

    <div class="login-page">

        <div class="login-box">

            <h1>ĐĂNG NHẬP</h1>

            <p class="login-subtitle">
                Chào mừng bạn quay trở lại!
            </p>

            <form>

                <div class="form-group">

                    <label for="username">
                        Tên đăng nhập
                    </label>

                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Nhập tên đăng nhập"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="password">
                        Mật khẩu
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Nhập mật khẩu"
                        required
                    >

                </div>


                <div class="login-options">

                    <label>
                        <input type="checkbox">
                        Ghi nhớ tôi
                    </label>

                    <a href="#">
                        Quên mật khẩu?
                    </a>

                </div>


                <button
                    type="submit"
                    class="form-btn"
                >
                    ĐĂNG NHẬP
                </button>

            </form>


            <p class="register-link">

                Chưa có tài khoản?

                <a href="register.html">
                    Đăng ký ngay
                </a>

            </p>

        </div>

    </div>

</body>

</html>