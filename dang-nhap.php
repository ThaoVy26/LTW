<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/khach.php';

$quayLai = khach_trang_quay_lai($_POST['quay_lai'] ?? $_GET['quay_lai'] ?? null);

// đã đăng nhập rồi thì về luôn
if (khach_hien_tai() !== null) {
    chuyen_trang($quayLai);
}

$loi     = '';
$emailCu = '';
$conKhoa = giay_con_khoa();

// khách bấm đăng nhập (sai nhiều lần thì bị khoá tạm)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailCu = trim((string)($_POST['email'] ?? ''));

    if (!csrf_hop_le($_POST['csrf'] ?? null)) {
        $loi = 'Phiên làm việc đã hết hạn, vui lòng thử lại.';
    } elseif ($conKhoa > 0) {
        $loi = 'Đăng nhập sai quá nhiều lần. Vui lòng thử lại sau ' . ceil($conKhoa / 60) . ' phút.';
    } elseif ($emailCu === '' || ($_POST['mat_khau'] ?? '') === '') {
        $loi = 'Vui lòng nhập email và mật khẩu.';
    } elseif (khach_dang_nhap($emailCu, (string)$_POST['mat_khau'])) {
        flash('ok', 'Đăng nhập thành công.');
        chuyen_trang($quayLai);
    } else {
        $conKhoa = giay_con_khoa();
        $loi = $conKhoa > 0
            ? 'Đăng nhập sai quá nhiều lần. Vui lòng thử lại sau ' . ceil($conKhoa / 60) . ' phút.'
            : 'Email hoặc mật khẩu không đúng.';
    }
}

$tieuDe = 'Đăng nhập';
$menu   = 'dang-nhap';
require __DIR__ . '/includes/header.php';
?>
<main class="page">
  <div class="container container--narrow">
    <div class="auth">
      <h1 class="page-title page-title--lg">Đăng nhập</h1>
      <p class="muted auth__sub">Đăng nhập để xem lại các đơn đã đặt. Không đăng nhập vẫn mua hàng bình thường.</p>

      <?php if ($loi !== ''): ?>
        <div class="flash flash--loi"><?= e($loi) ?></div>
      <?php endif; ?>

      <form method="post" class="card form-card" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="quay_lai" value="<?= e($quayLai) ?>">
        <label class="field">
          <span>Email</span>
          <input name="email" type="email" value="<?= e($emailCu) ?>" required autofocus autocomplete="email">
        </label>
        <label class="field">
          <span>Mật khẩu</span>
          <input name="mat_khau" type="password" required autocomplete="current-password">
        </label>
        <button type="submit" class="btn btn--primary btn--lg btn--block">Đăng nhập</button>
      </form>

      <p class="auth__alt">Chưa có tài khoản? <a href="dang-ky.php?quay_lai=<?= urlencode($quayLai) ?>">Đăng ký</a></p>
    </div>
  </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
