<?php
/**
 * Tổng quan: số liệu nhanh trong ngày và các đơn mới nhất.
 */
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
yeu_cau_admin();

$pdo = db();

// Doanh thu không tính đơn đã huỷ
$homNay = $pdo->query(
    'SELECT COUNT(*) AS so_don, COALESCE(SUM(tong_tien), 0) AS doanh_thu
     FROM DonHang
     WHERE ngay_dat >= CURDATE() AND ngay_dat < CURDATE() + INTERVAL 1 DAY
       AND trang_thai_don <> "huy"'
)->fetch();

$choXacNhan = (int)$pdo->query('SELECT COUNT(*) FROM DonHang WHERE trang_thai_don = "cho_xac_nhan"')->fetchColumn();
$soSanPham  = (int)$pdo->query('SELECT COUNT(*) FROM SanPham')->fetchColumn();
$soKhach    = (int)$pdo->query('SELECT COUNT(*) FROM KhachHang')->fetchColumn();

$donMoi = $pdo->query(
    'SELECT id, ho_ten_nhan, tong_tien, trang_thai_don, ngay_dat
     FROM DonHang ORDER BY ngay_dat DESC, id DESC LIMIT 5'
)->fetchAll();

$tieuDe = 'Tổng quan';
$menu   = 'tong-quan';
require __DIR__ . '/../includes/admin_header.php';
?>

<div class="ad-cards">
  <div class="ad-card">
    <div class="ad-card__label">Đơn hôm nay</div>
    <div class="ad-card__value"><?= (int)$homNay['so_don'] ?></div>
  </div>
  <div class="ad-card">
    <div class="ad-card__label">Doanh thu hôm nay</div>
    <div class="ad-card__value ad-card__value--pink"><?= tien($homNay['doanh_thu']) ?></div>
    <div class="ad-card__note">Không tính đơn đã huỷ</div>
  </div>
  <div class="ad-card">
    <div class="ad-card__label">Đơn chờ xác nhận</div>
    <div class="ad-card__value"><?= $choXacNhan ?></div>
  </div>
  <div class="ad-card">
    <div class="ad-card__label">Sản phẩm · Khách hàng</div>
    <div class="ad-card__value"><?= $soSanPham ?> · <?= $soKhach ?></div>
  </div>
</div>

<section class="ad-box">
  <div class="ad-box__head">
    <h2 class="ad-box__title">Đơn hàng mới nhất</h2>
    <a class="btn btn--light btn--sm" href="don-hang.php">Xem tất cả</a>
  </div>
  <?php if (!$donMoi): ?>
    <p class="ad-empty">Chưa có đơn hàng nào.</p>
  <?php else: ?>
  <div class="ad-table-wrap">
    <table class="ad-table">
      <thead>
        <tr><th>Mã đơn</th><th>Người nhận</th><th>Ngày đặt</th><th class="num">Tổng tiền</th><th>Trạng thái</th></tr>
      </thead>
      <tbody>
      <?php foreach ($donMoi as $d): ?>
        <tr>
          <td>#<?= (int)$d['id'] ?></td>
          <td><?= e($d['ho_ten_nhan']) ?></td>
          <td><?= e(date('d/m/Y H:i', strtotime($d['ngay_dat']))) ?></td>
          <td class="num"><?= tien($d['tong_tien']) ?></td>
          <td><span class="<?= e(class_trang_thai_don($d['trang_thai_don'])) ?>"><?= e(ten_trang_thai_don($d['trang_thai_don'])) ?></span></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</section>

<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
