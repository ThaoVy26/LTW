-- Mã giảm giá có điều kiện: đơn tối thiểu, giảm tối đa (cho mã %), số lượt dùng tối đa.
-- Để trống (NULL / 0) nghĩa là không giới hạn.
USE 52400092_52400313_52400041;

ALTER TABLE MaGiamGia
    ADD COLUMN IF NOT EXISTS don_toi_thieu DECIMAL(12,0) NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS giam_toi_da   DECIMAL(12,0) NULL,
    ADD COLUMN IF NOT EXISTS luot_toi_da   INT NULL;
