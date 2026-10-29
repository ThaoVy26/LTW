-- Chỉ mục cho các cột hay tra cứu: thống kê theo ngày, lọc đơn theo trạng thái,
-- tra cứu đơn / khách theo số điện thoại, lọc sản phẩm đang bán.
USE 52400092_52400313_52400041;

ALTER TABLE DonHang
    ADD INDEX IF NOT EXISTS idx_donhang_ngay (ngay_dat),
    ADD INDEX IF NOT EXISTS idx_donhang_tt   (trang_thai_don),
    ADD INDEX IF NOT EXISTS idx_donhang_sdt  (sdt_nhan);

ALTER TABLE KhachHang
    ADD INDEX IF NOT EXISTS idx_khach_sdt (so_dien_thoai);

ALTER TABLE SanPham
    ADD INDEX IF NOT EXISTS idx_sp_trangthai (trang_thai);
