
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id_category` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `ten_category` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id_category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chinh_sach`
--

DROP TABLE IF EXISTS `chinh_sach`;
CREATE TABLE IF NOT EXISTS `chinh_sach` (
  `id_chinh_sach` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `ten_chinh_sach` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gia_tri` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mo_ta` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_chinh_sach`),
  UNIQUE KEY `uq_ten_chinh_sach` (`ten_chinh_sach`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chinh_sach`
--

INSERT INTO `chinh_sach` (`id_chinh_sach`, `ten_chinh_sach`, `gia_tri`, `mo_ta`) VALUES
(1, 'so_ngay_muon_toi_da', '14', 'Số ngày tối đa được mượn 1 cuốn sách'),
(2, 'so_sach_toi_da_moi_doc_gia', '5', 'Số sách tối đa 1 độc giả được mượn cùng lúc'),
(3, 'tien_phat_moi_ngay', '5000', 'Tiền phạt mỗi ngày trả trễ (VNĐ)');

-- --------------------------------------------------------

--
-- Table structure for table `doc_gia`
--

DROP TABLE IF EXISTS `doc_gia`;
CREATE TABLE IF NOT EXISTS `doc_gia` (
  `id_doc_gia` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `ho_ten` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Họ và tên',
  `ngay_sinh` date DEFAULT NULL COMMENT 'Ngày sinh',
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Email',
  `thanh_pho` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Thành phố',
  `xa` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Xã',
  `dia_chi_chi_tiet` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Địa chỉ chi tiết',
  `ten_tai_khoan` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tên tài khoản',
  `mat_khau` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Mật khẩu - nên lưu hash (bcrypt/password_hash) ở tầng ứng dụng, không lưu plaintext',
  `ngay_dang_ky` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_doc_gia`),
  UNIQUE KEY `uq_email` (`email`),
  UNIQUE KEY `uq_ten_tai_khoan` (`ten_tai_khoan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `genres`
--

DROP TABLE IF EXISTS `genres`;
CREATE TABLE IF NOT EXISTS `genres` (
  `id_genre` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `ten_genre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_category` int UNSIGNED NOT NULL,
  PRIMARY KEY (`id_genre`),
  KEY `fk_genres_category` (`id_category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nha_xuat_ban`
--

DROP TABLE IF EXISTS `nha_xuat_ban`;
CREATE TABLE IF NOT EXISTS `nha_xuat_ban` (
  `id_nxb` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `ten_nxb` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id_nxb`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `phieu_muon`
--

DROP TABLE IF EXISTS `phieu_muon`;
CREATE TABLE IF NOT EXISTS `phieu_muon` (
  `id_phieu_muon` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_doc_gia` int UNSIGNED NOT NULL,
  `id_sach` int UNSIGNED NOT NULL,
  `so_luong` int UNSIGNED NOT NULL DEFAULT '1' COMMENT 'Số lượng',
  `ngay_muon` date NOT NULL DEFAULT (curdate()),
  `ngay_tra_du_kien` date DEFAULT NULL,
  `ngay_tra_thuc_te` date DEFAULT NULL,
  `trang_thai` enum('Đang mượn','Đã trả','Quá hạn') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Đang mượn',
  PRIMARY KEY (`id_phieu_muon`),
  KEY `fk_pm_doc_gia` (`id_doc_gia`),
  KEY `fk_pm_sach` (`id_sach`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sach`
--

DROP TABLE IF EXISTS `sach`;
CREATE TABLE IF NOT EXISTS `sach` (
  `id_sach` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `ten_sach` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Chọn tên',
  `id_genre` int UNSIGNED DEFAULT NULL COMMENT 'Thể loại',
  `id_tac_gia` int UNSIGNED DEFAULT NULL COMMENT 'Tác giả',
  `id_nxb` int UNSIGNED DEFAULT NULL,
  `anh_bia` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Đường dẫn ảnh bìa (chọn ảnh bìa)',
  `tinh_trang` enum('Có sẵn','Đang được mượn','Ngừng phát hành') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Có sẵn' COMMENT 'Tình trạng',
  `sach_vat_ly` enum('Còn sách','Hết sách') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Còn sách' COMMENT 'Sách vật lý',
  `so_luot_muon` int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Số lượt mượn/đọc',
  `phim_chuyen_the` enum('Có','Không') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Không' COMMENT 'Phim chuyển thể',
  `ngay_them` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_sach`),
  KEY `fk_sach_genre` (`id_genre`),
  KEY `fk_sach_tac_gia` (`id_tac_gia`),
  KEY `fk_sach_nxb` (`id_nxb`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tac_gia`
--

DROP TABLE IF EXISTS `tac_gia`;
CREATE TABLE IF NOT EXISTS `tac_gia` (
  `id_tac_gia` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `ten_tac_gia` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id_tac_gia`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `the_thanh_toan`
--

DROP TABLE IF EXISTS `the_thanh_toan`;
CREATE TABLE IF NOT EXISTS `the_thanh_toan` (
  `id_the` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_doc_gia` int UNSIGNED NOT NULL,
  `so_the` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Số thẻ tín dụng / Mã số thẻ',
  `ma_cvv` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Mã CVV',
  `het_han` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Hết hạn vào - định dạng MM/YYYY',
  PRIMARY KEY (`id_the`),
  KEY `fk_the_doc_gia` (`id_doc_gia`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `genres`
--
ALTER TABLE `genres`
  ADD CONSTRAINT `genres_ibfk_1` FOREIGN KEY (`id_category`) REFERENCES `categories` (`id_category`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `phieu_muon`
--
ALTER TABLE `phieu_muon`
  ADD CONSTRAINT `phieu_muon_ibfk_1` FOREIGN KEY (`id_doc_gia`) REFERENCES `doc_gia` (`id_doc_gia`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `phieu_muon_ibfk_2` FOREIGN KEY (`id_sach`) REFERENCES `sach` (`id_sach`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sach`
--
ALTER TABLE `sach`
  ADD CONSTRAINT `sach_ibfk_1` FOREIGN KEY (`id_genre`) REFERENCES `genres` (`id_genre`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `sach_ibfk_2` FOREIGN KEY (`id_tac_gia`) REFERENCES `tac_gia` (`id_tac_gia`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `sach_ibfk_3` FOREIGN KEY (`id_nxb`) REFERENCES `nha_xuat_ban` (`id_nxb`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `the_thanh_toan`
--
ALTER TABLE `the_thanh_toan`
  ADD CONSTRAINT `the_thanh_toan_ibfk_1` FOREIGN KEY (`id_doc_gia`) REFERENCES `doc_gia` (`id_doc_gia`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
