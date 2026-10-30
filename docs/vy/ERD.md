# ERD Charm Atelier

Sơ đồ đối chiếu với metadata của DB kiểm thử sau khi import schema repo 01–11 ngày 30/10/2026: **14 bảng, 14 khóa ngoại**. Đây là schema repo trong MySQL tạm độc lập, không phải bản chụp DB starter local.

![ERD](erd.png)

`PK`: khóa chính; `FK`: khóa ngoại; `?`: cột cho phép NULL. Chân quạ: phía nhiều; vòng tròn ở phía cha: quan hệ tùy chọn. `TaiKhoan` quản trị độc lập nên không có đường nối.

## Cập nhật MaGiamGia theo migration 11

| Cột mới | Kiểu dữ liệu | Cho phép NULL | Mặc định |
|---|---|---|---|
| `don_toi_thieu` | `DECIMAL(12,0)` | Không | `0` |
| `giam_toi_da` | `DECIMAL(12,0)` | Có | `NULL` |
| `luot_toi_da` | `INT` | Có | `NULL` |

Cả ba cột đã có trong PNG, SVG và nguồn DOT. Migration 10 chỉ thêm chỉ mục; migration 11 thêm thuộc tính, không thêm bảng hay khóa ngoại. Quan hệ `DonHang.id_ma_giam_gia → MaGiamGia.id` giữ nguyên.

## Các khóa ngoại

| Bảng con | Cột FK | Tham chiếu |
|---|---|---|
| BoSuuTapCharm | id_nhom | NhomCharm.id |
| Charm | id_bo_suu_tap | BoSuuTapCharm.id |
| ChiTietDonHang | id_don_hang | DonHang.id |
| ChiTietDonHang | id_san_pham | SanPham.id |
| ChiTietDonHang | id_thiet_ke | ThietKeTuyChinh.id |
| ChiTietThietKe | id_thiet_ke | ThietKeTuyChinh.id |
| ChiTietThietKe | id_charm | Charm.id |
| DonHang | id_khach_hang | KhachHang.id |
| DonHang | id_ma_giam_gia | MaGiamGia.id |
| SanPham | id_charm | Charm.id |
| SanPham | id_danh_muc | DanhMuc.id |
| ThietKeTuyChinh | id_khach_hang | KhachHang.id |
| ThietKeTuyChinh | id_kich_thuoc | KichThuocTay.id |
| ThietKeTuyChinh | id_day_vong | DayVong.id |

Nguồn có thể sửa: [erd.dot](erd.dot). Bản vector để phóng to: [erd.svg](erd.svg). Xuất lại bằng Graphviz: `dot -Tpng -Gdpi=150 erd.dot -o erd.png` và `dot -Tsvg erd.dot -o erd.svg`.
