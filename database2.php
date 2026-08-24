<?php

/* =========================================================
   CẤU HÌNH KẾT NỐI CSDL
   Sửa lại 4 giá trị bên dưới cho đúng với máy của bạn nếu cần
   (mặc định XAMPP/Laragon: user root, không mật khẩu)
========================================================= */

$host    = '127.0.0.1';
$db      = 'thu_vien_mini';
$user    = 'root';
$pass    = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {

    $conn = new PDO($dsn, $user, $pass, $options);

} catch (PDOException $e) {

    die('Không thể kết nối CSDL: ' . $e->getMessage());

}
