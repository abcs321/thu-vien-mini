<?php
// db.php — Kết nối CSDL dùng PDO (đổi $db, $user, $pass cho khớp WampServer của bạn)

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
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Không lộ chi tiết kết nối ra ngoài khi lên production
    die('Lỗi kết nối CSDL: ' . $e->getMessage());
}
