<?php
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    chuyen_trang('cart.php');
}

$quayLai = url_quay_lai($_POST['back'] ?? null, 'cart.php');

// trả kết quả: ajax thì json, form thường thì quay lại trang
function ket_thuc(bool $ok, string $thongBao, string $quayLai): never
{
    if (la_ajax()) {
        tra_json(['ok' => $ok, 'message' => $thongBao, 'cart_count' => gio_dem()], $ok ? 200 : 422);
    }
    flash($ok ? 'ok' : 'loi', $thongBao);
    chuyen_trang($quayLai);
}

// chặn request giả mạo
if (!csrf_hop_le($_POST['csrf'] ?? null)) {
    ket_thuc(false, 'Phiên làm việc đã hết hạn, vui lòng thử lại.', $quayLai);
}

$action = (string)($_POST['action'] ?? '');
$key    = (string)($_POST['key'] ?? '');

// xử lý từng thao tác trong giỏ: thêm, tăng giảm, xoá, mã giảm giá
switch ($action) {
    // thêm sản phẩm vào giỏ
    case 'add':
        $soLuong = max(1, (int)($_POST['so_luong'] ?? 1));
        [$ok, $msg] = gio_them_san_pham((int)($_POST['id'] ?? 0), $soLuong);
        ket_thuc($ok, $msg, $quayLai);

    // tăng hoặc giảm số lượng (không vượt tồn kho)
    case 'inc':
    case 'dec':
        if (!isset($_SESSION['gio'][$key])) {
            ket_thuc(false, 'Sản phẩm không có trong giỏ.', $quayLai);
        }
        $dong = &$_SESSION['gio'][$key];
        if ($action === 'dec') {
            $dong['so_luong'] = max(1, $dong['so_luong'] - 1);
        } else {
            if ($dong['loai'] === 'san_pham') {
                $st = db()->prepare('SELECT so_luong_ton FROM SanPham WHERE id = ?');
                $st->execute([$dong['id']]);
                if ($dong['so_luong'] + 1 > (int)$st->fetchColumn()) {
                    ket_thuc(false, 'Đã đạt số lượng tối đa trong kho.', $quayLai);
                }
            }
            $dong['so_luong']++;
        }
        unset($dong);
        ket_thuc(true, 'Đã cập nhật số lượng.', $quayLai);

    // xoá 1 dòng khỏi giỏ
    case 'remove':
        unset($_SESSION['gio'][$key]);
        ket_thuc(true, 'Đã xoá sản phẩm khỏi giỏ.', $quayLai);

    // áp mã giảm giá
    case 'coupon':
        $code = strtoupper(trim((string)($_POST['ma'] ?? '')));
        if ($code === '') {
            unset($_SESSION['ma_giam_gia']);
            ket_thuc(false, 'Vui lòng nhập mã giảm giá.', $quayLai);
        }
        [$ma, $lyDo] = kiem_tra_ma_giam_gia($code, gio_tinh_tien()['tam']);
        if (!$ma) {
            unset($_SESSION['ma_giam_gia']);
            ket_thuc(false, $lyDo, $quayLai);
        }
        $_SESSION['ma_giam_gia'] = $code;
        ket_thuc(true, 'Đã áp dụng mã ' . $code . '.', $quayLai);

    // bỏ mã giảm giá
    case 'coupon_remove':
        unset($_SESSION['ma_giam_gia']);
        ket_thuc(true, 'Đã bỏ mã giảm giá.', $quayLai);

    default:
        ket_thuc(false, 'Thao tác không hợp lệ.', $quayLai);
}
