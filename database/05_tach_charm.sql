-- =====================================================================
-- 05_tach_charm.sql — Charm tách từ ảnh bảng mẫu (sinh tự động)
-- Chạy SAU 04_san_pham_charm.sql
-- =====================================================================
USE 52400092_52400313_52400041;

-- Thứ tự hiển thị charm trong bộ sưu tập (giống vị trí trên ảnh bảng mẫu)
ALTER TABLE Charm ADD COLUMN thu_tu INT NOT NULL DEFAULT 0 AFTER kieu;

-- Tone Hồng: 25 charm (5 dùng lại ảnh gốc nét cao)
UPDATE Charm SET ten_charm = 'Tone Hồng 3', thu_tu = 3 WHERE hinh_anh = 'assets/img/charm/tone-hong/5.webp';
UPDATE Charm SET ten_charm = 'Tone Hồng 7', thu_tu = 7 WHERE hinh_anh = 'assets/img/charm/tone-hong/1.webp';
UPDATE Charm SET ten_charm = 'Tone Hồng 9', thu_tu = 9 WHERE hinh_anh = 'assets/img/charm/tone-hong/4.webp';
UPDATE Charm SET ten_charm = 'Tone Hồng 13', thu_tu = 13 WHERE hinh_anh = 'assets/img/charm/tone-hong/3.webp';
UPDATE Charm SET ten_charm = 'Tone Hồng 24', thu_tu = 24 WHERE hinh_anh = 'assets/img/charm/tone-hong/2.webp';
INSERT INTO Charm (id_bo_suu_tap, ten_charm, gia, kieu, thu_tu, hinh_anh)
SELECT b.id, x.ten, 25000, 'thuong', x.stt, x.anh FROM BoSuuTapCharm b JOIN (
  SELECT 'Tone Hồng 1' AS ten, 1 AS stt, 'assets/img/charm/tone-hong/g01.webp' AS anh
  UNION ALL SELECT 'Tone Hồng 2' AS ten, 2 AS stt, 'assets/img/charm/tone-hong/g02.webp' AS anh
  UNION ALL SELECT 'Tone Hồng 4' AS ten, 4 AS stt, 'assets/img/charm/tone-hong/g04.webp' AS anh
  UNION ALL SELECT 'Tone Hồng 5' AS ten, 5 AS stt, 'assets/img/charm/tone-hong/g05.webp' AS anh
  UNION ALL SELECT 'Tone Hồng 6' AS ten, 6 AS stt, 'assets/img/charm/tone-hong/g06.webp' AS anh
  UNION ALL SELECT 'Tone Hồng 8' AS ten, 8 AS stt, 'assets/img/charm/tone-hong/g08.webp' AS anh
  UNION ALL SELECT 'Tone Hồng 10' AS ten, 10 AS stt, 'assets/img/charm/tone-hong/g10.webp' AS anh
  UNION ALL SELECT 'Tone Hồng 11' AS ten, 11 AS stt, 'assets/img/charm/tone-hong/g11.webp' AS anh
  UNION ALL SELECT 'Tone Hồng 12' AS ten, 12 AS stt, 'assets/img/charm/tone-hong/g12.webp' AS anh
  UNION ALL SELECT 'Tone Hồng 14' AS ten, 14 AS stt, 'assets/img/charm/tone-hong/g14.webp' AS anh
  UNION ALL SELECT 'Tone Hồng 15' AS ten, 15 AS stt, 'assets/img/charm/tone-hong/g15.webp' AS anh
  UNION ALL SELECT 'Tone Hồng 16' AS ten, 16 AS stt, 'assets/img/charm/tone-hong/g16.webp' AS anh
  UNION ALL SELECT 'Tone Hồng 17' AS ten, 17 AS stt, 'assets/img/charm/tone-hong/g17.webp' AS anh
  UNION ALL SELECT 'Tone Hồng 18' AS ten, 18 AS stt, 'assets/img/charm/tone-hong/g18.webp' AS anh
  UNION ALL SELECT 'Tone Hồng 19' AS ten, 19 AS stt, 'assets/img/charm/tone-hong/g19.webp' AS anh
  UNION ALL SELECT 'Tone Hồng 20' AS ten, 20 AS stt, 'assets/img/charm/tone-hong/g20.webp' AS anh
  UNION ALL SELECT 'Tone Hồng 21' AS ten, 21 AS stt, 'assets/img/charm/tone-hong/g21.webp' AS anh
  UNION ALL SELECT 'Tone Hồng 22' AS ten, 22 AS stt, 'assets/img/charm/tone-hong/g22.webp' AS anh
  UNION ALL SELECT 'Tone Hồng 23' AS ten, 23 AS stt, 'assets/img/charm/tone-hong/g23.webp' AS anh
  UNION ALL SELECT 'Tone Hồng 25' AS ten, 25 AS stt, 'assets/img/charm/tone-hong/g25.webp' AS anh
) x WHERE b.slug = 'tone-hong';

-- Tone Đỏ: 16 charm (5 dùng lại ảnh gốc nét cao)
UPDATE Charm SET ten_charm = 'Tone Đỏ 2', thu_tu = 2 WHERE hinh_anh = 'assets/img/charm/tone-do/5.webp';
UPDATE Charm SET ten_charm = 'Tone Đỏ 3', thu_tu = 3 WHERE hinh_anh = 'assets/img/charm/tone-do/3.webp';
UPDATE Charm SET ten_charm = 'Tone Đỏ 6', thu_tu = 6 WHERE hinh_anh = 'assets/img/charm/tone-do/2.webp';
UPDATE Charm SET ten_charm = 'Tone Đỏ 9', thu_tu = 9 WHERE hinh_anh = 'assets/img/charm/tone-do/4.webp';
UPDATE Charm SET ten_charm = 'Tone Đỏ 12', thu_tu = 12 WHERE hinh_anh = 'assets/img/charm/tone-do/1.webp';
INSERT INTO Charm (id_bo_suu_tap, ten_charm, gia, kieu, thu_tu, hinh_anh)
SELECT b.id, x.ten, 25000, 'thuong', x.stt, x.anh FROM BoSuuTapCharm b JOIN (
  SELECT 'Tone Đỏ 1' AS ten, 1 AS stt, 'assets/img/charm/tone-do/g01.webp' AS anh
  UNION ALL SELECT 'Tone Đỏ 4' AS ten, 4 AS stt, 'assets/img/charm/tone-do/g04.webp' AS anh
  UNION ALL SELECT 'Tone Đỏ 5' AS ten, 5 AS stt, 'assets/img/charm/tone-do/g05.webp' AS anh
  UNION ALL SELECT 'Tone Đỏ 7' AS ten, 7 AS stt, 'assets/img/charm/tone-do/g07.webp' AS anh
  UNION ALL SELECT 'Tone Đỏ 8' AS ten, 8 AS stt, 'assets/img/charm/tone-do/g08.webp' AS anh
  UNION ALL SELECT 'Tone Đỏ 10' AS ten, 10 AS stt, 'assets/img/charm/tone-do/g10.webp' AS anh
  UNION ALL SELECT 'Tone Đỏ 11' AS ten, 11 AS stt, 'assets/img/charm/tone-do/g11.webp' AS anh
  UNION ALL SELECT 'Tone Đỏ 13' AS ten, 13 AS stt, 'assets/img/charm/tone-do/g13.webp' AS anh
  UNION ALL SELECT 'Tone Đỏ 14' AS ten, 14 AS stt, 'assets/img/charm/tone-do/g14.webp' AS anh
  UNION ALL SELECT 'Tone Đỏ 15' AS ten, 15 AS stt, 'assets/img/charm/tone-do/g15.webp' AS anh
  UNION ALL SELECT 'Tone Đỏ 16' AS ten, 16 AS stt, 'assets/img/charm/tone-do/g16.webp' AS anh
) x WHERE b.slug = 'tone-do';

-- Thú Cưng: 30 charm (5 dùng lại ảnh gốc nét cao)
UPDATE Charm SET ten_charm = 'Thú Cưng 3', thu_tu = 3 WHERE hinh_anh = 'assets/img/charm/thu-cung/2.webp';
UPDATE Charm SET ten_charm = 'Thú Cưng 6', thu_tu = 6 WHERE hinh_anh = 'assets/img/charm/thu-cung/3.webp';
UPDATE Charm SET ten_charm = 'Thú Cưng 16', thu_tu = 16 WHERE hinh_anh = 'assets/img/charm/thu-cung/1.webp';
UPDATE Charm SET ten_charm = 'Thú Cưng 18', thu_tu = 18 WHERE hinh_anh = 'assets/img/charm/thu-cung/5.webp';
UPDATE Charm SET ten_charm = 'Thú Cưng 28', thu_tu = 28 WHERE hinh_anh = 'assets/img/charm/thu-cung/4.webp';
INSERT INTO Charm (id_bo_suu_tap, ten_charm, gia, kieu, thu_tu, hinh_anh)
SELECT b.id, x.ten, 25000, 'thuong', x.stt, x.anh FROM BoSuuTapCharm b JOIN (
  SELECT 'Thú Cưng 1' AS ten, 1 AS stt, 'assets/img/charm/thu-cung/g01.webp' AS anh
  UNION ALL SELECT 'Thú Cưng 2' AS ten, 2 AS stt, 'assets/img/charm/thu-cung/g02.webp' AS anh
  UNION ALL SELECT 'Thú Cưng 4' AS ten, 4 AS stt, 'assets/img/charm/thu-cung/g04.webp' AS anh
  UNION ALL SELECT 'Thú Cưng 5' AS ten, 5 AS stt, 'assets/img/charm/thu-cung/g05.webp' AS anh
  UNION ALL SELECT 'Thú Cưng 7' AS ten, 7 AS stt, 'assets/img/charm/thu-cung/g07.webp' AS anh
  UNION ALL SELECT 'Thú Cưng 8' AS ten, 8 AS stt, 'assets/img/charm/thu-cung/g08.webp' AS anh
  UNION ALL SELECT 'Thú Cưng 9' AS ten, 9 AS stt, 'assets/img/charm/thu-cung/g09.webp' AS anh
  UNION ALL SELECT 'Thú Cưng 10' AS ten, 10 AS stt, 'assets/img/charm/thu-cung/g10.webp' AS anh
  UNION ALL SELECT 'Thú Cưng 11' AS ten, 11 AS stt, 'assets/img/charm/thu-cung/g11.webp' AS anh
  UNION ALL SELECT 'Thú Cưng 12' AS ten, 12 AS stt, 'assets/img/charm/thu-cung/g12.webp' AS anh
  UNION ALL SELECT 'Thú Cưng 13' AS ten, 13 AS stt, 'assets/img/charm/thu-cung/g13.webp' AS anh
  UNION ALL SELECT 'Thú Cưng 14' AS ten, 14 AS stt, 'assets/img/charm/thu-cung/g14.webp' AS anh
  UNION ALL SELECT 'Thú Cưng 15' AS ten, 15 AS stt, 'assets/img/charm/thu-cung/g15.webp' AS anh
  UNION ALL SELECT 'Thú Cưng 17' AS ten, 17 AS stt, 'assets/img/charm/thu-cung/g17.webp' AS anh
  UNION ALL SELECT 'Thú Cưng 19' AS ten, 19 AS stt, 'assets/img/charm/thu-cung/g19.webp' AS anh
  UNION ALL SELECT 'Thú Cưng 20' AS ten, 20 AS stt, 'assets/img/charm/thu-cung/g20.webp' AS anh
  UNION ALL SELECT 'Thú Cưng 21' AS ten, 21 AS stt, 'assets/img/charm/thu-cung/g21.webp' AS anh
  UNION ALL SELECT 'Thú Cưng 22' AS ten, 22 AS stt, 'assets/img/charm/thu-cung/g22.webp' AS anh
  UNION ALL SELECT 'Thú Cưng 23' AS ten, 23 AS stt, 'assets/img/charm/thu-cung/g23.webp' AS anh
  UNION ALL SELECT 'Thú Cưng 24' AS ten, 24 AS stt, 'assets/img/charm/thu-cung/g24.webp' AS anh
  UNION ALL SELECT 'Thú Cưng 25' AS ten, 25 AS stt, 'assets/img/charm/thu-cung/g25.webp' AS anh
  UNION ALL SELECT 'Thú Cưng 26' AS ten, 26 AS stt, 'assets/img/charm/thu-cung/g26.webp' AS anh
  UNION ALL SELECT 'Thú Cưng 27' AS ten, 27 AS stt, 'assets/img/charm/thu-cung/g27.webp' AS anh
  UNION ALL SELECT 'Thú Cưng 29' AS ten, 29 AS stt, 'assets/img/charm/thu-cung/g29.webp' AS anh
  UNION ALL SELECT 'Thú Cưng 30' AS ten, 30 AS stt, 'assets/img/charm/thu-cung/g30.webp' AS anh
) x WHERE b.slug = 'thu-cung';

-- Dài Đáng Yêu: 9 charm (3 dùng lại ảnh gốc nét cao)
UPDATE Charm SET ten_charm = 'Dài Đáng Yêu 5', thu_tu = 5 WHERE hinh_anh = 'assets/img/charm/dai-dang-yeu/1.webp';
UPDATE Charm SET ten_charm = 'Dài Đáng Yêu 7', thu_tu = 7 WHERE hinh_anh = 'assets/img/charm/dai-dang-yeu/2.webp';
UPDATE Charm SET ten_charm = 'Dài Đáng Yêu 9', thu_tu = 9 WHERE hinh_anh = 'assets/img/charm/dai-dang-yeu/3.webp';
INSERT INTO Charm (id_bo_suu_tap, ten_charm, gia, kieu, thu_tu, hinh_anh)
SELECT b.id, x.ten, 45000, 'dai', x.stt, x.anh FROM BoSuuTapCharm b JOIN (
  SELECT 'Dài Đáng Yêu 1' AS ten, 1 AS stt, 'assets/img/charm/dai-dang-yeu/g01.webp' AS anh
  UNION ALL SELECT 'Dài Đáng Yêu 2' AS ten, 2 AS stt, 'assets/img/charm/dai-dang-yeu/g02.webp' AS anh
  UNION ALL SELECT 'Dài Đáng Yêu 3' AS ten, 3 AS stt, 'assets/img/charm/dai-dang-yeu/g03.webp' AS anh
  UNION ALL SELECT 'Dài Đáng Yêu 4' AS ten, 4 AS stt, 'assets/img/charm/dai-dang-yeu/g04.webp' AS anh
  UNION ALL SELECT 'Dài Đáng Yêu 6' AS ten, 6 AS stt, 'assets/img/charm/dai-dang-yeu/g06.webp' AS anh
  UNION ALL SELECT 'Dài Đáng Yêu 8' AS ten, 8 AS stt, 'assets/img/charm/dai-dang-yeu/g08.webp' AS anh
) x WHERE b.slug = 'dai-dang-yeu';

-- Dây Chữ: 25 charm (3 dùng lại ảnh gốc nét cao)
UPDATE Charm SET ten_charm = 'Dây Chữ Q', thu_tu = 16 WHERE hinh_anh = 'assets/img/charm/day-chu/2.webp';
UPDATE Charm SET ten_charm = 'Dây Chữ Y', thu_tu = 24 WHERE hinh_anh = 'assets/img/charm/day-chu/3.webp';
UPDATE Charm SET ten_charm = 'Dây Chữ Z', thu_tu = 25 WHERE hinh_anh = 'assets/img/charm/day-chu/1.webp';
INSERT INTO Charm (id_bo_suu_tap, ten_charm, gia, kieu, thu_tu, hinh_anh)
SELECT b.id, x.ten, 35000, 'day', x.stt, x.anh FROM BoSuuTapCharm b JOIN (
  SELECT 'Dây Chữ A' AS ten, 1 AS stt, 'assets/img/charm/day-chu/g01.webp' AS anh
  UNION ALL SELECT 'Dây Chữ B' AS ten, 2 AS stt, 'assets/img/charm/day-chu/g02.webp' AS anh
  UNION ALL SELECT 'Dây Chữ C' AS ten, 3 AS stt, 'assets/img/charm/day-chu/g03.webp' AS anh
  UNION ALL SELECT 'Dây Chữ D' AS ten, 4 AS stt, 'assets/img/charm/day-chu/g04.webp' AS anh
  UNION ALL SELECT 'Dây Chữ E' AS ten, 5 AS stt, 'assets/img/charm/day-chu/g05.webp' AS anh
  UNION ALL SELECT 'Dây Chữ F' AS ten, 6 AS stt, 'assets/img/charm/day-chu/g06.webp' AS anh
  UNION ALL SELECT 'Dây Chữ G' AS ten, 7 AS stt, 'assets/img/charm/day-chu/g07.webp' AS anh
  UNION ALL SELECT 'Dây Chữ H' AS ten, 8 AS stt, 'assets/img/charm/day-chu/g08.webp' AS anh
  UNION ALL SELECT 'Dây Chữ J' AS ten, 9 AS stt, 'assets/img/charm/day-chu/g09.webp' AS anh
  UNION ALL SELECT 'Dây Chữ K' AS ten, 10 AS stt, 'assets/img/charm/day-chu/g10.webp' AS anh
  UNION ALL SELECT 'Dây Chữ L' AS ten, 11 AS stt, 'assets/img/charm/day-chu/g11.webp' AS anh
  UNION ALL SELECT 'Dây Chữ M' AS ten, 12 AS stt, 'assets/img/charm/day-chu/g12.webp' AS anh
  UNION ALL SELECT 'Dây Chữ N' AS ten, 13 AS stt, 'assets/img/charm/day-chu/g13.webp' AS anh
  UNION ALL SELECT 'Dây Chữ O' AS ten, 14 AS stt, 'assets/img/charm/day-chu/g14.webp' AS anh
  UNION ALL SELECT 'Dây Chữ P' AS ten, 15 AS stt, 'assets/img/charm/day-chu/g15.webp' AS anh
  UNION ALL SELECT 'Dây Chữ R' AS ten, 17 AS stt, 'assets/img/charm/day-chu/g17.webp' AS anh
  UNION ALL SELECT 'Dây Chữ S' AS ten, 18 AS stt, 'assets/img/charm/day-chu/g18.webp' AS anh
  UNION ALL SELECT 'Dây Chữ T' AS ten, 19 AS stt, 'assets/img/charm/day-chu/g19.webp' AS anh
  UNION ALL SELECT 'Dây Chữ U' AS ten, 20 AS stt, 'assets/img/charm/day-chu/g20.webp' AS anh
  UNION ALL SELECT 'Dây Chữ V' AS ten, 21 AS stt, 'assets/img/charm/day-chu/g21.webp' AS anh
  UNION ALL SELECT 'Dây Chữ W' AS ten, 22 AS stt, 'assets/img/charm/day-chu/g22.webp' AS anh
  UNION ALL SELECT 'Dây Chữ X' AS ten, 23 AS stt, 'assets/img/charm/day-chu/g23.webp' AS anh
) x WHERE b.slug = 'day-chu';

-- Chữ Bạc: 26 charm (3 dùng lại ảnh gốc nét cao)
UPDATE Charm SET ten_charm = 'Chữ Bạc A', thu_tu = 1 WHERE hinh_anh = 'assets/img/charm/chu-bac/1.webp';
UPDATE Charm SET ten_charm = 'Chữ Bạc Q', thu_tu = 17 WHERE hinh_anh = 'assets/img/charm/chu-bac/2.webp';
UPDATE Charm SET ten_charm = 'Chữ Bạc V', thu_tu = 22 WHERE hinh_anh = 'assets/img/charm/chu-bac/3.webp';
INSERT INTO Charm (id_bo_suu_tap, ten_charm, gia, kieu, thu_tu, hinh_anh)
SELECT b.id, x.ten, 20000, 'thuong', x.stt, x.anh FROM BoSuuTapCharm b JOIN (
  SELECT 'Chữ Bạc B' AS ten, 2 AS stt, 'assets/img/charm/chu-bac/g02.webp' AS anh
  UNION ALL SELECT 'Chữ Bạc C' AS ten, 3 AS stt, 'assets/img/charm/chu-bac/g03.webp' AS anh
  UNION ALL SELECT 'Chữ Bạc D' AS ten, 4 AS stt, 'assets/img/charm/chu-bac/g04.webp' AS anh
  UNION ALL SELECT 'Chữ Bạc E' AS ten, 5 AS stt, 'assets/img/charm/chu-bac/g05.webp' AS anh
  UNION ALL SELECT 'Chữ Bạc F' AS ten, 6 AS stt, 'assets/img/charm/chu-bac/g06.webp' AS anh
  UNION ALL SELECT 'Chữ Bạc G' AS ten, 7 AS stt, 'assets/img/charm/chu-bac/g07.webp' AS anh
  UNION ALL SELECT 'Chữ Bạc H' AS ten, 8 AS stt, 'assets/img/charm/chu-bac/g08.webp' AS anh
  UNION ALL SELECT 'Chữ Bạc I' AS ten, 9 AS stt, 'assets/img/charm/chu-bac/g09.webp' AS anh
  UNION ALL SELECT 'Chữ Bạc J' AS ten, 10 AS stt, 'assets/img/charm/chu-bac/g10.webp' AS anh
  UNION ALL SELECT 'Chữ Bạc K' AS ten, 11 AS stt, 'assets/img/charm/chu-bac/g11.webp' AS anh
  UNION ALL SELECT 'Chữ Bạc L' AS ten, 12 AS stt, 'assets/img/charm/chu-bac/g12.webp' AS anh
  UNION ALL SELECT 'Chữ Bạc M' AS ten, 13 AS stt, 'assets/img/charm/chu-bac/g13.webp' AS anh
  UNION ALL SELECT 'Chữ Bạc N' AS ten, 14 AS stt, 'assets/img/charm/chu-bac/g14.webp' AS anh
  UNION ALL SELECT 'Chữ Bạc O' AS ten, 15 AS stt, 'assets/img/charm/chu-bac/g15.webp' AS anh
  UNION ALL SELECT 'Chữ Bạc P' AS ten, 16 AS stt, 'assets/img/charm/chu-bac/g16.webp' AS anh
  UNION ALL SELECT 'Chữ Bạc R' AS ten, 18 AS stt, 'assets/img/charm/chu-bac/g18.webp' AS anh
  UNION ALL SELECT 'Chữ Bạc S' AS ten, 19 AS stt, 'assets/img/charm/chu-bac/g19.webp' AS anh
  UNION ALL SELECT 'Chữ Bạc T' AS ten, 20 AS stt, 'assets/img/charm/chu-bac/g20.webp' AS anh
  UNION ALL SELECT 'Chữ Bạc U' AS ten, 21 AS stt, 'assets/img/charm/chu-bac/g21.webp' AS anh
  UNION ALL SELECT 'Chữ Bạc W' AS ten, 23 AS stt, 'assets/img/charm/chu-bac/g23.webp' AS anh
  UNION ALL SELECT 'Chữ Bạc X' AS ten, 24 AS stt, 'assets/img/charm/chu-bac/g24.webp' AS anh
  UNION ALL SELECT 'Chữ Bạc Y' AS ten, 25 AS stt, 'assets/img/charm/chu-bac/g25.webp' AS anh
  UNION ALL SELECT 'Chữ Bạc Z' AS ten, 26 AS stt, 'assets/img/charm/chu-bac/g26.webp' AS anh
) x WHERE b.slug = 'chu-bac';

-- Hình Ẩn: 12 charm (5 dùng lại ảnh gốc nét cao)
UPDATE Charm SET ten_charm = 'Hình Ẩn 3', thu_tu = 3 WHERE hinh_anh = 'assets/img/charm/hinh-an/5.webp';
UPDATE Charm SET ten_charm = 'Hình Ẩn 4', thu_tu = 4 WHERE hinh_anh = 'assets/img/charm/hinh-an/4.webp';
UPDATE Charm SET ten_charm = 'Hình Ẩn 6', thu_tu = 6 WHERE hinh_anh = 'assets/img/charm/hinh-an/3.webp';
UPDATE Charm SET ten_charm = 'Hình Ẩn 9', thu_tu = 9 WHERE hinh_anh = 'assets/img/charm/hinh-an/2.webp';
UPDATE Charm SET ten_charm = 'Hình Ẩn 10', thu_tu = 10 WHERE hinh_anh = 'assets/img/charm/hinh-an/1.webp';
INSERT INTO Charm (id_bo_suu_tap, ten_charm, gia, kieu, thu_tu, hinh_anh)
SELECT b.id, x.ten, 20000, 'thuong', x.stt, x.anh FROM BoSuuTapCharm b JOIN (
  SELECT 'Hình Ẩn 1' AS ten, 1 AS stt, 'assets/img/charm/hinh-an/g01.webp' AS anh
  UNION ALL SELECT 'Hình Ẩn 2' AS ten, 2 AS stt, 'assets/img/charm/hinh-an/g02.webp' AS anh
  UNION ALL SELECT 'Hình Ẩn 5' AS ten, 5 AS stt, 'assets/img/charm/hinh-an/g05.webp' AS anh
  UNION ALL SELECT 'Hình Ẩn 7' AS ten, 7 AS stt, 'assets/img/charm/hinh-an/g07.webp' AS anh
  UNION ALL SELECT 'Hình Ẩn 8' AS ten, 8 AS stt, 'assets/img/charm/hinh-an/g08.webp' AS anh
  UNION ALL SELECT 'Hình Ẩn 11' AS ten, 11 AS stt, 'assets/img/charm/hinh-an/g11.webp' AS anh
  UNION ALL SELECT 'Hình Ẩn 12' AS ten, 12 AS stt, 'assets/img/charm/hinh-an/g12.webp' AS anh
) x WHERE b.slug = 'hinh-an';

-- Các bộ không tách (Nam Châm, Cá Nhân): giữ thứ tự theo id
UPDATE Charm SET thu_tu = id WHERE thu_tu = 0;

-- Đồng bộ tên sản phẩm với tên charm mới
UPDATE SanPham sp JOIN Charm c ON c.id = sp.id_charm SET sp.ten_sp = CONCAT('Charm ', c.ten_charm);

-- Tạo sản phẩm cho charm mới (danh mục Charm)
INSERT INTO SanPham (ten_sp, id_danh_muc, id_charm, gia, so_luong_ton, hinh_anh, mo_ta, nhan)
SELECT CONCAT('Charm ', c.ten_charm),
       (SELECT id FROM DanhMuc WHERE ten_danh_muc = 'Charm' LIMIT 1),
       c.id, c.gia, 50, c.hinh_anh,
       CONCAT(n.ten_nhom, ' · Bộ ', b.ten_bo_suu_tap,
              CASE c.kieu WHEN 'dai' THEN ' · Charm dài, chiếm 2 ô' WHEN 'day' THEN ' · Có mặt treo' ELSE '' END),
       CASE WHEN n.slug = 'charm-ca-nhan' THEN 'Cá nhân hoá' END
FROM Charm c
JOIN BoSuuTapCharm b ON b.id = c.id_bo_suu_tap
JOIN NhomCharm n     ON n.id = b.id_nhom
WHERE NOT EXISTS (SELECT 1 FROM SanPham sp WHERE sp.id_charm = c.id)
ORDER BY n.thu_tu, b.id, c.thu_tu;
