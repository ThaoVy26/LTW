<?php
/**
 * Hàm dùng chung cho ĐƠN HÀNG (Mỹ Anh): admin/don-hang*.php và track-order.php cùng dùng.
 * Đặt riêng một file để không phải sửa includes/functions.php (file của Quỳnh).
 * Cần include SAU includes/functions.php (dùng db()).
 */
declare(strict_types=1);

const DON_TRANG_THAI = [
    'cho_xac_nhan' => 'Chờ xác nhận',
    'dang_giao'    => 'Đang giao',
    'hoan_tat'     => 'Hoàn tất',
    'huy'          => 'Đã huỷ',
];

/** Trạng thái hiện tại => các trạng thái được phép chuyển tới. hoan_tat và huy là điểm cuối. */
const DON_CHUYEN_HOP_LE = [
    'cho_xac_nhan' => ['dang_giao', 'huy'],
    'dang_giao'    => ['hoan_tat', 'huy'],
    'hoan_tat'     => [],
    'huy'          => [],
];

const DON_PHUONG_THUC = [
    'COD'    => 'Thanh toán khi nhận hàng',
    'BANK'   => 'Chuyển khoản',
    'WALLET' => 'Ví điện tử',
];

/** Mã đơn hiển thị, giống order-success.php: #CA000012 */
function don_ma(int $id): string
{
    return '#CA' . str_pad((string)$id, 6, '0', STR_PAD_LEFT);
}

/**
 * Tách số đơn từ chuỗi người dùng nhập: "12", "#12", "CA000012", "#CA000012" => 12.
 * Không hợp lệ thì trả null. (Dùng chung cho admin/don-hang.php và track-order.php)
 */
function don_tach_ma(string $s): ?int
{
    if (preg_match('/^#?(?:CA)?0*(\d{1,9})$/i', trim($s), $m) && (int)$m[1] >= 1) {
        return (int)$m[1];
    }
    return null;
}

function don_nhan_trang_thai(?string $tt): string
{
    return DON_TRANG_THAI[(string)$tt] ?? (string)$tt;
}

/** Class badge có sẵn trong assets/css/style.css (dùng cho trang khách). */
function don_lop_badge(?string $tt): string
{
    return match ($tt) {
        'cho_xac_nhan' => 'badge--yellow',
        'dang_giao'    => 'badge--purple',
        'hoan_tat'     => 'badge--green',
        default        => 'badge--pink',
    };
}

/** Lấy tên file ảnh khách đính kèm từ ghi chú ("... | Ảnh: uploads/yc_xxx.jpg"). Chỉ nhận tên an toàn. */
function don_anh_dinh_kem(?string $ghiChu): ?string
{
    if ($ghiChu !== null && preg_match('#Ảnh: uploads/([A-Za-z0-9_]+\.(?:jpg|png|webp))#u', $ghiChu, $m)) {
        return $m[1];
    }
    return null;
}

/** Ghi chú để hiển thị: bỏ phần "Ảnh: uploads/..." (ảnh được hiện riêng). */
function don_ghi_chu_sach(?string $ghiChu): string
{
    $s = preg_replace('#\s*\|?\s*Ảnh: uploads/[A-Za-z0-9_]+\.(?:jpg|png|webp)#u', '', (string)$ghiChu);
    return trim((string)$s, " |\t\n\r");
}

/** Các dòng hàng của một đơn. */
function don_lay_dong_hang(int $idDon): array
{
    $st = db()->prepare('SELECT * FROM ChiTietDonHang WHERE id_don_hang = ? ORDER BY id');
    $st->execute([$idDon]);
    return $st->fetchAll();
}

/**
 * Đổi trạng thái đơn. Trả về [thành công?, thông báo].
 * - Chỉ cho phép chuyển theo DON_CHUYEN_HOP_LE (kiểm tra ở server).
 * - UPDATE có điều kiện "trạng thái hiện tại vẫn như cũ" nên bấm đúp / 2 admin cùng bấm
 *   thì chỉ 1 lần có hiệu lực.
 * - Huỷ đơn: hoàn tồn kho trong CÙNG transaction, và CHỈ khi UPDATE ở trên đổi được đúng 1 dòng
 *   (tránh hoàn kho hai lần). Dòng thiết kế tuỳ chỉnh (id_san_pham NULL) không có tồn kho nên bỏ qua.
 */
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
    if (!in_array($moi, DON_CHUYEN_HOP_LE[$cu] ?? [], true)) {
        return [false, 'Không thể chuyển đơn từ "' . don_nhan_trang_thai($cu) . '" sang "' . don_nhan_trang_thai($moi) . '".'];
    }

    try {
        $pdo->beginTransaction();

        $up = $pdo->prepare('UPDATE DonHang SET trang_thai_don = ? WHERE id = ? AND trang_thai_don = ?');
        $up->execute([$moi, $idDon, $cu]);
        if ($up->rowCount() !== 1) {
            $pdo->rollBack();
            return [false, 'Đơn vừa được cập nhật ở nơi khác, hãy tải lại trang.'];
        }

        $soDongHoan = 0;
        if ($moi === 'huy') {
            $ds = $pdo->prepare('SELECT id_san_pham, so_luong FROM ChiTietDonHang WHERE id_don_hang = ? AND id_san_pham IS NOT NULL');
            $ds->execute([$idDon]);
            $hoan = $pdo->prepare('UPDATE SanPham SET so_luong_ton = so_luong_ton + ? WHERE id = ?');
            foreach ($ds->fetchAll() as $d) {
                $hoan->execute([(int)$d['so_luong'], (int)$d['id_san_pham']]);
                $soDongHoan += (int)$d['so_luong'];   // cộng theo SỐ LƯỢNG, không phải số dòng
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
