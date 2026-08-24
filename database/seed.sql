USE thu_vien_mini;

-- =========================================
-- DỮ LIỆU MẪU CHO BẢNG CHÍNH SÁCH
-- =========================================

INSERT INTO chinh_sach
(
    id_chinh_sach,
    ten_chinh_sach,
    gia_tri,
    mo_ta
)
VALUES
(
    1,
    'so_ngay_muon_toi_da',
    '14',
    'Số ngày tối đa được mượn 1 cuốn sách'
),
(
    2,
    'so_sach_toi_da_moi_doc_gia',
    '5',
    'Số sách tối đa 1 độc giả được mượn cùng lúc'
),
(
    3,
    'tien_phat_moi_ngay',
    '5000',
    'Tiền phạt mỗi ngày trả trễ (VNĐ)'
);
