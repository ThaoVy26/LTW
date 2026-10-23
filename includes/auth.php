<?php
/**
 * Đăng nhập quản trị: kiểm tra mật khẩu, giữ phiên, chặn trang admin khi chưa đăng nhập.
 * Dùng trong các trang nằm trong thư mục admin/:
 *   require_once __DIR__ . '/../includes/functions.php';
 *   require_once __DIR__ . '/../includes/auth.php';
 *   yeu_cau_admin();
 */
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

const DANG_NHAP_SAI_TOI_DA = 5;     // sai quá số lần này thì tạm khoá
const THOI_GIAN_KHOA       = 300;   // giây
const MAT_KHAU_TOI_THIEU   = 6;

/* ---------------- Phiên đăng nhập ---------------- */

function admin_hien_tai(): ?array
{
    return $_SESSION['admin'] ?? null;
}

/** Gọi ở đầu mỗi trang admin: chưa đăng nhập thì về trang login. */
function yeu_cau_admin(): void
{
    if (admin_hien_tai() === null) {
        chuyen_trang('login.php');
    }
}

function dang_xuat(): void
{
    unset($_SESSION['admin']);
    session_regenerate_id(true);
}

/* ---------------- Chống đoán mật khẩu ---------------- */

/** Số giây còn bị khoá, 0 nếu được phép thử. */
function giay_con_khoa(): int
{
    $den = (int)($_SESSION['dn_khoa_den'] ?? 0);
    if ($den > time()) {
        return $den - time();
    }
    if ($den > 0) {
        unset($_SESSION['dn_khoa_den'], $_SESSION['dn_sai']);
    }
    return 0;
}

function ghi_nhan_dang_nhap_sai(): void
{
    $_SESSION['dn_sai'] = (int)($_SESSION['dn_sai'] ?? 0) + 1;
    if ($_SESSION['dn_sai'] >= DANG_NHAP_SAI_TOI_DA) {
        $_SESSION['dn_khoa_den'] = time() + THOI_GIAN_KHOA;
    }
}

/* ---------------- Đăng nhập / đổi mật khẩu ---------------- */

function dang_nhap(string $tenDangNhap, string $matKhau): bool
{
    $st = db()->prepare('SELECT id, ten_dang_nhap, ho_ten, mat_khau_hash
                         FROM TaiKhoan
                         WHERE ten_dang_nhap = ? AND trang_thai = "hoat_dong"');
    $st->execute([$tenDangNhap]);
    $tk = $st->fetch();

    if (!$tk || !password_verify($matKhau, $tk['mat_khau_hash'])) {
        ghi_nhan_dang_nhap_sai();
        return false;
    }

    unset($_SESSION['dn_sai'], $_SESSION['dn_khoa_den']);
    session_regenerate_id(true);
    $_SESSION['admin'] = [
        'id'    => (int)$tk['id'],
        'ten'   => $tk['ten_dang_nhap'],
        'ho_ten' => $tk['ho_ten'] ?: $tk['ten_dang_nhap'],
    ];
    return true;
}

/** Trả về thông báo lỗi, hoặc null nếu đổi thành công. */
function doi_mat_khau(int $idTaiKhoan, string $cu, string $moi, string $nhapLai): ?string
{
    if ($cu === '' || $moi === '' || $nhapLai === '') {
        return 'Vui lòng nhập đầy đủ các ô.';
    }
    if (mb_strlen($moi) < MAT_KHAU_TOI_THIEU) {
        return 'Mật khẩu mới phải có ít nhất ' . MAT_KHAU_TOI_THIEU . ' ký tự.';
    }
    if ($moi !== $nhapLai) {
        return 'Mật khẩu nhập lại không khớp.';
    }
    if ($moi === $cu) {
        return 'Mật khẩu mới phải khác mật khẩu cũ.';
    }

    $st = db()->prepare('SELECT mat_khau_hash FROM TaiKhoan WHERE id = ?');
    $st->execute([$idTaiKhoan]);
    $hash = $st->fetchColumn();
    if (!$hash || !password_verify($cu, $hash)) {
        return 'Mật khẩu hiện tại không đúng.';
    }

    $st = db()->prepare('UPDATE TaiKhoan SET mat_khau_hash = ? WHERE id = ?');
    $st->execute([password_hash($moi, PASSWORD_DEFAULT), $idTaiKhoan]);
    return null;
}
