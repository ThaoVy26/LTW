<?php
/**
 * Hàm dùng chung cho mọi trang: escape, định dạng tiền, CSRF, flash message,
 * giỏ hàng (lưu trong SESSION, giá luôn đọc lại từ DB), mã giảm giá.
 */
declare(strict_types=1);

// Mốc "hôm nay" của thống kê phải khớp giờ Việt Nam (XAMPP mặc định dùng múi giờ khác)
date_default_timezone_set('Asia/Ho_Chi_Minh');

if (session_status() === PHP_SESSION_NONE) {
    // Cookie phiên: JavaScript không đọc được, không gửi kèm khi bị nhúng từ trang khác
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}
require_once __DIR__ . '/../config/database.php';

const PHI_VAN_CHUYEN   = 30000;
const NGUONG_MIEN_PHI  = 500000;   // đơn từ mức này được miễn phí vận chuyển
const SP_MOI_TRANG   = 6;

/* ---------------- Tiện ích chung ---------------- */

function e($s): string
{
    return htmlspecialchars((string)($s ?? ''), ENT_QUOTES, 'UTF-8');
}

/** Thêm ?v=<thời gian sửa file> để trình duyệt tự tải lại CSS/JS mới (không cần Ctrl+F5). */
function phien_ban(string $duongDan): string
{
    $file = __DIR__ . '/../' . $duongDan;
    return $duongDan . (is_file($file) ? '?v=' . filemtime($file) : '');
}

function tien($n): string
{
    return number_format((float)$n, 0, ',', '.') . 'đ';
}

function chuyen_trang(string $url): never
{
    header('Location: ' . $url);
    exit;
}

/** Chỉ cho phép quay lại URL nội bộ (chống open-redirect). */
function url_quay_lai(?string $url, string $macDinh = 'index.php'): string
{
    $url = (string)$url;
    if ($url === '' || preg_match('#^(https?:)?//#i', $url) || str_contains($url, "\n")) {
        return $macDinh;
    }
    return $url;
}

function la_ajax(): bool
{
    return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
}

function tra_json(array $data, int $code = 200): never
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/* ---------------- Flash message ---------------- */

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

/* ---------------- CSRF ---------------- */

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

/* ---------------- Hiển thị sản phẩm ---------------- */

/** Icon SVG minh hoạ khi sản phẩm chưa có ảnh, chọn theo danh mục. */
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

/** Khung ảnh sản phẩm: dùng ảnh thật nếu có, không thì icon + nền gradient. */
function anh_san_pham(array $sp, string $nhan = 'Ảnh sản phẩm'): string
{
    if (!empty($sp['hinh_anh'])) {
        return '<img src="' . e($sp['hinh_anh']) . '" alt="' . e($sp['ten_sp']) . '" loading="lazy">';
    }
    $tone = ['Vòng cơ bản' => 'tone-gray', 'Combo' => 'tone-pink', 'Charm' => 'tone-yellow'];
    return '<div class="ph ' . ($tone[$sp['ten_danh_muc'] ?? ''] ?? 'tone-gray') . '">'
        . '<span class="ph__ring">' . icon_san_pham($sp) . '</span>'
        . '<span class="ph__text">' . e($nhan) . '</span></div>';
}

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

/** Chuỗi có phải đường dẫn file ảnh không (phân biệt với emoji tạm). */
function la_anh(?string $hinh): bool
{
    return (bool)preg_match('/\.(png|jpe?g|webp|gif|svg)$/i', (string)$hinh);
}

/** Charm: hinh_anh là đường dẫn ảnh thì hiện <img>, còn lại hiện emoji/chữ. */
function icon_charm(?string $hinh): string
{
    $hinh = (string)$hinh;
    if (la_anh($hinh)) {
        return '<img src="' . e($hinh) . '" alt="">';
    }
    return e($hinh !== '' ? $hinh : '•');
}

/** Màu chấm tròn cho từng loại dây (bảng DayVong chưa có cột màu). */
function mau_day(string $tenDay): string
{
    $t = mb_strtolower($tenDay);
    return match (true) {
        str_contains($t, 'vàng')  => 'linear-gradient(135deg,#fff3b0,#e9c74f)',
        str_contains($t, 'trắng') => '#f1f3f8',
        str_contains($t, 'đen')   => '#1f2937',
        default                   => 'linear-gradient(135deg,#f5f6f8,#b9bec7)',
    };
}

/* ---------------- Giỏ hàng (SESSION) ----------------
 * $_SESSION['gio'] = [
 *   'sp_5' => ['loai' => 'san_pham', 'id' => 5, 'so_luong' => 2],
 *   'tk_12' => ['loai' => 'thiet_ke', 'id' => 12, 'so_luong' => 1],
 * ]
 */

function gio(): array
{
    return $_SESSION['gio'] ?? [];
}

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

function gio_them_thiet_ke(int $idThietKe, ?string $thayKey = null): void
{
    if ($thayKey !== null) {
        unset($_SESSION['gio'][$thayKey]);
    }
    $_SESSION['gio']['tk_' . $idThietKe] = ['loai' => 'thiet_ke', 'id' => $idThietKe, 'so_luong' => 1];
}

function gio_dem(): int
{
    return array_sum(array_column(gio(), 'so_luong'));
}

/**
 * Lấy chi tiết từng dòng giỏ từ DB (tên, giá hiện tại...).
 * Dòng nào không còn trong DB thì tự xoá khỏi giỏ.
 */
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

/** Tính tiền giỏ + mã giảm giá đang áp dụng. */
/**
 * Tìm mã và kiểm tra điều kiện áp dụng theo giá trị giỏ hàng.
 * Trả về [mã hoặc null, lý do khi không dùng được].
 */
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

function gio_tinh_tien(?array $dong = null): array
{
    $dong ??= gio_chi_tiet();
    $tam  = 0;
    foreach ($dong as $d) {
        $tam += $d['don_gia'] * $d['so_luong'];
    }
    $ship = ($dong && $tam < NGUONG_MIEN_PHI) ? PHI_VAN_CHUYEN : 0;
    $ma   = null;
    $giam = 0;
    if (!empty($_SESSION['ma_giam_gia'])) {
        [$ma, $lyDo] = kiem_tra_ma_giam_gia($_SESSION['ma_giam_gia'], $tam);
        if ($ma) {
            $giam = $ma['loai_giam'] === 'phan_tram'
                ? (int)round($tam * $ma['gia_tri'] / 100)
                : (int)$ma['gia_tri'];
            if ($ma['loai_giam'] === 'phan_tram' && ($ma['giam_toi_da'] ?? null) !== null) {
                $giam = min($giam, (int)$ma['giam_toi_da']);
            }
            $giam = min($giam, $tam + $ship);
        } else {
            unset($_SESSION['ma_giam_gia']);
            flash('loi', $lyDo);   // vd: giỏ giảm xuống dưới mức đơn tối thiểu
        }
    }
    return ['tam' => $tam, 'ship' => $ship, 'giam' => $giam, 'tong' => $tam + $ship - $giam, 'ma' => $ma];
}

/** Mã hợp lệ: đang hoạt động và trong khoảng ngày (nếu có). */
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

/* ---------------- Thiết kế tuỳ chỉnh ---------------- */

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

/* ---------------- Trang chính sách (footer) ---------------- */

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
