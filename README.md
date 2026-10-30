CHARM ATELIER - Website bán và tự thiết kế vòng tay Italian Charm
Đồ án môn Lập trình Web và Ứng dụng (503073) - Nhóm 19
Thành viên: Lê Thị Như Quỳnh (52400313), Nguyễn Hoàng Thảo Vy (52400092), Hoàng Ngọc Mỹ Anh (52400041)
Công nghệ: PHP 8.x thuần + PDO, MySQL/MariaDB, HTML/CSS/JavaScript. Chạy bằng XAMPP.

======================================================================
1. CÀI ĐẶT (khoảng 10 phút)
======================================================================
Yêu cầu: XAMPP (PHP 8.1 trở lên, MariaDB/MySQL đi kèm), trình duyệt, và kết nối
Internet (font Google Fonts tải qua mạng).

Bước 1. Giải nén và chép thư mục "charm_atelier" vào  C:\xampp\htdocs\
        (đường dẫn sau khi chép:  C:\xampp\htdocs\charm_atelier\index.php)

Bước 2. Mở XAMPP Control Panel, bấm Start cho Apache và MySQL.

Bước 3. Mở  http://localhost/phpmyadmin  -> chọn tab "Import" (Nhập)
        và import LẦN LƯỢT, ĐÚNG THỨ TỰ từ 01 đến 16, từng file trong
        thư mục database/:
          01_schema.sql                  tạo CSDL và các bảng
          02_seed.sql                    dữ liệu mẫu (sản phẩm, mã giảm giá...)
          03_charm_that.sql              nhóm charm và charm thật
          04_san_pham_charm.sql          đưa charm vào trang Sản phẩm
          05_tach_charm.sql              thêm charm tách từ ảnh bảng mẫu
          06_anh_san_pham.sql            ảnh thật cho sản phẩm vòng
          07_admin.sql                   bảng tài khoản quản trị
          08_vy_catalog.sql              cột giá nhập sản phẩm, trạng thái charm
          09_du_lieu_demo.sql            ảnh thật cho combo, giá nhập mẫu cho thống kê
          10_chi_muc.sql                 chỉ mục (index) tăng tốc tra cứu đơn, thống kê
          11_ma_giam_gia.sql             điều kiện mã giảm giá (đơn tối thiểu, giảm tối đa, số lượt dùng)
          12_xoa_charm_mo.sql            xoá các charm có ảnh bị mờ
          13_bo_sung_charm.sql           bổ sung bộ sưu tập và charm mới
          14_day_hong.sql                thêm dây "Trơn Hồng" cho trang Tự thiết kế
          15_cap_nhat_vong_tron.sql      cập nhật tên, mô tả, ảnh các vòng dây trơn
          16_chuyen_combo_vong_hoa.sql   chuyển "Vòng charm hoa" sang danh mục Combo
        (Mỗi file chỉ import MỘT lần. Muốn làm lại từ đầu: xoá CSDL
        52400092_52400313_52400041 trong phpMyAdmin rồi import lại từ file 01.)

Bước 4. Nếu MySQL của bạn có mật khẩu, sửa DB_PASS trong config/database.php
        (XAMPP mặc định: user root, không mật khẩu, không cần sửa).

Bước 5. Mở trình duyệt:
          Trang khách hàng:  http://localhost/charm_atelier/
          Trang quản trị:    http://localhost/charm_atelier/admin/

======================================================================
2. TÀI KHOẢN VÀ DỮ LIỆU THỬ
======================================================================
Quản trị (admin):  tên đăng nhập  admin   mật khẩu  charm2026
  -> Nên đổi mật khẩu ở mục "Đổi mật khẩu" sau lần đăng nhập đầu.
  -> Đăng nhập sai 5 lần liên tiếp sẽ bị tạm khoá 5 phút.
Khách hàng: KHÔNG cần đăng ký hay đăng nhập. Chỉ chủ shop (admin) đăng nhập.

Mã giảm giá có sẵn để thử ở giỏ hàng:
  CHARM10  giảm 10.000đ        FREESHIP  giảm 30.000đ
  SALE15   giảm 15%            HETHAN    đã hết hạn (để thử báo lỗi)

======================================================================
3. HƯỚNG DẪN THỬ NHANH
======================================================================
A. Khách đặt hàng:  Sản phẩm -> thêm vào giỏ (hoặc "Tự thiết kế" một vòng)
   -> Giỏ hàng (nhập mã giảm giá) -> Thanh toán (SĐT gồm 10 số, bắt đầu bằng 0)
   -> trang "Đặt hàng thành công" hiện mã đơn dạng #CA000001 (có nút In / Lưu PDF).
B. Khách tra cứu đơn:  menu "Tra cứu đơn" -> nhập SĐT đã đặt + mã đơn.
C. Admin xử lý đơn:  Đơn hàng -> bấm vào đơn -> đổi trạng thái
   (Chờ xác nhận -> Đang giao -> Hoàn tất, hoặc Huỷ; huỷ đơn sẽ hoàn lại tồn kho).
   Nút "In hoá đơn" mở hoá đơn A4: bấm "In / Lưu PDF" và chọn "Lưu thành PDF".
D. Admin xem khách hàng:  menu "Khách hàng" (gom theo số điện thoại, xem lịch sử mua).
E. Admin tạo mã giảm giá:  menu "Mã giảm giá" -> "Tạo mã mới" -> thử áp ở giỏ hàng.
Quy ước thống kê: đơn đã huỷ KHÔNG tính vào doanh thu, số đơn, tổng tiền khách đã mua.

======================================================================
4. CẤU TRÚC THƯ MỤC CHÍNH
======================================================================
  index.php, shop.php, product.php, customizer.php, cart.php, checkout.php,
  order-success.php, track-order.php     trang khách
  admin/                                 trang quản trị (cần đăng nhập)
  includes/                              hàm dùng chung (functions, auth, đơn hàng...)
  config/database.php                    kết nối CSDL
  database/                              file SQL 01 -> 16
  assets/                                CSS, JS, hình ảnh
  uploads/                               ảnh khách gửi khi đặt hàng

======================================================================
5. LỖI THƯỜNG GẶP
======================================================================
- "Không kết nối được cơ sở dữ liệu": chưa bật MySQL trong XAMPP, chưa import
  file 01, hoặc DB_PASS trong config/database.php không đúng.
- phpMyAdmin báo "#1049 Unknown database": chưa import 01_schema.sql.
  Import file 01 trước rồi mới import các file còn lại.
- phpMyAdmin báo "#1146 Table ... doesn't exist" hoặc "#1054 Unknown column":
  đã bỏ sót file hoặc import sai thứ tự. Xoá CSDL 52400092_52400313_52400041
  rồi import lại từ 01 đến 16, đúng thứ tự.
- File 10 hoặc 11 báo lỗi cú pháp ở "IF NOT EXISTS": đang dùng MySQL bản cũ.
  Hãy dùng MariaDB đi kèm XAMPP.
- Charm hoặc sản phẩm bị lặp đôi: đã import một file (nhất là 13) hai lần.
  Xoá CSDL và import lại từ file 01.
- Apache không Start được: cổng 80 đang bị chương trình khác dùng (Skype, IIS...).
  Tắt chương trình đó hoặc đổi cổng Apache trong XAMPP.
- Mở trang bị 404 hoặc mất ảnh: thư mục phải đặt tên đúng "charm_atelier" và
  nằm trong C:\xampp\htdocs\ (địa chỉ http://localhost/charm_atelier/).
- Lỗi font chữ: kiểm tra kết nối Internet (Google Fonts).
- Đăng nhập admin báo không đúng: chưa import 07_admin.sql, hoặc nhập sai mật khẩu
  (tài khoản admin / charm2026). Sai 5 lần sẽ bị khoá 5 phút.
- Không tải được ảnh khi đặt hàng: thư mục uploads/ cần cho phép ghi.

======================================================================
6. VIDEO DEMO
======================================================================
Link video: ____________________________________________
(điền link sau khi upload video)
