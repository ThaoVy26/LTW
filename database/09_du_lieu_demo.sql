-- Chạy sau 08_vy_catalog.sql. Có thể chạy lại mà không tạo dữ liệu trùng.
-- Giá nhập 60% giá bán chỉ là DỮ LIỆU MẪU phục vụ demo thống kê.
USE 52400092_52400313_52400041;
SET NAMES utf8mb4;
START TRANSACTION;

-- Dùng đúng ảnh thật đã có trong repository; giữ nguyên ảnh người dùng đã upload.
UPDATE SanPham SET hinh_anh = 'assets/img/sp/vong-charm-hoa-co-ban.jpg'
WHERE ten_sp = 'Vòng charm hoa cơ bản' AND (hinh_anh IS NULL OR TRIM(hinh_anh) = '');
UPDATE SanPham SET hinh_anh = 'assets/img/sp/combo-vong-doi-tinh-yeu.jpg'
WHERE ten_sp = 'Combo vòng đôi tình yêu' AND (hinh_anh IS NULL OR TRIM(hinh_anh) = '');
UPDATE SanPham SET hinh_anh = 'assets/img/sp/combo-3-vong-ban-than.jpg'
WHERE ten_sp = 'Combo 3 vòng bạn thân' AND (hinh_anh IS NULL OR TRIM(hinh_anh) = '');
UPDATE SanPham SET hinh_anh = 'assets/img/sp/combo-vong-gia-dinh.jpg'
WHERE ten_sp = 'Combo vòng gia đình' AND (hinh_anh IS NULL OR TRIM(hinh_anh) = '');
UPDATE SanPham SET hinh_anh = 'assets/img/sp/combo-vong-me-va-be.jpg'
WHERE ten_sp = 'Combo vòng mẹ và bé' AND (hinh_anh IS NULL OR TRIM(hinh_anh) = '');
UPDATE SanPham SET hinh_anh = 'assets/img/sp/combo-sweet-dream.jpg'
WHERE ten_sp = 'Combo Sweet Dream' AND (hinh_anh IS NULL OR TRIM(hinh_anh) = '');
UPDATE SanPham SET hinh_anh = 'assets/img/sp/combo-y2k-star.jpg'
WHERE ten_sp = 'Combo Y2K Star' AND (hinh_anh IS NULL OR TRIM(hinh_anh) = '');

-- Những sản phẩm chưa có ảnh thật (Love Story, Sporty Chic, các dây vòng mẫu)
-- tạm ngừng bán để không hiện ô ảnh xám. Không gán ảnh của sản phẩm khác.
UPDATE SanPham SET trang_thai = 'ngung_ban'
WHERE trang_thai = 'dang_ban' AND (hinh_anh IS NULL OR TRIM(hinh_anh) = '');

UPDATE SanPham SET gia_nhap = ROUND(gia * 0.6) WHERE gia_nhap = 0;
COMMIT;
