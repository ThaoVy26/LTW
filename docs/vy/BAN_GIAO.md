# Phần việc của Vy — đối chiếu KEHOACH2

Cập nhật 30/10/2026. Nhánh triển khai: `vy-catalog`.

| Mục | Kết quả | Trạng thái |
|---|---|---|
| V1 | Bốn trang dùng `ad-box`, bảng `ad-table`, nút chung, ảnh nhỏ, nhãn trạng thái tiếng Việt; form sản phẩm hai cột và xem trước ảnh; charm lọc theo bộ sưu tập, phân trang 20 dòng, lưu riêng từng dòng | Đã triển khai và thử trên Chrome |
| V2 | `database/09_du_lieu_demo.sql`: điền đường dẫn ảnh thật cho mẫu có ảnh, ngừng bán mẫu còn thiếu ảnh, giá nhập mẫu bằng 60% giá bán khi giá nhập đang bằng 0 | Đã import và thử chạy lại trên DB kiểm thử |
| V3 | [ERD PNG](erd.png), [SVG](erd.svg), [nguồn Graphviz](erd.dot), [danh sách khóa ngoại](ERD.md) | Đã đối chiếu schema 01–11, đủ 14 bảng, 14 khóa ngoại và ba cột mới của MaGiamGia |
| V4 | [Kịch bản quay và ghép video](KICH_BAN_VIDEO.md) | Chờ ba thành viên quay phần của mình; chưa có video MP4 hoàn chỉnh |
| V5 | [Kết quả kiểm thử](KIEM_THU.md), 12 ảnh desktop/tablet/mobile trong `screenshots/` | Đã chụp lại cửa sổ Chrome thật có thanh địa chỉ ngày 30/10; chưa thử điện thoại thật |

## Dùng dữ liệu mẫu

Với DB nhóm đã import đầy đủ 01–08, import thêm `database/09_du_lieu_demo.sql`. File có thể chạy lại, không tạo thêm sản phẩm và không thay ảnh upload/giá nhập đã điền. Giá nhập 60% chỉ dùng cho demo, phải ghi rõ trong báo cáo. Lợi nhuận thực tế còn phụ thuộc đơn hàng đã hoàn tất và công thức thống kê của Quỳnh.

Ở máy hiện tại, DB `52400092_52400313_52400041` là bản starter: không có sản phẩm/charm, thiếu `NhomCharm` và `ChiTietDonHang` còn cấu trúc rút gọn. **File 09 không chuyển đổi DB starter sang bản đầy đủ.** Không import đè toàn bộ schema lên DB này. Sao lưu dữ liệu cần giữ rồi dùng một DB mới để import bộ gốc theo thứ tự bên dưới; cấu hình kết nối DB thuộc phần tích hợp của Quỳnh.

Thứ tự các file chuẩn đã kiểm thử:

1. `01_schema.sql`
2. `02_seed.sql`
3. `03_charm_that.sql`
4. `04_san_pham_charm.sql`
5. `05_tach_charm.sql`
6. `06_anh_san_pham.sql`
7. `07_admin.sql`
8. `08_vy_catalog.sql`
9. `09_du_lieu_demo.sql`
10. `10_chi_muc.sql`
11. `11_ma_giam_gia.sql`

`01_vy_starter.sql` và `03_admin.sql` là file thử local cũ, không thuộc chuỗi import chuẩn này. Migration 10–11 của Quỳnh đã được đồng bộ và import thành công trên MySQL tạm độc lập.

## Phạm vi và giới hạn

Chỉ sửa bốn trang admin của Vy, tạo CSS riêng, file SQL 09 và tài liệu/ảnh bàn giao. Không thay đổi file của thành viên khác. CSS bảng rộng cuộn trong khung bảng trên mobile, không kéo rộng toàn trang. Tên `includes/vy_catalog.php` và `08_vy_catalog.sql` được giữ nguyên theo yêu cầu.

Ảnh hiện tại được chụp trực tiếp cửa sổ Chrome riêng bằng Windows PrintWindow, có thanh tab và thanh địa chỉ thật, không ghép khung trình duyệt. Dữ liệu là bộ mẫu từ SQL 01–11 chạy ở máy chủ kiểm thử `127.0.0.1:8091`; địa chỉ này chỉ phục vụ chụp ảnh, không phải link triển khai. Xem kích thước và URL từng ảnh trong `screenshots/capture-info.json`. Bản mobile/tablet là cửa sổ desktop thu hẹp để kiểm tra responsive, không phải ảnh chụp thiết bị thật. Trên cửa sổ hẹp Chrome có thể rút gọn URL; bộ ảnh desktop hiển thị đầy đủ đường dẫn trang. Chưa gửi tài liệu cho thành viên khác.
