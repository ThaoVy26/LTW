# ERD Charm Atelier

Sơ đồ sinh từ metadata của DB kiểm thử sau khi import schema repo 01–09: **14 bảng, 14 khóa ngoại**. Đây là schema repo, không phải bản chụp DB starter local.

![ERD](erd.png)

`PK`: khóa chính; `FK`: khóa ngoại; `?`: cột cho phép NULL. Chân quạ: phía nhiều; vòng tròn ở phía cha: quan hệ tùy chọn. `TaiKhoan` quản trị độc lập nên không có đường nối.

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
