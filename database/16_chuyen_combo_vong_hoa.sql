USE 52400092_52400313_52400041;

UPDATE SanPham
   SET id_danh_muc = (SELECT id FROM DanhMuc WHERE ten_danh_muc = 'Combo' LIMIT 1),
       ten_sp = 'Combo Vòng charm hoa'
 WHERE ten_sp = 'Vòng charm hoa cơ bản';

DELETE FROM SanPham WHERE ten_sp IN ('Combo Love Story', 'Combo Sporty Chic')
  AND NOT EXISTS (SELECT 1 FROM ChiTietDonHang d WHERE d.id_san_pham = SanPham.id);
UPDATE SanPham SET gia_nhap = ROUND(gia * 0.6) WHERE gia > 0 AND gia_nhap = 0;