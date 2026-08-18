<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Phiếu mượn</title>

    <link rel="stylesheet" href="style.css">
</head>

<body>

    <!-- THANH MENU -->
    <nav class="navbar">

        <div class="logo">
            📚 <span>THƯ VIỆN</span>
        </div>

        <div class="menu">
            <a href="#">TRANG CHỦ</a>
            <a href="#">GIỚI THIỆU</a>
            <a href="#">SÁCH</a>
            <a href="#">THỂ LOẠI</a>
            <a href="#">LIÊN HỆ</a>
        </div>

        <button class="login">
            ĐĂNG NHẬP
        </button>

    </nav>


    <!-- BANNER -->
    <section class="banner">

        <div class="banner-overlay"></div>

        <h1>Phiếu mượn</h1>

    </section>


    <!-- FORM -->
    <main class="form-container">

        <!-- HỌ TÊN + NGÀY SINH -->
        <div class="row">

            <div class="form-group name">
                <label>HỌ VÀ TÊN</label>

                <input type="text">
            </div>


            <div class="form-group birthday">
                <label>NGÀY SINH</label>

                <input
                    type="text"
                    placeholder="dd/mm/yyyy"
                >
            </div>

        </div>


        <!-- EMAIL -->
        <div class="form-group">

            <label>EMAIL</label>

            <input type="email">

        </div>


        <!-- NỘI DUNG -->
        <div class="form-group">

            <label>NỘI DUNG</label>

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


        <!-- THANH TOÁN -->
        <section class="payment">

            <h2>Thông tin thanh toán</h2>


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

                <p>Thanh toán qua QR</p>

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

</body>
</html>