<?php

// =========================================================
// 1. KHỞI ĐỘNG SESSION
// =========================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// =========================================================
// 2. GỌI INCLUDES.PHP
//    Dùng chung HEADER + FOOTER với INDEX.PHP
// =========================================================

require __DIR__ . '/includes.php';


// =========================================================
// 3. KẾT NỐI CƠ SỞ DỮ LIỆU
// =========================================================

$host = "localhost";
$user = "root";
$pass = "@Quytrinh1503";
$dbname = "thu_vien_mini";

// Nếu XAMPP của bạn dùng MySQL port 3307
// thì đổi 3306 thành 3307.
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
    die("Lỗi kết nối CSDL: " . $conn->connect_error);
}


// Sử dụng UTF-8

$conn->set_charset("utf8mb4");


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
// Không cần JOIN vì tác giả và thể loại
// đang nằm trực tiếp trong bảng sach.
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


// Kiểm tra câu lệnh SQL

if (!$result) {
    die("Lỗi truy vấn CSDL: " . $conn->error);
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
         CSS CHUNG
         Dùng chung với index.php
    ====================================================== -->

    <link
        rel="stylesheet"
        href="style.css"
    >


    <!-- =====================================================
         FONT AWESOME
    ====================================================== -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    >


    <!-- =====================================================
         CSS RIÊNG CỦA TRANG KHÁM PHÁ
    ====================================================== -->

    <style>

        /* =====================================================
           PHẦN HERO
        ===================================================== */

        .explore-hero {

            width: 100%;

            min-height: 400px;

            background:
                linear-gradient(
                    rgba(0, 0, 0, 0.65),
                    rgba(0, 0, 0, 0.75)
                ),
                url(
                    'https://images.unsplash.com/photo-1521587760476-6c12a4b040da?q=80&w=1600&auto=format&fit=crop'
                );

            background-size: cover;

            background-position: center;

            display: flex;

            flex-direction: column;

            justify-content: center;

            align-items: center;

            text-align: center;

            padding: 40px 20px;

        }


        .explore-hero h1 {

            font-size: 48px;

            font-weight: 700;

            margin-bottom: 20px;

            color: #ffffff;

        }


        .explore-hero h1 span {

            color: #e74c3c;

        }


        .explore-hero p {

            max-width: 850px;

            font-size: 16px;

            line-height: 1.7;

            color: #eeeeee;

        }


        /* =====================================================
           KHU VỰC NỘI DUNG
        ===================================================== */

        .explore-container {

            max-width: 1200px;

            margin: 0 auto;

            padding: 40px 20px;

        }


        /* =====================================================
           TÌM KIẾM
        ===================================================== */

        .explore-search-wrapper {

            display: flex;

            justify-content: flex-end;

            margin-bottom: 35px;

        }


        .explore-search {

            width: 300px;

            height: 42px;

            background: #ffffff;

            border-radius: 5px;

            display: flex;

            align-items: center;

            padding: 0 15px;

        }


        .explore-search input {

            width: 100%;

            border: none;

            outline: none;

            background: transparent;

            color: #333333;

            font-size: 14px;

        }


        .explore-search i {

            color: #e74c3c;

            font-size: 15px;

        }


        /* =====================================================
           TIÊU ĐỀ
        ===================================================== */

        .explore-section-title {

            color: #ffffff;

            font-size: 22px;

            font-weight: 700;

            text-transform: uppercase;

            margin-bottom: 30px;

        }


        /* =====================================================
           MỘT CUỐN SÁCH
        ===================================================== */

        .explore-book {

            display: flex;

            gap: 30px;

            margin-bottom: 40px;

            align-items: stretch;

        }


        /* =====================================================
           ẢNH BÌA
        ===================================================== */

        .explore-book-cover {

            width: 300px;

            min-width: 300px;

            height: 420px;

            position: relative;

        }


        .explore-book-cover img {

            width: 100%;

            height: 100%;

            object-fit: cover;

            border-radius: 6px;

            display: block;

            box-shadow:
                0 5px 15px rgba(0, 0, 0, 0.45);

        }


        .explore-book-placeholder {

            width: 100%;

            height: 100%;

            background: #30313b;

            border-radius: 6px;

            display: flex;

            flex-direction: column;

            justify-content: center;

            align-items: center;

            color: #aaaaaa;

            gap: 12px;

        }


        .explore-book-placeholder i {

            font-size: 55px;

        }


        .explore-watermark {

            position: absolute;

            left: 15px;

            bottom: 12px;

            color: rgba(255, 255, 255, 0.85);

            font-size: 18px;

            font-weight: bold;

            text-shadow:
                1px 1px 4px #000000;

        }


        /* =====================================================
           THÔNG TIN SÁCH
        ===================================================== */

        .explore-book-info {

            flex: 1;

            min-height: 420px;

            background: #24252f;

            border-radius: 12px;

            padding: 30px 35px;

            display: flex;

            flex-direction: column;

            justify-content: space-between;

        }


        .explore-book-title {

            text-align: center;

            font-size: 20px;

            font-weight: 700;

            text-transform: uppercase;

            line-height: 1.5;

            margin-bottom: 25px;

            color: #ffffff;

        }


        /* =====================================================
           DÒNG THÔNG TIN
        ===================================================== */

        .explore-info-row {

            display: flex;

            align-items: center;

            margin-bottom: 16px;

            font-size: 14px;

        }


        .explore-info-label {

            width: 165px;

            min-width: 165px;

            color: #999999;

        }


        .explore-info-value {

            color: #ffffff;

            font-weight: 600;

        }


        /* =====================================================
           TAG
        ===================================================== */

        .explore-tag {

            display: inline-block;

            padding: 5px 10px;

            border-radius: 4px;

            font-size: 11px;

            font-weight: bold;

            text-transform: uppercase;

        }


        .explore-tag-genre {

            background: #383a48;

            color: #ffffff;

        }


        .explore-tag-status {

            background: #b58900;

            color: #ffffff;

        }


        .explore-tag-physical {

            background: #00695c;

            color: #ffffff;

        }


        .explore-tag-available {

            background: #2e7d32;

            color: #ffffff;

        }


        .explore-tag-unavailable {

            background: #c62828;

            color: #ffffff;

        }


        .explore-tag-movie {

            background: #795548;

            color: #ffffff;

        }


        /* =====================================================
           NÚT
        ===================================================== */

        .explore-more-btn {

            width: fit-content;

            border: none;

            background: #e74c3c;

            color: #ffffff;

            padding: 10px 20px;

            border-radius: 4px;

            cursor: pointer;

            font-size: 12px;

            font-weight: bold;

            text-transform: uppercase;

            transition: 0.3s;

        }


        .explore-more-btn:hover {

            background: #c0392b;

        }


        /* =====================================================
           KHÔNG CÓ SÁCH
        ===================================================== */

        .explore-no-books {

            background: #24252f;

            border-radius: 10px;

            padding: 50px 20px;

            text-align: center;

            color: #aaaaaa;

        }


        .explore-no-books i {

            font-size: 45px;

            margin-bottom: 15px;

        }


        /* =====================================================
           KHÔNG TÌM THẤY
        ===================================================== */

        .explore-no-result {

            display: none;

            background: #24252f;

            border-radius: 10px;

            padding: 40px 20px;

            text-align: center;

            color: #aaaaaa;

        }


        .explore-no-result i {

            font-size: 35px;

            margin-bottom: 15px;

        }


        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 900px) {

            .explore-book {

                flex-direction: column;

            }


            .explore-book-cover {

                margin: 0 auto;

            }

        }


        @media (max-width: 600px) {

            .explore-hero h1 {

                font-size: 32px;

            }


            .explore-hero p {

                font-size: 14px;

            }


            .explore-search-wrapper {

                justify-content: center;

            }


            .explore-search {

                width: 100%;

            }


            .explore-book-cover {

                width: 100%;

                min-width: 0;

                max-width: 300px;

                margin: 0 auto;

            }


            .explore-book-info {

                padding: 25px 20px;

            }


            .explore-info-row {

                flex-direction: column;

                align-items: flex-start;

                gap: 5px;

            }


            .explore-info-label {

                width: auto;

                min-width: 0;

            }

        }

    </style>

</head>


<body>


<!-- =========================================================
     HEADER
     
     QUAN TRỌNG:
     Header này lấy trực tiếp từ includes.php
     giống index.php
========================================================= -->

<?php render_header($nav, 'explore'); ?>


<!-- =========================================================
     HERO
========================================================= -->

<section class="explore-hero">


    <h1>

        <span>
            Khám phá
        </span>

        / Trang chủ

    </h1>


    <p>

        Khám phá kho tri thức phong phú cùng Thư viện Online.
        Tìm kiếm và khám phá những cuốn sách yêu thích,
        các tài liệu học tập và nhiều nội dung bổ ích
        thuộc nhiều lĩnh vực khác nhau.

    </p>


</section>


<!-- =========================================================
     NỘI DUNG
========================================================= -->

<main class="explore-container">


    <!-- =====================================================
         TÌM KIẾM
    ====================================================== -->

    <div class="explore-search-wrapper">


        <div class="explore-search">


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

    <h2 class="explore-section-title">

        Những đầu sách nổi bật

    </h2>


    <!-- =====================================================
         DANH SÁCH SÁCH
    ====================================================== -->

    <div id="bookList">


        <?php if ($result->num_rows > 0): ?>


            <?php while ($row = $result->fetch_assoc()): ?>


                <?php

                // =================================================
                // LẤY DỮ LIỆU
                // =================================================

                $id = (int)($row['id'] ?? 0);


                $ten_sach =
                    $row['ten_sach']
                    ?? 'Chưa có tên sách';


                $bia_sach =
                    $row['bia_sach']
                    ?? '';


                $tac_gia =
                    $row['tac_gia']
                    ?? 'Chưa có tác giả';


                $the_loai =
                    $row['the_loai']
                    ?? 'Chưa có thể loại';


                $tinh_trang =
                    $row['tinh_trang']
                    ?? 'Chưa xác định';


                $sach_vat_ly =
                    $row['sach_vat_ly']
                    ?? 'Chưa xác định';


                $con_sach =
                    $row['con_sach']
                    ?? 'Chưa xác định';


                $luot_muon =
                    (int)($row['luot_muon'] ?? 0);


                $phim_chuyen_the =
                    $row['phim_chuyen_the']
                    ?? 'Chưa xác định';


                $so_luong =
                    (int)($row['so_luong'] ?? 0);


                // Nội dung dùng cho tìm kiếm

                $search_text =
                    $ten_sach
                    . ' '
                    . $tac_gia
                    . ' '
                    . $the_loai;


                ?>


                <!-- =================================================
                     MỘT CUỐN SÁCH
                ================================================== -->

                <article
                    class="explore-book"
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

                    <div class="explore-book-cover">


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
                            >


                        <?php else: ?>


                            <div class="explore-book-placeholder">


                                <i class="fa-solid fa-book"></i>


                                <span>
                                    Chưa có ảnh bìa
                                </span>


                            </div>


                        <?php endif; ?>


                        <span class="explore-watermark">

                            THƯ VIỆN

                        </span>


                    </div>


                    <!-- =============================================
                         THÔNG TIN SÁCH
                    ============================================== -->

                    <div class="explore-book-info">


                        <div>


                            <!-- TÊN SÁCH -->

                            <h3 class="explore-book-title">

                                <?php

                                echo htmlspecialchars(
                                    $ten_sach,
                                    ENT_QUOTES,
                                    'UTF-8'
                                );

                                ?>

                            </h3>


                            <!-- THỂ LOẠI -->

                            <div class="explore-info-row">


                                <span class="explore-info-label">

                                    Thể loại:

                                </span>


                                <span class="explore-info-value">


                                    <span
                                        class="explore-tag explore-tag-genre"
                                    >

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

                            <div class="explore-info-row">


                                <span class="explore-info-label">

                                    Tác giả:

                                </span>


                                <span class="explore-info-value">

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

                            <div class="explore-info-row">


                                <span class="explore-info-label">

                                    Tình trạng:

                                </span>


                                <span class="explore-info-value">


                                    <span
                                        class="explore-tag explore-tag-status"
                                    >

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

                            <div class="explore-info-row">


                                <span class="explore-info-label">

                                    Sách vật lý:

                                </span>


                                <span class="explore-info-value">


                                    <span
                                        class="explore-tag explore-tag-physical"
                                    >

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

                            <div class="explore-info-row">


                                <span class="explore-info-label">

                                    Còn sách:

                                </span>


                                <span class="explore-info-value">

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

                            <div class="explore-info-row">


                                <span class="explore-info-label">

                                    Số lượng:

                                </span>


                                <span class="explore-info-value">

                                    <?php

                                    echo $so_luong;

                                    ?>

                                </span>


                            </div>


                            <!-- LƯỢT MƯỢN -->

                            <div class="explore-info-row">


                                <span class="explore-info-label">

                                    Lượt mượn/đọc:

                                </span>


                                <span class="explore-info-value">

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

                            <div class="explore-info-row">


                                <span class="explore-info-label">

                                    Phim chuyển thể:

                                </span>


                                <span class="explore-info-value">


                                    <span
                                        class="explore-tag explore-tag-movie"
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
                            class="explore-more-btn"
                            onclick="showBookDetail(<?php

                                echo htmlspecialchars(
                                    json_encode(
                                        $ten_sach,
                                        JSON_UNESCAPED_UNICODE
                                    ),
                                    ENT_QUOTES,
                                    'UTF-8'
                                );

                            ?>)"
                        >

                            Tìm hiểu thêm

                        </button>


                    </div>


                </article>


            <?php endwhile; ?>


        <?php else: ?>


            <!-- =============================================
                 KHÔNG CÓ SÁCH
            ============================================== -->

            <div class="explore-no-books">


                <i class="fa-solid fa-book-open"></i>


                <p>

                    Chưa có sách trong cơ sở dữ liệu.

                </p>


            </div>


        <?php endif; ?>


    </div>


    <!-- =====================================================
         KHÔNG TÌM THẤY KẾT QUẢ
    ====================================================== -->

    <div
        id="noSearchResult"
        class="explore-no-result"
    >


        <i class="fa-solid fa-magnifying-glass"></i>


        <p>

            Không tìm thấy cuốn sách nào phù hợp.

        </p>


    </div>


</main>


<!-- =========================================================
     FOOTER
     
     Footer này lấy trực tiếp từ includes.php
     giống index.php
========================================================= -->

<?php render_footer($footer); ?>


<!-- =========================================================
     JAVASCRIPT
========================================================= -->

<script>


    // =========================================================
    // 1. TÌM KIẾM SÁCH
    // =========================================================

    const searchInput =
        document.getElementById('searchInput');


    const books =
        document.querySelectorAll('.explore-book');


    const noSearchResult =
        document.getElementById('noSearchResult');


    searchInput.addEventListener(
        'input',
        function () {


            // Lấy từ khóa

            const keyword =
                this.value
                    .toLowerCase()
                    .trim();


            let found = 0;


            // Duyệt từng sách

            books.forEach(
                function (book) {


                    const searchText =
                        (
                            book.getAttribute(
                                'data-search'
                            ) || ''
                        ).toLowerCase();


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


            // Hiển thị thông báo nếu không tìm thấy

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
    // 2. TÌM HIỂU THÊM
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
// 5. ĐÓNG KẾT NỐI DATABASE
// =========================================================

$conn->close();

?>