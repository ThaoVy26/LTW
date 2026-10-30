<?php
declare(strict_types=1);

// các trạng thái của đơn
const DON_TRANG_THAI = [
    'cho_xac_nhan' => 'Chờ xác nhận',
    'dang_giao'    => 'Đang giao',
    'hoan_tat'     => 'Hoàn tất',
    'huy'          => 'Đã huỷ',
];

// trạng thái nào được chuyển sang trạng thái nào
const DON_CHUYEN_HOP_LE = [
    'cho_xac_nhan' => ['dang_giao', 'huy'],
    'dang_giao'    => ['hoan_tat', 'huy'],
    'hoan_tat'     => [],
    'huy'          => [],
];

// các cách thanh toán
const DON_PHUONG_THUC = [
    'COD'    => 'Thanh toán khi nhận hàng',
    'BANK'   => 'Chuyển khoản',
    'WALLET' => 'Ví điện tử',
];

// mã đơn hiển thị dạng #CA000012
function don_ma(int $id): string
{
    return '#CA' . str_pad((string)$id, 6, '0', STR_PAD_LEFT);
}

// đọc ngược mã khách gõ (#CA12, CA12 hoặc 12) ra số id
function don_tach_ma(string $s): ?int
{
    if (preg_match('/^#?(?:CA)?0*(\d{1,9})$/i', trim($s), $m) && (int)$m[1] >= 1) {
        return (int)$m[1];
    }
    return null;
}

// tên tiếng việt của trạng thái
function don_nhan_trang_thai(?string $tt): string
{
    return DON_TRANG_THAI[(string)$tt] ?? (string)$tt;
}

// màu badge theo trạng thái đơn
function don_lop_badge(?string $tt): string
{
    return match ($tt) {
        'cho_xac_nhan' => 'badge--yellow',
        'dang_giao'    => 'badge--purple',
        'hoan_tat'     => 'badge--green',
        default        => 'badge--pink',
    };
}

// lấy tên ảnh khách đính kèm trong ghi chú
function don_anh_dinh_kem(?string $ghiChu): ?string
{
    if ($ghiChu !== null && preg_match('#Ảnh: uploads/([A-Za-z0-9_]+\.(?:jpg|png|webp))#u', $ghiChu, $m)) {
        return $m[1];
    }
    return null;
}

// bỏ phần đường dẫn ảnh khỏi ghi chú cho đẹp
function don_ghi_chu_sach(?string $ghiChu): string
{
    $s = preg_replace('#\s*\|?\s*Ảnh: uploads/[A-Za-z0-9_]+\.(?:jpg|png|webp)#u', '', (string)$ghiChu);
    return trim((string)$s, " |\t\n\r");
}

// lấy các dòng hàng của 1 đơn
function don_lay_dong_hang(int $idDon): array
{
    $st = db()->prepare('SELECT * FROM ChiTietDonHang WHERE id_don_hang = ? ORDER BY id');
    $st->execute([$idDon]);
    return $st->fetchAll();
}

// đổi trạng thái đơn, huỷ đơn thì hoàn kho
function don_doi_trang_thai(int $idDon, string $moi): array
{
    if (!isset(DON_TRANG_THAI[$moi])) {
        return [false, 'Trạng thái không hợp lệ.'];
    }
    $pdo = db();
    $st = $pdo->prepare('SELECT trang_thai_don FROM DonHang WHERE id = ?');
    $st->execute([$idDon]);
    $cu = $st->fetchColumn();
    if ($cu === false) {
        return [false, 'Không tìm thấy đơn hàng.'];
    }
    // chỉ cho đổi theo thứ tự cho phép
    if (!in_array($moi, DON_CHUYEN_HOP_LE[$cu] ?? [], true)) {
        return [false, 'Không thể chuyển đơn từ "' . don_nhan_trang_thai($cu) . '" sang "' . don_nhan_trang_thai($moi) . '".'];
    }

    try {
        // dùng transaction, lỗi giữa chừng thì không đổi dở
        $pdo->beginTransaction();

        $up = $pdo->prepare('UPDATE DonHang SET trang_thai_don = ? WHERE id = ? AND trang_thai_don = ?');
        $up->execute([$moi, $idDon, $cu]);
        if ($up->rowCount() !== 1) {
            $pdo->rollBack();
            return [false, 'Đơn vừa được cập nhật ở nơi khác, hãy tải lại trang.'];
        }

        $soDongHoan = 0;
        // hoàn kho khi huỷ đơn
        if ($moi === 'huy') {
            $ds = $pdo->prepare('SELECT id_san_pham, so_luong FROM ChiTietDonHang WHERE id_don_hang = ? AND id_san_pham IS NOT NULL');
            $ds->execute([$idDon]);
            $hoan = $pdo->prepare('UPDATE SanPham SET so_luong_ton = so_luong_ton + ? WHERE id = ?');
            foreach ($ds->fetchAll() as $d) {
                $hoan->execute([(int)$d['so_luong'], (int)$d['id_san_pham']]);
                $soDongHoan += (int)$d['so_luong'];
            }
        }

        $pdo->commit();
    } catch (Throwable $ex) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('don_doi_trang_thai: ' . $ex->getMessage());
        return [false, 'Có lỗi khi cập nhật đơn hàng, vui lòng thử lại.'];
    }

    $tb = 'Đã chuyển đơn ' . don_ma($idDon) . ' sang "' . don_nhan_trang_thai($moi) . '".';
    if ($moi === 'huy') {
        $tb .= $soDongHoan > 0 ? ' Đã hoàn lại ' . $soDongHoan . ' sản phẩm vào tồn kho.' : ' Đơn không có sản phẩm cần hoàn kho.';
    }
    return [true, $tb];
}
