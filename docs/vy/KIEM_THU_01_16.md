# Kiểm thử sau khi pull giao diện mới — 30/10/2026

Nguồn: `origin/main` tại **899ea7f**, đã gộp vào `vy-catalog` tại **0b4fdd7** trước khi kiểm thử. Đây là bản đổi màu chủ đạo sang hồng, bổ sung dữ liệu charm và SQL 12–16.

## Kết luận

- **Không thấy vỡ bố cục ở bốn trang admin**: sản phẩm, form sản phẩm, danh mục, charm. Có 60 điểm kiểm tra trình duyệt đạt, gồm thao tác CRUD, lọc/phân trang, upload/xem trước ảnh, CSRF, kiểm tra tràn ngang và ảnh đang nhìn thấy ở các trang mặc định.
- **Import từ DB trống: 16/16 file thành công, không có file báo lỗi SQL** trên MariaDB 10.4.32 / PHP 8.0.30. Sau import có 270 sản phẩm, 259 charm; sản phẩm có giá bán dương đều có giá nhập dương.
- Có **ba vấn đề cần xử lý/xác nhận** dưới đây. Import thành công không có nghĩa là mọi ảnh và trạng thái dữ liệu đều đúng.
- ERD được đối chiếu với metadata sau SQL 16: **14 bảng, 14 khóa ngoại**, không có bảng/cột mới so với bản SQL 11. Ba cột `MaGiamGia.don_toi_thieu`, `giam_toi_da`, `luot_toi_da` đã đầy đủ trong [PNG](erd.png), [SVG](erd.svg) và [nguồn DOT](erd.dot).

## Các vấn đề phát hiện

| Vấn đề | Bằng chứng và cách tái hiện | Ảnh |
|---|---|---|
| Thiếu hai ảnh charm | Tìm “Charm Dây Chữ A” hoặc “Charm Dây Chữ W” ở sản phẩm. DB vẫn trỏ đến `assets/img/charm/day-chu/g01.webp` và `g22.webp`, nhưng cả hai file không có trong repo mới; trình duyệt hiển thị ảnh vỡ. ID sản phẩm trên DB vừa import là 139 và 159. Hai charm gốc cũng dùng các đường dẫn này. | [A](screenshots/loi-anh-day-chu-A-desktop.png), [W](screenshots/loi-anh-day-chu-W-desktop.png) |
| Số trang đang chọn khó đọc sau đổi màu | Cuộn cuối danh sách sản phẩm/charm: `.sp-admin .dh-pagination .is-active` dùng chữ trắng `rgb(255,255,255)` trên nền `rgb(252,147,160)`; tỷ lệ tương phản tính được khoảng **2,14:1**. Bố cục không vỡ nhưng chữ số khá nhạt. Có thể dùng chữ màu đậm hoặc nền nút đậm hơn khi người phụ trách chốt màu. | [Phân trang](screenshots/phan-trang-mau-moi-desktop.png) |
| Bốn vòng trơn có ảnh nhưng vẫn ngừng bán | SQL 09 ngừng bán sản phẩm thiếu ảnh; SQL 15 điền ảnh nhưng không bật bán lại. Sau import tuần tự, “Vòng dây bạc trơn”, “Vòng dây vàng trơn”, “Vòng dây đen trơn”, “Vòng dây hồng nhám” đều có ảnh nhưng `trang_thai = 'ngung_ban'`. Nếu mục tiêu là bán bốn mẫu này, cần cập nhật trạng thái ở migration phù hợp. | Trạng thái chi tiết trong [kết quả JSON](ket-qua-01-16.json) |

Các vấn đề được ghi nhận để nhóm xử lý theo đúng phần việc.

## Kết quả import từng file

Dùng một DB hoàn toàn mới trên MySQL tạm ở cổng 3307, thư mục dữ liệu độc lập với XAMPP local. Chỉ đổi tên DB trong các câu `CREATE DATABASE`/`USE` để cách ly kiểm thử; nội dung và thứ tự câu lệnh còn lại giữ nguyên. Không import `01_vy_starter.sql` và `03_admin.sql` là các file thử local cũ.

| File | Kết quả |
|---|---|
| `01_schema.sql` | Thành công |
| `02_seed.sql` | Thành công |
| `03_charm_that.sql` | Thành công |
| `04_san_pham_charm.sql` | Thành công |
| `05_tach_charm.sql` | Thành công |
| `06_anh_san_pham.sql` | Thành công |
| `07_admin.sql` | Thành công |
| `08_vy_catalog.sql` | Thành công |
| `09_du_lieu_demo.sql` | Thành công |
| `10_chi_muc.sql` | Thành công |
| `11_ma_giam_gia.sql` | Thành công |
| `12_xoa_charm_mo.sql` | Thành công |
| `13_bo_sung_charm.sql` | Thành công |
| `14_day_hong.sql` | Thành công |
| `15_cap_nhat_vong_tron.sql` | Thành công |
| `16_chuyen_combo_vong_hoa.sql` | Thành công |

Kết quả này dành cho **import lần đầu từ DB trống**; không khẳng định cả bộ có thể chạy lại hoặc áp dụng an toàn lên DB đã có đơn hàng/thiết kế. Ví dụ SQL 12 có lệnh xóa dữ liệu nên cần kiểm thử riêng nếu chạy trên DB có dữ liệu nghiệp vụ.

## Kiểm tra bốn trang admin

| Trang | Đã thử | Kết quả |
|---|---|---|
| `san-pham.php` | Tìm theo tên + danh mục, phân trang ngoài phạm vi, nhãn trạng thái, xóa sản phẩm thử | Đạt; có lỗi ảnh A/W và màu phân trang như trên |
| `san-pham-form.php` | Thêm/sửa, lưu giá nhập, chuyển trạng thái, chọn/xem trước/upload JPG, từ chối CSRF sai | Đạt |
| `danh-muc.php` | Thêm/sửa/xóa danh mục thử | Đạt |
| `charm.php` | Tối đa 20 dòng/trang, lọc bộ sưu tập, lưu riêng một dòng và giữ số trang | Đạt; màu phân trang cần chỉnh |

Kiểm tra bố cục trên Chrome ở viewport **1440×1000**, **768×1024**, **390×844**: không tràn chiều ngang toàn trang; bảng rộng cuộn trong khung riêng. Ảnh thu nhỏ ở vùng nhìn thấy của các trang mặc định tải được. Việc quét toàn bộ ảnh sản phẩm đang bán mới phát hiện hai đường dẫn thiếu ở trên.

## Ảnh cho Mỹ Anh lấy từ vy-catalog

12 ảnh chính đã chụp lại theo màu mới, có thanh tab và thanh địa chỉ Chrome thật. Đây là ảnh chụp nguyên cửa sổ bằng Windows PrintWindow, không ghép thanh địa chỉ. Không gửi qua dịch vụ nhắn tin; ảnh được đưa lên `vy-catalog` theo yêu cầu.

| Trang | Desktop | Tablet | Mobile |
|---|---|---|---|
| Sản phẩm | [Ảnh](screenshots/san-pham-desktop.png) | [Ảnh](screenshots/san-pham-tablet.png) | [Ảnh](screenshots/san-pham-mobile.png) |
| Form sản phẩm | [Ảnh](screenshots/san-pham-form-desktop.png) | [Ảnh](screenshots/san-pham-form-tablet.png) | [Ảnh](screenshots/san-pham-form-mobile.png) |
| Danh mục | [Ảnh](screenshots/danh-muc-desktop.png) | [Ảnh](screenshots/danh-muc-tablet.png) | [Ảnh](screenshots/danh-muc-mobile.png) |
| Charm | [Ảnh](screenshots/charm-desktop.png) | [Ảnh](screenshots/charm-tablet.png) | [Ảnh](screenshots/charm-mobile.png) |

Ảnh có thanh địa chỉ được chụp ở viewport thực tế **1266×686**, **786×686**, **501×686** do giới hạn kích thước cửa sổ Chrome của máy chụp. “Tablet/mobile” là cửa sổ thu hẹp trên máy tính, chưa phải thiết bị thật. Ở cửa sổ hẹp Chrome rút gọn URL; ảnh desktop hiện đầy đủ đường dẫn. Metadata kích thước, URL và màu thực tế: [capture-info.json](screenshots/capture-info.json).

Các giới hạn: chưa thử điện thoại thật, chưa chạy toàn bộ luồng thanh toán/thống kê; DB XAMPP local bị lỗi InnoDB nên toàn bộ kiểm thử dùng MySQL tạm độc lập. Chỉ kết luận trong phạm vi bốn trang admin và import từ DB trống.

Kết quả chi tiết có thể đối chiếu: [ket-qua-01-16.json](ket-qua-01-16.json).
