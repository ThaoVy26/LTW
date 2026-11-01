USE 52400092_52400313_52400041;

-- tài khoản khách: dùng chung bảng TaiKhoan, phân biệt bằng vai_tro
ALTER TABLE TaiKhoan
    MODIFY ten_dang_nhap VARCHAR(150) NOT NULL,
    ADD COLUMN IF NOT EXISTS vai_tro VARCHAR(20) NOT NULL DEFAULT 'admin',
    ADD COLUMN IF NOT EXISTS email VARCHAR(150) NULL,
    ADD COLUMN IF NOT EXISTS so_dien_thoai VARCHAR(15) NULL,
    ADD UNIQUE INDEX IF NOT EXISTS uq_taikhoan_email (email);

-- đơn hàng: email để khách xem lại đơn, và yêu cầu huỷ đơn
ALTER TABLE DonHang
    ADD COLUMN IF NOT EXISTS email_nhan VARCHAR(150) NULL,
    ADD COLUMN IF NOT EXISTS yeu_cau_huy TINYINT NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS ly_do_huy VARCHAR(200) NULL,
    ADD COLUMN IF NOT EXISTS ngay_yeu_cau_huy DATETIME NULL,
    ADD INDEX IF NOT EXISTS idx_donhang_email (email_nhan);

-- đơn cũ: lấy email từ bảng khách hàng
UPDATE DonHang d JOIN KhachHang k ON k.id = d.id_khach_hang
SET d.email_nhan = LOWER(k.email)
WHERE d.email_nhan IS NULL AND k.email IS NOT NULL AND k.email <> '';

-- yêu cầu tư vấn của khách
CREATE TABLE IF NOT EXISTS YeuCauTuVan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_tai_khoan INT NOT NULL,
    noi_dung VARCHAR(1000) NOT NULL,
    tra_loi VARCHAR(1000) NULL,
    trang_thai VARCHAR(20) NOT NULL DEFAULT 'moi',
    ngay_tao DATETIME DEFAULT CURRENT_TIMESTAMP,
    ngay_tra_loi DATETIME NULL,
    CONSTRAINT fk_tuvan_taikhoan FOREIGN KEY (id_tai_khoan) REFERENCES TaiKhoan(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- tài khoản demo cho giảng viên (khach@demo.vn / khach123)
INSERT IGNORE INTO TaiKhoan (ten_dang_nhap, mat_khau_hash, ho_ten, trang_thai, vai_tro, email)
VALUES ('khach@demo.vn', '$2y$10$KAq6KoycgeX9UizWQi1nRukaK7bbW9zKH4Xo44aYQpo3K0R0XCSjO', 'Khách Demo', 'hoat_dong', 'khach', 'khach@demo.vn');
