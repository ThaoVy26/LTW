CHARM ATELIER - Website bán và tự thiết kế vòng tay Italian Charm
Đồ án môn Lập trình Web và Ứng dụng (503073) - Nhóm 19
Thành viên: Lê Thị Như Quỳnh (52400313), Nguyễn Hoàng Thảo Vy (52400092), Hoàng Ngọc Mỹ Anh (52400041)
Công nghệ: PHP 8.x thuần + PDO, MySQL/MariaDB, HTML/CSS/JavaScript. Chạy bằng XAMPP.

======================================================================
1. CÀI ĐẶT (khoảng 10 phút)
======================================================================
Yêu cầu: XAMPP (PHP 8.1 trở lên, MySQL), trình duyệt, và kết nối Internet
(font Google Fonts và thư viện Chart.js tải qua mạng).

Bước 1. Giải nén và chép thư mục "charm_atelier" vào  C:\xampp\htdocs\
        (đường dẫn sau khi chép:  C:\xampp\htdocs\charm_atelier\index.php)

Bước 2. Mở XAMPP Control Panel, bấm Start cho Apache và MySQL.

Bước 3. Mở  http://localhost/phpmyadmin  -> chọn tab "Import" (Nhập)
        và import LẦN LƯỢT, ĐÚNG THỨ TỰ, từng file trong thư mục database/:
          01_schema.sql         tạo CSDL và các bảng
          02_seed.sql           dữ liệu mẫu (sản phẩm, mã giảm giá...)
          03_charm_that.sql     nhóm charm và charm thật
          04_san_pham_charm.sql đưa charm vào trang Sản phẩm
          05_tach_charm.sql     thêm charm (tổng 160 charm)
          06_anh_san_pham.sql   ảnh thật cho sản phẩm vòng
          07_admin.sql          bảng tài khoản quản trị
          08_vy_catalog.sql     cột giá nhập sản phẩm, trạng thái charm
        (Mỗi file chỉ import MỘT lần. Muốn làm lại từ đầu: xoá CSDL
        52400092_52400313_52400041 trong phpMyAdmin rồi import lại từ file 01.)
        Nếu có thêm file 09_*.sql trở đi thì import tiếp theo số thứ tự.

Bước 4. Nếu MySQL của bạn có mật khẩu, sửa DB_PASS trong config/database.php
        (XAMPP mặc định: user root, không mật khẩu, không cần sửa).

Bước 5. Mở trình duyệt:
          Trang khách hàng:  http://localhost/charm_atelier/
          Trang quản trị:    http://localhost/charm_atelier/admin/

======================================================================
2. TÀI KHOẢN VÀ DỮ LIỆU THỬ
======================================================================
Quản trị (admin):  tên đăng nhập  admin   mật khẩu  admin
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
  database/                              file SQL 01 -> 08
  assets/                                CSS, JS, hình ảnh
  uploads/                               ảnh khách gửi khi đặt hàng

======================================================================
5. LỖI THƯỜNG GẶP
======================================================================
- "Không kết nối được cơ sở dữ liệu": chưa bật MySQL trong XAMPP, hoặc chưa import file 01.
- Trang trắng / lỗi font: kiểm tra Internet (Google Fonts, Chart.js).
- Đăng nhập admin báo không đúng: chưa import 07_admin.sql.
- Không tải được ảnh khi đặt hàng: thư mục uploads/ cần cho phép ghi.

======================================================================
6. VIDEO DEMO
======================================================================
Link video: (điền link sau khi upload)
