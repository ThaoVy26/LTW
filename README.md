# Charm Atelier — Web bán vòng Italian Charm (PHP + MySQL)

Đồ án cuối kì: nhóm 52400313 · 52400092 · 52400041

## 1. Cài đặt (XAMPP)

1. Copy thư mục `charm_atelier` vào `C:\xampp\htdocs\`.
2. Mở XAMPP Control Panel, bật **Apache** và **MySQL**.
3. Vào http://localhost/phpmyadmin, chọn tab **Import** và import lần lượt:
   - `database/01_schema.sql`: tạo CSDL và các bảng
   - `database/02_seed.sql`: dữ liệu mẫu (sản phẩm, charm, mã giảm giá)
4. Mở http://localhost/charm_atelier/

Nếu MySQL của bạn có mật khẩu, sửa trong `config/database.php`.

## 2. Cấu trúc thư mục

```
charm_atelier/
├── config/database.php     # Kết nối PDO
├── includes/
│   ├── functions.php       # Hàm dùng chung: tiền, CSRF, giỏ hàng (SESSION), mã giảm giá
│   ├── header.php          # Header + menu (dùng chung mọi trang)
│   └── footer.php          # Footer
├── index.php               # Trang chủ
├── shop.php                # Sản phẩm: lọc danh mục, tìm kiếm, khoảng giá, sắp xếp, phân trang
├── product.php             # Chi tiết sản phẩm
├── customizer.php          # Tự thiết kế vòng (kéo thả)
├── cart.php                # Giỏ hàng + mã giảm giá
├── cart_action.php         # Xử lý thêm/sửa/xoá giỏ, áp mã (POST)
├── checkout.php            # Thanh toán → lưu DonHang, ChiTietDonHang
├── order-success.php       # Đặt hàng thành công
├── policy.php              # Các trang chính sách ở footer
├── api/design.php          # API lưu thiết kế vòng (JSON)
├── assets/css/style.css
├── assets/js/              # main.js, shop.js, customizer.js, checkout.js
├── database/               # 01_schema.sql, 02_seed.sql
└── uploads/                # Ảnh khách gửi khi thanh toán
```

## 3. Mức độ hoàn thành Front-end

| Trang / chức năng | Trạng thái |
|---|---|
| Trang chủ: hero, combo nổi bật lấy từ DB, thông tin mua hàng | ✅ |
| Sản phẩm: danh mục (đếm SL), tìm kiếm, lọc giá, sắp xếp, phân trang, thêm giỏ AJAX, yêu thích | ✅ |
| Chi tiết sản phẩm + sản phẩm liên quan | ✅ |
| Tự thiết kế: size, dây, bộ sưu tập, kéo thả / đổi chỗ, hoàn tác / làm lại, Ngang / Dọc / Xoay 360, tạm tính, lưu thiết kế, thêm giỏ, sửa thiết kế từ giỏ | ✅ |
| Giỏ hàng: tăng / giảm / xoá, mã giảm giá (số tiền / %), kiểm tra tồn kho | ✅ |
| Thanh toán: kiểm tra form (JS + PHP), 3 phương thức, upload ảnh, lưu đơn bằng transaction, trừ kho | ✅ |
| Trang chính sách / FAQ | ✅ (nội dung tĩnh) |
| Responsive điện thoại | ✅ |
| Đăng nhập / đăng ký khách hàng | ⏳ Chưa làm |
| Tra cứu đơn hàng, lịch sử đơn | ⏳ Chưa làm |
| Ảnh sản phẩm / charm thật | ⏳ Đang dùng ảnh giả lập (icon / emoji) |
| Trang Admin (Back-end) | ⏳ Chưa làm |

## 4. Ghi chú kỹ thuật

- **Bảo mật:** mọi truy vấn dùng prepared statement (PDO), output qua `e()` để chống XSS, form POST có CSRF token.
- **Giá luôn tính ở server:** giỏ hàng chỉ lưu `id` và số lượng trong SESSION. Giá được đọc lại từ DB, và giá thiết kế do `api/design.php` tự tính.
- **Ảnh:** khi có ảnh thật, chỉ cần `UPDATE SanPham SET hinh_anh = 'uploads/sp/abc.jpg'` (tương tự với cột `Charm.hinh_anh`), code sẽ tự hiện `<img>`.
- `02_seed.sql` có thêm cột `SanPham.nhan` (badge Bán chạy / Cặp đôi...) và dây **Trơn Trắng**. Nhớ cập nhật lại file thiết kế CSDL.
- Mã giảm giá để test: `CHARM10` (-10k), `FREESHIP` (-30k), `SALE15` (-15%), `HETHAN` (đã hết hạn).
