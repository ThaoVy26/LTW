USE 52400092_52400313_52400041;

-- tư vấn kiểu chat: mỗi tin là một dòng, gom theo tài khoản khách
CREATE TABLE IF NOT EXISTS TinNhanTuVan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_tai_khoan INT NOT NULL,
    nguoi_gui VARCHAR(10) NOT NULL,
    noi_dung VARCHAR(500) NOT NULL,
    ngay_gui DATETIME DEFAULT CURRENT_TIMESTAMP,
    da_doc TINYINT NOT NULL DEFAULT 0,
    INDEX idx_tinnhan_taikhoan (id_tai_khoan, id),
    CONSTRAINT fk_tinnhan_taikhoan FOREIGN KEY (id_tai_khoan) REFERENCES TaiKhoan(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- bảng yêu cầu tư vấn cũ không còn dùng
DROP TABLE IF EXISTS YeuCauTuVan;
