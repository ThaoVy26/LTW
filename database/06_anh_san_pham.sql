-- =====================================================================
-- 06_anh_san_pham.sql — Ảnh thật cho các sản phẩm vòng / combo
-- Chạy SAU 05_tach_charm.sql. Ảnh nằm trong assets/img/sp/
-- =====================================================================
USE 52400092_52400313_52400041;

UPDATE SanPham SET hinh_anh = 'assets/img/sp/vong-charm-hoa-co-ban.jpg'
WHERE ten_sp = 'Vòng charm hoa cơ bản';
