<?php
require_once __DIR__ . '/includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
if (!in_array($id, $_SESSION['don_cua_toi'] ?? [], true)) {
    chuyen_trang('index.php');
}
$st = db()->prepare('SELECT * FROM DonHang WHERE id = ?');
$st->execute([$id]);
$don = $st->fetch();
$st = db()->prepare('SELECT * FROM ChiTietDonHang WHERE id_don_hang = ?');
$st->execute([$id]);
$chiTiet = $st->fetchAll();

$tenPttt = ['COD' => 'Thanh toán khi nhận hàng', 'BANK' => 'Chuyển khoản', 'WALLET' => 'Ví điện tử'];
$maDon   = '#CA' . str_pad((string)$id, 6, '0', STR_PAD_LEFT);

$tieuDe = 'Đặt hàng thành công';
require __DIR__ . '/includes/header.php';
?>
<link rel="stylesheet" href="<?= e(phien_ban('assets/css/in-an.css')) ?>" media="print">
<main class="page">
  <div class="container container--narrow">
    <div class="card success">
      <div class="success__icon">✓</div>
      <h1>Đặt hàng thành công!</h1>
      <p class="muted">Cảm ơn <b><?= e($don['ho_ten_nhan']) ?></b>. Mã đơn của bạn là <b class="pink"><?= $maDon ?></b>.
        Charm Atelier sẽ gọi xác nhận qua số <b><?= e($don['sdt_nhan']) ?></b> trong 24 giờ.</p>

      <div class="success__box">
        <div class="summary__row"><span>Giao tới:</span><span><?= e($don['dia_chi_giao']) ?></span></div>
        <div class="summary__row"><span>Thanh toán:</span><span><?= e($tenPttt[$don['phuong_thuc_tt']] ?? $don['phuong_thuc_tt']) ?></span></div>
        <div class="summary__row"><span>Trạng thái:</span><span class="badge badge--yellow">Chờ xác nhận</span></div>
        <hr>
        <?php foreach ($chiTiet as $c): ?>
          <div class="summary__row summary__row--item"><span><?= e($c['ten_hien_thi']) ?> <small>× <?= $c['so_luong'] ?></small></span><span><?= tien($c['thanh_tien']) ?></span></div>
        <?php endforeach; ?>
        <hr>
        <div class="summary__row"><span>Phí vận chuyển:</span><span><?= tien($don['phi_van_chuyen']) ?></span></div>
        <div class="summary__row"><span>Giảm giá:</span><span>-<?= tien($don['giam_gia']) ?></span></div>
        <div class="summary__total"><span>Tổng cộng:</span><span><?= tien($don['tong_tien']) ?></span></div>
      </div>

      <div class="success__actions no-print">
        <button type="button" class="btn btn--light" onclick="window.print()">In / Lưu PDF</button>
        <a class="btn btn--outline" href="shop.php">Tiếp tục mua sắm</a>
        <a class="btn btn--primary" href="index.php">Về trang chủ</a>
      </div>
    </div>
  </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
