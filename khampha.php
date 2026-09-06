<?php

// =========================================================
// 1. KHỞI ĐỘNG SESSION
// =========================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// =========================================================
// 2. KẾT NỐI CƠ SỞ DỮ LIỆU
// =========================================================

$host = "localhost";
$user = "root";
$pass = "@Quytrinh1503";
$dbname = "thu_vien_mini";

// Nếu MySQL của bạn chạy cổng 3307
// thì đổi 3306 thành 3307 ở dòng dưới.
$port = 3306;


$conn = new mysqli(
    $host,
    $user,
    $pass,
    $dbname,
    $port
);


// Kiểm tra kết nối

if ($conn->connect_error) {

    die(
        "Lỗi kết nối CSDL: "
        . $conn->connect_error
    );

}


// Sử dụng tiếng Việt UTF-8

$conn->set_charset("utf8mb4");


// =========================================================
// 3. KIỂM TRA ĐĂNG NHẬP
// =========================================================

$is_logged_in = isset($_SESSION['id_doc_gia']);

$user_name = $is_logged_in
    ? ($_SESSION['ho_ten'] ?? '')
    : '';


// =========================================================
// 4. LẤY DANH SÁCH SÁCH
// =========================================================
//
// Bảng sach thực tế của bạn:
//
// id
// ten_sach
// bia_sach
// tac_gia
// the_loai
// tinh_trang
// sach_vat_ly
// con_sach
// luot_muon
// phim_chuyen_the
// so_luong
// id_nxb
//
// Vì tác giả và thể loại đã nằm trực tiếp
// trong bảng sach nên KHÔNG cần JOIN.
//

$sql = "
    SELECT
        id,
        ten_sach,
        bia_sach,
        tac_gia,
        the_loai,
        tinh_trang,
        sach_vat_ly,
        con_sach,
        luot_muon,
        phim_chuyen_the,
        so_luong,
        id_nxb
    FROM sach
    ORDER BY luot_muon DESC, ten_sach ASC
";


$result = $conn->query($sql);


// Kiểm tra câu SQL

if (!$result) {

    die(
        "Lỗi truy vấn CSDL: "
        . $conn->error
    );

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

    <title>
        Khám phá - Thư Viện
    </title>


    <!-- =====================================================
         FONT AWESOME
    ====================================================== -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    >


    <style>

        /* =====================================================
           RESET
        ===================================================== */

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }


        /* =====================================================
           BODY
        ===================================================== */

        body {
            background-color: #1a1b22;

            color: #ffffff;

            font-family:
                Arial,
                "Times New Roman",
                sans-serif;

            width: 100%;

            overflow-x: hidden;
        }


        /* =====================================================
           HEADER
        ===================================================== */

        header {
            width: 100%;

            min-height: 70px;

            background-color: #161622;

            border-top: 3px solid #e74c3c;

            display: flex;

            align-items: center;

            justify-content: space-between;

            padding: 15px 5%;

            gap: 20px;
        }


        /* =====================================================
           LOGO
        ===================================================== */

        .logo {
            display: flex;

            align-items: center;

            gap: 10px;

            white-space: nowrap;
        }


        .logo-icon {
            width: 35px;

            height: 35px;

            background-color: #e74c3c;

            border-radius: 50%;

            display: flex;

            align-items: center;

            justify-content: center;

            color: #ffffff;

            font-size: 14px;
        }


        .logo span {
            font-size: 20px;

            font-weight: bold;

            letter-spacing: 1px;
        }


        /* =====================================================
           MENU
        ===================================================== */

        nav ul {
            list-style: none;

            display: flex;

            align-items: center;

            gap: 24px;
        }


        nav ul li a {
            text-decoration: none;

            color: #d1d1d1;

            font-size: 13px;

            font-weight: 600;

            text-transform: uppercase;

            transition: 0.3s;
        }


        nav ul li a:hover {
            color: #e74c3c;
        }


        nav ul li a.active {
            color: #e74c3c;
        }


        /* =====================================================
           NÚT ĐĂNG NHẬP
        ===================================================== */

        .btn-login {
            background-color: #e74c3c;

            color: #ffffff;

            text-decoration: none;

            padding: 9px 18px;

            border-radius: 4px;

            font-size: 13px;

            font-weight: bold;

            white-space: nowrap;

            transition: 0.3s;
        }


        .btn-login:hover {
            background-color: #c0392b;
        }


        /* =====================================================
           HERO
        ===================================================== */

        .hero {
            width: 100%;

            height: 400px;

            background:
                linear-gradient(
                    rgba(0, 0, 0, 0.65),
                    rgba(0, 0, 0, 0.75)
                ),
                url(
                    'https://images.unsplash.com/photo-1521587760476-6c12a4b040da?q=80&w=1200&auto=format&fit=crop'
                );

            background-size: cover;

            background-position: center;

            display: flex;

            flex-direction: column;

            align-items: center;

            justify-content: center;

            text-align: center;

            padding: 20px;
        }


        .hero-title {
            font-size: 48px;

            font-weight: bold;

            margin-bottom: 20px;
        }


        .hero-title .highlight {
            color: #e74c3c;
        }


        .hero-description {
            max-width: 850px;

            font-size: 16px;

            line-height: 1.7;

            color: #eeeeee;
        }


        /* =====================================================
           CONTENT
        ===================================================== */

        .content-container {
            max-width: 1200px;

            margin: 0 auto;

            padding: 35px 20px;
        }


        /* =====================================================
           SEARCH
        ===================================================== */

        .search-container {
            display: flex;

            justify-content: flex-end;

            margin-bottom: 35px;
        }


        .search-box {
            width: 300px;

            height: 42px;

            background-color: #ffffff;

            border-radius: 5px;

            display: flex;

            align-items: center;

            padding: 0 15px;
        }


        .search-box input {
            width: 100%;

            border: none;

            outline: none;

            font-size: 14px;

            color: #333;

            background: transparent;
        }


        .search-box i {
            color: #e74c3c;

            font-size: 15px;
        }


        /* =====================================================
           TIÊU ĐỀ
        ===================================================== */

        .section-title {
            font-size: 21px;

            text-transform: uppercase;

            margin-bottom: 25px;

            font-weight: bold;
        }


        /* =====================================================
           BOOK
        ===================================================== */

        .book-detail-container {
            display: flex;

            gap: 30px;

            margin-bottom: 35px;

            align-items: stretch;
        }


        /* =====================================================
           ẢNH SÁCH
        ===================================================== */

        .book-cover-wrapper {
            width: 300px;

            min-width: 300px;

            position: relative;
        }


        .book-cover-image {
            width: 100%;

            height: 420px;

            object-fit: cover;

            border-radius: 5px;

            display: block;

            box-shadow:
                0 5px 15px rgba(0, 0, 0, 0.5);
        }


        .book-cover-placeholder {
            width: 100%;

            height: 420px;

            background-color: #30313b;

            border-radius: 5px;

            display: flex;

            flex-direction: column;

            align-items: center;

            justify-content: center;

            color: #aaaaaa;

            font-size: 14px;

            gap: 10px;
        }


        .watermark {
            position: absolute;

            bottom: 12px;

            left: 15px;

            color: rgba(255,255,255,0.85);

            font-size: 18px;

            font-weight: bold;

            text-shadow:
                1px 1px 4px #000000;
        }


        /* =====================================================
           THÔNG TIN SÁCH
        ===================================================== */

        .book-info-block {
            flex: 1;

            min-height: 420px;

            background-color: #24252f;

            border-radius: 12px;

            padding: 30px 35px;

            display: flex;

            flex-direction: column;

            justify-content: space-between;
        }


        .book-main-title {
            font-size: 20px;

            text-align: center;

            text-transform: uppercase;

            margin-bottom: 20px;

            letter-spacing: 0.8px;

            line-height: 1.4;
        }


        /* =====================================================
           INFO ROW
        ===================================================== */

        .info-row {
            display: flex;

            align-items: center;

            margin-bottom: 16px;

            font-size: 14px;
        }


        .info-label {
            width: 160px;

            flex-shrink: 0;

            color: #999999;
        }


        .info-value {
            color: #ffffff;

            font-weight: bold;
        }


        /* =====================================================
           TAG
        ===================================================== */

        .tag {
            display: inline-block;

            padding: 5px 10px;

            border-radius: 4px;

            font-size: 11px;

            font-weight: bold;

            text-transform: uppercase;
        }


        .tag-genre {
            background-color: #383a48;

            color: #ffffff;
        }


        .tag-status {
            background-color: #b58900;

            color: #ffffff;
        }


        .tag-physical {
            background-color: #00695c;

            color: #ffffff;
        }


        .tag-available {
            background-color: #2e7d32;

            color: #ffffff;
        }


        .tag-unavailable {
            background-color: #c62828;

            color: #ffffff;
        }


        .tag-adaptation {
            background-color: #795548;

            color: #ffffff;
        }


        /* =====================================================
           NÚT TÌM HIỂU
        ===================================================== */

        .btn-more-info {
            border: none;

            background-color: #e74c3c;

            color: #ffffff;

            padding: 10px 20px;

            border-radius: 4px;

            font-size: 12px;

            font-weight: bold;

            text-transform: uppercase;

            cursor: pointer;

            width: fit-content;

            transition: 0.3s;
        }


        .btn-more-info:hover {
            background-color: #c0392b;
        }


        /* =====================================================
           KHÔNG CÓ SÁCH
        ===================================================== */

        .no-books {
            width: 100%;

            padding: 50px;

            background-color: #24252f;

            border-radius: 10px;

            text-align: center;

            color: #aaaaaa;
        }


        /* =====================================================
           KẾT QUẢ TÌM KIẾM KHÔNG CÓ
        ===================================================== */

        .no-search-result {
            display: none;

            padding: 40px;

            text-align: center;

            color: #aaaaaa;

            background-color: #24252f;

            border-radius: 10px;
        }


        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 1000px) {

            header {
                flex-wrap: wrap;

                justify-content: center;
            }


            nav ul {
                flex-wrap: wrap;

                justify-content: center;
            }


            .book-detail-container {
                flex-direction: column;
            }


            .book-cover-wrapper {
                width: 300px;

                min-width: 300px;

                margin: 0 auto;
            }

        }


        @media (max-width: 600px) {

            .hero-title {
                font-size: 32px;
            }


            .hero-description {
                font-size: 14px;
            }


            .search-container {
                justify-content: center;
            }


            .search-box {
                width: 100%;
            }


            .book-info-block {
                padding: 25px 20px;
            }


            .info-row {
                flex-direction: column;

                align-items: flex-start;

                gap: 5px;
            }


            .info-label {
                width: auto;
            }

        }

    </style>

</head>


<body>


<!-- =========================================================
     HEADER
========================================================= -->

<header>


    <!-- LOGO -->

    <div class="logo">

        <div class="logo-icon">

            <i class="fa-solid fa-book-open"></i>

        </div>

        <span>
            THƯ VIỆN
        </span>

    </div>


    <!-- MENU -->

    <nav>

        <ul>

            <li>
                <a href="index.php">
                    TRANG CHỦ
                </a>
            </li>


            <li>
                <a href="ve-chung-toi.php">
                    VỀ CHÚNG TÔI
                </a>
            </li>


            <li>
                <a href="danh-sach-sach.php">
                    DANH SÁCH SÁCH
                </a>
            </li>


            <li>
                <a href="phieu_muon.php">
                    PHIẾU MƯỢN
                </a>
            </li>


            <li>
                <a
                    href="khampha.php"
                    class="active"
                >
                    KHÁM PHÁ
                </a>
            </li>


            <li>
                <a href="lien_lac.php">
                    LIÊN LẠC
                </a>
            </li>

        </ul>

    </nav>


    <!-- =====================================================
         ĐĂNG NHẬP / TÀI KHOẢN
    ====================================================== -->

    <?php if ($is_logged_in): ?>

        <div
            style="
                display:flex;
                align-items:center;
                gap:12px;
            "
        >

            <span
                style="
                    color:#dddddd;
                    font-size:13px;
                "
            >

                Xin chào,

                <strong>
                    <?php

                    echo htmlspecialchars(
                        $user_name,
                        ENT_QUOTES,
                        'UTF-8'
                    );

                    ?>
                </strong>

            </span>


            <a
                href="logout.php"
                class="btn-login"
            >
                Đăng xuất
            </a>

        </div>

    <?php else: ?>

        <a
            href="login.php"
            class="btn-login"
        >

            <i class="fa-regular fa-user"></i>

            Đăng nhập

        </a>

    <?php endif; ?>


</header>


<!-- =========================================================
     HERO
========================================================= -->

<section class="hero">


    <h1 class="hero-title">

        <span class="highlight">
            Khám phá
        </span>

        / Trang chủ

    </h1>


    <p class="hero-description">

        Khám phá kho tri thức phong phú cùng Thư viện Online.
        Tìm kiếm và khám phá những cuốn sách yêu thích,
        các tài liệu học tập và nhiều nội dung bổ ích
        thuộc nhiều lĩnh vực khác nhau.

    </p>


</section>


<!-- =========================================================
     NỘI DUNG
========================================================= -->

<div class="content-container">


    <!-- =====================================================
         TÌM KIẾM
    ====================================================== -->

    <div class="search-container">

        <div class="search-box">

            <input
                type="text"
                id="searchInput"
                placeholder="Tìm kiếm sách..."
                autocomplete="off"
            >

            <i class="fa-solid fa-magnifying-glass"></i>

        </div>

    </div>


    <!-- =====================================================
         TIÊU ĐỀ
    ====================================================== -->

    <section>

        <h2 class="section-title">
            Những đầu sách nổi bật
        </h2>


        <!-- =================================================
             DANH SÁCH SÁCH
        ================================================== -->

        <div id="bookList">


            <?php if ($result->num_rows > 0): ?>


                <?php while ($row = $result->fetch_assoc()): ?>


                    <?php

                    // =================================================
                    // Lấy dữ liệu
                    // =================================================

                    $id = (int)$row['id'];

                    $ten_sach =
                        $row['ten_sach'] ?? 'Chưa có tên sách';

                    $bia_sach =
                        $row['bia_sach'] ?? '';

                    $tac_gia =
                        $row['tac_gia'] ?? 'Chưa có tác giả';

                    $the_loai =
                        $row['the_loai'] ?? 'Chưa có thể loại';

                    $tinh_trang =
                        $row['tinh_trang'] ?? 'Chưa xác định';

                    $sach_vat_ly =
                        $row['sach_vat_ly'] ?? 'Chưa xác định';

                    $con_sach =
                        $row['con_sach'] ?? 'Chưa xác định';

                    $luot_muon =
                        (int)($row['luot_muon'] ?? 0);

                    $phim_chuyen_the =
                        $row['phim_chuyen_the']
                        ?? 'Chưa xác định';

                    $so_luong =
                        (int)($row['so_luong'] ?? 0);


                    // =================================================
                    // Xử lý tìm kiếm
                    // =================================================

                    $search_text = strtolower(
                        $ten_sach
                        . ' '
                        . $tac_gia
                        . ' '
                        . $the_loai
                    );

                    ?>


                    <!-- =================================================
                         MỘT CUỐN SÁCH
                    ================================================== -->

                    <div
                        class="book-detail-container"
                        data-search="<?php

                            echo htmlspecialchars(
                                $search_text,
                                ENT_QUOTES,
                                'UTF-8'
                            );

                        ?>"
                    >


                        <!-- =============================================
                             ẢNH BÌA
                        ============================================== -->

                        <div class="book-cover-wrapper">


                            <?php if (!empty($bia_sach)): ?>


                                <img
                                    src="<?php

                                        echo htmlspecialchars(
                                            $bia_sach,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        );

                                    ?>"
                                    alt="<?php

                                        echo htmlspecialchars(
                                            $ten_sach,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        );

                                    ?>"
                                    class="book-cover-image"
                                >


                            <?php else: ?>


                                <div
                                    class="book-cover-placeholder"
                                >

                                    <i
                                        class="fa-solid fa-book"
                                        style="
                                            font-size:50px;
                                        "
                                    ></i>

                                    <span>
                                        Chưa có ảnh bìa
                                    </span>

                                </div>


                            <?php endif; ?>


                            <span class="watermark">
                                THƯ VIỆN
                            </span>


                        </div>


                        <!-- =============================================
                             THÔNG TIN SÁCH
                        ============================================== -->

                        <div class="book-info-block">


                            <div>


                                <!-- TÊN SÁCH -->

                                <h3 class="book-main-title">

                                    <?php

                                    echo htmlspecialchars(
                                        $ten_sach,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    );

                                    ?>

                                </h3>


                                <!-- THỂ LOẠI -->

                                <div class="info-row">

                                    <span class="info-label">
                                        Thể loại:
                                    </span>


                                    <span class="info-value">

                                        <span class="tag tag-genre">

                                            <?php

                                            echo htmlspecialchars(
                                                $the_loai,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );

                                            ?>

                                        </span>

                                    </span>

                                </div>


                                <!-- TÁC GIẢ -->

                                <div class="info-row">

                                    <span class="info-label">
                                        Tác giả:
                                    </span>


                                    <span class="info-value">

                                        <?php

                                        echo htmlspecialchars(
                                            $tac_gia,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        );

                                        ?>

                                    </span>

                                </div>


                                <!-- TÌNH TRẠNG -->

                                <div class="info-row">

                                    <span class="info-label">
                                        Tình trạng:
                                    </span>


                                    <span class="info-value">

                                        <span class="tag tag-status">

                                            <?php

                                            echo htmlspecialchars(
                                                $tinh_trang,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );

                                            ?>

                                        </span>

                                    </span>

                                </div>


                                <!-- SÁCH VẬT LÝ -->

                                <div class="info-row">

                                    <span class="info-label">
                                        Sách vật lý:
                                    </span>


                                    <span class="info-value">

                                        <span class="tag tag-physical">

                                            <?php

                                            echo htmlspecialchars(
                                                $sach_vat_ly,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );

                                            ?>

                                        </span>

                                    </span>

                                </div>


                                <!-- CÒN SÁCH -->

                                <div class="info-row">

                                    <span class="info-label">
                                        Tình trạng kho:
                                    </span>


                                    <span class="info-value">

                                        <?php

                                        echo htmlspecialchars(
                                            $con_sach,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        );

                                        ?>

                                    </span>

                                </div>


                                <!-- SỐ LƯỢNG -->

                                <div class="info-row">

                                    <span class="info-label">
                                        Số lượng:
                                    </span>


                                    <span class="info-value">

                                        <?php

                                        echo $so_luong;

                                        ?>

                                    </span>

                                </div>


                                <!-- LƯỢT MƯỢN -->

                                <div class="info-row">

                                    <span class="info-label">
                                        Lượt mượn/đọc:
                                    </span>


                                    <span class="info-value">

                                        <?php

                                        echo number_format(
                                            $luot_muon,
                                            0,
                                            ',',
                                            '.'
                                        );

                                        ?>

                                    </span>

                                </div>


                                <!-- PHIM CHUYỂN THỂ -->

                                <div class="info-row">

                                    <span class="info-label">
                                        Phim chuyển thể:
                                    </span>


                                    <span class="info-value">

                                        <span
                                            class="tag tag-adaptation"
                                        >

                                            <?php

                                            echo htmlspecialchars(
                                                $phim_chuyen_the,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );

                                            ?>

                                        </span>

                                    </span>

                                </div>


                            </div>


                            <!-- =========================================
                                 NÚT TÌM HIỂU THÊM
                            ========================================== -->

                            <button
                                type="button"
                                class="btn-more-info"
                                onclick="showBookDetail(
                                    <?php

                                    echo htmlspecialchars(
                                        json_encode(
                                            $ten_sach,
                                            JSON_UNESCAPED_UNICODE
                                        ),
                                        ENT_QUOTES,
                                        'UTF-8'
                                    );

                                    ?>
                                )"
                            >

                                Tìm hiểu thêm

                            </button>


                        </div>


                    </div>


                <?php endwhile; ?>


            <?php else: ?>


                <!-- =============================================
                     KHÔNG CÓ SÁCH
                ============================================== -->

                <div class="no-books">

                    <i
                        class="fa-solid fa-book-open"
                        style="
                            font-size:45px;
                            margin-bottom:15px;
                        "
                    ></i>


                    <p>
                        Chưa có sách trong cơ sở dữ liệu.
                    </p>

                </div>


            <?php endif; ?>


        </div>


        <!-- =================================================
             KHÔNG TÌM THẤY KẾT QUẢ
        ================================================== -->

        <div
            id="noSearchResult"
            class="no-search-result"
        >

            <i
                class="fa-solid fa-magnifying-glass"
                style="
                    font-size:35px;
                    margin-bottom:15px;
                "
            ></i>


            <p>
                Không tìm thấy cuốn sách nào phù hợp.
            </p>

        </div>


    </section>


</div>


<!-- =========================================================
     JAVASCRIPT
========================================================= -->

<script>


    // =========================================================
    // 1. TÌM KIẾM SÁCH
    // =========================================================

    const searchInput =
        document.getElementById('searchInput');


    const bookItems =
        document.querySelectorAll(
            '.book-detail-container'
        );


    const noSearchResult =
        document.getElementById(
            'noSearchResult'
        );


    searchInput.addEventListener(
        'input',
        function () {


            // Lấy từ khóa

            const keyword =
                this.value
                    .toLowerCase()
                    .trim();


            let found = 0;


            // Duyệt tất cả sách

            bookItems.forEach(
                function (book) {


                    const searchText =
                        book.getAttribute(
                            'data-search'
                        ) || '';


                    if (
                        searchText.includes(keyword)
                    ) {

                        book.style.display =
                            'flex';

                        found++;

                    } else {

                        book.style.display =
                            'none';

                    }

                }
            );


            // Nếu không tìm thấy

            if (
                keyword !== ''
                && found === 0
            ) {

                noSearchResult.style.display =
                    'block';

            } else {

                noSearchResult.style.display =
                    'none';

            }

        }
    );


    // =========================================================
    // 2. XEM THÔNG TIN SÁCH
    // =========================================================

    function showBookDetail(bookName) {

        alert(
            'Bạn đang xem thông tin của sách: '
            + bookName
        );

    }


</script>


</body>

</html>


<?php

// =========================================================
// 5. ĐÓNG KẾT NỐI
// =========================================================

$conn->close();

?>