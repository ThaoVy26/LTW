-- =====================================================================
-- 03_charm_that.sql — Dùng ảnh charm thật của nhóm (sinh tự động)
-- Chạy SAU 01_schema.sql và 02_seed.sql
-- =====================================================================
USE 52400092_52400313_52400041;

-- Nhóm charm lớn (Charm Hình, Charm Dây...) — mỗi nhóm có banner ở trang chủ
CREATE TABLE NhomCharm (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    ten_nhom    VARCHAR(100) NOT NULL,
    slug        VARCHAR(50)  NOT NULL UNIQUE,
    hinh_banner VARCHAR(255),
    mo_ta       VARCHAR(255),
    thu_tu      INT NOT NULL DEFAULT 0
);

ALTER TABLE BoSuuTapCharm
    ADD COLUMN id_nhom INT NULL AFTER id,
    ADD COLUMN slug VARCHAR(50) NULL UNIQUE AFTER ten_bo_suu_tap,
    ADD CONSTRAINT fk_bst_nhom FOREIGN KEY (id_nhom) REFERENCES NhomCharm(id);

-- kieu: thuong = 1 ô | dai = chiếm 2 ô | day = có mặt treo bên dưới
ALTER TABLE Charm ADD COLUMN kieu ENUM('thuong','dai','day') NOT NULL DEFAULT 'thuong' AFTER gia;

-- Bỏ bộ charm mẫu (emoji) của 02_seed.sql. Thiết kế cũ dùng charm đó sẽ thành ô bạc trơn.
UPDATE ChiTietThietKe SET id_charm = NULL
  WHERE id_charm IN (SELECT id FROM Charm WHERE id_bo_suu_tap IN (SELECT id FROM BoSuuTapCharm WHERE id_nhom IS NULL));
DELETE FROM Charm WHERE id_bo_suu_tap IN (SELECT id FROM BoSuuTapCharm WHERE id_nhom IS NULL);
DELETE FROM BoSuuTapCharm WHERE id_nhom IS NULL;

INSERT INTO NhomCharm (ten_nhom, slug, hinh_banner, mo_ta, thu_tu) VALUES
 ('Charm Hình', 'charm-hinh', 'assets/img/nhom/charm-hinh.jpg', 'Charm in hình màu sắc: tone hồng, tone đỏ, thú cưng', 1),
 ('Charm Dây', 'charm-day', 'assets/img/nhom/charm-day.jpg', 'Charm có mặt treo lủng lẳng: chữ cái, nam châm', 2),
 ('Charm Dài', 'charm-dai', 'assets/img/nhom/charm-dai.jpg', 'Mắt charm dài gấp đôi, chiếm 2 ô trên vòng', 3),
 ('Charm Ẩn', 'charm-an', 'assets/img/nhom/charm-an.jpg', 'Charm bạc dập nổi tinh tế', 4),
 ('Charm Yêu Cầu Cá Nhân', 'charm-ca-nhan', 'assets/img/nhom/charm-ca-nhan.jpg', 'In ảnh, khắc chữ theo yêu cầu của bạn', 5);

-- Tone Hồng
INSERT INTO BoSuuTapCharm (id_nhom, ten_bo_suu_tap, slug, hinh_anh) SELECT id, 'Tone Hồng', 'tone-hong', 'assets/img/bst/tone-hong.jpg' FROM NhomCharm WHERE slug = 'charm-hinh';
INSERT INTO Charm (id_bo_suu_tap, ten_charm, gia, kieu, hinh_anh)
SELECT b.id, x.ten, x.gia, x.kieu, x.anh FROM BoSuuTapCharm b JOIN (
  SELECT 'Tone Hồng 1' AS ten, 25000 AS gia, 'thuong' AS kieu, 'assets/img/charm/tone-hong/1.webp' AS anh
  UNION ALL SELECT 'Tone Hồng 2' AS ten, 25000 AS gia, 'thuong' AS kieu, 'assets/img/charm/tone-hong/2.webp' AS anh
  UNION ALL SELECT 'Tone Hồng 3' AS ten, 25000 AS gia, 'thuong' AS kieu, 'assets/img/charm/tone-hong/3.webp' AS anh
  UNION ALL SELECT 'Tone Hồng 4' AS ten, 25000 AS gia, 'thuong' AS kieu, 'assets/img/charm/tone-hong/4.webp' AS anh
  UNION ALL SELECT 'Tone Hồng 5' AS ten, 25000 AS gia, 'thuong' AS kieu, 'assets/img/charm/tone-hong/5.webp' AS anh
) x WHERE b.slug = 'tone-hong';

-- Tone Đỏ
INSERT INTO BoSuuTapCharm (id_nhom, ten_bo_suu_tap, slug, hinh_anh) SELECT id, 'Tone Đỏ', 'tone-do', 'assets/img/bst/tone-do.jpg' FROM NhomCharm WHERE slug = 'charm-hinh';
INSERT INTO Charm (id_bo_suu_tap, ten_charm, gia, kieu, hinh_anh)
SELECT b.id, x.ten, x.gia, x.kieu, x.anh FROM BoSuuTapCharm b JOIN (
  SELECT 'Tone Đỏ 1' AS ten, 25000 AS gia, 'thuong' AS kieu, 'assets/img/charm/tone-do/1.webp' AS anh
  UNION ALL SELECT 'Tone Đỏ 2' AS ten, 25000 AS gia, 'thuong' AS kieu, 'assets/img/charm/tone-do/2.webp' AS anh
  UNION ALL SELECT 'Tone Đỏ 3' AS ten, 25000 AS gia, 'thuong' AS kieu, 'assets/img/charm/tone-do/3.webp' AS anh
  UNION ALL SELECT 'Tone Đỏ 4' AS ten, 25000 AS gia, 'thuong' AS kieu, 'assets/img/charm/tone-do/4.webp' AS anh
  UNION ALL SELECT 'Tone Đỏ 5' AS ten, 25000 AS gia, 'thuong' AS kieu, 'assets/img/charm/tone-do/5.webp' AS anh
) x WHERE b.slug = 'tone-do';

-- Thú Cưng
INSERT INTO BoSuuTapCharm (id_nhom, ten_bo_suu_tap, slug, hinh_anh) SELECT id, 'Thú Cưng', 'thu-cung', 'assets/img/bst/thu-cung.jpg' FROM NhomCharm WHERE slug = 'charm-hinh';
INSERT INTO Charm (id_bo_suu_tap, ten_charm, gia, kieu, hinh_anh)
SELECT b.id, x.ten, x.gia, x.kieu, x.anh FROM BoSuuTapCharm b JOIN (
  SELECT 'Thú Cưng 1' AS ten, 25000 AS gia, 'thuong' AS kieu, 'assets/img/charm/thu-cung/1.webp' AS anh
  UNION ALL SELECT 'Thú Cưng 2' AS ten, 25000 AS gia, 'thuong' AS kieu, 'assets/img/charm/thu-cung/2.webp' AS anh
  UNION ALL SELECT 'Thú Cưng 3' AS ten, 25000 AS gia, 'thuong' AS kieu, 'assets/img/charm/thu-cung/3.webp' AS anh
  UNION ALL SELECT 'Thú Cưng 4' AS ten, 25000 AS gia, 'thuong' AS kieu, 'assets/img/charm/thu-cung/4.webp' AS anh
  UNION ALL SELECT 'Thú Cưng 5' AS ten, 25000 AS gia, 'thuong' AS kieu, 'assets/img/charm/thu-cung/5.webp' AS anh
) x WHERE b.slug = 'thu-cung';

-- Dây Chữ
INSERT INTO BoSuuTapCharm (id_nhom, ten_bo_suu_tap, slug, hinh_anh) SELECT id, 'Dây Chữ', 'day-chu', 'assets/img/bst/day-chu.jpg' FROM NhomCharm WHERE slug = 'charm-day';
INSERT INTO Charm (id_bo_suu_tap, ten_charm, gia, kieu, hinh_anh)
SELECT b.id, x.ten, x.gia, x.kieu, x.anh FROM BoSuuTapCharm b JOIN (
  SELECT 'Dây Chữ 1' AS ten, 35000 AS gia, 'day' AS kieu, 'assets/img/charm/day-chu/1.webp' AS anh
  UNION ALL SELECT 'Dây Chữ 2' AS ten, 35000 AS gia, 'day' AS kieu, 'assets/img/charm/day-chu/2.webp' AS anh
  UNION ALL SELECT 'Dây Chữ 3' AS ten, 35000 AS gia, 'day' AS kieu, 'assets/img/charm/day-chu/3.webp' AS anh
) x WHERE b.slug = 'day-chu';

-- Dây Nam Châm
INSERT INTO BoSuuTapCharm (id_nhom, ten_bo_suu_tap, slug, hinh_anh) SELECT id, 'Dây Nam Châm', 'day-nam-cham', 'assets/img/bst/day-nam-cham.jpg' FROM NhomCharm WHERE slug = 'charm-day';
INSERT INTO Charm (id_bo_suu_tap, ten_charm, gia, kieu, hinh_anh)
SELECT b.id, x.ten, x.gia, x.kieu, x.anh FROM BoSuuTapCharm b JOIN (
  SELECT 'Dây Nam Châm 1' AS ten, 39000 AS gia, 'day' AS kieu, 'assets/img/charm/day-nam-cham/1.webp' AS anh
  UNION ALL SELECT 'Dây Nam Châm 2' AS ten, 39000 AS gia, 'day' AS kieu, 'assets/img/charm/day-nam-cham/2.webp' AS anh
  UNION ALL SELECT 'Dây Nam Châm 3' AS ten, 39000 AS gia, 'day' AS kieu, 'assets/img/charm/day-nam-cham/3.webp' AS anh
  UNION ALL SELECT 'Dây Nam Châm 4' AS ten, 39000 AS gia, 'day' AS kieu, 'assets/img/charm/day-nam-cham/4.webp' AS anh
) x WHERE b.slug = 'day-nam-cham';

-- Dài Đáng Yêu
INSERT INTO BoSuuTapCharm (id_nhom, ten_bo_suu_tap, slug, hinh_anh) SELECT id, 'Dài Đáng Yêu', 'dai-dang-yeu', 'assets/img/bst/dai-dang-yeu.jpg' FROM NhomCharm WHERE slug = 'charm-dai';
INSERT INTO Charm (id_bo_suu_tap, ten_charm, gia, kieu, hinh_anh)
SELECT b.id, x.ten, x.gia, x.kieu, x.anh FROM BoSuuTapCharm b JOIN (
  SELECT 'Dài Đáng Yêu 1' AS ten, 45000 AS gia, 'dai' AS kieu, 'assets/img/charm/dai-dang-yeu/1.webp' AS anh
  UNION ALL SELECT 'Dài Đáng Yêu 2' AS ten, 45000 AS gia, 'dai' AS kieu, 'assets/img/charm/dai-dang-yeu/2.webp' AS anh
  UNION ALL SELECT 'Dài Đáng Yêu 3' AS ten, 45000 AS gia, 'dai' AS kieu, 'assets/img/charm/dai-dang-yeu/3.webp' AS anh
) x WHERE b.slug = 'dai-dang-yeu';

-- Chữ Bạc
INSERT INTO BoSuuTapCharm (id_nhom, ten_bo_suu_tap, slug, hinh_anh) SELECT id, 'Chữ Bạc', 'chu-bac', 'assets/img/bst/chu-bac.jpg' FROM NhomCharm WHERE slug = 'charm-an';
INSERT INTO Charm (id_bo_suu_tap, ten_charm, gia, kieu, hinh_anh)
SELECT b.id, x.ten, x.gia, x.kieu, x.anh FROM BoSuuTapCharm b JOIN (
  SELECT 'Chữ Bạc 1' AS ten, 20000 AS gia, 'thuong' AS kieu, 'assets/img/charm/chu-bac/1.webp' AS anh
  UNION ALL SELECT 'Chữ Bạc 2' AS ten, 20000 AS gia, 'thuong' AS kieu, 'assets/img/charm/chu-bac/2.webp' AS anh
  UNION ALL SELECT 'Chữ Bạc 3' AS ten, 20000 AS gia, 'thuong' AS kieu, 'assets/img/charm/chu-bac/3.webp' AS anh
) x WHERE b.slug = 'chu-bac';

-- Hình Ẩn
INSERT INTO BoSuuTapCharm (id_nhom, ten_bo_suu_tap, slug, hinh_anh) SELECT id, 'Hình Ẩn', 'hinh-an', 'assets/img/bst/hinh-an.jpg' FROM NhomCharm WHERE slug = 'charm-an';
INSERT INTO Charm (id_bo_suu_tap, ten_charm, gia, kieu, hinh_anh)
SELECT b.id, x.ten, x.gia, x.kieu, x.anh FROM BoSuuTapCharm b JOIN (
  SELECT 'Hình Ẩn 1' AS ten, 20000 AS gia, 'thuong' AS kieu, 'assets/img/charm/hinh-an/1.webp' AS anh
  UNION ALL SELECT 'Hình Ẩn 2' AS ten, 20000 AS gia, 'thuong' AS kieu, 'assets/img/charm/hinh-an/2.webp' AS anh
  UNION ALL SELECT 'Hình Ẩn 3' AS ten, 20000 AS gia, 'thuong' AS kieu, 'assets/img/charm/hinh-an/3.webp' AS anh
  UNION ALL SELECT 'Hình Ẩn 4' AS ten, 20000 AS gia, 'thuong' AS kieu, 'assets/img/charm/hinh-an/4.webp' AS anh
  UNION ALL SELECT 'Hình Ẩn 5' AS ten, 20000 AS gia, 'thuong' AS kieu, 'assets/img/charm/hinh-an/5.webp' AS anh
) x WHERE b.slug = 'hinh-an';

-- In Ảnh
INSERT INTO BoSuuTapCharm (id_nhom, ten_bo_suu_tap, slug, hinh_anh) SELECT id, 'In Ảnh', 'in-anh', 'assets/img/bst/in-anh.jpg' FROM NhomCharm WHERE slug = 'charm-ca-nhan';
INSERT INTO Charm (id_bo_suu_tap, ten_charm, gia, kieu, hinh_anh)
SELECT b.id, x.ten, x.gia, x.kieu, x.anh FROM BoSuuTapCharm b JOIN (
  SELECT 'In Ảnh 1' AS ten, 55000 AS gia, 'thuong' AS kieu, 'assets/img/charm/in-anh/1.webp' AS anh
  UNION ALL SELECT 'In Ảnh 2' AS ten, 55000 AS gia, 'thuong' AS kieu, 'assets/img/charm/in-anh/2.webp' AS anh
  UNION ALL SELECT 'In Ảnh 3' AS ten, 55000 AS gia, 'thuong' AS kieu, 'assets/img/charm/in-anh/3.webp' AS anh
  UNION ALL SELECT 'In Ảnh 4' AS ten, 55000 AS gia, 'thuong' AS kieu, 'assets/img/charm/in-anh/4.webp' AS anh
  UNION ALL SELECT 'In Ảnh 5' AS ten, 55000 AS gia, 'thuong' AS kieu, 'assets/img/charm/in-anh/5.webp' AS anh
  UNION ALL SELECT 'In Ảnh 6' AS ten, 55000 AS gia, 'dai' AS kieu, 'assets/img/charm/in-anh/6.webp' AS anh
) x WHERE b.slug = 'in-anh';

-- Khắc Chữ
INSERT INTO BoSuuTapCharm (id_nhom, ten_bo_suu_tap, slug, hinh_anh) SELECT id, 'Khắc Chữ', 'khac', 'assets/img/bst/khac.jpg' FROM NhomCharm WHERE slug = 'charm-ca-nhan';
INSERT INTO Charm (id_bo_suu_tap, ten_charm, gia, kieu, hinh_anh)
SELECT b.id, x.ten, x.gia, x.kieu, x.anh FROM BoSuuTapCharm b JOIN (
  SELECT 'Khắc Chữ 1' AS ten, 45000 AS gia, 'thuong' AS kieu, 'assets/img/charm/khac/1.webp' AS anh
  UNION ALL SELECT 'Khắc Chữ 2' AS ten, 45000 AS gia, 'dai' AS kieu, 'assets/img/charm/khac/2.webp' AS anh
  UNION ALL SELECT 'Khắc Chữ 3' AS ten, 45000 AS gia, 'day' AS kieu, 'assets/img/charm/khac/3.webp' AS anh
) x WHERE b.slug = 'khac';

-- Dây In Ảnh
INSERT INTO BoSuuTapCharm (id_nhom, ten_bo_suu_tap, slug, hinh_anh) SELECT id, 'Dây In Ảnh', 'day-in-anh', 'assets/img/bst/day-in-anh.jpg' FROM NhomCharm WHERE slug = 'charm-ca-nhan';
INSERT INTO Charm (id_bo_suu_tap, ten_charm, gia, kieu, hinh_anh)
SELECT b.id, x.ten, x.gia, x.kieu, x.anh FROM BoSuuTapCharm b JOIN (
  SELECT 'Dây In Ảnh 1' AS ten, 65000 AS gia, 'day' AS kieu, 'assets/img/charm/day-in-anh/1.webp' AS anh
  UNION ALL SELECT 'Dây In Ảnh 2' AS ten, 65000 AS gia, 'day' AS kieu, 'assets/img/charm/day-in-anh/2.webp' AS anh
  UNION ALL SELECT 'Dây In Ảnh 3' AS ten, 65000 AS gia, 'day' AS kieu, 'assets/img/charm/day-in-anh/3.webp' AS anh
  UNION ALL SELECT 'Dây In Ảnh 4' AS ten, 65000 AS gia, 'day' AS kieu, 'assets/img/charm/day-in-anh/4.webp' AS anh
) x WHERE b.slug = 'day-in-anh';
