CREATE DATABASE IF NOT EXISTS 52400092_52400313_52400041
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE 52400092_52400313_52400041;
CREATE TABLE KhachHang (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    ho_ten          VARCHAR(150) NOT NULL,
    so_dien_thoai   VARCHAR(15)  NOT NULL,
    email           VARCHAR(150),
    dia_chi         VARCHAR(255),
    ngay_tao        DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE DanhMuc (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    ten_danh_muc    VARCHAR(100) NOT NULL,
    mo_ta           VARCHAR(255)
);

CREATE TABLE SanPham (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    ten_sp          VARCHAR(200) NOT NULL,
    id_danh_muc     INT NOT NULL,
    gia             DECIMAL(12,0) NOT NULL DEFAULT 0,
    so_luong_ton    INT NOT NULL DEFAULT 0,
    hinh_anh        VARCHAR(255),
    mo_ta           VARCHAR(500),
    trang_thai      VARCHAR(20) DEFAULT 'dang_ban',
    FOREIGN KEY (id_danh_muc) REFERENCES DanhMuc(id)
);

CREATE TABLE BoSuuTapCharm (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    ten_bo_suu_tap  VARCHAR(150) NOT NULL,
    hinh_anh        VARCHAR(255)
);

CREATE TABLE Charm (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    id_bo_suu_tap   INT NOT NULL,
    ten_charm       VARCHAR(150) NOT NULL,
    gia             DECIMAL(12,0) NOT NULL DEFAULT 0,
    hinh_anh        VARCHAR(255),
    FOREIGN KEY (id_bo_suu_tap) REFERENCES BoSuuTapCharm(id)
);

CREATE TABLE KichThuocTay (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    ten_size            VARCHAR(30) NOT NULL,  
    so_charm_toi_da     INT NOT NULL            
);

CREATE TABLE DayVong (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    ten_day         VARCHAR(50) NOT NULL,      
    gia_charm_bac   DECIMAL(12,0) NOT NULL DEFAULT 0  
);

CREATE TABLE ThietKeTuyChinh (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    id_khach_hang   INT NULL,                  
    id_kich_thuoc   INT NOT NULL,
    id_day_vong     INT NOT NULL,
    so_charm_bac    INT NOT NULL DEFAULT 0,     
    tong_gia        DECIMAL(12,0) NOT NULL DEFAULT 0,
    ngay_tao        DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_khach_hang) REFERENCES KhachHang(id),
    FOREIGN KEY (id_kich_thuoc) REFERENCES KichThuocTay(id),
    FOREIGN KEY (id_day_vong)   REFERENCES DayVong(id)
);

CREATE TABLE ChiTietThietKe (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    id_thiet_ke     INT NOT NULL,
    vi_tri_slot     INT NOT NULL,               -- vị trí 0..slotCount-1
    id_charm        INT NULL,                   -- NULL = vẫn là charm bạc nền
    FOREIGN KEY (id_thiet_ke) REFERENCES ThietKeTuyChinh(id) ON DELETE CASCADE,
    FOREIGN KEY (id_charm)    REFERENCES Charm(id),
    UNIQUE KEY uq_thietke_slot (id_thiet_ke, vi_tri_slot)
);

CREATE TABLE MaGiamGia (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    ma_code         VARCHAR(30) NOT NULL UNIQUE,   -- vd: CHARM10
    loai_giam       ENUM('phan_tram','so_tien') NOT NULL DEFAULT 'so_tien',
    gia_tri         DECIMAL(12,0) NOT NULL,
    ngay_bat_dau    DATE,
    ngay_het_han    DATE,
    trang_thai      VARCHAR(20) DEFAULT 'hoat_dong'
);

CREATE TABLE DonHang (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    id_khach_hang       INT NULL,
    id_ma_giam_gia      INT NULL,
    ho_ten_nhan         VARCHAR(150) NOT NULL,
    sdt_nhan            VARCHAR(15) NOT NULL,
    dia_chi_giao        VARCHAR(255) NOT NULL,
    phuong_thuc_tt      ENUM('COD','BANK','WALLET') NOT NULL DEFAULT 'COD',
    ghi_chu             VARCHAR(500),
    tam_tinh            DECIMAL(12,0) NOT NULL DEFAULT 0,
    phi_van_chuyen      DECIMAL(12,0) NOT NULL DEFAULT 0,
    giam_gia            DECIMAL(12,0) NOT NULL DEFAULT 0,
    tong_tien           DECIMAL(12,0) NOT NULL DEFAULT 0,
    trang_thai_don      VARCHAR(30) NOT NULL DEFAULT 'cho_xac_nhan',
    ngay_dat            DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_khach_hang)  REFERENCES KhachHang(id),
    FOREIGN KEY (id_ma_giam_gia) REFERENCES MaGiamGia(id)
);

CREATE TABLE ChiTietDonHang (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    id_don_hang     INT NOT NULL,
    id_san_pham     INT NULL,  
    id_thiet_ke     INT NULL,  
    ten_hien_thi    VARCHAR(200) NOT NULL,  
    don_gia         DECIMAL(12,0) NOT NULL,
    so_luong        INT NOT NULL DEFAULT 1,
    thanh_tien      DECIMAL(12,0) NOT NULL,
    FOREIGN KEY (id_don_hang)  REFERENCES DonHang(id) ON DELETE CASCADE,
    FOREIGN KEY (id_san_pham)  REFERENCES SanPham(id),
    FOREIGN KEY (id_thiet_ke)  REFERENCES ThietKeTuyChinh(id),
    CONSTRAINT chk_dong_hang_1_loai CHECK (
        (id_san_pham IS NOT NULL AND id_thiet_ke IS NULL) OR
        (id_san_pham IS NULL AND id_thiet_ke IS NOT NULL)
    )
);

INSERT INTO DanhMuc (ten_danh_muc) VALUES
 ('Vòng cơ bản'), ('Combo'), ('Charm'), ('Đồng hồ');

INSERT INTO KichThuocTay (ten_size, so_charm_toi_da) VALUES
 ('11.5-12 cm', 14), ('12.5-13 cm', 15), ('13.5-14 cm', 16),
 ('14.5-15 cm', 17), ('15.5-16 cm', 18), ('16.5-17 cm', 19), ('17.5-18 cm', 20);

INSERT INTO DayVong (ten_day, gia_charm_bac) VALUES
 ('Trơn Bạc', 8000), ('Trơn Vàng', 8000), ('Trơn Đen', 7000);

INSERT INTO MaGiamGia (ma_code, loai_giam, gia_tri, trang_thai) VALUES
 ('CHARM10', 'so_tien', 10000, 'hoat_dong');