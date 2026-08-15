<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "thu_vien_mini";

// Kết nối database
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// Thêm độc giả
if (isset($_POST['them'])) {

    $ho_ten = $_POST['ho_ten'];
    $mssv = $_POST['mssv'];
    $lop = $_POST['lop'];
    $so_dien_thoai = $_POST['so_dien_thoai'];
    $ten_dang_nhap = $_POST['ten_dang_nhap'];
    $mat_khau = $_POST['mat_khau'];

    // Mã hóa mật khẩu
    $mat_khau_ma_hoa = password_hash($mat_khau, PASSWORD_DEFAULT);

    $sql = "INSERT INTO doc_gia
            (ho_ten, mssv, lop, so_dien_thoai, ten_dang_nhap, mat_khau)
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "ssssss",
        $ho_ten,
        $mssv,
        $lop,
        $so_dien_thoai,
        $ten_dang_nhap,
        $mat_khau_ma_hoa
    );

    if ($stmt->execute()) {
        echo "<script>
                alert('Thêm độc giả thành công!');
              </script>";
    } else {
        echo "Lỗi: " . $stmt->error;
    }

    $stmt->close();
}

// Xóa độc giả
if (isset($_GET['xoa'])) {

    $id = (int)$_GET['xoa'];

    $sql = "DELETE FROM doc_gia WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "<script>
                alert('Xóa độc giả thành công!');
                window.location='doc_gia.php';
              </script>";
    } else {
        echo "Lỗi: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý độc giả</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background: #f5f5f5;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        form {
            width: 500px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px #ccc;
        }

        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            box-sizing: border-box;
        }

        button {
            margin-top: 20px;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background: #0056b3;
        }

        table {
            width: 95%;
            margin: 30px auto;
            border-collapse: collapse;
            background: white;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }

        th {
            background: #007bff;
            color: white;
        }

        a {
            text-decoration: none;
            margin: 0 5px;
        }
    </style>
</head>

<body>

<h1>QUẢN LÝ ĐỘC GIẢ</h1>

<form method="POST">

    <label>Họ tên:</label>
    <input type="text" name="ho_ten" required>

    <label>MSSV:</label>
    <input type="text" name="mssv" required>

    <label>Lớp:</label>
    <input type="text" name="lop" required>

    <label>Số điện thoại:</label>
    <input type="text" name="so_dien_thoai" required>

    <label>Tên đăng nhập:</label>
    <input type="text" name="ten_dang_nhap" required>

    <label>Mật khẩu:</label>
    <input type="password" name="mat_khau" required>

    <button type="submit" name="them">Thêm độc giả</button>

</form>

<h2 style="text-align:center;">Danh sách độc giả</h2>

<table>

    <tr>
        <th>ID</th>
        <th>Họ tên</th>
        <th>MSSV</th>
        <th>Lớp</th>
        <th>Số điện thoại</th>
        <th>Tên đăng nhập</th>
        <th>Mật khẩu</th>
        <th>Thao tác</th>
    </tr>

<?php

$sql = "SELECT * FROM doc_gia";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {

    while ($row = $result->fetch_assoc()) {

        echo "<tr>";

        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['ho_ten']) . "</td>";
        echo "<td>" . htmlspecialchars($row['mssv']) . "</td>";
        echo "<td>" . htmlspecialchars($row['lop']) . "</td>";
        echo "<td>" . htmlspecialchars($row['so_dien_thoai']) . "</td>";
        echo "<td>" . htmlspecialchars($row['ten_dang_nhap']) . "</td>";

        // Không hiển thị mật khẩu thật
        echo "<td>********</td>";

        echo "<td>
                <a href='?xoa=" . $row['id'] . "'
                   onclick=\"return confirm('Bạn có chắc muốn xóa độc giả này không?');\">
                   Xóa
                </a>
              </td>";

        echo "</tr>";
    }
}

?>

</table>

</body>
</html>