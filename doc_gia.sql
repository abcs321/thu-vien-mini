CREATE TABLE IF NOT EXISTS `doc_gia` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `ho_ten` VARCHAR(100) NOT NULL,
  `mssv` VARCHAR(20) NOT NULL,
  `lop` VARCHAR(50) NOT NULL,
  `so_dien_thoai` VARCHAR(15) NOT NULL,
  `ten_dang_nhap` VARCHAR(50) NOT NULL,
  `mat_khau` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
