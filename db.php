<?php

// ==========================
// KẾT NỐI CƠ SỞ DỮ LIỆU
// ==========================
// Mặc định theo WampServer: user root, không mật khẩu.
// Nếu MySQL trên máy bạn có đặt mật khẩu root thì đổi DB_PASS lại.

define("DB_HOST", "localhost");
define("DB_NAME", "thu_vien_mini");
define("DB_USER", "root");
define("DB_PASS", "");


try {

    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

} catch (PDOException $e) {

    die("Không kết nối được cơ sở dữ liệu: " . $e->getMessage());

}
