<?php
declare(strict_types=1);

date_default_timezone_set('Asia/Ho_Chi_Minh');

// bật session để giữ giỏ hàng và đăng nhập
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}
require_once __DIR__ . '/../config/database.php';

// phí ship 30k, đơn từ 500k trở lên thì miễn phí
const PHI_VAN_CHUYEN   = 30000;
const NGUONG_MIEN_PHI  = 500000;
const SP_MOI_TRANG   = 6;

// chống XSS, in ra html thì bọc qua hàm này
function e($s): string
{
    return htmlspecialchars((string)($s ?? ''), ENT_QUOTES, 'UTF-8');
}

// thêm ?v=giờ sửa file để trình duyệt không giữ ảnh/css cũ
function phien_ban(string $duongDan): string
{
    $file = __DIR__ . '/../' . $duongDan;
    return $duongDan . (is_file($file) ? '?v=' . filemtime($file) : '');
}

// đổi số sang dạng 250.000đ
function tien($n): string
{
    return number_format((float)$n, 0, ',', '.') . 'đ';
}

// chuyển trang rồi dừng
function chuyen_trang(string $url): never
{
    header('Location: ' . $url);
    exit;
}

// chỉ cho quay lại link trong web, chặn link ra ngoài
function url_quay_lai(?string $url, string $macDinh = 'index.php'): string
{
    $url = (string)$url;
    if ($url === '' || preg_match('#^(https?:)?//#i', $url) || str_contains($url, "\n")) {
        return $macDinh;
    }
    return $url;
}

// request này gửi bằng fetch hay form thường
function la_ajax(): bool
{
    return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
}

// trả json cho js rồi dừng
function tra_json(array $data, int $code = 200): never
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// thông báo chỉ hiện 1 lần rồi mất
function flash(string $loai, string $noiDung): void
{
    $_SESSION['flash'][] = ['loai' => $loai, 'noi_dung' => $noiDung];
}

function lay_flash(): array
{
    $ds = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $ds;
}

// chống giả mạo form (csrf)
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_hop_le(?string $token): bool
{
    return is_string($token) && hash_equals(csrf_token(), $token);
}

// icon thay cho ảnh khi sản phẩm chưa có ảnh
function icon_san_pham(array $sp): string
{
    $icons = [
        'Vòng cơ bản' => '<ellipse cx="12" cy="12" rx="8" ry="4.5"/>',
        'Combo'       => '<path d="M12 20s-7-4.4-7-9.5A4 4 0 0 1 12 8a4 4 0 0 1 7 2.5C19 15.6 12 20 12 20z"/>',
        'Charm'       => '<path d="m12 3 2.6 5.6 6 .7-4.5 4.1 1.2 6L12 16.4 6.7 19.4l1.2-6L3.4 9.3l6-.7z"/>',
    ];
    $path = $icons[$sp['ten_danh_muc'] ?? ''] ?? $icons['Vòng cơ bản'];
    return '<svg viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="currentColor" '
        . 'stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $path . '</svg>';
}

// ảnh sản phẩm, chưa có ảnh thì hiện khung icon
function anh_san_pham(array $sp, string $nhan = 'Ảnh sản phẩm'): string
{
    if (!empty($sp['hinh_anh'])) {
        return '<img src="' . e(phien_ban($sp['hinh_anh'])) . '" alt="' . e($sp['ten_sp']) . '" loading="lazy">';
    }
    $tone = ['Vòng cơ bản' => 'tone-gray', 'Combo' => 'tone-pink', 'Charm' => 'tone-yellow'];
    return '<div class="ph ' . ($tone[$sp['ten_danh_muc'] ?? ''] ?? 'tone-gray') . '">'
        . '<span class="ph__ring">' . icon_san_pham($sp) . '</span>'
        . '<span class="ph__text">' . e($nhan) . '</span></div>';
}

// màu cho nhãn Bán chạy, Mới, Cặp đôi...
function class_nhan(?string $nhan): string
{
    return match ($nhan) {
        'Cặp đôi'   => 'badge--purple',
        'Tiết kiệm' => 'badge--yellow',
        'Cá nhân hoá' => 'badge--purple',
        'Mới'       => 'badge--green',
        default     => 'badge--pink',
    };
}

// kiểm tra đây là đường dẫn ảnh hay chỉ là emoji
function la_anh(?string $hinh): bool
{
    return (bool)preg_match('/\.(png|jpe?g|webp|gif|svg)$/i', (string)$hinh);
}

// hiện ảnh charm, không có ảnh thì hiện chữ
function icon_charm(?string $hinh): string
{
    $hinh = (string)$hinh;
    if (la_anh($hinh)) {
        return '<img src="' . e(phien_ban($hinh)) . '" alt="">';
    }
    return e($hinh !== '' ? $hinh : '•');
}

// màu chấm tròn cho từng loại dây
function mau_day(string $tenDay): string
{
    $t = mb_strtolower($tenDay);
    return match (true) {
        str_contains($t, 'vàng')  => 'linear-gradient(135deg,#fff3b0,#e9c74f)',
        str_contains($t, 'trắng') => '#f1f3f8',
        str_contains($t, 'đen')   => '#1f2937',
        str_contains($t, 'hồng')  => 'linear-gradient(135deg,#fde4e8,#e99aab)',
        default                   => 'linear-gradient(135deg,#f5f6f8,#b9bec7)',
    };
}

// giỏ hàng lưu trong session
function gio(): array
{
    return $_SESSION['gio'] ?? [];
}

// thêm sản phẩm vào giỏ, kiểm tra còn hàng không
function gio_them_san_pham(int $idSp, int $soLuong = 1): array
{
    $st = db()->prepare(
        "SELECT sp.id, sp.ten_sp, sp.so_luong_ton FROM SanPham sp
         LEFT JOIN Charm c ON c.id = sp.id_charm
         WHERE sp.id = ? AND sp.trang_thai = 'dang_ban' AND (c.id IS NULL OR c.trang_thai = 'hien')"
    );
    $st->execute([$idSp]);
    $sp = $st->fetch();
    if (!$sp) {
        return [false, 'Sản phẩm không tồn tại hoặc đã ngừng bán.'];
    }
    $key   = 'sp_' . $sp['id'];
    $daCo  = $_SESSION['gio'][$key]['so_luong'] ?? 0;
    $moi   = $daCo + max(1, $soLuong);
    if ($moi > (int)$sp['so_luong_ton']) {
        return [false, 'Chỉ còn ' . $sp['so_luong_ton'] . ' sản phẩm "' . $sp['ten_sp'] . '" trong kho.'];
    }
    $_SESSION['gio'][$key] = ['loai' => 'san_pham', 'id' => (int)$sp['id'], 'so_luong' => $moi];
    return [true, 'Đã thêm "' . $sp['ten_sp'] . '" vào giỏ.'];
}

// thêm vòng tự thiết kế vào giỏ (sửa lại thì thay dòng cũ)
function gio_them_thiet_ke(int $idThietKe, ?string $thayKey = null): void
{
    if ($thayKey !== null) {
        unset($_SESSION['gio'][$thayKey]);
    }
    $_SESSION['gio']['tk_' . $idThietKe] = ['loai' => 'thiet_ke', 'id' => $idThietKe, 'so_luong' => 1];
}

// đếm tổng số món trong giỏ
function gio_dem(): int
{
    return array_sum(array_column(gio(), 'so_luong'));
}

// lấy thông tin đầy đủ của từng dòng trong giỏ
function gio_chi_tiet(): array
{
    $dong = [];
    foreach (gio() as $key => $g) {
        if ($g['loai'] === 'san_pham') {
            $st = db()->prepare(
                'SELECT sp.*, dm.ten_danh_muc FROM SanPham sp JOIN DanhMuc dm ON dm.id = sp.id_danh_muc WHERE sp.id = ?'
            );
            $st->execute([$g['id']]);
            $sp = $st->fetch();
            if (!$sp) { unset($_SESSION['gio'][$key]); continue; }
            $dong[] = [
                'key' => $key, 'loai' => 'san_pham', 'id' => $sp['id'],
                'ten' => $sp['ten_sp'], 'mo_ta' => $sp['ten_danh_muc'],
                'don_gia' => (int)$sp['gia'], 'so_luong' => $g['so_luong'],
                'ton' => (int)$sp['so_luong_ton'], 'sp' => $sp,
            ];
        } else {
            $tk = lay_thiet_ke($g['id']);
            if (!$tk) { unset($_SESSION['gio'][$key]); continue; }
            $dong[] = [
                'key' => $key, 'loai' => 'thiet_ke', 'id' => $tk['id'],
                'ten' => 'Vòng tự thiết kế #' . $tk['id'],
                'mo_ta' => 'Size ' . $tk['ten_size'] . ' · Dây ' . $tk['ten_day'] . ' · ' . $tk['so_charm_tuy_chinh'] . ' charm',
                'don_gia' => (int)$tk['tong_gia'], 'so_luong' => $g['so_luong'],
                'ton' => 99, 'thiet_ke' => $tk,
            ];
        }
    }
    return $dong;
}

// kiểm tra mã giảm giá: còn hạn, đủ đơn tối thiểu, còn lượt dùng
function kiem_tra_ma_giam_gia(string $code, int $tamTinh): array
{
    $ma = tim_ma_giam_gia($code);
    if (!$ma) {
        return [null, 'Mã "' . strtoupper(trim($code)) . '" không hợp lệ hoặc đã hết hạn.'];
    }
    $toiThieu = (int)($ma['don_toi_thieu'] ?? 0);
    if ($toiThieu > 0 && $tamTinh < $toiThieu) {
        return [null, 'Mã ' . $ma['ma_code'] . ' chỉ áp dụng cho đơn từ ' . tien($toiThieu)
            . ' (bạn cần mua thêm ' . tien($toiThieu - $tamTinh) . ').'];
    }
    if (($ma['luot_toi_da'] ?? null) !== null) {
        $st = db()->prepare("SELECT COUNT(*) FROM DonHang WHERE id_ma_giam_gia = ? AND trang_thai_don <> 'huy'");
        $st->execute([$ma['id']]);
        if ((int)$st->fetchColumn() >= (int)$ma['luot_toi_da']) {
            return [null, 'Mã ' . $ma['ma_code'] . ' đã hết lượt sử dụng.'];
        }
    }
    return [$ma, ''];
}

// tính tiền giỏ hàng: tạm tính, ship, giảm giá, tổng
function gio_tinh_tien(?array $dong = null): array
{
    $dong ??= gio_chi_tiet();
    $tam  = 0;
    foreach ($dong as $d) {
        $tam += $d['don_gia'] * $d['so_luong'];
    }
    // dưới 500k thì tính phí ship
    $ship = ($dong && $tam < NGUONG_MIEN_PHI) ? PHI_VAN_CHUYEN : 0;
    $ma   = null;
    $giam = 0;
    // có mã giảm giá thì áp vào
    if (!empty($_SESSION['ma_giam_gia'])) {
        [$ma, $lyDo] = kiem_tra_ma_giam_gia($_SESSION['ma_giam_gia'], $tam);
        if ($ma) {
            // giảm theo % hoặc theo số tiền, có trần giảm tối đa
            $giam = $ma['loai_giam'] === 'phan_tram'
                ? (int)round($tam * $ma['gia_tri'] / 100)
                : (int)$ma['gia_tri'];
            if ($ma['loai_giam'] === 'phan_tram' && ($ma['giam_toi_da'] ?? null) !== null) {
                $giam = min($giam, (int)$ma['giam_toi_da']);
            }
            $giam = min($giam, $tam + $ship);
        } else {
            unset($_SESSION['ma_giam_gia']);
            flash('loi', $lyDo);
        }
    }
    return ['tam' => $tam, 'ship' => $ship, 'giam' => $giam, 'tong' => $tam + $ship - $giam, 'ma' => $ma];
}

// tìm mã đang bật và còn hạn
function tim_ma_giam_gia(string $code): ?array
{
    $st = db()->prepare(
        "SELECT * FROM MaGiamGia
         WHERE ma_code = ? AND trang_thai = 'hoat_dong'
           AND (ngay_bat_dau IS NULL OR ngay_bat_dau <= CURDATE())
           AND (ngay_het_han IS NULL OR ngay_het_han >= CURDATE())"
    );
    $st->execute([strtoupper(trim($code))]);
    return $st->fetch() ?: null;
}

// lấy 1 thiết kế kèm size, dây và các charm trong từng ô
function lay_thiet_ke(int $id): ?array
{
    $st = db()->prepare(
        'SELECT tk.*, kt.ten_size, kt.so_charm_toi_da, dv.ten_day, dv.gia_charm_bac
         FROM ThietKeTuyChinh tk
         JOIN KichThuocTay kt ON kt.id = tk.id_kich_thuoc
         JOIN DayVong dv ON dv.id = tk.id_day_vong
         WHERE tk.id = ?'
    );
    $st->execute([$id]);
    $tk = $st->fetch();
    if (!$tk) {
        return null;
    }
    $st = db()->prepare(
        'SELECT ct.vi_tri_slot, ct.id_charm, c.ten_charm, c.gia, c.kieu, c.hinh_anh
         FROM ChiTietThietKe ct LEFT JOIN Charm c ON c.id = ct.id_charm
         WHERE ct.id_thiet_ke = ? ORDER BY ct.vi_tri_slot'
    );
    $st->execute([$id]);
    $tk['slots'] = $st->fetchAll();
    $tk['so_charm_tuy_chinh'] = count(array_filter($tk['slots'], fn($s) => $s['id_charm'] !== null));
    return $tk;
}

// danh sách trang chính sách ở chân trang
function ds_chinh_sach(): array
{
    return [
        'giao-hang'  => 'Chính sách giao hàng',
        'doi-tra'    => 'Chính sách đổi trả',
        'thanh-toan' => 'Hướng dẫn thanh toán',
        'bao-mat'    => 'Chính sách bảo mật',
        'faq'        => 'FAQ Page',
    ];
}
