<?php
/**
 * Kết nối CSDL bằng PDO.
 *
 * LƯU Ý: nếu file includes.php của bạn ĐÃ có sẵn kết nối CSDL (biến $pdo
 * hoặc $conn), hãy XOÁ dòng require file này trong danh-sach-sach.php và
 * quan-ly-danh-muc.php, rồi đổi các đoạn code dùng $pdo bên dưới cho khớp
 * với biến bạn đang dùng. File này chỉ là phương án dự phòng khi chưa có.
 */

$db_host = 'localhost';
$db_name = 'thu_vien_mini';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO(
        "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4",
        $db_user,
        $db_pass
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Không kết nối được CSDL: ' . $e->getMessage());
}
