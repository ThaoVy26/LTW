# Kiểm thử phần sản phẩm

Ngày: 29/10/2026. PHP 8.0.30, MariaDB qua XAMPP, Chrome headless. Dùng DB kiểm thử riêng dựng từ schema 01–09, không sửa dữ liệu DB local. Tài khoản/mật khẩu kiểm thử chỉ tồn tại trong DB kiểm thử.

## Đã đạt

- Kiểm tra cú pháp bốn trang bằng `C:\xampp\php\php.exe -l`.
- Import 01–09 từ đầu; chạy lại file 09 thành công. Có 173 sản phẩm, 160 charm, 14 bảng, 14 khóa ngoại.
- Không còn sản phẩm đang bán thiếu ảnh; kiểm tra cả đường dẫn trong DB và file thật trên đĩa. Bốn combo mới nhất có ảnh đúng sản phẩm.
- Không còn sản phẩm có giá bán dương nhưng giá nhập bằng 0 sau file 09.
- Đăng nhập admin; tìm sản phẩm kết hợp danh mục; phân trang giới hạn đúng số dòng và chặn trang vượt phạm vi.
- Thêm/sửa/xóa sản phẩm; giá nhập lưu đúng; trạng thái hiển thị bằng nhãn tiếng Việt.
- Chọn ảnh xem trước, upload JPG và xác nhận đường dẫn lưu có tiền tố `uploads/`.
- Thêm/sửa/xóa danh mục.
- Charm tối đa 20 dòng; lọc bộ sưu tập; lưu tên một dòng và giữ trang đang xem; trả tên về ban đầu.
- Yêu cầu POST với CSRF không hợp lệ trả HTTP 403.
- Sản phẩm đã có trong đơn hàng: nút xóa bị khóa, gửi POST trực tiếp cũng bị từ chối. Mô tả danh mục quá 255 ký tự bị từ chối phía server.
- Charm lưu giá/trạng thái và giữ cả bộ lọc bộ sưu tập lẫn số trang.
- Cả bốn trang không làm tràn chiều ngang toàn trang ở 1440×1000, 768×1024 và 390×844. Ảnh thu nhỏ trong vùng nhìn thấy tải thành công. Bảng lớn vẫn cuộn ngang trong khung của bảng.

Tổng cộng 60 điểm kiểm tra trình duyệt đã đạt, gồm các lượt mở trang, thao tác và kiểm tra bố cục/ảnh. Đã xem trực tiếp ảnh chụp desktop và mobile để đối chiếu giao diện.

## Ảnh minh chứng

| Trang | Desktop | Tablet | Mobile |
|---|---|---|---|
| Sản phẩm | [Ảnh](screenshots/san-pham-desktop.png) | [Ảnh](screenshots/san-pham-tablet.png) | [Ảnh](screenshots/san-pham-mobile.png) |
| Form sản phẩm | [Ảnh](screenshots/san-pham-form-desktop.png) | [Ảnh](screenshots/san-pham-form-tablet.png) | [Ảnh](screenshots/san-pham-form-mobile.png) |
| Danh mục | [Ảnh](screenshots/danh-muc-desktop.png) | [Ảnh](screenshots/danh-muc-tablet.png) | [Ảnh](screenshots/danh-muc-mobile.png) |
| Charm | [Ảnh](screenshots/charm-desktop.png) | [Ảnh](screenshots/charm-tablet.png) | [Ảnh](screenshots/charm-mobile.png) |

## Cần kiểm tra khi tích hợp

- Điện thoại thật; chụp lại có thanh địa chỉ để đưa vào báo cáo.
- Biểu đồ lợi nhuận khi có đơn hoàn tất; repo hiện chưa có trang `admin/thong-ke.php` của Quỳnh.
- Luồng khách ẩn charm phải do Q8 xử lý; việc lưu trạng thái admin không tự thay đổi truy vấn trang khách.
- Chạy lại bộ kiểm thử sau khi gộp migration 10–11 và chốt dữ liệu nhóm.
