-- =====================================================
-- seed_accounts.sql
-- Chạy sau khi đã import thu_vien_mini_v2.sql
-- =====================================================

USE `thu_vien_mini`;

-- register.php (bản đăng ký nhanh) chỉ thu thập username/email/password,
-- chưa thu thập họ tên/ngày sinh/địa chỉ -> nới ho_ten thành cho phép NULL.
-- Họ tên đầy đủ sẽ được bổ sung sau ở bước "hoàn thiện hồ sơ".
ALTER TABLE `doc_gia` MODIFY `ho_ten` VARCHAR(150) DEFAULT NULL;

-- --------------------------------------------------------
-- 2 tài khoản mẫu
-- Phân biệt admin/user thường qua ten_tai_khoan trong login.php:
-- ten_tai_khoan = 'admin' -> admin, còn lại -> user thường.
--
-- Mật khẩu đã hash bằng password_hash($pw, PASSWORD_DEFAULT) của PHP,
-- khớp với password_verify() trong login.php:
--   admin  / Admin@123
--   user01 / User@123
-- --------------------------------------------------------
INSERT INTO `doc_gia` (`ten_tai_khoan`, `email`, `mat_khau`) VALUES
('admin',  'admin@thuvien.local', '$2y$10$R/o.mnqGzXGQvt.da7.GWuiKTlUyME7WSkKrGbnUy2oTO2fiUrvx.'),
('user01', 'user01@example.com',  '$2y$10$ApmBR6SL/HFP/r6ErE77ceP./enACiWCGxyo0x58ghgabeb4SzTli');
