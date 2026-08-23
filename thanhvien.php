<?php

session_start();


// =====================================================
// 1. KHỞI TẠO DỮ LIỆU THÀNH VIÊN
// =====================================================

// Đánh version cho dữ liệu mẫu: mỗi khi đổi nội dung mẫu bên dưới,
// tăng số này lên để session cũ (thiếu dữ liệu) tự làm mới,
// không cần người dùng phải tự xoá cookie/session thủ công.
define('MEMBERS_SAMPLE_VERSION', 2);

if (
    !isset($_SESSION['members']) ||
    !isset($_SESSION['members_version']) ||
    $_SESSION['members_version'] !== MEMBERS_SAMPLE_VERSION
) {

    $_SESSION['members_version'] = MEMBERS_SAMPLE_VERSION;

    $_SESSION['members'] = [

        [
            'id' => 1,
            'name' => 'Lê Hà Nam',
            'code' => '23456WR',
            'date' => '10/08/2026',
            'expire' => '20/10/2026',
            'birthday' => '15/03/1998',
            'email' => 'lehanam@gmail.com',
            'city' => 'Hà Nội',
            'ward' => 'Cầu Giấy',
            'address' => 'Số 12 ngõ 45 Trần Thái Tông',
            'card_number' => '4111111111111111',
            'card_code' => 'VCB4521',
            'cvv' => '123',
            'expired_card' => '09/28'
        ],

        [
            'id' => 2,
            'name' => 'Hà Vy',
            'code' => '444440P',
            'date' => '11/09/2025',
            'expire' => '20/12/2026',
            'birthday' => '22/07/2000',
            'email' => 'havy@gmail.com',
            'city' => 'Hà Nội',
            'ward' => 'Đống Đa',
            'address' => 'Số 8 phố Tây Sơn',
            'card_number' => '5500005555555559',
            'card_code' => 'TCB1187',
            'cvv' => '456',
            'expired_card' => '11/27'
        ],

        [
            'id' => 3,
            'name' => 'Vũ khánh',
            'code' => '5959GHY',
            'date' => '16/11/2019',
            'expire' => '15/09/2026',
            'birthday' => '05/12/1995',
            'email' => 'vukhanh@gmail.com',
            'city' => 'Hà Nội',
            'ward' => 'Ba Đình',
            'address' => 'Số 20 phố Đội Cấn',
            'card_number' => '4000123456789010',
            'card_code' => 'MB9032',
            'cvv' => '789',
            'expired_card' => '02/29'
        ]

    ];
}


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
// 3. LẤY DANH SÁCH THÀNH VIÊN
// =====================================================

$members = $_SESSION['members'];


// =====================================================
// 4. TÌM KIẾM
// =====================================================

$keyword = trim($_GET['search'] ?? '');


// =====================================================
// 5. LỌC THÀNH VIÊN BẰNG PHP
// =====================================================

$displayMembers = [];

foreach ($members as $member) {

    if ($keyword === '') {

        $displayMembers[] = $member;

    } else {

        $searchText =
            $member['name'] . ' ' .
            $member['code'] . ' ' .
            $member['date'] . ' ' .
            $member['expire'];

        if (
            stripos(
                $searchText,
                $keyword
            ) !== false
        ) {

            $displayMembers[] = $member;

        }

    }
}


// =====================================================
// 6. XÁC ĐỊNH THÀNH VIÊN ĐƯỢC CHỌN
// =====================================================

$selectedMember = $members[0];


// Nếu người dùng bấm "Thay đổi"
if (isset($_GET['id'])) {

    $id = (int)$_GET['id'];

    foreach ($members as $member) {

        if ($member['id'] === $id) {

            $selectedMember = $member;

            break;
        }
    }
}


// =====================================================
// 7. THÔNG BÁO
// =====================================================

$message = '';


// =====================================================
// 8. XỬ LÝ FORM CẬP NHẬT THÀNH VIÊN
// =====================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = (int)($_POST['id'] ?? 0);

    $name = trim($_POST['name'] ?? '');
    $birthday = trim($_POST['birthday'] ?? '');
    $email = trim($_POST['email'] ?? '');

    $city = trim($_POST['city'] ?? '');
    $ward = trim($_POST['ward'] ?? '');
    $address = trim($_POST['address'] ?? '');

    $card_number = trim($_POST['card_number'] ?? '');
    $card_code = trim($_POST['card_code'] ?? '');
    $cvv = trim($_POST['cvv'] ?? '');
    $expired_card = trim($_POST['expired_card'] ?? '');

    $errors = [];


    // =================================================
    // KIỂM TRA HỌ TÊN
    // =================================================

    if ($name === '') {

        $errors[] = 'Họ và tên không được để trống.';

    } elseif (mb_strlen($name, 'UTF-8') < 2) {

        $errors[] = 'Họ và tên phải có ít nhất 2 ký tự.';

    }


    // =================================================
    // KIỂM TRA EMAIL
    // =================================================

    if ($email !== '') {

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            $errors[] = 'Email không đúng định dạng.';

        }

    }


    // =================================================
    // KIỂM TRA NGÀY SINH
    // =================================================

    if ($birthday !== '') {

        $dateObject = DateTime::createFromFormat(
            'd/m/Y',
            $birthday
        );

        if (
            !$dateObject ||
            $dateObject->format('d/m/Y') !== $birthday
        ) {

            $errors[] =
                'Ngày sinh phải có dạng dd/mm/yyyy.';

        }

    }


    // =================================================
    // KIỂM TRA SỐ THẺ
    // =================================================

    if ($card_number !== '') {

        $cleanCard =
            str_replace(' ', '', $card_number);

        if (!ctype_digit($cleanCard)) {

            $errors[] =
                'Số thẻ chỉ được chứa chữ số.';

        } elseif (
            strlen($cleanCard) < 12 ||
            strlen($cleanCard) > 19
        ) {

            $errors[] =
                'Số thẻ phải từ 12 đến 19 chữ số.';

        }

    }


    // =================================================
    // KIỂM TRA CVV
    // =================================================

    if ($cvv !== '') {

        if (!ctype_digit($cvv)) {

            $errors[] =
                'Mã CVV chỉ được chứa chữ số.';

        } elseif (
            strlen($cvv) < 3 ||
            strlen($cvv) > 4
        ) {

            $errors[] =
                'Mã CVV phải có 3 hoặc 4 chữ số.';

        }

    }


    // =================================================
    // NẾU KHÔNG CÓ LỖI
    // =================================================

    if (empty($errors)) {

        foreach (
            $_SESSION['members']
            as &$member
        ) {

            if ($member['id'] === $id) {

                $member['name'] = $name;
                $member['birthday'] = $birthday;
                $member['email'] = $email;

                $member['city'] = $city;
                $member['ward'] = $ward;
                $member['address'] = $address;

                $member['card_number'] =
                    $card_number;

                $member['card_code'] =
                    $card_code;

                $member['cvv'] =
                    $cvv;

                $member['expired_card'] =
                    $expired_card;

                break;
            }
        }

        unset($member);


        $message =
            'Cập nhật thông tin thành viên thành công.';


        $members = $_SESSION['members'];


        // Lấy lại thành viên vừa sửa

        foreach ($members as $member) {

            if ($member['id'] === $id) {

                $selectedMember =
                    $member;

                break;
            }
        }

    } else {

        $message =
            implode('<br>', $errors);

        // Giữ lại dữ liệu người dùng vừa nhập
        $selectedMember = [

            'id' => $id,

            'name' => $name,

            'code' => '',

            'date' => '',

            'expire' => '',

            'birthday' => $birthday,

            'email' => $email,

            'city' => $city,

            'ward' => $ward,

            'address' => $address,

            'card_number' => $card_number,

            'card_code' => $card_code,

            'cvv' => $cvv,

            'expired_card' => $expired_card

        ];

    }

}


// =====================================================
// 9. XÓA THÀNH VIÊN
// =====================================================

if (
    isset($_GET['delete']) &&
    is_numeric($_GET['delete'])
) {

    $deleteId = (int)$_GET['delete'];

    foreach (
        $_SESSION['members']
        as $key => $member
    ) {

        if ($member['id'] === $deleteId) {

            unset(
                $_SESSION['members'][$key]
            );

        }
    }

    $_SESSION['members'] =
        array_values(
            $_SESSION['members']
        );

    $members =
        $_SESSION['members'];

    $displayMembers =
        $members;

    $message =
        'Đã xóa thành viên thành công.';

    if (!empty($members)) {

        $selectedMember =
            $members[0];

    } else {

        $selectedMember = [

            'id' => 0,
            'name' => '',
            'code' => '',
            'date' => '',
            'expire' => '',
            'birthday' => '',
            'email' => '',
            'city' => '',
            'ward' => '',
            'address' => '',
            'card_number' => '',
            'card_code' => '',
            'cvv' => '',
            'expired_card' => ''

        ];

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

<title>Quản lý thành viên</title>


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

}


/* ==================================================
   HEADER
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
   SEARCH
================================================== */

.search-area {

    height: 53px;

    background: #242424;

    padding: 11px 37px;

}


.search-area input {

    width: 100%;

    height: 34px;

    border: none;

    outline: none;

    border-radius: 15px;

    padding: 0 28px;

    font-size: 14px;

}


/* ==================================================
   MEMBER BOX
================================================== */

.member-box {

    margin: 8px 10px 14px;

    height: 200px;

    background: #fff;

    border: none;

    padding: 14px 12px;

    overflow: hidden;

}


/* ==================================================
   TABLE
================================================== */

.member-table {

    width: 100%;

    border-collapse: collapse;

    table-layout: fixed;

}


.member-table th {

    height: 30px;

    background: #b7b7b7;

    font-size: 13px;

    font-weight: normal;

}


.member-table th:first-child {

    border-radius: 15px 0 0 15px;

}


.member-table th:last-child {

    border-radius: 0 15px 15px 0;

}


.member-table td {

    height: 40px;

    border-bottom:
        1px solid #c9c9c9;

    text-align: center;

    font-size: 13px;

}


.member-table th:nth-child(1) {
    width: 30%;
}

.member-table th:nth-child(2) {
    width: 20%;
}

.member-table th:nth-child(3) {
    width: 32%;
}

.member-table th:nth-child(4) {
    width: 18%;
}


/* ==================================================
   NÚT THAY ĐỔI
================================================== */

.edit-button {

    display: inline-block;

    width: 24px;

    height: 27px;

    background: #bcbcbc;

    text-decoration: none;

}


.edit-button:hover {

    background: #999;

}


/* ==================================================
   FORM
================================================== */

.form-area {

    padding:
        21px
        10px
        30px;

    background: #dedede;

    min-height: 434px;

}


/* ==================================================
   LABEL
================================================== */

label {

    display: block;

    font-size: 13px;

    margin-bottom: 5px;

}


/* ==================================================
   INPUT
================================================== */

input {

    border: none;

    outline: none;

    background: #fff;

    box-shadow:
        1px 2px 5px
        rgba(0,0,0,.15);

    padding:
        0 12px;

    font-size: 14px;

    height: 38px;

}


/* ==================================================
   ROW
================================================== */

.row {

    display: flex;

    justify-content:
        space-between;

    gap: 27px;

    margin-bottom: 14px;

}


.name-field {

    width: 440px;

}


.birthday-field {

    width: 218px;

}


.name-field input,
.birthday-field input {

    width: 100%;

}


/* ==================================================
   EMAIL
================================================== */

.full {

    width: 100%;

    margin-bottom: 29px;

}


.full input {

    width: 100%;

}


/* ==================================================
   NƠI SỐNG
================================================== */

.living {

    margin-bottom: 33px;

}


.living-row {

    display: flex;

    gap: 20px;

}


.living-row input:nth-child(1) {

    width: 103px;

}


.living-row input:nth-child(2) {

    width: 104px;

}


.living-row input:nth-child(3) {

    width: 103px;

}


/* ==================================================
   ĐƯỜNG KẺ
================================================== */

.line {

    height: 1px;

    background: #888;

    margin:
        0 11px
        16px;

}


/* ==================================================
   PAYMENT
================================================== */

.payment {

    padding: 0 11px;

}


.card-number {

    width: 100%;

    margin-bottom: 28px;

}


/* ==================================================
   PAYMENT ROW
================================================== */

.payment-row {

    display: flex;

    justify-content:
        space-between;

    gap: 53px;

}


.payment-row input {

    width: 190px;

}


/* ==================================================
   BUTTON
================================================== */

.save-button {

    width: 100%;

    height: 35px;

    margin-top: 25px;

    border: none;

    background: #555;

    color: white;

    cursor: pointer;

}


.save-button:hover {

    background: #333;

}


/* ==================================================
   THÔNG BÁO
================================================== */

.message {

    margin:
        0 11px
        15px;

    padding: 10px;

    background: #e8f5e9;

    border:
        1px solid #81c784;

    color: #2e7d32;

    font-size: 12px;

}


/* ==================================================
   MOBILE
================================================== */

@media (max-width: 760px) {
    .site-header-inner {
    width: 100%;
    max-width: none;
    padding: 0 15px;
}

.brand {
    min-width: auto;
}

.brand-name {
    font-size: 20px;
}

.main-nav {
    gap: 12px;
}

.main-nav a {
    font-size: 12px;
}

    .page {

        width:
            calc(100% - 30px);

        margin: 20px auto;

    }


    .member-box {

        overflow-x: auto;

    }


    .member-table {

        min-width: 650px;

    }


    .row {

        flex-direction: column;

    }


    .name-field,
    .birthday-field {

        width: 100%;

    }


    .living-row {

        flex-wrap: wrap;

    }


    .payment-row {

        flex-direction: column;

        gap: 15px;

    }


    .payment-row input {

        width: 100%;

    }

}

</style>

</head>


<body>

<header class="site-header">

    <div class="site-header-inner">

        <!-- LOGO -->
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


        <!-- MENU -->
        <nav class="main-nav">

            <a href="index.php">
                TRANG CHỦ
            </a>

            <a href="#">
                VỀ CHÚNG TÔI
            </a>

            <a href="danh-sach-sach.php" class="active">
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
         HEADER
    ================================================== -->

    


    <!-- ==================================================
         TÌM KIẾM
    ================================================== -->

    <div class="search-area">

        <form method="GET">

            <input
                type="text"
                name="search"
                value="<?= e($keyword) ?>"
                placeholder="TÌM KIẾM....."
            >

        </form>

    </div>


    <!-- ==================================================
         DANH SÁCH THÀNH VIÊN
    ================================================== -->

    <div class="member-box">

        <table class="member-table">

            <thead>

                <tr>

                    <th>
                        TÊN THÀNH VIÊN
                    </th>

                    <th>
                        MÃ THÀNH VIÊN
                    </th>

                    <th>
                        Ngày tham gia/hết hạn
                    </th>

                    <th>
                        Thay đổi
                    </th>

                </tr>

            </thead>


            <tbody>

            <?php if (empty($displayMembers)): ?>

                <tr>

                    <td colspan="4">

                        Không tìm thấy thành viên.

                    </td>

                </tr>

            <?php else: ?>


                <?php foreach (
                    $displayMembers
                    as $member
                ): ?>

                    <tr>

                        <td>

                            <?= e(
                                $member['name']
                            ) ?>

                        </td>


                        <td>

                            <?= e(
                                $member['code']
                            ) ?>

                        </td>


                        <td>

                            <?= e(
                                $member['date']
                            ) ?>

                            -

                            <?= e(
                                $member['expire']
                            ) ?>

                        </td>


                        <td>

                            <a
                                class="edit-button"
                                href="?id=<?= $member['id'] ?>"
                                title="Thay đổi"
                            >
                            </a>

                        </td>

                    </tr>

                <?php endforeach; ?>


            <?php endif; ?>

            </tbody>

        </table>

    </div>


    <!-- ==================================================
         FORM
    ================================================== -->

    <div class="form-area">


        <?php if ($message !== ''): ?>

            <div class="message">

                <?= $message ?>

            </div>

        <?php endif; ?>


        <form method="POST">


            <!-- ID -->

            <input
                type="hidden"
                name="id"
                value="<?= e(
                    $selectedMember['id']
                ) ?>"
            >


            <!-- HỌ TÊN + NGÀY SINH -->

            <div class="row">


                <div class="field name-field">

                    <label>
                        HỌ VÀ TÊN
                    </label>


                    <input
                        type="text"
                        name="name"
                        value="<?= e(
                            $selectedMember['name']
                        ) ?>"
                        placeholder="....."
                    >

                </div>


                <div class="field birthday-field">

                    <label>
                        NGÀY SINH
                    </label>


                    <input
                        type="text"
                        name="birthday"
                        value="<?= e(
                            $selectedMember['birthday']
                        ) ?>"
                        placeholder="dd/mm/yyyy"
                    >

                </div>


            </div>


            <!-- EMAIL -->

            <div class="field full">

                <label>
                    EMAIL
                </label>


                <input
                    type="email"
                    name="email"
                    value="<?= e(
                        $selectedMember['email']
                    ) ?>"
                >

            </div>


            <!-- NƠI SỐNG -->

            <div class="living">

                <label>
                    Nơi sống
                </label>


                <div class="living-row">


                    <input
                        type="text"
                        name="city"
                        value="<?= e(
                            $selectedMember['city']
                        ) ?>"
                        placeholder="Thành phố"
                    >


                    <input
                        type="text"
                        name="ward"
                        value="<?= e(
                            $selectedMember['ward']
                        ) ?>"
                        placeholder="xã"
                    >


                    <input
                        type="text"
                        name="address"
                        value="<?= e(
                            $selectedMember['address']
                        ) ?>"
                        placeholder="địa chỉ chi tiết"
                    >

                </div>

            </div>


            <!-- ĐƯỜNG KẺ -->

            <div class="line"></div>


            <!-- THANH TOÁN -->

            <div class="payment">

                <label>
                    Thông tin thanh toán
                </label>


                <input
                    class="card-number"
                    type="text"
                    name="card_number"
                    value="<?= e(
                        $selectedMember['card_number']
                    ) ?>"
                    placeholder="Số thẻ tín dụng"
                >


                <div class="payment-row">


                    <input
                        type="text"
                        name="card_code"
                        value="<?= e(
                            $selectedMember['card_code']
                        ) ?>"
                        placeholder="Mã số thẻ"
                    >


                    <input
                        type="text"
                        name="cvv"
                        value="<?= e(
                            $selectedMember['cvv']
                        ) ?>"
                        placeholder="mã cvv"
                    >


                    <input
                        type="text"
                        name="expired_card"
                        value="<?= e(
                            $selectedMember['expired_card']
                        ) ?>"
                        placeholder="hết hạn vào"
                    >

                </div>


                <!-- NÚT LƯU -->

                <button
                    type="submit"
                    class="save-button"
                >

                    LƯU THAY ĐỔI

                </button>


            </div>


        </form>

    </div>


</div>


</body>

</html>
