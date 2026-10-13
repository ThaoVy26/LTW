-- =====================================================================
-- 06_anh_san_pham.sql — Ảnh thật cho các sản phẩm vòng / combo
-- Chạy SAU 05_tach_charm.sql. Ảnh nằm trong assets/img/sp/
-- =====================================================================
USE 52400092_52400313_52400041;

UPDATE SanPham SET hinh_anh = 'assets/img/sp/vong-charm-hoa-co-ban.jpg'
WHERE ten_sp = 'Vòng charm hoa cơ bản';

UPDATE SanPham SET hinh_anh = 'assets/img/sp/combo-vong-doi-tinh-yeu.jpg'
WHERE ten_sp = 'Combo vòng đôi tình yêu';

UPDATE SanPham SET hinh_anh = 'assets/img/sp/combo-3-vong-ban-than.jpg' WHERE ten_sp = 'Combo 3 vòng bạn thân';
UPDATE SanPham SET hinh_anh = 'assets/img/sp/combo-vong-gia-dinh.jpg'   WHERE ten_sp = 'Combo vòng gia đình';
UPDATE SanPham SET hinh_anh = 'assets/img/sp/combo-vong-me-va-be.jpg'   WHERE ten_sp = 'Combo vòng mẹ và bé';
UPDATE SanPham SET hinh_anh = 'assets/img/sp/combo-sweet-dream.jpg'     WHERE ten_sp = 'Combo Sweet Dream';
UPDATE SanPham SET hinh_anh = 'assets/img/sp/combo-y2k-star.jpg'        WHERE ten_sp = 'Combo Y2K Star';
