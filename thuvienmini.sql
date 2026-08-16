-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th8 16, 2026 lúc 09:43 AM
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
-- Cơ sở dữ liệu: `thuvienmini`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `books`
--

CREATE TABLE `books` (
  `id` int(11) NOT NULL,
  `genre_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `author` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT 0.00,
  `quantity` int(11) DEFAULT 0,
  `status` tinyint(4) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `books`
--

INSERT INTO `books` (`id`, `genre_id`, `name`, `author`, `description`, `price`, `quantity`, `status`, `created_at`) VALUES
(1, 2, 'lập trình web', 'quy', 'bla bla', 10000.00, 1, 1, '2026-08-14 04:30:21'),
(2, 3, 'lập trình web', 'quy', '', 110000.00, 1, 1, '2026-08-14 04:36:02'),
(3, 7, 'khoa học', 'quy', '', 0.00, 0, 1, '2026-08-14 05:04:08');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` tinyint(4) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `status`, `created_at`) VALUES
(1, 'công nghệ ', 'sách về công nghệ thông tin', 1, '2026-08-13 14:17:34'),
(2, 'Văn học việt nam', 'Sách văn học Việt Nam và thế giới', 1, '2026-08-13 14:25:15'),
(3, 'Kinh tế', 'Sách về kinh doanh và tài chính', 1, '2026-08-13 14:25:15'),
(4, 'Ngoại ngữ 1', 'Sách học tiếng Anh và các ngoại ngữ khác', 1, '2026-08-13 14:25:15');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `genres`
--

CREATE TABLE `genres` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` tinyint(4) DEFAULT 1,
  `create_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `genres`
--

INSERT INTO `genres` (`id`, `category_id`, `name`, `description`, `status`, `create_at`) VALUES
(1, 1, 'Lập trình', 'Các sách về lập trình và phát triển phần mềm', 1, '2026-08-13 14:33:16'),
(2, 1, 'Cơ sở dữ liệu', 'Các sách về cơ sở dữ liệu và SQL', 1, '2026-08-13 14:33:16'),
(3, 1, 'Mạng máy tính', 'Các sách về mạng máy tính', 1, '2026-08-13 14:33:16'),
(4, 2, 'Tiểu thuyết', 'Các tác phẩm tiểu thuyết', 1, '2026-08-13 14:33:16'),
(5, 2, 'Truyện ngắn', 'Các tuyển tập truyện ngắn', 1, '2026-08-13 14:33:16'),
(6, 2, 'Thơ', 'Các tác phẩm thơ', 1, '2026-08-13 14:33:16'),
(7, 3, 'Marketing', 'Sách về marketing và quảng cáo', 1, '2026-08-13 14:33:16'),
(8, 3, 'Tài chính', 'Sách về tài chính và đầu tư', 1, '2026-08-13 14:33:16'),
(9, 3, 'Quản trị', 'Sách về quản trị doanh nghiệp', 1, '2026-08-13 14:33:16'),
(10, 4, 'Tiếng Anh', 'Sách học tiếng Anh', 1, '2026-08-13 14:33:16'),
(11, 4, 'Tiếng Nhật', 'Sách học tiếng Nhật', 1, '2026-08-13 14:33:16'),
(12, 4, 'Tiếng Hàn', 'Sách học tiếng Hàn', 1, '2026-08-13 14:33:16'),
(13, 4, 'tiếng trung quốc', 'nì hão', 1, '2026-08-14 03:14:44');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_books_genre` (`genre_id`);

--
-- Chỉ mục cho bảng `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `genres`
--
ALTER TABLE `genres`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_genres_category` (`category_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `books`
--
ALTER TABLE `books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `genres`
--
ALTER TABLE `genres`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `books`
--
ALTER TABLE `books`
  ADD CONSTRAINT `fk_books_genre` FOREIGN KEY (`genre_id`) REFERENCES `genres` (`id`) ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `genres`
--
ALTER TABLE `genres`
  ADD CONSTRAINT `fk_genres_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
