CREATE DATABASE IF NOT EXISTS thu_vien_mini
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE thu_vien_mini;

-- =========================================
-- 1. BẢNG CATEGORIES
-- =========================================

CREATE TABLE IF NOT EXISTS categories (
    id_category INT UNSIGNED NOT NULL AUTO_INCREMENT,
    ten_category VARCHAR(100) NOT NULL,
    PRIMARY KEY (id_category)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- =========================================
-- 2. BẢNG CHÍNH SÁCH
-- =========================================

CREATE TABLE IF NOT EXISTS chinh_sach (
    id_chinh_sach INT UNSIGNED NOT NULL AUTO_INCREMENT,
    ten_chinh_sach VARCHAR(100) NOT NULL,
    gia_tri VARCHAR(50) NOT NULL,
    mo_ta VARCHAR(255) DEFAULT NULL,

    PRIMARY KEY (id_chinh_sach),
    UNIQUE KEY uq_ten_chinh_sach (ten_chinh_sach)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- =========================================
-- 3. BẢNG ĐỘC GIẢ
-- =========================================

CREATE TABLE IF NOT EXISTS doc_gia (
    id_doc_gia INT UNSIGNED NOT NULL AUTO_INCREMENT,
    ho_ten VARCHAR(150) NOT NULL,
    ngay_sinh DATE DEFAULT NULL,
    email VARCHAR(150) NOT NULL,
    thanh_pho VARCHAR(100) DEFAULT NULL,
    xa VARCHAR(100) DEFAULT NULL,
    dia_chi_chi_tiet VARCHAR(255) DEFAULT NULL,
    ten_tai_khoan VARCHAR(50) NOT NULL,
    mat_khau VARCHAR(255) NOT NULL,
    ngay_dang_ky TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id_doc_gia),
    UNIQUE KEY uq_email (email),
    UNIQUE KEY uq_ten_tai_khoan (ten_tai_khoan)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- =========================================
-- 4. BẢNG NHÀ XUẤT BẢN
-- =========================================

CREATE TABLE IF NOT EXISTS nha_xuat_ban (
    id_nxb INT UNSIGNED NOT NULL AUTO_INCREMENT,
    ten_nxb VARCHAR(150) NOT NULL,

    PRIMARY KEY (id_nxb)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- =========================================
-- 5. BẢNG TÁC GIẢ
-- =========================================

CREATE TABLE IF NOT EXISTS tac_gia (
    id_tac_gia INT UNSIGNED NOT NULL AUTO_INCREMENT,
    ten_tac_gia VARCHAR(150) NOT NULL,

    PRIMARY KEY (id_tac_gia)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- =========================================
-- 6. BẢNG THỂ LOẠI
-- =========================================

CREATE TABLE IF NOT EXISTS genres (
    id_genre INT UNSIGNED NOT NULL AUTO_INCREMENT,
    ten_genre VARCHAR(100) NOT NULL,
    id_category INT UNSIGNED NOT NULL,

    PRIMARY KEY (id_genre),

    KEY fk_genres_category (id_category),

    CONSTRAINT genres_ibfk_1
        FOREIGN KEY (id_category)
        REFERENCES categories (id_category)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- =========================================
-- 7. BẢNG SÁCH
-- =========================================

CREATE TABLE IF NOT EXISTS sach (
    id_sach INT UNSIGNED NOT NULL AUTO_INCREMENT,
    ten_sach VARCHAR(255) NOT NULL,
    id_genre INT UNSIGNED DEFAULT NULL,
    id_tac_gia INT UNSIGNED DEFAULT NULL,
    id_nxb INT UNSIGNED DEFAULT NULL,
    anh_bia VARCHAR(255) DEFAULT NULL,
    tinh_trang ENUM(
        'Có sẵn',
        'Đang được mượn',
        'Ngừng phát hành'
    ) NOT NULL DEFAULT 'Có sẵn',
    sach_vat_ly ENUM(
        'Còn sách',
        'Hết sách'
    ) NOT NULL DEFAULT 'Còn sách',
    so_luot_muon INT UNSIGNED NOT NULL DEFAULT 0,
    phim_chuyen_the ENUM(
        'Có',
        'Không'
    ) NOT NULL DEFAULT 'Không',
    ngay_them TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id_sach),

    KEY fk_sach_genre (id_genre),
    KEY fk_sach_tac_gia (id_tac_gia),
    KEY fk_sach_nxb (id_nxb),

    CONSTRAINT sach_ibfk_1
        FOREIGN KEY (id_genre)
        REFERENCES genres (id_genre)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT sach_ibfk_2
        FOREIGN KEY (id_tac_gia)
        REFERENCES tac_gia (id_tac_gia)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT sach_ibfk_3
        FOREIGN KEY (id_nxb)
        REFERENCES nha_xuat_ban (id_nxb)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- =========================================
-- 8. BẢNG PHIẾU MƯỢN
-- =========================================

CREATE TABLE IF NOT EXISTS phieu_muon (
    id_phieu_muon INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_doc_gia INT UNSIGNED NOT NULL,
    id_sach INT UNSIGNED NOT NULL,
    so_luong INT UNSIGNED NOT NULL DEFAULT 1,
    ngay_muon DATE NOT NULL DEFAULT (CURRENT_DATE),
    ngay_tra_du_kien DATE DEFAULT NULL,
    ngay_tra_thuc_te DATE DEFAULT NULL,
    trang_thai ENUM(
        'Đang mượn',
        'Đã trả',
        'Quá hạn'
    ) NOT NULL DEFAULT 'Đang mượn',

    PRIMARY KEY (id_phieu_muon),

    KEY fk_pm_doc_gia (id_doc_gia),
    KEY fk_pm_sach (id_sach),

    CONSTRAINT phieu_muon_ibfk_1
        FOREIGN KEY (id_doc_gia)
        REFERENCES doc_gia (id_doc_gia)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT phieu_muon_ibfk_2
        FOREIGN KEY (id_sach)
        REFERENCES sach (id_sach)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- =========================================
-- 9. BẢNG THẺ THANH TOÁN
-- =========================================

CREATE TABLE IF NOT EXISTS the_thanh_toan (
    id_the INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_doc_gia INT UNSIGNED NOT NULL,
    so_the VARCHAR(20) NOT NULL,
    ma_cvv VARCHAR(4) NOT NULL,
    het_han VARCHAR(7) NOT NULL,

    PRIMARY KEY (id_the),

    KEY fk_the_doc_gia (id_doc_gia),

    CONSTRAINT the_thanh_toan_ibfk_1
        FOREIGN KEY (id_doc_gia)
        REFERENCES doc_gia (id_doc_gia)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;
