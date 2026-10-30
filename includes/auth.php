<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

// sai 5 lần thì khoá 5 phút
const DANG_NHAP_SAI_TOI_DA = 5;
const THOI_GIAN_KHOA       = 300;
const MAT_KHAU_TOI_THIEU   = 6;

// lấy admin đang đăng nhập
function admin_hien_tai(): ?array
{
    return $_SESSION['admin'] ?? null;
}

// chưa đăng nhập thì đá về trang login
function yeu_cau_admin(): void
{
    if (admin_hien_tai() === null) {
        chuyen_trang('login.php');
    }
}

// đăng xuất, đổi lại session id
function dang_xuat(): void
{
    unset($_SESSION['admin']);
    session_regenerate_id(true);
}

// còn bao nhiêu giây bị khoá đăng nhập
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

// đếm số lần đăng nhập sai, đủ 5 lần thì khoá
function ghi_nhan_dang_nhap_sai(): void
{
    $_SESSION['dn_sai'] = (int)($_SESSION['dn_sai'] ?? 0) + 1;
    if ($_SESSION['dn_sai'] >= DANG_NHAP_SAI_TOI_DA) {
        $_SESSION['dn_khoa_den'] = time() + THOI_GIAN_KHOA;
    }
}

// kiểm tra tài khoản và mật khẩu (mật khẩu lưu dạng hash)
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

// đổi mật khẩu, trả về câu báo lỗi nếu có (null là ổn)
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
