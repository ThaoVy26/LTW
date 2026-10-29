USE 52400092_52400313_52400041;

CREATE TABLE IF NOT EXISTS TaiKhoan (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    ten_dang_nhap   VARCHAR(50) NOT NULL UNIQUE,
    mat_khau_hash   VARCHAR(255) NOT NULL,
    ho_ten          VARCHAR(100),
    trang_thai      VARCHAR(20) NOT NULL DEFAULT 'hoat_dong',
    ngay_tao        DATETIME DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO TaiKhoan (ten_dang_nhap, mat_khau_hash, ho_ten)
VALUES ('admin', '$2y$10$e5l/mCnTSYgbpBLmZxfgmeo2H.B8QcpGJJAtwmkl4uTnAxVlJVs.q', 'Quản trị viên');