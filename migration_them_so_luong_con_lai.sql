-- Migration: thêm cột lưu số lượng sách còn lại (số bản vật lý hiện có trong kho)
-- Chạy file này trong phpMyAdmin (tab SQL) hoặc mysql CLI, sau khi đã có bảng `sach`.

ALTER TABLE `sach`
  ADD COLUMN `so_luong_con_lai` INT UNSIGNED NOT NULL DEFAULT 0
  COMMENT 'Số lượng sách còn lại trong kho (bản vật lý)'
  AFTER `sach_vat_ly`;
