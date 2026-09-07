-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Sep 07, 2026 at 08:10 AM
-- Server version: 8.4.11
-- PHP Version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `thu_vien_mini`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id_category` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `ten_category` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id_category`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id_category`, `ten_category`) VALUES
(1, 'manga'),
(2, 'Thiếu nhi'),
(3, 'Truyện tranh'),
(4, 'trahn'),
(5, 'tranh');

-- --------------------------------------------------------

--
-- Table structure for table `chinh_sach`
--

DROP TABLE IF EXISTS `chinh_sach`;
CREATE TABLE IF NOT EXISTS `chinh_sach` (
  `id_chinh_sach` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `ten_chinh_sach` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `gia_tri` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mo_ta` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
  `ho_ten` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ngay_sinh` date DEFAULT NULL COMMENT 'Ngày sinh',
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Email',
  `thanh_pho` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Thành phố',
  `xa` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Xã',
  `dia_chi_chi_tiet` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Địa chỉ chi tiết',
  `ten_tai_khoan` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tên tài khoản',
  `mat_khau` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Mật khẩu - nên lưu hash (bcrypt/password_hash) ở tầng ứng dụng, không lưu plaintext',
  `vai_tro` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'doc_gia',
  `ngay_dang_ky` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lan_dang_nhap_cuoi` datetime DEFAULT NULL,
  PRIMARY KEY (`id_doc_gia`),
  UNIQUE KEY `uq_email` (`email`),
  UNIQUE KEY `uq_ten_tai_khoan` (`ten_tai_khoan`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `doc_gia`
--

INSERT INTO `doc_gia` (`id_doc_gia`, `ho_ten`, `ngay_sinh`, `email`, `thanh_pho`, `xa`, `dia_chi_chi_tiet`, `ten_tai_khoan`, `mat_khau`, `vai_tro`, `ngay_dang_ky`, `lan_dang_nhap_cuoi`) VALUES
(1, '123444', '2026-08-20', 'admin@thuvien.local', NULL, NULL, NULL, 'admin', '$2y$10$sOpIF7nraxKnI1P9vF2xbev0GC2bN85wDM6ImlqwuMQgXb79oSj/.', 'admin', '2026-08-22 11:06:02', '2026-09-07 14:51:21'),
(2, NULL, NULL, 'user01@example.com', NULL, NULL, NULL, 'user01', '$2y$10$ApmBR6SL/HFP/r6ErE77ceP./enACiWCGxyo0x58ghgabeb4SzTli', 'doc_gia', '2026-08-22 11:06:02', NULL),
(3, 'Nguyễn Văn An', NULL, 'docgia1@example.com', NULL, NULL, NULL, 'doc_gia1', 'docgia123', 'doc_gia', '2026-08-23 16:41:06', NULL),
(4, 'Trần Thị Bích', NULL, 'docgia2@example.com', NULL, NULL, NULL, 'doc_gia2', 'docgia123', 'doc_gia', '2026-08-23 16:41:06', NULL),
(5, 'Lê Hoàng Cường', NULL, 'docgia3@example.com', NULL, NULL, NULL, 'doc_gia3', 'docgia123', 'doc_gia', '2026-08-23 16:41:06', NULL),
(6, NULL, NULL, '1@f.com', NULL, NULL, NULL, 'bruh', '$2y$10$mQkYtupFJWQ/fNJeqUzFB..nho2Y5VCpKuIyL7U3kPScLgyKYoxn6', 'doc_gia', '2026-08-24 10:13:58', NULL),
(7, NULL, NULL, '1@fm.com', NULL, NULL, NULL, '1234', '$2y$10$/qI2Z3BAk7Me.64sNUP8O.k5zD4vAQ5VmrTcu3O/AVWqFEscWx/u2', 'doc_gia', '2026-08-24 10:14:21', NULL),
(8, NULL, NULL, '1@g.f', NULL, NULL, NULL, '12345', '$2y$10$wcW2CuUaDVs/DGmP1HthZOb9XLcaa5FTsQI1drQLoplS5n.ZqJkoK', 'doc_gia', '2026-08-25 03:08:32', NULL),
(9, NULL, NULL, '12@fm.com', NULL, NULL, NULL, 'testdemo', '$2y$10$ApKNEO704PIzbyzL4OMwVuRKaCcWFX8CY.nvmfpSEJpO1625CFema', 'doc_gia', '2026-09-06 13:54:14', NULL),
(10, 'Thủ thư 1', NULL, 'thuthu1@thuvien.local', NULL, NULL, NULL, 'thuthu1', '$2y$10$x5qwux6tBwZ0awaeR0vUVeENwKdSGoyfTqo4hNfWj1XbD9u5oJzwa', 'thu_thu', '2026-09-07 06:45:35', '2026-09-07 14:54:39');

-- --------------------------------------------------------

--
-- Table structure for table `genres`
--

DROP TABLE IF EXISTS `genres`;
CREATE TABLE IF NOT EXISTS `genres` (
  `id_genre` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `ten_genre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id_genre`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `genres`
--

INSERT INTO `genres` (`id_genre`, `ten_genre`) VALUES
(3, 'Manga'),
(4, 'Giả tưởng'),
(11, 'ThểThao');

-- --------------------------------------------------------

--
-- Table structure for table `lien_lac`
--

DROP TABLE IF EXISTS `lien_lac`;
CREATE TABLE IF NOT EXISTS `lien_lac` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ho` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `ten` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `so_dien_thoai` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `noi_dung` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `ngay_gui` datetime DEFAULT CURRENT_TIMESTAMP,
  `trang_thai` tinyint DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lien_lac`
--

INSERT INTO `lien_lac` (`id`, `ho`, `ten`, `so_dien_thoai`, `email`, `noi_dung`, `ngay_gui`, `trang_thai`) VALUES
(1, 'Trịnh', 'Minh Quý', '0899643183', 'minhquytrinh88@gmail.com', 'CNTT D2024A', '2026-08-25 20:45:42', 0),
(2, '123', '333', '`1234567890', '1231@k.m', '313131231', '2026-08-25 21:28:09', 0),
(3, '123', '333', '1234567890', '1@fm.com', '111141', '2026-08-25 21:28:23', 0),
(4, '123', '333', '1234567890', '1@fm.com', '111141', '2026-08-26 07:41:42', 0),
(5, '1', '1', '3123', '1@fm.com', '123', '2026-09-06 22:30:46', 0),
(6, '123123', '1231231', '123123123', 't29998473@gmail.com', '1231312321', '2026-09-06 22:34:09', 0),
(7, '123123', '1231231', '123123123', 't29998473@gmail.com', '1231312321', '2026-09-06 22:34:20', 0),
(8, '123123', '1231231', '123123123', 't29998473@gmail.com', '1231312321', '2026-09-06 22:34:21', 0),
(9, '123123', '1231231', '123123123', 't29998473@gmail.com', '1231312321', '2026-09-06 22:34:21', 0),
(10, '123123', '1231231', '123123123', 't29998473@gmail.com', '1231312321', '2026-09-06 22:34:26', 0),
(11, '123123', '1231231', '123123123', 't29998473@gmail.com', '1231312321', '2026-09-06 22:34:31', 0),
(12, '123123', '1231231', '123123123', 't29998473@gmail.com', '1231312321', '2026-09-06 22:35:00', 0),
(13, '123123', '1231231', '123123123', 't29998473@gmail.com', '1231312321', '2026-09-06 22:35:01', 0),
(14, '1231232', '123123', '2131', 'pnga2771@gmail.com', '1231231231', '2026-09-06 22:35:14', 0),
(15, '1231232', '123123', '2131', 'pnga2771@gmail.com', '1231231231', '2026-09-06 22:36:58', 0),
(16, '123', '123', '123', 'admin@thuvien.local', '123123123', '2026-09-06 22:37:05', 0);

-- --------------------------------------------------------

--
-- Table structure for table `nha_xuat_ban`
--

DROP TABLE IF EXISTS `nha_xuat_ban`;
CREATE TABLE IF NOT EXISTS `nha_xuat_ban` (
  `id_nxb` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `ten_nxb` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id_nxb`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `nha_xuat_ban`
--

INSERT INTO `nha_xuat_ban` (`id_nxb`, `ten_nxb`) VALUES
(1, 'NXB Trẻ'),
(2, 'NXB Kim Đồng'),
(3, 'NXB Văn Học'),
(4, 'NXB Hội Nhà Văn');

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
  `trang_thai` enum('Đang mượn','Đã trả','Quá hạn') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Đang mượn',
  PRIMARY KEY (`id_phieu_muon`),
  KEY `fk_pm_doc_gia` (`id_doc_gia`),
  KEY `fk_pm_sach` (`id_sach`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `phieu_muon`
--

INSERT INTO `phieu_muon` (`id_phieu_muon`, `id_doc_gia`, `id_sach`, `so_luong`, `ngay_muon`, `ngay_tra_du_kien`, `ngay_tra_thuc_te`, `trang_thai`) VALUES
(19, 1, 18, 1, '2026-09-06', '2026-09-20', NULL, 'Đang mượn'),
(20, 1, 18, 1, '2026-09-06', '2026-09-20', NULL, 'Đang mượn'),
(21, 1, 16, 1, '2026-09-06', '2026-09-20', NULL, 'Đang mượn'),
(22, 1, 18, 1, '2026-09-06', '2026-09-20', '2026-09-06', 'Đã trả'),
(23, 1, 19, 1, '2026-09-06', '2026-09-20', '2026-09-06', 'Đã trả'),
(24, 1, 19, 1, '2026-09-06', '2026-09-20', '2026-09-06', 'Đã trả'),
(25, 9, 19, 1, '2026-09-06', '2026-09-20', NULL, 'Đang mượn'),
(26, 9, 19, 1, '2026-09-06', '2026-09-20', NULL, 'Đang mượn');

-- --------------------------------------------------------

--
-- Table structure for table `sach`
--

DROP TABLE IF EXISTS `sach`;
CREATE TABLE IF NOT EXISTS `sach` (
  `id_sach` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `ten_sach` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Chọn tên',
  `id_genre` int UNSIGNED DEFAULT NULL COMMENT 'Thể loại',
  `id_tac_gia` int UNSIGNED DEFAULT NULL COMMENT 'Tác giả',
  `id_nxb` int UNSIGNED DEFAULT NULL,
  `anh_bia` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Đường dẫn ảnh bìa (chọn ảnh bìa)',
  `tinh_trang` enum('Có sẵn','Đang được mượn','Ngừng phát hành') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Có sẵn' COMMENT 'Tình trạng',
  `sach_vat_ly` enum('Còn sách','Hết sách') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Còn sách' COMMENT 'Sách vật lý',
  `so_luong_con_lai` int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Số lượng sách còn lại trong kho (bản vật lý)',
  `so_luong` int UNSIGNED NOT NULL DEFAULT '0',
  `so_luot_muon` int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Số lượt mượn/đọc',
  `phim_chuyen_the` enum('Có','Không') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Không' COMMENT 'Phim chuyển thể',
  `ngay_them` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_sach`),
  KEY `fk_sach_genre` (`id_genre`),
  KEY `fk_sach_tac_gia` (`id_tac_gia`),
  KEY `fk_sach_nxb` (`id_nxb`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sach`
--

INSERT INTO `sach` (`id_sach`, `ten_sach`, `id_genre`, `id_tac_gia`, `id_nxb`, `anh_bia`, `tinh_trang`, `sach_vat_ly`, `so_luong_con_lai`, `so_luong`, `so_luot_muon`, `phim_chuyen_the`, `ngay_them`) VALUES
(8, 'jujutsu kaisen', 3, NULL, NULL, 'images/sach-6a8d17bb6467a.jpg', 'Có sẵn', 'Còn sách', 0, 0, 0, 'Không', '2026-08-25 04:19:07'),
(11, 'l4', 3, NULL, NULL, 'images/sach-6a8d5c23f276c.jpg', 'Có sẵn', 'Còn sách', 0, 0, 0, 'Không', '2026-08-25 09:10:59'),
(13, 'MHA', 3, NULL, NULL, 'images/sach-6a8e409fdf7eb.jpg', 'Có sẵn', 'Còn sách', 100, 0, 0, 'Không', '2026-08-26 01:25:51'),
(14, 'The promised neverland', 3, NULL, NULL, 'images/sach-6a8e409fdfaf8.png', 'Có sẵn', 'Còn sách', 0, 0, 0, 'Không', '2026-08-26 01:25:51'),
(15, 'demo3', 4, 8, NULL, 'uploads/covers/bia_6a8e42141d660.jpg', 'Có sẵn', 'Còn sách', 10, 0, 0, 'Không', '2026-08-26 01:32:04'),
(16, 'demo1', 4, 8, NULL, 'uploads/covers/bia_6a8e422a6767d.jpg', 'Có sẵn', 'Còn sách', 10, 0, 1, 'Không', '2026-08-26 01:32:26'),
(17, 'demo2', 4, 8, NULL, 'uploads/covers/bia_6a8e42361725c.jpg', 'Có sẵn', 'Còn sách', 1, 0, 0, 'Không', '2026-08-26 01:32:38'),
(18, 'demo', 4, 8, NULL, 'uploads/covers/bia_6a8e423fdf582.jpg', 'Có sẵn', 'Còn sách', 9, 0, 3, 'Không', '2026-08-26 01:32:47'),
(19, 'onepunch', 3, NULL, NULL, 'images/sach-6a9d6a9745ada.jpg', 'Có sẵn', 'Còn sách', 98, 0, 4, 'Không', '2026-09-06 13:28:55'),
(21, 'sport1', 11, NULL, NULL, 'images/sach-6a9d8e0b15430.jpg', 'Có sẵn', 'Còn sách', 0, 0, 0, 'Không', '2026-09-06 16:00:11'),
(22, 'sport2', 11, NULL, NULL, 'images/sach-6a9d8f8a62474.jpg', 'Có sẵn', 'Còn sách', 0, 0, 0, 'Không', '2026-09-06 16:06:34'),
(23, 'sport3', 11, NULL, NULL, 'images/sach-6a9d8f8a6296b.jpg', 'Có sẵn', 'Còn sách', 0, 0, 0, 'Không', '2026-09-06 16:06:34');

-- --------------------------------------------------------

--
-- Table structure for table `tac_gia`
--

DROP TABLE IF EXISTS `tac_gia`;
CREATE TABLE IF NOT EXISTS `tac_gia` (
  `id_tac_gia` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `ten_tac_gia` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id_tac_gia`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tac_gia`
--

INSERT INTO `tac_gia` (`id_tac_gia`, `ten_tac_gia`) VALUES
(1, 'Nguyễn Nhật Ánh'),
(2, 'Tô Hoài'),
(3, 'J.K. Rowling'),
(4, 'Eiichiro Oda'),
(5, 'Paulo Coelho'),
(6, 'abcxyz'),
(7, 'murata/one'),
(8, 'demo');

-- --------------------------------------------------------

--
-- Table structure for table `the_thanh_toan`
--

DROP TABLE IF EXISTS `the_thanh_toan`;
CREATE TABLE IF NOT EXISTS `the_thanh_toan` (
  `id_the` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_doc_gia` int UNSIGNED NOT NULL,
  `so_the` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Số thẻ tín dụng / Mã số thẻ',
  `ma_cvv` varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Mã CVV',
  `het_han` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Hết hạn vào - định dạng MM/YYYY',
  PRIMARY KEY (`id_the`),
  KEY `fk_the_doc_gia` (`id_doc_gia`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `the_thanh_toan`
--

INSERT INTO `the_thanh_toan` (`id_the`, `id_doc_gia`, `so_the`, `ma_cvv`, `het_han`) VALUES
(1, 4, '123456786123', '123', '12/01');

-- --------------------------------------------------------

--
-- Table structure for table `trang_chu_muc`
--

DROP TABLE IF EXISTS `trang_chu_muc`;
CREATE TABLE IF NOT EXISTS `trang_chu_muc` (
  `id_muc` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `khoa` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Định danh mục, vd: sap_ra_mat',
  `tieu_de` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id_muc`),
  UNIQUE KEY `uq_khoa` (`khoa`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `trang_chu_muc`
--

INSERT INTO `trang_chu_muc` (`id_muc`, `khoa`, `tieu_de`) VALUES
(1, 'sap_ra_mat', 'test'),
(2, 'danh_muc_th_thao', 'SÁCH THỂ THAO');

-- --------------------------------------------------------

--
-- Table structure for table `trang_chu_muc_anh`
--

DROP TABLE IF EXISTS `trang_chu_muc_anh`;
CREATE TABLE IF NOT EXISTS `trang_chu_muc_anh` (
  `id_anh` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_muc` int UNSIGNED NOT NULL,
  `anh_bia` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_sach` int UNSIGNED DEFAULT NULL,
  `thu_tu` int UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_anh`),
  KEY `fk_anh_muc` (`id_muc`),
  KEY `fk_tcma_sach` (`id_sach`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `trang_chu_muc_anh`
--

INSERT INTO `trang_chu_muc_anh` (`id_anh`, `id_muc`, `anh_bia`, `id_sach`, `thu_tu`) VALUES
(33, 1, 'images/onepunch_1788702774.jpg', 19, 0),
(34, 1, 'images/carousel_sap_ra_mat_3_1788624774_1788704902.png', 14, 1),
(35, 1, 'images/sach-6a8d17bb6467a_1788704915.jpg', 8, 2),
(37, 2, 'images/sach-6a9d8e0b15430_1788710425.jpg', 21, 0),
(38, 2, 'images/sach-6a9d8f8a62474_1788710816.jpg', 22, 1),
(39, 2, 'images/sport3_1788710816.jpg', 23, 2);

--
-- Constraints for dumped tables
--

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

--
-- Constraints for table `trang_chu_muc_anh`
--
ALTER TABLE `trang_chu_muc_anh`
  ADD CONSTRAINT `fk_tcma_sach` FOREIGN KEY (`id_sach`) REFERENCES `sach` (`id_sach`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_trang_chu_muc_anh_muc` FOREIGN KEY (`id_muc`) REFERENCES `trang_chu_muc` (`id_muc`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
