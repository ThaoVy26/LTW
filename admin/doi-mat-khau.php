<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
// phải đăng nhập admin mới vào được
yeu_cau_admin();

$loi = '';

// đổi mật khẩu admin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_hop_le($_POST['csrf'] ?? null)) {
        $loi = 'Phiên làm việc đã hết hạn, vui lòng thử lại.';
    } else {
        $loi = doi_mat_khau(
            admin_hien_tai()['id'],
            (string)($_POST['mat_khau_cu'] ?? ''),
            (string)($_POST['mat_khau_moi'] ?? ''),
            (string)($_POST['nhap_lai'] ?? '')
        ) ?? '';

        if ($loi === '') {
            flash('ok', 'Đã đổi mật khẩu thành công.');
            chuyen_trang('doi-mat-khau.php');
        }
    }
}

$tieuDe = 'Đổi mật khẩu';
$menu   = '';
require __DIR__ . '/../includes/admin_header.php';
?>

<section class="ad-box">
  <?php if ($loi !== ''): ?>
    <div class="flash flash--loi" style="margin-bottom:16px"><?= e($loi) ?></div>
  <?php endif; ?>

  <form method="post" class="ad-form" autocomplete="off">
    <?= csrf_field() ?>
    <div class="ad-field">
      <label for="mat_khau_cu">Mật khẩu hiện tại</label>
      <input type="password" id="mat_khau_cu" name="mat_khau_cu" required>
    </div>
    <div class="ad-field">
      <label for="mat_khau_moi">Mật khẩu mới</label>
      <input type="password" id="mat_khau_moi" name="mat_khau_moi" minlength="<?= MAT_KHAU_TOI_THIEU ?>" required>
      <div class="ad-field__hint">Tối thiểu <?= MAT_KHAU_TOI_THIEU ?> ký tự.</div>
    </div>
    <div class="ad-field">
      <label for="nhap_lai">Nhập lại mật khẩu mới</label>
      <input type="password" id="nhap_lai" name="nhap_lai" required>
    </div>
    <button type="submit" class="btn btn--primary">Lưu mật khẩu</button>
  </form>
</section>

<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
