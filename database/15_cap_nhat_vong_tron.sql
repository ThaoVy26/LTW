USE 52400092_52400313_52400041;

UPDATE SanPham SET hinh_anh = 'assets/img/sp/vong-day-bac-tron.jpg'  WHERE ten_sp = 'Vòng dây bạc trơn';
UPDATE SanPham SET hinh_anh = 'assets/img/sp/vong-day-vang-tron.jpg' WHERE ten_sp = 'Vòng dây vàng trơn';

UPDATE SanPham SET ten_sp = 'Vòng dây đen trơn',
                   mo_ta = 'Dây đen 18 mắt, chưa gắn charm',
                   hinh_anh = 'assets/img/sp/vong-day-den-tron.jpg'
 WHERE ten_sp = 'Vòng dây trắng ngọc';

UPDATE SanPham SET ten_sp = 'Vòng dây hồng nhám',
                   mo_ta = 'Dây hồng nhám 18 mắt, chưa gắn charm',
                   hinh_anh = 'assets/img/sp/vong-day-hong-nham.jpg'
 WHERE ten_sp = 'Vòng dây da nâu';
