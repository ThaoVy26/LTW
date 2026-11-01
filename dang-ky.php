<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/khach.php';

$quayLai = khach_trang_quay_lai($_POST['quay_lai'] ?? $_GET['quay_lai'] ?? 'don-cua-toi.php');

// đã đăng nhập rồi thì khỏi đăng ký nữa
if (khach_hien_tai() !== null) {
    chuyen_trang($quayLai);
}

$du  = ['ho_ten' => '', 'email' => '', 'sdt' => ''];
$loi = '';

// khách bấm đăng ký
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($du as $k => $_) {
        $du[$k] = trim((string)($_POST[$k] ?? ''));
    }
    if (!csrf_hop_le($_POST['csrf'] ?? null)) {
        $loi = 'Phiên làm việc đã hết hạn, vui lòng thử lại.';
    } else {
        $loi = khach_dang_ky($du['ho_ten'], $du['email'], $du['sdt'], (string)($_POST['mat_khau'] ?? ''), (string)($_POST['nhap_lai'] ?? '')) ?? '';
        if ($loi === '') {
            flash('ok', 'Đăng ký thành công, chào mừng bạn đến với Charm Atelier!');
            chuyen_trang($quayLai);
        }
    }
}

$tieuDe = 'Đăng ký';
$menu   = 'dang-nhap';
require __DIR__ . '/includes/header.php';
?>
<main class="page">
  <div class="container container--narrow">
    <div class="auth">
      <h1 class="page-title page-title--lg">Đăng ký tài khoản</h1>
      <p class="muted auth__sub">Có tài khoản thì xem lại được các đơn đã đặt. Không đăng ký vẫn mua hàng bình thường.</p>

      <?php if ($loi !== ''): ?>
        <div class="flash flash--loi"><?= e($loi) ?></div>
      <?php endif; ?>

      <form method="post" class="card form-card" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="quay_lai" value="<?= e($quayLai) ?>">
        <label class="field">
          <span>Họ và tên *</span>
          <input name="ho_ten" value="<?= e($du['ho_ten']) ?>" required autocomplete="name">
        </label>
        <div class="row2">
          <label class="field">
            <span>Email *</span>
            <input name="email" type="email" value="<?= e($du['email']) ?>" required autocomplete="email">
          </label>
          <label class="field">
            <span>Số điện thoại <small class="muted">(tuỳ chọn)</small></span>
            <input name="sdt" value="<?= e($du['sdt']) ?>" inputmode="tel" pattern="0\d{9}" autocomplete="tel">
          </label>
        </div>
        <div class="row2">
          <label class="field">
            <span>Mật khẩu * <small class="muted">(từ <?= MAT_KHAU_TOI_THIEU ?> ký tự)</small></span>
            <input name="mat_khau" type="password" required autocomplete="new-password">
          </label>
          <label class="field">
            <span>Nhập lại mật khẩu *</span>
            <input name="nhap_lai" type="password" required autocomplete="new-password">
          </label>
        </div>
        <button type="submit" class="btn btn--primary btn--lg btn--block">Đăng ký</button>
      </form>

      <p class="auth__alt">Đã có tài khoản? <a href="dang-nhap.php?quay_lai=<?= urlencode($quayLai) ?>">Đăng nhập</a></p>
    </div>
  </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
