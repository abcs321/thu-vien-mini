<?php
 
session_start();
 
require_once __DIR__ . '/database.php';
 
 
function e($value)
{
    return htmlspecialchars(
        $value ?? '',
        ENT_QUOTES,
        'UTF-8'
    );
}
 
function to_display_date(?string $d): string
{
    if (!$d) {
        return '';
    }
 
    $t = DateTime::createFromFormat('Y-m-d', $d);
 
    return $t ? $t->format('d/m/Y') : '';
}
 
function to_db_date(string $d): ?string
{
    if ($d === '') {
        return null;
    }
 
    $t = DateTime::createFromFormat('d/m/Y', $d);
 
    return ($t && $t->format('d/m/Y') === $d)
        ? $t->format('Y-m-d')
        : null;
}
 
 
$message = '';
 
 
 
if (
    isset($_GET['delete']) &&
    is_numeric($_GET['delete'])
) {
 
    $deleteId = (int)$_GET['delete'];
 
    $stmt = $conn->prepare(
        "DELETE FROM doc_gia WHERE id_doc_gia = :id"
    );
 
    $stmt->execute([':id' => $deleteId]);
 
    $message = 'Đã xóa thành viên thành công.';
}
 
 
$id = 0;
 
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
 
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
 
    $errors = [];
 
 
    if ($name === '') {
 
        $errors[] = 'Họ và tên không được để trống.';
 
    } elseif (mb_strlen($name, 'UTF-8') < 2) {
 
        $errors[] = 'Họ và tên phải có ít nhất 2 ký tự.';
 
    }
 
 
 
    if ($email !== '') {
 
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
 
            $errors[] = 'Email không đúng định dạng.';
 
        }
 
    }
 
 
 
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


    if ($username === '') {

        $errors[] = 'Tên tài khoản không được để trống.';

    } elseif (!preg_match('/^[A-Za-z0-9_]{3,50}$/', $username)) {

        $errors[] =
            'Tên tài khoản chỉ gồm chữ, số, dấu gạch dưới và từ 3-50 ký tự.';

    } else {

        $usernameCheck = $conn->prepare(
            "SELECT id_doc_gia FROM doc_gia
             WHERE ten_tai_khoan = :username AND id_doc_gia != :id"
        );

        $usernameCheck->execute([
            ':username' => $username,
            ':id' => $id,
        ]);

        if ($usernameCheck->fetchColumn()) {

            $errors[] = 'Tên tài khoản đã được sử dụng.';

        }

    }


    if ($password !== '' && mb_strlen($password, 'UTF-8') < 6) {

        $errors[] = 'Mật khẩu phải có ít nhất 6 ký tự.';

    }

 
    if (empty($errors)) {
 
        $passwordSql = $password !== ''
            ? ', mat_khau = :mat_khau'
            : '';

        $sql = "
            UPDATE doc_gia
            SET
                ho_ten = :ho_ten,
                ngay_sinh = :ngay_sinh,
                email = :email,
                thanh_pho = :thanh_pho,
                xa = :xa,
                dia_chi_chi_tiet = :dia_chi,
                ten_tai_khoan = :ten_tai_khoan
                {$passwordSql}
            WHERE id_doc_gia = :id
        ";

        $stmt = $conn->prepare($sql);

        $params = [
            ':ho_ten' => $name,
            ':ngay_sinh' => to_db_date($birthday),
            ':email' => $email !== '' ? $email : null,
            ':thanh_pho' => $city !== '' ? $city : null,
            ':xa' => $ward !== '' ? $ward : null,
            ':dia_chi' => $address !== '' ? $address : null,
            ':ten_tai_khoan' => $username,
            ':id' => $id,
        ];

        if ($password !== '') {
            $params[':mat_khau'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $stmt->execute($params);


        if ($card_number !== '' && $cvv !== '' && $expired_card !== '') {

            $cardStmt = $conn->prepare(
                "SELECT id_the FROM the_thanh_toan
                 WHERE id_doc_gia = :id
                 ORDER BY id_the ASC LIMIT 1"
            );
            $cardStmt->execute([':id' => $id]);
            $existingCardId = $cardStmt->fetchColumn();

            if ($existingCardId) {

                $cardUpdate = $conn->prepare(
                    "UPDATE the_thanh_toan
                     SET so_the = :so_the, ma_cvv = :ma_cvv, het_han = :het_han
                     WHERE id_the = :id_the"
                );

                $cardUpdate->execute([
                    ':so_the' => str_replace(' ', '', $card_number),
                    ':ma_cvv' => $cvv,
                    ':het_han' => $expired_card,
                    ':id_the' => $existingCardId,
                ]);

            } else {

                $cardInsert = $conn->prepare(
                    "INSERT INTO the_thanh_toan (id_doc_gia, so_the, ma_cvv, het_han)
                     VALUES (:id, :so_the, :ma_cvv, :het_han)"
                );

                $cardInsert->execute([
                    ':id' => $id,
                    ':so_the' => str_replace(' ', '', $card_number),
                    ':ma_cvv' => $cvv,
                    ':het_han' => $expired_card,
                ]);

            }

        }

        $message =
            'Cập nhật thông tin thành viên thành công.';
 
    } else {
 
        $message =
            implode('<br>', $errors);
 
    }
 
}
 
$keyword = trim($_GET['search'] ?? '');
 
 
if ($keyword === '') {

    $sql = "
        SELECT
            dg.id_doc_gia AS id,
            dg.ho_ten,
            dg.ten_tai_khoan AS ma_doc_gia,
            dg.ngay_dang_ky AS ngay_tham_gia,
            NULL AS ngay_het_han,
            dg.ngay_sinh, dg.email,
            dg.thanh_pho, dg.xa, dg.dia_chi_chi_tiet AS dia_chi,
            tt.so_the, NULL AS ma_the, tt.ma_cvv AS cvv, tt.het_han AS han_the
        FROM doc_gia dg
        LEFT JOIN the_thanh_toan tt
            ON tt.id_the = (
                SELECT MIN(id_the) FROM the_thanh_toan WHERE id_doc_gia = dg.id_doc_gia
            )
        ORDER BY dg.id_doc_gia ASC
    ";

    $stmt = $conn->query($sql);

} else {

    $sql = "
        SELECT
            dg.id_doc_gia AS id,
            dg.ho_ten,
            dg.ten_tai_khoan AS ma_doc_gia,
            dg.ngay_dang_ky AS ngay_tham_gia,
            NULL AS ngay_het_han,
            dg.ngay_sinh, dg.email,
            dg.thanh_pho, dg.xa, dg.dia_chi_chi_tiet AS dia_chi,
            tt.so_the, NULL AS ma_the, tt.ma_cvv AS cvv, tt.het_han AS han_the
        FROM doc_gia dg
        LEFT JOIN the_thanh_toan tt
            ON tt.id_the = (
                SELECT MIN(id_the) FROM the_thanh_toan WHERE id_doc_gia = dg.id_doc_gia
            )
        WHERE
            dg.ho_ten LIKE :kw
            OR dg.ten_tai_khoan LIKE :kw
        ORDER BY dg.id_doc_gia ASC
    ";

    $stmt = $conn->prepare($sql);

    $stmt->execute([':kw' => '%' . $keyword . '%']);

}
 
$rows = $stmt->fetchAll();
 
$displayMembers = [];
 
foreach ($rows as $row) {
 
    $displayMembers[] = [
        'id' => (int)$row['id'],
        'name' => $row['ho_ten'],
        'code' => $row['ma_doc_gia'],
        'date' => to_display_date($row['ngay_tham_gia']),
        'expire' => to_display_date($row['ngay_het_han']),
        'birthday' => to_display_date($row['ngay_sinh']),
        'email' => $row['email'] ?? '',
        'city' => $row['thanh_pho'] ?? '',
        'ward' => $row['xa'] ?? '',
        'address' => $row['dia_chi'] ?? '',
        'card_number' => $row['so_the'] ?? '',
        'card_code' => $row['ma_the'] ?? '',
        'cvv' => $row['cvv'] ?? '',
        'expired_card' => $row['han_the'] ?? '',
        'username' => $row['ma_doc_gia'],
    ];
 
}
 
 
 
$emptyMember = [
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
    'expired_card' => '',
    'username' => ''
];
 
$selectedMember = $displayMembers[0] ?? $emptyMember;
 
$focusId = $id !== 0 ? $id : (int)($_GET['id'] ?? 0);
 
if ($focusId !== 0) {
 
    foreach ($displayMembers as $member) {
 
        if ($member['id'] === $focusId) {
 
            $selectedMember = $member;
 
            break;
        }
    }
}
 
// Nếu vừa submit form mà bị lỗi validate -> giữ lại dữ liệu người dùng vừa nhập
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    !empty($errors)
) {
 
    $selectedMember = [
        'id' => $id,
        'name' => $name,
        'code' => $selectedMember['code'] ?? '',
        'date' => $selectedMember['date'] ?? '',
        'expire' => $selectedMember['expire'] ?? '',
        'birthday' => $birthday,
        'email' => $email,
        'city' => $city,
        'ward' => $ward,
        'address' => $address,
        'card_number' => $card_number,
        'card_code' => $card_code,
        'cvv' => $cvv,
        'expired_card' => $expired_card,
        'username' => $username
    ];
 
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


* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}


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


.page {

    width: 720px;

    min-height: 715px;

    margin: 46px auto;

    background: #e3e3e3;

}


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


.page-nav {

    display: flex;

    justify-content: space-between;

    align-items: center;

    background: #ffffff;

    padding: 14px 30px;

}

.page-nav a {

    text-decoration: none;

    font-size: 12px;

    font-weight: 700;

    letter-spacing: 0.4px;

    color: #222;

    text-transform: uppercase;

}

.page-nav a:hover {

    color: #ef5350;

}

.page-nav a.active {

    color: #ef5350;

}


/* ==================================================
   SEARCH
================================================== */

.search-area {

    height: 53px;

    background: #242424;

    padding: 11px 20px;

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



.form-area {

    padding:
        21px
        10px
        30px;

    background: #dedede;

    min-height: 434px;

}


label {

    display: block;

    font-size: 13px;

    margin-bottom: 5px;

}


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

    position: relative;

}


.date-input-wrap {

    position: relative;

}


.date-input-wrap input {

    width: 100%;

    padding-right: 40px;

}


.date-picker-btn {

    position: absolute;

    top: 50%;

    right: 8px;

    transform: translateY(-50%);

    width: 26px;

    height: 26px;

    border: none;

    background: transparent;

    cursor: pointer;

    display: flex;

    align-items: center;

    justify-content: center;

    padding: 0;

    border-radius: 6px;

}


.date-picker-btn:hover {

    background: #eaeaea;

}


.date-picker-btn svg {

    width: 17px;

    height: 17px;

    stroke: #7a7a7a;

}


.calendar-popup {

    display: none;

    position: absolute;

    top: calc(100% + 6px);

    left: 0;

    z-index: 20;

    width: 240px;

    background: #fff;

    border-radius: 10px;

    box-shadow: 0 4px 14px rgba(0,0,0,.2);

    padding: 12px;

}


.calendar-popup.open {

    display: block;

}


.calendar-header {

    display: flex;

    align-items: center;

    justify-content: space-between;

    margin-bottom: 10px;

}


.calendar-header span {

    font-size: 13px;

    font-weight: bold;

}


.calendar-nav {

    border: none;

    background: #ececec;

    width: 24px;

    height: 24px;

    border-radius: 6px;

    cursor: pointer;

    font-size: 13px;

    line-height: 1;

}


.calendar-nav:hover {

    background: #d9d9d9;

}


.calendar-grid {

    display: grid;

    grid-template-columns: repeat(7, 1fr);

    gap: 2px;

}


.calendar-grid .dow {

    font-size: 11px;

    text-align: center;

    color: #999;

    padding-bottom: 4px;

}


.calendar-day {

    border: none;

    background: transparent;

    height: 26px;

    font-size: 12px;

    border-radius: 6px;

    cursor: pointer;

}


.calendar-day:hover {

    background: #ececec;

}


.calendar-day.muted {

    color: #ccc;

}


.calendar-day.selected {

    background: #6f6f6f;

    color: #fff;

}


.name-field input,
.birthday-field input {

    width: 100%;

}


.full {

    width: 100%;

    margin-bottom: 29px;

}


.full input {

    width: 100%;

}

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


.account {

    margin-bottom: 33px;

}


.account-row {

    display: flex;

    gap: 20px;

}


.account-row input {

    flex: 1;

}


.line {

    height: 1px;

    background: #888;

    margin:
        0 11px
        16px;

}

.payment {

    padding: 0 11px;

}


.card-number {

    width: 100%;

    margin-bottom: 28px;

}


.payment-row {

    display: flex;

    justify-content:
        space-between;

    gap: 53px;

}


.payment-row input {

    width: 190px;

}

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


    .living-row,
    .account-row {

        flex-wrap: wrap;

    }


    .account-row input {

        min-width: 100%;

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


    <nav class="page-nav">

        <a href="index.php">
            Trang chủ
        </a>

        <a href="danh-sach-sach.php">
            Sách
        </a>

        <a href="thanhvien.php" class="active">
            Thành viên
        </a>

    </nav>


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
                        Ngày tham gia
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


                    <div class="date-input-wrap">

                        <input
                            type="text"
                            name="birthday"
                            id="birthdayInput"
                            value="<?= e(
                                $selectedMember['birthday']
                            ) ?>"
                            placeholder="dd/mm/yyyy"
                            autocomplete="off"
                        >

                        <button
                            type="button"
                            class="date-picker-btn"
                            id="birthdayPickerBtn"
                            aria-label="Chọn ngày"
                        >
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="3" y="5" width="18" height="16" rx="2" stroke-width="2"></rect>
                                <path d="M8 3v4M16 3v4M3 10h18" stroke-width="2" stroke-linecap="round"></path>
                            </svg>
                        </button>

                        <div class="calendar-popup" id="birthdayCalendar">

                            <div class="calendar-header">
                                <button type="button" class="calendar-nav" id="calPrev">‹</button>
                                <span id="calTitle"></span>
                                <button type="button" class="calendar-nav" id="calNext">›</button>
                            </div>

                            <div class="calendar-grid" id="calGrid"></div>

                        </div>

                    </div>

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


            <!-- TÀI KHOẢN -->

            <div class="account">

                <label>
                    tài khoản
                </label>


                <div class="account-row">


                    <input
                        type="text"
                        name="username"
                        value="<?= e(
                            $selectedMember['username']
                        ) ?>"
                        placeholder="tên tài khoản"
                        autocomplete="off"
                    >


                    <input
                        type="password"
                        name="password"
                        value=""
                        placeholder="mật khẩu (để trống nếu không đổi)"
                        autocomplete="new-password"
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



<script>
(function () {

    var input = document.getElementById('birthdayInput');
    var btn = document.getElementById('birthdayPickerBtn');
    var popup = document.getElementById('birthdayCalendar');
    var titleEl = document.getElementById('calTitle');
    var gridEl = document.getElementById('calGrid');
    var prevBtn = document.getElementById('calPrev');
    var nextBtn = document.getElementById('calNext');

    if (!input || !btn || !popup) {
        return;
    }

    var dowNames = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'];
    var monthNames = [
        'Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4',
        'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8',
        'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'
    ];

    function parseInputDate() {

        var m = input.value.trim().match(/^(\d{2})\/(\d{2})\/(\d{4})$/);

        if (!m) {
            return null;
        }

        var d = new Date(
            parseInt(m[3], 10),
            parseInt(m[2], 10) - 1,
            parseInt(m[1], 10)
        );

        return isNaN(d.getTime()) ? null : d;
    }

    var selectedDate = parseInputDate();
    var viewDate = selectedDate ? new Date(selectedDate) : new Date();
    viewDate.setDate(1);

    function pad(n) {
        return n < 10 ? '0' + n : '' + n;
    }

    function render() {

        titleEl.textContent =
            monthNames[viewDate.getMonth()] + ' ' + viewDate.getFullYear();

        gridEl.innerHTML = '';

        dowNames.forEach(function (name) {
            var el = document.createElement('div');
            el.className = 'dow';
            el.textContent = name;
            gridEl.appendChild(el);
        });

        var firstOfMonth = new Date(viewDate.getFullYear(), viewDate.getMonth(), 1);
        var startOffset = firstOfMonth.getDay();
        var gridStart = new Date(firstOfMonth);
        gridStart.setDate(gridStart.getDate() - startOffset);

        for (var i = 0; i < 42; i++) {

            var cellDate = new Date(gridStart);
            cellDate.setDate(gridStart.getDate() + i);

            var cellBtn = document.createElement('button');
            cellBtn.type = 'button';
            cellBtn.className = 'calendar-day';
            cellBtn.textContent = cellDate.getDate();

            if (cellDate.getMonth() !== viewDate.getMonth()) {
                cellBtn.classList.add('muted');
            }

            if (
                selectedDate &&
                cellDate.getFullYear() === selectedDate.getFullYear() &&
                cellDate.getMonth() === selectedDate.getMonth() &&
                cellDate.getDate() === selectedDate.getDate()
            ) {
                cellBtn.classList.add('selected');
            }

            (function (d) {
                cellBtn.addEventListener('click', function () {
                    selectedDate = d;
                    input.value =
                        pad(d.getDate()) + '/' +
                        pad(d.getMonth() + 1) + '/' +
                        d.getFullYear();
                    closePopup();
                });
            })(cellDate);

            gridEl.appendChild(cellBtn);
        }
    }

    function openPopup() {
        var current = parseInputDate();
        if (current) {
            selectedDate = current;
            viewDate = new Date(current);
            viewDate.setDate(1);
        }
        render();
        popup.classList.add('open');
    }

    function closePopup() {
        popup.classList.remove('open');
    }

    function togglePopup(e) {
        e.stopPropagation();
        if (popup.classList.contains('open')) {
            closePopup();
        } else {
            openPopup();
        }
    }

    btn.addEventListener('click', togglePopup);
    input.addEventListener('focus', openPopup);

    prevBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        viewDate.setMonth(viewDate.getMonth() - 1);
        render();
    });

    nextBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        viewDate.setMonth(viewDate.getMonth() + 1);
        render();
    });

    popup.addEventListener('click', function (e) {
        e.stopPropagation();
    });

    document.addEventListener('click', function (e) {
        if (!popup.contains(e.target) && e.target !== input && e.target !== btn) {
            closePopup();
        }
    });

})();
</script>

</body>

</html>