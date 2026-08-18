<?php

// ===============================
// DỮ LIỆU TẠM - CHƯA CẦN DATABASE
// ===============================

$members = [
    [
        'id' => 1,
        'name' => 'Lê Hà Nam',
        'code' => '23456WR',
        'date' => '10/08/2026',
        'expire' => '20/10/2026',
        'birthday' => '',
        'email' => '',
        'city' => '',
        'ward' => '',
        'address' => '',
        'card_number' => '',
        'card_code' => '',
        'cvv' => '',
        'expired_card' => ''
    ],

    [
        'id' => 2,
        'name' => 'Hà Vy',
        'code' => '444440P',
        'date' => '11/09/2025',
        'expire' => '20/12/2026',
        'birthday' => '',
        'email' => '',
        'city' => '',
        'ward' => '',
        'address' => '',
        'card_number' => '',
        'card_code' => '',
        'cvv' => '',
        'expired_card' => ''
    ],

    [
        'id' => 3,
        'name' => 'Vũ khánh',
        'code' => '5959GHY',
        'date' => '16/11/2019',
        'expire' => '15/09/2026',
        'birthday' => '',
        'email' => '',
        'city' => '',
        'ward' => '',
        'address' => '',
        'card_number' => '',
        'card_code' => '',
        'cvv' => '',
        'expired_card' => ''
    ]
];


// ===============================
// XỬ LÝ CHỌN THÀNH VIÊN
// ===============================

$selectedMember = $members[0];

if (isset($_GET['id'])) {

    $id = (int) $_GET['id'];

    foreach ($members as $member) {

        if ($member['id'] == $id) {
            $selectedMember = $member;
            break;
        }

    }
}

?>

<!DOCTYPE html>
<html lang="vi">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Quản lý thành viên</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

<div class="page">

    <!-- ========================= -->
    <!-- MENU -->
    <!-- ========================= -->

    <div class="menu">

        <div class="menu-item">
            TRANG CHỦ
        </div>

        <div class="menu-item">
            SÁCH
        </div>

        <div class="menu-item active">
            THÀNH VIÊN
        </div>

    </div>


    <!-- ========================= -->
    <!-- THANH TÌM KIẾM -->
    <!-- ========================= -->

    <div class="search-area">

        <input
            type="text"
            id="searchInput"
            placeholder="TÌM KIẾM....."
        >

    </div>


    <!-- ========================= -->
    <!-- DANH SÁCH THÀNH VIÊN -->
    <!-- ========================= -->

    <div class="member-box">

        <table id="memberTable">

            <thead>

                <tr>

                    <th>TÊN THÀNH VIÊN</th>

                    <th>MÃ THÀNH VIÊN</th>

                    <th>Ngày tham gia/hết hạn</th>

                    <th>Thay đổi</th>

                </tr>

            </thead>

            <tbody>

            <?php foreach ($members as $member): ?>

                <tr>

                    <td>
                        <?= htmlspecialchars($member['name']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($member['code']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($member['date']) ?>
                        -
                        <?= htmlspecialchars($member['expire']) ?>
                    </td>

                    <td>

                        <a
                            class="edit-button"
                            href="?id=<?= $member['id'] ?>"
                        >
                        </a>

                    </td>

                </tr>

            <?php endforeach; ?>

            </tbody>

        </table>

    </div>


    <!-- ========================= -->
    <!-- FORM THÔNG TIN -->
    <!-- ========================= -->

    <div class="form-area">

        <!-- HÀNG HỌ TÊN + NGÀY SINH -->

        <div class="row">

            <div class="field name-field">

                <label>HỌ VÀ TÊN</label>

                <input
                    type="text"
                    value="<?= htmlspecialchars($selectedMember['name']) ?>"
                    placeholder="....."
                >

            </div>


            <div class="field birthday-field">

                <label>NGÀY SINH</label>

                <input
                    type="text"
                    value="<?= htmlspecialchars($selectedMember['birthday']) ?>"
                    placeholder="dd/mm/yyyy"
                >

            </div>

        </div>


        <!-- EMAIL -->

        <div class="field full">

            <label>EMAIL</label>

            <input
                type="text"
                value="<?= htmlspecialchars($selectedMember['email']) ?>"
            >

        </div>


        <!-- NƠI SỐNG -->

        <div class="living">

            <label>Nơi sống</label>

            <div class="living-row">

                <input
                    type="text"
                    value="<?= htmlspecialchars($selectedMember['city']) ?>"
                    placeholder="Thành phố"
                >

                <input
                    type="text"
                    value="<?= htmlspecialchars($selectedMember['ward']) ?>"
                    placeholder="xã"
                >

                <input
                    type="text"
                    value="<?= htmlspecialchars($selectedMember['address']) ?>"
                    placeholder="địa chỉ chi tiết"
                >

            </div>

        </div>


        <!-- ĐƯỜNG KẺ -->

        <div class="line"></div>


        <!-- THANH TOÁN -->

        <div class="payment">

            <label>Thông tin thanh toán</label>


            <input
                class="card-number"
                type="text"
                value="<?= htmlspecialchars($selectedMember['card_number']) ?>"
                placeholder="Số thẻ tín dụng"
            >


            <div class="payment-row">

                <input
                    type="text"
                    value="<?= htmlspecialchars($selectedMember['card_code']) ?>"
                    placeholder="Mã số thẻ"
                >

                <input
                    type="text"
                    value="<?= htmlspecialchars($selectedMember['cvv']) ?>"
                    placeholder="mã cvv"
                >

                <input
                    type="text"
                    value="<?= htmlspecialchars($selectedMember['expired_card']) ?>"
                    placeholder="hết hạn vào"
                >

            </div>

        </div>

    </div>

</div>


<!-- ========================= -->
<!-- TÌM KIẾM BẰNG JAVASCRIPT -->
<!-- ========================= -->

<script>

const searchInput = document.getElementById("searchInput");

const tableRows = document.querySelectorAll(
    "#memberTable tbody tr"
);

searchInput.addEventListener("keyup", function () {

    const keyword = this.value.toLowerCase();

    tableRows.forEach(function (row) {

        const text = row.innerText.toLowerCase();

        if (text.includes(keyword)) {

            row.style.display = "";

        } else {

            row.style.display = "none";

        }

    });

});

</script>

</body>

</html>