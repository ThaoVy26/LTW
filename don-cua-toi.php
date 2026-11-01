<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/khach.php';
require_once __DIR__ . '/includes/don_hang.php';

yeu_cau_khach('don-cua-toi.php');
$khach = khach_hien_tai();

// khách gửi yêu cầu huỷ: kiểm tra lại đơn đúng email và còn chờ xác nhận ngay trong câu UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lyDo = trim((string)($_POST['ly_do'] ?? ''));
    if (!csrf_hop_le($_POST['csrf'] ?? null)) {
        flash('loi', 'Phiên làm việc đã hết hạn, vui lòng thử lại.');
    } elseif ($lyDo === '' || mb_strlen($lyDo) > 200) {
        flash('loi', 'Vui lòng nhập lý do huỷ (tối đa 200 ký tự).');
    } else {
        $st = db()->prepare(
            "UPDATE DonHang SET yeu_cau_huy = 1, ly_do_huy = ?, ngay_yeu_cau_huy = NOW()
             WHERE id = ? AND email_nhan = ? AND trang_thai_don = 'cho_xac_nhan' AND yeu_cau_huy = 0"
        );
        $st->execute([$lyDo, (int)($_POST['id'] ?? 0), $khach['email']]);
        if ($st->rowCount() === 1) {
            flash('ok', 'Đã gửi yêu cầu huỷ đơn, shop sẽ liên hệ xác nhận.');
        } else {
            flash('loi', 'Không thể gửi yêu cầu huỷ cho đơn này.');
        }
    }
    chuyen_trang('don-cua-toi.php');
}

// các đơn có email nhận trùng email tài khoản
$st = db()->prepare('SELECT * FROM DonHang WHERE email_nhan = ? ORDER BY ngay_dat DESC, id DESC LIMIT 50');
$st->execute([$khach['email']]);
$dsDon = $st->fetchAll();

$tieuDe = 'Đơn của tôi';
$menu   = 'don-cua-toi';
require __DIR__ . '/includes/header.php';
?>
<main class="page">
  <div class="container container--narrow">
    <h1 class="page-title page-title--lg">Đơn của tôi</h1>
    <p class="muted auth__sub">Xin chào <?= e($khach['ten']) ?>. Đây là các đơn đặt bằng email <b><?= e($khach['email']) ?></b>.</p>

    <?php if (!$dsDon): ?>
      <div class="card form-card don-rong">
        <p>Bạn chưa có đơn nào.</p>
        <a class="btn btn--primary" href="shop.php">Đi mua sắm</a>
      </div>
    <?php endif; ?>

    <?php foreach ($dsDon as $don): ?>
      <div class="card form-card don-item">
        <div class="don-item__top">
          <div>
            <b><?= e(don_ma((int)$don['id'])) ?></b>
            <span class="muted"> · <?= e(date('d/m/Y H:i', strtotime((string)$don['ngay_dat']))) ?></span>
          </div>
          <span class="badge <?= e(don_lop_badge($don['trang_thai_don'])) ?>"><?= e(don_nhan_trang_thai($don['trang_thai_don'])) ?></span>
        </div>
        <div class="don-item__tong">Tổng tiền: <b><?= tien($don['tong_tien']) ?></b></div>
        <div class="don-item__dong muted">Thanh toán: <?= e(DON_PHUONG_THUC[$don['phuong_thuc_tt']] ?? $don['phuong_thuc_tt']) ?></div>
        <div class="don-item__dong muted">Giao tới: <?= e(dia_chi_rut_gon((string)$don['dia_chi_giao'])) ?></div>

        <details class="don-item__ct">
          <summary>Xem các món trong đơn</summary>
          <?php foreach (don_lay_dong_hang((int)$don['id']) as $d): ?>
            <div class="summary__row">
              <span><?= e($d['ten_hien_thi']) ?> <small>× <?= (int)$d['so_luong'] ?></small></span>
              <span><?= tien($d['thanh_tien']) ?></span>
            </div>
          <?php endforeach; ?>
        </details>

        <?php if ($don['trang_thai_don'] === 'cho_xac_nhan' && (int)$don['yeu_cau_huy'] === 0): ?>
          <details class="don-item__huy">
            <summary class="btn btn--light btn--sm">Yêu cầu huỷ đơn</summary>
            <form method="post">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int)$don['id'] ?>">
              <label class="field">
                <span>Lý do huỷ</span>
                <input name="ly_do" maxlength="200" required>
              </label>
              <button type="submit" class="btn btn--primary btn--sm">Gửi yêu cầu huỷ</button>
            </form>
          </details>
        <?php elseif ((int)$don['yeu_cau_huy'] === 1 && $don['trang_thai_don'] !== 'huy'): ?>
          <p class="don-item__huy-tb">Đã gửi yêu cầu huỷ, shop sẽ liên hệ xác nhận.</p>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
