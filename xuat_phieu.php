<?php

/* =========================================================
   xuat_phieu.php
   ---------------------------------------------------------
   Xuất 1 phiếu mượn (theo id_phieu_muon) ra dạng văn bản
   thuần (.txt) để xem/đọc trực tiếp trên trình duyệt.

   Cách gọi: xuat_phieu.php?id=<id_phieu_muon>
========================================================= */

require_once __DIR__ . '/database.php';


/* ---------------------------------------------------------
   1. LẤY & KIỂM TRA ID
--------------------------------------------------------- */

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {

    /* Không có id trên URL -> hiện danh sách phiếu gần đây
       để chọn, thay vì chỉ báo lỗi trơ. */

    try {

        $sql = "
            SELECT
                pm.id_phieu_muon,
                pm.ngay_muon,
                pm.trang_thai,
                dg.ho_ten,
                dg.ten_tai_khoan,
                s.ten_sach
            FROM phieu_muon pm
            JOIN doc_gia dg ON dg.id_doc_gia = pm.id_doc_gia
            JOIN sach s ON s.id_sach = pm.id_sach
            ORDER BY pm.id_phieu_muon DESC
            LIMIT 20
        ";

        $dsPhieu = $conn->query($sql)->fetchAll();

    } catch (PDOException $e) {

        $dsPhieu = [];
    }

    header('Content-Type: text/html; charset=utf-8');

    echo '<!DOCTYPE html><html lang="vi"><head><meta charset="UTF-8">';
    echo '<title>Danh sách phiếu mượn</title>';
    echo '<style>
        body { background:#141414; color:#eee; font-family: Arial, sans-serif; padding: 24px; }
        h1 { font-size: 18px; margin-bottom: 16px; }
        table { border-collapse: collapse; width: 100%; max-width: 900px; }
        th, td { border: 1px solid #444; padding: 8px 12px; text-align: left; font-size: 13px; }
        th { background: #1e1e1e; }
        a { color: #6cb6ff; }
        .empty { color: #999; }
    </style></head><body>';

    echo '<h1>Thiếu mã phiếu trên URL — chọn 1 phiếu bên dưới để xem:</h1>';

    if (empty($dsPhieu)) {

        echo '<p class="empty">Chưa có phiếu mượn nào trong hệ thống.</p>';

    } else {

        echo '<table><tr>
                <th>Mã phiếu</th>
                <th>Độc giả</th>
                <th>Sách</th>
                <th>Ngày mượn</th>
                <th>Trạng thái</th>
              </tr>';

        foreach ($dsPhieu as $p) {

            $ten = $p['ho_ten'] ?: $p['ten_tai_khoan'];

            echo '<tr>';
            echo '<td><a href="xuat_phieu.php?id=' . (int)$p['id_phieu_muon'] . '">#' . (int)$p['id_phieu_muon'] . '</a></td>';
            echo '<td>' . htmlspecialchars($ten) . '</td>';
            echo '<td>' . htmlspecialchars($p['ten_sach']) . '</td>';
            echo '<td>' . htmlspecialchars($p['ngay_muon']) . '</td>';
            echo '<td>' . htmlspecialchars($p['trang_thai']) . '</td>';
            echo '</tr>';
        }

        echo '</table>';
    }

    echo '</body></html>';
    exit;
}


/* ---------------------------------------------------------
   2. TRUY VẤN DỮ LIỆU PHIẾU MƯỢN (kèm độc giả + sách)
--------------------------------------------------------- */

try {

    $sql = "
        SELECT
            pm.id_phieu_muon,
            pm.so_luong,
            pm.ngay_muon,
            pm.ngay_tra_du_kien,
            pm.ngay_tra_thuc_te,
            pm.trang_thai,
            dg.ho_ten,
            dg.ten_tai_khoan,
            s.ten_sach
        FROM phieu_muon pm
        JOIN doc_gia dg ON dg.id_doc_gia = pm.id_doc_gia
        JOIN sach s ON s.id_sach = pm.id_sach
        WHERE pm.id_phieu_muon = :id
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $id]);

    $phieu = $stmt->fetch();

} catch (PDOException $e) {

    header('Content-Type: text/plain; charset=utf-8');
    echo "Không thể đọc dữ liệu phiếu mượn.";
    exit;
}


if (!$phieu) {

    header('Content-Type: text/plain; charset=utf-8');
    echo "Không tìm thấy phiếu mượn có mã #{$id}.";
    exit;
}


/* ---------------------------------------------------------
   3. ĐỊNH DẠNG NỘI DUNG PHIẾU
--------------------------------------------------------- */

function dinhDangNgay(?string $ngay): string
{
    if (!$ngay) {
        return '(chưa có)';
    }

    $ts = strtotime($ngay);

    return $ts ? date('d/m/Y', $ts) : $ngay;
}

$hoTen = $phieu['ho_ten'] ?? $phieu['ten_tai_khoan'];

$noiDung = "";
$noiDung .= "========================================\r\n";
$noiDung .= "          PHIẾU MƯỢN SÁCH - THƯ VIỆN\r\n";
$noiDung .= "========================================\r\n\r\n";

$noiDung .= "Mã phiếu       : #" . $phieu['id_phieu_muon'] . "\r\n";
$noiDung .= "Độc giả        : " . $hoTen . " (" . $phieu['ten_tai_khoan'] . ")\r\n";
$noiDung .= "Tên sách       : " . $phieu['ten_sach'] . "\r\n";
$noiDung .= "Số lượng       : " . $phieu['so_luong'] . "\r\n";
$noiDung .= "Ngày mượn      : " . dinhDangNgay($phieu['ngay_muon']) . "\r\n";
$noiDung .= "Ngày hẹn trả   : " . dinhDangNgay($phieu['ngay_tra_du_kien']) . "\r\n";
$noiDung .= "Ngày trả thực tế: " . dinhDangNgay($phieu['ngay_tra_thuc_te']) . "\r\n";
$noiDung .= "Trạng thái     : " . $phieu['trang_thai'] . "\r\n\r\n";

$noiDung .= "----------------------------------------\r\n";
$noiDung .= "Xuất lúc: " . date('d/m/Y H:i:s') . "\r\n";


/* ---------------------------------------------------------
   4. TRẢ VỀ DẠNG VĂN BẢN THUẦN (.txt)
   Không dùng Content-Disposition: attachment để trình duyệt
   hiển thị trực tiếp, đọc được ngay (thay vì tự tải file về).
--------------------------------------------------------- */

header('Content-Type: text/plain; charset=utf-8');
echo $noiDung;
