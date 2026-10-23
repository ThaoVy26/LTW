<?php
/**
 * Đăng nhập quản trị.
 */
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

if (admin_hien_tai() !== null) {
    chuyen_trang('index.php');
}

$loi      = '';
$tenCu    = '';
$conKhoa  = giay_con_khoa();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tenCu = trim((string)($_POST['ten_dang_nhap'] ?? ''));

    if (!csrf_hop_le($_POST['csrf'] ?? null)) {
        $loi = 'Phiên làm việc đã hết hạn, vui lòng thử lại.';
    } elseif ($conKhoa > 0) {
        $loi = 'Đăng nhập sai quá nhiều lần. Vui lòng thử lại sau ' . ceil($conKhoa / 60) . ' phút.';
    } elseif ($tenCu === '' || ($_POST['mat_khau'] ?? '') === '') {
        $loi = 'Vui lòng nhập tên đăng nhập và mật khẩu.';
    } elseif (dang_nhap($tenCu, (string)$_POST['mat_khau'])) {
        chuyen_trang('index.php');
    } else {
        $conKhoa = giay_con_khoa();
        $loi = $conKhoa > 0
            ? 'Đăng nhập sai quá nhiều lần. Vui lòng thử lại sau ' . ceil($conKhoa / 60) . ' phút.'
            : 'Tên đăng nhập hoặc mật khẩu không đúng.';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Đăng nhập quản trị · Charm Atelier</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="dn">
  <div class="dn-card">
    <a class="logo" href="../index.php">Charm Atelier.</a>
    <p class="dn-card__sub">Đăng nhập quản trị</p>

    <?php if ($loi !== ''): ?>
      <div class="flash flash--loi"><?= e($loi) ?></div>
    <?php endif; ?>

    <form method="post" autocomplete="off">
      <?= csrf_field() ?>
      <div class="ad-field">
        <label for="ten_dang_nhap">Tên đăng nhập</label>
        <input type="text" id="ten_dang_nhap" name="ten_dang_nhap" value="<?= e($tenCu) ?>" autofocus required>
      </div>
      <div class="ad-field">
        <label for="mat_khau">Mật khẩu</label>
        <input type="password" id="mat_khau" name="mat_khau" required>
      </div>
      <button type="submit" class="btn btn--primary btn--lg btn--block"<?= $conKhoa > 0 ? ' disabled' : '' ?>>Đăng nhập</button>
    </form>

    <a class="dn-back" href="../index.php">← Về trang bán hàng</a>
  </div>
</body>
</html>
