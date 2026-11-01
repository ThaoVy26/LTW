<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

// khách đang đăng nhập (session riêng, không lẫn với admin)
function khach_hien_tai(): ?array
{
    return $_SESSION['khach'] ?? null;
}

// chỉ cho quay lại 1 trang .php trong web, chặn link ra ngoài (kể cả kiểu /\evil.com)
function khach_trang_quay_lai(?string $v): string
{
    $v = url_quay_lai($v, 'index.php');
    return preg_match('/^[A-Za-z0-9_-]+\.php(\?[A-Za-z0-9_=&%.-]*)?$/', $v) ? $v : 'index.php';
}

// trang cần đăng nhập: chưa đăng nhập thì chuyển qua trang đăng nhập, xong quay lại đúng trang này
function yeu_cau_khach(string $quayLai = 'index.php'): void
{
    if (khach_hien_tai() === null) {
        chuyen_trang('dang-nhap.php?quay_lai=' . urlencode($quayLai));
    }
}

// ghi thông tin khách vào session, đổi session id cho an toàn
function khach_vao_phien(array $tk): void
{
    unset($_SESSION['dn_sai'], $_SESSION['dn_khoa_den']);
    session_regenerate_id(true);
    $_SESSION['khach'] = [
        'id'    => (int)$tk['id'],
        'ten'   => (string)($tk['ho_ten'] ?: $tk['email']),
        'email' => (string)$tk['email'],
        'sdt'   => (string)($tk['so_dien_thoai'] ?? ''),
    ];
}

// đăng nhập bằng email + mật khẩu, chỉ nhận tài khoản vai trò khách
function khach_dang_nhap(string $email, string $matKhau): bool
{
    $st = db()->prepare(
        "SELECT id, ho_ten, email, so_dien_thoai, mat_khau_hash
         FROM TaiKhoan
         WHERE email = ? AND vai_tro = 'khach' AND trang_thai = 'hoat_dong'"
    );
    $st->execute([mb_strtolower(trim($email))]);
    $tk = $st->fetch();

    if (!$tk || !password_verify($matKhau, $tk['mat_khau_hash'])) {
        ghi_nhan_dang_nhap_sai();
        return false;
    }
    khach_vao_phien($tk);
    return true;
}

// đăng ký tài khoản khách: trả về câu báo lỗi, null là thành công (đã tự đăng nhập)
function khach_dang_ky(string $hoTen, string $email, string $sdt, string $matKhau, string $nhapLai): ?string
{
    $hoTen = trim($hoTen);
    $email = mb_strtolower(trim($email));
    $sdt   = trim($sdt);

    if (mb_strlen($hoTen) < 2) {
        return 'Vui lòng nhập họ và tên.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
        return 'Email không hợp lệ.';
    }
    if ($sdt !== '' && !preg_match('/^0\d{9}$/', $sdt)) {
        return 'Số điện thoại gồm 10 số, bắt đầu bằng 0.';
    }
    if (mb_strlen($matKhau) < MAT_KHAU_TOI_THIEU) {
        return 'Mật khẩu phải có ít nhất ' . MAT_KHAU_TOI_THIEU . ' ký tự.';
    }
    if ($matKhau !== $nhapLai) {
        return 'Mật khẩu nhập lại không khớp.';
    }

    // email đã có người dùng thì báo, không tạo trùng
    $st = db()->prepare('SELECT COUNT(*) FROM TaiKhoan WHERE email = ? OR ten_dang_nhap = ?');
    $st->execute([$email, $email]);
    if ((int)$st->fetchColumn() > 0) {
        return 'Email này đã được đăng ký.';
    }

    try {
        $st = db()->prepare(
            "INSERT INTO TaiKhoan (ten_dang_nhap, mat_khau_hash, ho_ten, vai_tro, email, so_dien_thoai)
             VALUES (?, ?, ?, 'khach', ?, ?)"
        );
        $st->execute([$email, password_hash($matKhau, PASSWORD_DEFAULT), $hoTen, $email, $sdt ?: null]);
    } catch (PDOException $ex) {
        // 2 người đăng ký cùng lúc thì dòng sau bị chặn bởi khoá unique
        return 'Email này đã được đăng ký.';
    }

    khach_vao_phien([
        'id' => (int)db()->lastInsertId(), 'ho_ten' => $hoTen, 'email' => $email, 'so_dien_thoai' => $sdt,
    ]);
    return null;
}

// đăng xuất khách, giỏ hàng vẫn giữ nguyên
function khach_dang_xuat(): void
{
    unset($_SESSION['khach']);
    session_regenerate_id(true);
}

// địa chỉ rút gọn: che số nhà, tên đường bằng *, giữ phần cuối (phường/quận/tỉnh)
function dia_chi_rut_gon(string $dc): string
{
    $phan = array_values(array_filter(array_map('trim', explode(',', $dc)), fn($p) => $p !== ''));
    if (count($phan) <= 1) {
        return '***';
    }
    return '***, ' . implode(', ', array_slice($phan, -min(3, count($phan) - 1)));
}

// số tin của shop mà khách này chưa đọc
function tu_van_chua_doc_khach(int $idTaiKhoan): int
{
    $st = db()->prepare("SELECT COUNT(*) FROM TinNhanTuVan WHERE id_tai_khoan = ? AND nguoi_gui = 'shop' AND da_doc = 0");
    $st->execute([$idTaiKhoan]);
    return (int)$st->fetchColumn();
}
