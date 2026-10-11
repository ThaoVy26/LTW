-- =====================================================================
-- 02_seed.sql — Dữ liệu mẫu cho Charm Atelier
-- Chạy SAU file 01_schema.sql (import vào phpMyAdmin theo thứ tự 01 → 02)
-- =====================================================================
USE 52400092_52400313_52400041;

-- Cột "nhãn" để hiện badge Bán chạy / Cặp đôi / Tiết kiệm trên thẻ sản phẩm
ALTER TABLE SanPham ADD COLUMN nhan VARCHAR(30) NULL AFTER mo_ta;

-- Thêm dây Trắng cho đủ 4 màu như thiết kế Figma
INSERT INTO DayVong (ten_day, gia_charm_bac) VALUES ('Trơn Trắng', 8000);

-- ---------------------------------------------------------------------
-- Sản phẩm (danh mục: 1 Vòng cơ bản, 2 Combo, 3 Charm, 4 Đồng hồ)
-- ---------------------------------------------------------------------
INSERT INTO SanPham (ten_sp, id_danh_muc, gia, so_luong_ton, mo_ta, nhan) VALUES
 ('Vòng charm hoa cơ bản',      1, 250000, 20, '1 dây bạc + 5 charm hoa nhí',          'Bán chạy'),
 ('Combo vòng đôi tình yêu',    2, 450000, 10, '2 dây bạc + 6 charm tim',              'Cặp đôi'),
 ('Vòng dây bạc trơn',          1, 180000, 15, 'Dây bạc 18 mắt, chưa gắn charm',       NULL),
 ('Combo 3 vòng bạn thân',      2, 600000,  8, '3 dây bạc + 12 charm tình bạn',        'Tiết kiệm'),
 ('Đồng hồ mini đính charm',    4, 320000, 12, 'Mặt đồng hồ mini gắn trên dây charm',  NULL),
 ('Vòng dây da nâu',            1, 200000, 18, 'Dây da nâu phối mắt charm bạc',        NULL),
 ('Combo vòng gia đình',        2, 780000,  6, '4 dây bạc + 16 charm gia đình',        'Tiết kiệm'),
 ('Charm rời hình hoa (lẻ)',    3,  15000, 50, '1 mắt charm hoa, gắn được mọi dây',     NULL),
 ('Đồng hồ dây charm cổ điển',  4, 350000,  9, 'Đồng hồ mặt tròn, dây charm vàng',     NULL),
 ('Combo vòng mẹ và bé',        2, 520000,  7, '2 dây bạc (size lớn + nhỏ) + 8 charm', 'Mới'),
 ('Vòng dây vàng trơn',         1, 190000, 14, 'Dây vàng 18 mắt, chưa gắn charm',      NULL),
 ('Charm rời hình tim (lẻ)',    3,  15000, 40, '1 mắt charm tim đỏ',                   NULL),
 ('Đồng hồ dây đen',            4, 380000,  5, 'Đồng hồ dây charm đen cá tính',        NULL),
 ('Vòng dây trắng ngọc',        1, 210000, 11, 'Dây trắng ngọc trai 18 mắt',           NULL),
 ('Charm rời chữ cái (lẻ)',     3,  18000, 60, '1 mắt charm chữ cái A–Z',              NULL),
 ('Combo Sweet Dream',          2, 450000, 10, '1 dây bạc + 9 charm pastel',           'Bán chạy'),
 ('Combo Y2K Star',             2, 520000, 10, '1 dây bạc + 11 charm cool',            'Mới'),
 ('Combo Love Story',           2, 480000, 10, '1 dây vàng hồng + 8 charm tim',        'Cặp đôi'),
 ('Combo Sporty Chic',          2, 490000, 10, '1 dây bạc + 10 charm thể thao',        NULL);

-- ---------------------------------------------------------------------
-- Bộ sưu tập charm + charm
-- hinh_anh: đường dẫn ảnh (vd 'uploads/charm/hoa1.png') HOẶC 1 emoji/chữ tạm thời.
-- Khi có ảnh thật chỉ cần UPDATE cột hinh_anh, code tự hiện <img>.
-- ---------------------------------------------------------------------
INSERT INTO BoSuuTapCharm (ten_bo_suu_tap) VALUES
 ('Charm Hoa'), ('Charm Tình Yêu'), ('Charm Thú Cưng'), ('Charm Chữ Cái');

INSERT INTO Charm (id_bo_suu_tap, ten_charm, gia, hinh_anh) VALUES
 (1, 'Hoa anh đào', 25000, '🌸'), (1, 'Hướng dương', 25000, '🌻'), (1, 'Hoa hồng', 25000, '🌹'),
 (1, 'Cỏ bốn lá',   25000, '🍀'), (1, 'Dâm bụt',     25000, '🌺'), (1, 'Cúc trắng', 25000, '🌼'),
 (1, 'Tulip',       25000, '🌷'), (1, 'Bó hoa',      25000, '💐'), (1, 'Oải hương', 25000, '🪻'),
 (1, 'Ngôi sao',    25000, '⭐'),
 (2, 'Trái tim đỏ', 28000, '❤️'), (2, 'Tim lấp lánh', 28000, '💖'), (2, 'Thư tình', 28000, '💌'),
 (2, 'Nhẫn cưới',   28000, '💍'), (2, 'Mũi tên tình yêu', 28000, '💘'), (2, 'Nơ hồng', 28000, '🎀'),
 (2, 'Gấu bông',    28000, '🧸'), (2, 'Đôi tim', 28000, '💕'), (2, 'Cầu vồng', 28000, '🌈'),
 (2, 'Nụ hôn',      28000, '💋'),
 (3, 'Cún con', 22000, '🐶'), (3, 'Mèo con', 22000, '🐱'), (3, 'Thỏ trắng', 22000, '🐰'),
 (3, 'Gấu nâu', 22000, '🐻'), (3, 'Gấu trúc', 22000, '🐼'), (3, 'Cáo nhỏ',   22000, '🦊'),
 (3, 'Chim cánh cụt', 22000, '🐧'), (3, 'Gà con', 22000, '🐥'), (3, 'Dấu chân', 22000, '🐾'),
 (3, 'Cá vàng', 22000, '🐟'),
 (4, 'Chữ A', 20000, 'A'), (4, 'Chữ B', 20000, 'B'), (4, 'Chữ C', 20000, 'C'), (4, 'Chữ D', 20000, 'D'),
 (4, 'Chữ E', 20000, 'E'), (4, 'Chữ G', 20000, 'G'), (4, 'Chữ H', 20000, 'H'), (4, 'Chữ K', 20000, 'K'),
 (4, 'Chữ L', 20000, 'L'), (4, 'Chữ M', 20000, 'M'), (4, 'Chữ N', 20000, 'N'), (4, 'Chữ R', 20000, 'R'),
 (4, 'Chữ S', 20000, 'S'), (4, 'Chữ T', 20000, 'T'), (4, 'Chữ V', 20000, 'V');

-- Thêm vài mã giảm giá để test
INSERT INTO MaGiamGia (ma_code, loai_giam, gia_tri, ngay_bat_dau, ngay_het_han, trang_thai) VALUES
 ('FREESHIP', 'so_tien',   30000, NULL, NULL, 'hoat_dong'),
 ('SALE15',   'phan_tram',    15, NULL, NULL, 'hoat_dong'),
 ('HETHAN',   'so_tien',   50000, '2025-01-01', '2025-02-01', 'hoat_dong');
