<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/don_hang.php';
// phải đăng nhập admin mới vào được
yeu_cau_admin();

$id = (int)($_GET['id'] ?? 0);
$st = db()->prepare(
    'SELECT d.*, k.email, m.ma_code
     FROM DonHang d
     LEFT JOIN KhachHang k ON k.id = d.id_khach_hang
     LEFT JOIN MaGiamGia m ON m.id = d.id_ma_giam_gia
     WHERE d.id = ?'
);
$st->execute([$id]);
$don = $st->fetch();
if (!$don) {
    flash('loi', 'Không tìm thấy đơn hàng để in hoá đơn.');
    chuyen_trang('don-hang.php');
}

$dong = don_lay_dong_hang($id);
$huy  = $don['trang_thai_don'] === 'huy';
$ma   = don_ma($id);

// mô tả 1 thiết kế để in lên hoá đơn
function hd_mo_ta_thiet_ke(int $idThietKe): string
{
    $tk = lay_thiet_ke($idThietKe);
    if (!$tk) {
        return '';
    }
    $charm = [];
    foreach ($tk['slots'] as $s) {
        if ($s['id_charm'] !== null) {
            $charm[] = $s['ten_charm'];
        }
    }
    return 'Size ' . $tk['ten_size'] . ' · Dây ' . $tk['ten_day'] . ' · ' . count($charm) . ' charm'
        . ($charm ? ': ' . implode(', ', $charm) : '');
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Hoá đơn <?= e($ma) ?> · Charm Atelier</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../<?= phien_ban('assets/css/style.css') ?>">
<link rel="stylesheet" href="../<?= phien_ban('assets/css/admin.css') ?>">
<link rel="stylesheet" href="../<?= phien_ban('assets/css/hoa-don.css') ?>">
</head>
<body class="hd">

<div class="hd-bar no-print">
  <a href="don-hang-chi-tiet.php?id=<?= (int)$id ?>">← Quay lại đơn hàng</a>
  <button type="button" class="btn btn--primary btn--sm" onclick="window.print()">In / Lưu PDF</button>
</div>

<main class="hd-sheet">
  <header class="hd-head">
    <div>
      <div class="logo">Charm Atelier.</div>
      <div class="hd-shop">
        Email: charmatelierxx@gmail.com<br>
        Hỗ trợ: 08:30 – 22:00 hàng ngày
      </div>
    </div>
    <div class="hd-title">
      <h1>HOÁ ĐƠN BÁN HÀNG</h1>
      <div>Số: <b><?= e($ma) ?></b></div>
      <div>Ngày đặt: <?= e(date('d/m/Y H:i', strtotime((string)$don['ngay_dat']))) ?></div>
    </div>
  </header>

  <?php if ($huy): ?>
    <div class="hd-huy">Đơn hàng này đã bị huỷ, hoá đơn không có giá trị thanh toán.</div>
  <?php endif; ?>

  <section class="hd-info">
    <div>
      <h2>Khách hàng</h2>
      <p><b><?= e($don['ho_ten_nhan']) ?></b></p>
      <p>Điện thoại: <?= e($don['sdt_nhan']) ?></p>
      <?php if (!empty($don['email'])): ?><p>Email: <?= e($don['email']) ?></p><?php endif; ?>
      <p>Địa chỉ giao: <?= e($don['dia_chi_giao']) ?></p>
    </div>
    <div>
      <h2>Thanh toán</h2>
      <p>Phương thức: <?= e(DON_PHUONG_THUC[$don['phuong_thuc_tt']] ?? $don['phuong_thuc_tt']) ?></p>
      <p>Trạng thái đơn: <?= e(don_nhan_trang_thai($don['trang_thai_don'])) ?></p>
      <?php if ($don['ma_code']): ?><p>Mã giảm giá: <?= e($don['ma_code']) ?></p><?php endif; ?>
    </div>
  </section>

  <table class="hd-table">
    <thead>
      <tr><th class="c">STT</th><th>Sản phẩm</th><th class="num">Đơn giá</th><th class="c">SL</th><th class="num">Thành tiền</th></tr>
    </thead>
    <tbody>
    <?php foreach ($dong as $i => $c): ?>
      <tr>
        <td class="c"><?= $i + 1 ?></td>
        <td>
          <?= e($c['ten_hien_thi']) ?>
          <?php if ($c['id_thiet_ke'] && ($mo = hd_mo_ta_thiet_ke((int)$c['id_thiet_ke'])) !== ''): ?>
            <div class="hd-sub"><?= e($mo) ?></div>
          <?php endif; ?>
        </td>
        <td class="num"><?= tien($c['don_gia']) ?></td>
        <td class="c"><?= (int)$c['so_luong'] ?></td>
        <td class="num"><?= tien($c['thanh_tien']) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>

  <div class="hd-sum">
    <div><span>Tạm tính</span><span><?= tien($don['tam_tinh']) ?></span></div>
    <div><span>Phí vận chuyển</span><span><?= tien($don['phi_van_chuyen']) ?></span></div>
    <div><span>Giảm giá<?= $don['ma_code'] ? ' (' . e($don['ma_code']) . ')' : '' ?></span><span>-<?= tien($don['giam_gia']) ?></span></div>
    <div class="hd-sum__total"><span>Tổng cộng</span><span><?= tien($don['tong_tien']) ?></span></div>
  </div>

  <?php $ghiChu = don_ghi_chu_sach($don['ghi_chu']); if ($ghiChu !== ''): ?>
    <p class="hd-note"><b>Ghi chú:</b> <?= e($ghiChu) ?></p>
  <?php endif; ?>

  <footer class="hd-foot">
    Cảm ơn bạn đã mua sắm tại Charm Atelier!
    <small>Hoá đơn in lúc <?= e(date('d/m/Y H:i')) ?></small>
  </footer>
</main>

</body>
</html>
