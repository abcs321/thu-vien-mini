<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "thu_vien_mini";

// Kết nối database
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");


// ==========================
// THÊM TÁC GIẢ
// ==========================
if (isset($_POST['them'])) {

    $ten_tac_gia = trim($_POST['ten_tac_gia']);
    $quoc_tich = trim($_POST['quoc_tich']);
    $nam_sinh = intval($_POST['nam_sinh']);

    $stmt = $conn->prepare(
        "INSERT INTO tac_gia (ten_tac_gia, quoc_tich, nam_sinh)
         VALUES (?, ?, ?)"
    );

    $stmt->bind_param(
        "ssi",
        $ten_tac_gia,
        $quoc_tich,
        $nam_sinh
    );

    $stmt->execute();
    $stmt->close();

    header("Location: tacgia.php");
    exit();
}


// ==========================
// XÓA TÁC GIẢ
// ==========================
if (isset($_GET['xoa'])) {

    $id = intval($_GET['xoa']);

    $stmt = $conn->prepare(
        "DELETE FROM tac_gia WHERE id = ?"
    );

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: tacgia.php");
    exit();
}


// ==========================
// CẬP NHẬT TÁC GIẢ
// ==========================
if (isset($_POST['cap_nhat'])) {

    $id = intval($_POST['id']);

    $ten_tac_gia = trim($_POST['ten_tac_gia']);
    $quoc_tich = trim($_POST['quoc_tich']);
    $nam_sinh = intval($_POST['nam_sinh']);

    $stmt = $conn->prepare(
        "UPDATE tac_gia
         SET ten_tac_gia = ?, quoc_tich = ?, nam_sinh = ?
         WHERE id = ?"
    );

    $stmt->bind_param(
        "ssii",
        $ten_tac_gia,
        $quoc_tich,
        $nam_sinh,
        $id
    );

    $stmt->execute();
    $stmt->close();

    header("Location: tacgia.php");
    exit();
}


// ==========================
// LẤY DỮ LIỆU KHI BẤM SỬA
// ==========================
$edit_author = null;

if (isset($_GET['sua'])) {

    $id = intval($_GET['sua']);

    $stmt = $conn->prepare(
        "SELECT * FROM tac_gia WHERE id = ?"
    );

    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result_edit = $stmt->get_result();

    if ($result_edit->num_rows > 0) {
        $edit_author = $result_edit->fetch_assoc();
    }

    $stmt->close();
}


// ==========================
// LẤY DANH SÁCH TÁC GIẢ
// ==========================
$sql = "SELECT * FROM tac_gia ORDER BY id DESC";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="vi">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Quản lý tác giả - Thư viện Mini</title>


    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }


        body {

            font-family: Arial, Helvetica, sans-serif;

            background: #f1f5f9;

            color: #1e293b;

        }


        /* =========================
           LAYOUT
        ========================= */

        .container {

            display: flex;

            min-height: 100vh;

        }


        /* =========================
           SIDEBAR
        ========================= */

        .sidebar {

            width: 250px;

            background: linear-gradient(
                180deg,
                #0f172a,
                #1e293b
            );

            color: white;

            padding: 25px 15px;

            position: fixed;

            left: 0;

            top: 0;

            bottom: 0;

        }


        .logo {

            text-align: center;

            font-size: 22px;

            font-weight: bold;

            padding: 15px 5px 30px;

            border-bottom: 1px solid
                rgba(255,255,255,0.1);

            margin-bottom: 20px;

        }


        .logo span {

            font-size: 32px;

            display: block;

            margin-bottom: 8px;

        }


        .menu {

            list-style: none;

        }


        .menu li {

            margin-bottom: 8px;

        }


        .menu a {

            display: flex;

            align-items: center;

            gap: 12px;

            padding: 13px 15px;

            color: #cbd5e1;

            text-decoration: none;

            border-radius: 10px;

            transition: 0.2s;

        }


        .menu a:hover {

            background: #334155;

            color: white;

        }


        .menu a.active {

            background: #2563eb;

            color: white;

            box-shadow:
                0 5px 15px
                rgba(37,99,235,0.3);

        }


        .menu-icon {

            font-size: 20px;

            width: 25px;

        }


        /* =========================
           MAIN
        ========================= */

        .main {

            margin-left: 250px;

            width: calc(100% - 250px);

            padding: 30px;

        }


        /* =========================
           HEADER
        ========================= */

        .topbar {

            background: white;

            padding: 22px 25px;

            border-radius: 15px;

            margin-bottom: 25px;

            display: flex;

            justify-content: space-between;

            align-items: center;

            box-shadow:
                0 4px 15px
                rgba(15,23,42,0.05);

        }


        .topbar h1 {

            font-size: 26px;

            color: #0f172a;

        }


        .topbar p {

            color: #64748b;

            margin-top: 6px;

            font-size: 14px;

        }


        .admin {

            background: #eff6ff;

            color: #2563eb;

            padding: 10px 15px;

            border-radius: 10px;

            font-weight: bold;

        }


        /* =========================
           CARDS
        ========================= */

        .cards {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 20px;

            margin-bottom: 25px;

        }


        .card {

            background: white;

            padding: 22px;

            border-radius: 15px;

            box-shadow:
                0 4px 15px
                rgba(15,23,42,0.05);

            display: flex;

            align-items: center;

            gap: 18px;

        }


        .card-icon {

            width: 55px;

            height: 55px;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 12px;

            background: #dbeafe;

            font-size: 27px;

        }


        .card h3 {

            font-size: 14px;

            color: #64748b;

            margin-bottom: 5px;

        }


        .card strong {

            font-size: 24px;

            color: #0f172a;

        }


        /* =========================
           FORM
        ========================= */

        .content-grid {

            display: grid;

            grid-template-columns:
                350px 1fr;

            gap: 25px;

        }


        .box {

            background: white;

            border-radius: 15px;

            padding: 25px;

            box-shadow:
                0 4px 15px
                rgba(15,23,42,0.05);

        }


        .box-title {

            font-size: 20px;

            font-weight: bold;

            margin-bottom: 20px;

            color: #0f172a;

        }


        .box-title small {

            display: block;

            color: #64748b;

            font-size: 13px;

            font-weight: normal;

            margin-top: 5px;

        }


        label {

            display: block;

            font-weight: bold;

            font-size: 14px;

            margin-bottom: 7px;

            color: #334155;

        }


        .form-group {

            margin-bottom: 17px;

        }


        input {

            width: 100%;

            padding: 12px 13px;

            border: 1px solid #cbd5e1;

            border-radius: 8px;

            font-size: 14px;

            outline: none;

            transition: 0.2s;

        }


        input:focus {

            border-color: #2563eb;

            box-shadow:
                0 0 0 3px
                rgba(37,99,235,0.1);

        }


        .btn {

            width: 100%;

            border: none;

            padding: 12px;

            border-radius: 8px;

            color: white;

            font-weight: bold;

            cursor: pointer;

            font-size: 14px;

            transition: 0.2s;

        }


        .btn-add {

            background: #2563eb;

        }


        .btn-add:hover {

            background: #1d4ed8;

        }


        .btn-update {

            background: #f59e0b;

        }


        .btn-update:hover {

            background: #d97706;

        }


        .cancel {

            display: block;

            text-align: center;

            margin-top: 12px;

            padding: 10px;

            text-decoration: none;

            color: #64748b;

            font-size: 14px;

        }


        /* =========================
           TABLE
        ========================= */

        .table-wrapper {

            overflow-x: auto;

        }


        table {

            width: 100%;

            border-collapse: collapse;

        }


        th {

            background: #2563eb;

            color: white;

            padding: 14px;

            text-align: left;

            font-size: 14px;

        }


        th:first-child {

            border-radius: 8px 0 0 8px;

        }


        th:last-child {

            border-radius: 0 8px 8px 0;

        }


        td {

            padding: 14px;

            border-bottom: 1px solid #e2e8f0;

            font-size: 14px;

        }


        tr:hover td {

            background: #f8fafc;

        }


        .id {

            font-weight: bold;

            color: #64748b;

        }


        .author-name {

            font-weight: bold;

            color: #0f172a;

        }


        .badge {

            display: inline-block;

            padding: 5px 10px;

            border-radius: 20px;

            background: #eff6ff;

            color: #2563eb;

            font-size: 12px;

            font-weight: bold;

        }


        .actions {

            display: flex;

            gap: 8px;

        }


        .btn-edit {

            background: #fef3c7;

            color: #92400e;

            padding: 7px 12px;

            border-radius: 6px;

            text-decoration: none;

            font-size: 13px;

            font-weight: bold;

        }


        .btn-edit:hover {

            background: #fde68a;

        }


        .btn-delete {

            background: #fee2e2;

            color: #dc2626;

            padding: 7px 12px;

            border-radius: 6px;

            text-decoration: none;

            font-size: 13px;

            font-weight: bold;

        }


        .btn-delete:hover {

            background: #fecaca;

        }


        .empty {

            text-align: center;

            padding: 30px;

            color: #64748b;

        }


        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 1000px) {

            .cards {

                grid-template-columns:
                    1fr;

            }

            .content-grid {

                grid-template-columns:
                    1fr;

            }

        }


        @media (max-width: 700px) {

            .sidebar {

                width: 70px;

                padding: 15px 8px;

            }

            .logo {

                font-size: 0;

            }

            .logo span {

                font-size: 25px;

            }

            .menu a {

                justify-content: center;

                padding: 12px;

            }

            .menu a span:last-child {

                display: none;

            }

            .main {

                margin-left: 70px;

                width: calc(100% - 70px);

                padding: 15px;

            }

            .topbar {

                align-items: flex-start;

                gap: 10px;

            }

            .admin {

                display: none;

            }

        }

    </style>

</head>


<body>


<div class="container">


    <!-- =========================
         SIDEBAR
    ========================== -->

    <aside class="sidebar">

        <div class="logo">

            <span>📚</span>

            THƯ VIỆN MINI

        </div>


        <ul class="menu">

            <li>

                <a href="#" >

                    <span class="menu-icon">🏠</span>

                    <span>Trang chủ</span>

                </a>

            </li>


            <li>

            </li>


            <li>

                <a href="tacgia.php"
                   class="active">

                    <span class="menu-icon">✍️</span>

                    <span>Tác giả</span>

                </a>

            </li>


            <li>

            </li>
            <li>

            </li>

        </ul>

    </aside>

    <!-- =========================
         MAIN
    ========================== -->

    <main class="main">
        <!-- HEADER -->

        <div class="topbar">

            <div>

                <h1>Quản lý tác giả</h1>

                <p>
                    Quản lý thông tin các tác giả
                    trong thư viện
                </p>

            </div>


            <div class="admin">

                👤 Quản trị viên

            </div>

        </div>

        <!-- =========================
             CARDS
        ========================== -->

        <div class="cards">

            <div class="card">

                <div class="card-icon">

                    ✍️

                </div>

                <div>

                    <h3>Tổng tác giả</h3>

                    <strong>
                        <?php echo $result->num_rows; ?>
                    </strong>

                </div>

            </div>


            <div class="card">

                <div class="card-icon">

                    🌍

                </div>

                <div>

                    <h3>Quản lý quốc tịch</h3>

                    <strong>---</strong>

                </div>

            </div>


            <div class="card">

                <div class="card-icon">

                    📚

                </div>

                <div>

                    <h3>Thư viện</h3>

                    <strong>Mini</strong>

                </div>

            </div>

        </div>



        <!-- =========================
             FORM + TABLE
        ========================== -->

        <div class="content-grid">


            <!-- FORM -->

            <div class="box">

                <div class="box-title">

                    <?php if ($edit_author): ?>

                        ✏️ Sửa tác giả

                        <small>
                            Cập nhật thông tin tác giả
                        </small>

                    <?php else: ?>

                        ➕ Thêm tác giả

                        <small>
                            Nhập thông tin tác giả mới
                        </small>

                    <?php endif; ?>

                </div>


                <?php if ($edit_author): ?>

                    <form method="POST">

                        <input
                            type="hidden"
                            name="id"
                            value="<?php echo $edit_author['id']; ?>"
                        >


                        <div class="form-group">

                            <label>
                                Tên tác giả
                            </label>

                            <input
                                type="text"
                                name="ten_tac_gia"
                                value="<?php echo htmlspecialchars($edit_author['ten_tac_gia']); ?>"
                                required
                            >

                        </div>


                        <div class="form-group">

                            <label>
                                Quốc tịch
                            </label>

                            <input
                                type="text"
                                name="quoc_tich"
                                value="<?php echo htmlspecialchars($edit_author['quoc_tich']); ?>"
                                required
                            >

                        </div>


                        <div class="form-group">

                            <label>
                                Năm sinh
                            </label>

                            <input
                                type="number"
                                name="nam_sinh"
                                value="<?php echo $edit_author['nam_sinh']; ?>"
                                required
                            >

                        </div>


                        <button
                            type="submit"
                            name="cap_nhat"
                            class="btn btn-update"
                        >

                            💾 Lưu thay đổi

                        </button>


                        <a
                            href="tacgia.php"
                            class="cancel"
                        >

                            Hủy sửa

                        </a>

                    </form>


                <?php else: ?>


                    <form method="POST">


                        <div class="form-group">

                            <label>
                                Tên tác giả
                            </label>

                            <input
                                type="text"
                                name="ten_tac_gia"
                                placeholder="Ví dụ: Nguyễn Nhật Ánh"
                                required
                            >

                        </div>


                        <div class="form-group">

                            <label>
                                Quốc tịch
                            </label>

                            <input
                                type="text"
                                name="quoc_tich"
                                placeholder="Ví dụ: Việt Nam"
                                required
                            >

                        </div>


                        <div class="form-group">

                            <label>
                                Năm sinh
                            </label>

                            <input
                                type="number"
                                name="nam_sinh"
                                placeholder="Ví dụ: 1955"
                                required
                            >

                        </div>


                        <button
                            type="submit"
                            name="them"
                            class="btn btn-add"
                        >

                            ➕ Thêm tác giả

                        </button>

                    </form>


                <?php endif; ?>

            </div>



            <!-- TABLE -->

            <div class="box">

                <div class="box-title">

                    📋 Danh sách tác giả

                    <small>
                        Thông tin các tác giả hiện có
                    </small>

                </div>


                <div class="table-wrapper">

                    <table>

                        <thead>

                            <tr>

                                <th>ID</th>

                                <th>Tên tác giả</th>

                                <th>Quốc tịch</th>

                                <th>Năm sinh</th>

                                <th>Thao tác</th>

                            </tr>

                        </thead>


                        <tbody>

                        <?php

                        if ($result->num_rows > 0) {

                            while ($row = $result->fetch_assoc()) {

                        ?>

                            <tr>

                                <td class="id">

                                    #<?php
                                    echo $row['id'];
                                    ?>

                                </td>


                                <td class="author-name">

                                    <?php
                                    echo htmlspecialchars(
                                        $row['ten_tac_gia']
                                    );
                                    ?>

                                </td>


                                <td>

                                    <span class="badge">

                                        <?php
                                        echo htmlspecialchars(
                                            $row['quoc_tich']
                                        );
                                        ?>

                                    </span>

                                </td>


                                <td>

                                    <?php
                                    echo $row['nam_sinh'];
                                    ?>

                                </td>


                                <td>

                                    <div class="actions">

                                        <a
                                            href="?sua=<?php echo $row['id']; ?>"
                                            class="btn-edit"
                                        >

                                            ✏️ Sửa

                                        </a>


                                        <a
                                            href="?xoa=<?php echo $row['id']; ?>"
                                            class="btn-delete"
                                            onclick="return confirm('Bạn có chắc muốn xóa tác giả này không?');"
                                        >

                                            🗑️ Xóa

                                        </a>

                                    </div>

                                </td>

                            </tr>

                        <?php

                            }

                        } else {

                        ?>

                            <tr>

                                <td
                                    colspan="5"
                                    class="empty"
                                >

                                    📭 Chưa có tác giả nào

                                </td>

                            </tr>

                        <?php

                        }

                        ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>


    </main>

</div>


</body>

</html>