-- =====================================================================
-- 04_san_pham_charm.sql
-- Chạy SAU 03_charm_that.sql
--   1. Bỏ nhóm Charm Trơn (nếu còn)
--   2. Bỏ danh mục Đồng hồ và 3 sản phẩm "Charm rời" mẫu
--   3. Mỗi charm của trang Tự thiết kế thành 1 sản phẩm trong danh mục Charm
-- Sản phẩm nào đã nằm trong đơn hàng thì chỉ ẩn (ngung_ban), không xoá.
-- =====================================================================
USE 52400092_52400313_52400041;

-- 1. Bỏ Charm Trơn ------------------------------------------------------
UPDATE ChiTietThietKe SET id_charm = NULL
  WHERE id_charm IN (SELECT c.id FROM Charm c JOIN BoSuuTapCharm b ON b.id = c.id_bo_suu_tap WHERE b.slug = 'tron');
DELETE c FROM Charm c JOIN BoSuuTapCharm b ON b.id = c.id_bo_suu_tap WHERE b.slug = 'tron';
DELETE FROM BoSuuTapCharm WHERE slug = 'tron';
DELETE FROM NhomCharm WHERE slug = 'charm-tron';

-- 2. Bỏ Đồng hồ + charm rời mẫu --------------------------------------------
SET @dong_ho = (SELECT id FROM DanhMuc WHERE ten_danh_muc = 'Đồng hồ');
SET @charm   = (SELECT id FROM DanhMuc WHERE ten_danh_muc = 'Charm');

UPDATE SanPham SET trang_thai = 'ngung_ban' WHERE id_danh_muc IN (@dong_ho, @charm);
DELETE FROM SanPham
  WHERE id_danh_muc IN (@dong_ho, @charm)
    AND id NOT IN (SELECT id_san_pham FROM ChiTietDonHang WHERE id_san_pham IS NOT NULL);
DELETE FROM DanhMuc
  WHERE id = @dong_ho AND NOT EXISTS (SELECT 1 FROM SanPham WHERE id_danh_muc = @dong_ho);

-- 3. Charm → sản phẩm ------------------------------------------------------
-- id_charm nối sản phẩm với charm gốc (dùng để lọc theo nhóm và mở đúng bộ sưu tập)
ALTER TABLE SanPham
    ADD COLUMN id_charm INT NULL AFTER id_danh_muc,
    ADD UNIQUE KEY uq_sanpham_charm (id_charm),
    ADD CONSTRAINT fk_sanpham_charm FOREIGN KEY (id_charm) REFERENCES Charm(id) ON DELETE SET NULL;

INSERT INTO SanPham (ten_sp, id_danh_muc, id_charm, gia, so_luong_ton, hinh_anh, mo_ta, nhan)
SELECT CONCAT('Charm ', c.ten_charm),
       @charm,
       c.id,
       c.gia,
       50,
       c.hinh_anh,
       CONCAT(n.ten_nhom, ' · Bộ ', b.ten_bo_suu_tap,
              CASE c.kieu WHEN 'dai' THEN ' · Charm dài, chiếm 2 ô'
                          WHEN 'day' THEN ' · Có mặt treo' ELSE '' END),
       CASE WHEN n.slug = 'charm-ca-nhan' THEN 'Cá nhân hoá' END
FROM Charm c
JOIN BoSuuTapCharm b ON b.id = c.id_bo_suu_tap
JOIN NhomCharm n     ON n.id = b.id_nhom
ORDER BY n.thu_tu, b.id, c.id;

-- Lưu ý: đổi giá charm thì nhớ đổi cả 2 nơi:
--   UPDATE Charm   SET gia = ... WHERE id = X;
--   UPDATE SanPham SET gia = ... WHERE id_charm = X;
