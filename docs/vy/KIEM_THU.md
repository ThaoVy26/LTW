# Kiểm thử phần sản phẩm

**Kết quả mới nhất sau đổi màu và import 01–16:** [KIEM_THU_01_16.md](KIEM_THU_01_16.md). Ảnh màu mới từ main `899ea7f` chỉ giữ local trong `docs/vy/screenshots/` để người dùng gửi riêng cho Mỹ Anh, không kèm trong bản hiện tại của repository. Các phần ghi kết quả ngày 29/10 bên dưới được giữ làm lịch sử kiểm thử.

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

Đã thay toàn bộ 12 ảnh bằng ảnh chụp **cửa sổ Chrome thật có thanh tab và thanh địa chỉ** ngày 30/10/2026 theo màu mới của main `899ea7f`. Chụp nguyên cửa sổ bằng Windows PrintWindow, không ghép ảnh trang với thanh địa chỉ giả. Import thành công SQL 01–16 trên MariaDB tạm độc lập, cổng 3307; bản web chụp ảnh chạy tại `127.0.0.1:8091`. Có thêm ba ảnh minh chứng lỗi ảnh và màu phân trang; xem báo cáo mới.

Viewport CSS thực tế lúc chụp: desktop **1266×686**, tablet **786×686**, mobile **501×686**. Đây là kiểm tra responsive bằng thay đổi độ rộng cửa sổ Chrome trên máy tính; không phải thiết bị di động thật. Kích thước pixel ảnh lớn hơn do DPI Windows. Metadata từng ảnh nằm trong [capture-info.json](screenshots/capture-info.json).

Trong đợt chụp lại, cả bốn trang mặc định đều tải thành công, không lỗi PHP, không tràn chiều ngang toàn trang, ảnh thu nhỏ đang nhìn thấy tải được. Bộ 60 điểm kiểm tra thao tác đã được chạy lại trên màu mới và dữ liệu 01–16; kết quả và các vấn đề còn lại nằm trong báo cáo mới.

| Trang | Desktop | Tablet | Mobile |
|---|---|---|---|
| Sản phẩm | `san-pham-desktop.png` | `san-pham-tablet.png` | `san-pham-mobile.png` |
| Form sản phẩm | `san-pham-form-desktop.png` | `san-pham-form-tablet.png` | `san-pham-form-mobile.png` |
| Danh mục | `danh-muc-desktop.png` | `danh-muc-tablet.png` | `danh-muc-mobile.png` |
| Charm | `charm-desktop.png` | `charm-tablet.png` | `charm-mobile.png` |

## Cần kiểm tra khi tích hợp

- Điện thoại thật. Yêu cầu ảnh có thanh địa chỉ cho báo cáo đã được hoàn tất bằng bộ ảnh Chrome ngày 30/10.
- Biểu đồ lợi nhuận khi có đơn hoàn tất; repo hiện chưa có trang `admin/thong-ke.php` của Quỳnh.
- Luồng khách ẩn charm Q8 cần kiểm thử khi tích hợp; việc lưu trạng thái admin không tự thay đổi truy vấn trang khách.
- SQL 01–16 đã import thành công từ DB trống để cập nhật ERD và chụp ảnh; vẫn cần kiểm thử toàn bộ luồng nghiệp vụ khi chốt dữ liệu nhóm.
