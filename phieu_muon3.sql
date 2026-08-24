-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th8 23, 2026 lúc 11:59 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `thu_vien_mini`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `phieu_muon`
--

CREATE TABLE `phieu_muon` (
  `id` int(11) NOT NULL,
  `doc_gia_id` int(11) NOT NULL,
  `id_sach` int(11) NOT NULL,
  `so_luong` int(11) NOT NULL DEFAULT 1,
  `ngay_muon` date NOT NULL,
  `ngay_tra_du_kien` date NOT NULL,
  `trang_thai` varchar(50) NOT NULL DEFAULT 'Đang mượn',
  `ghi_chu` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `phieu_muon`
--

INSERT INTO `phieu_muon` (`id`, `doc_gia_id`, `id_sach`, `so_luong`, `ngay_muon`, `ngay_tra_du_kien`, `trang_thai`, `ghi_chu`, `created_at`) VALUES
(1, 1, 3, 1, '2026-03-15', '2026-03-26', 'Đang mượn', '', '2026-08-23 09:28:48'),
(2, 1, 3, 1, '2026-08-23', '2026-09-10', 'Đang mượn', '', '2026-08-23 09:29:23'),
(3, 1, 3, 1, '2026-03-13', '2026-03-26', 'Đã trả', '', '2026-08-23 09:51:16');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `phieu_muon`
--
ALTER TABLE `phieu_muon`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_phieu_muon_doc_gia` (`doc_gia_id`),
  ADD KEY `fk_phieu_muon_sach` (`id_sach`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `phieu_muon`
--
ALTER TABLE `phieu_muon`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `phieu_muon`
--
ALTER TABLE `phieu_muon`
  ADD CONSTRAINT `fk_phieu_muon_doc_gia` FOREIGN KEY (`doc_gia_id`) REFERENCES `doc_gia` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_phieu_muon_sach` FOREIGN KEY (`id_sach`) REFERENCES `sach` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
