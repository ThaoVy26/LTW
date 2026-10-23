<?php
/**
 * Chi tiết đơn hàng + đổi trạng thái (+ hoàn kho khi huỷ). (Mỹ Anh, B6)
 * Đổi trạng thái chỉ nhận POST có CSRF; logic nằm ở don_doi_trang_thai() trong includes/don_hang.php.
 */
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/don_hang.php';
yeu_cau_admin();

$pdo = db();
$id  = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_hop_le($_POST['csrf'] ?? null)) {
        flash('loi', 'Phiên làm việc đã hết hạn, vui lòng thử lại.');
    } else {
        [$ok, $tb] = don_doi_trang_thai($id, (string)($_POST['trang_thai_moi'] ?? ''));
        flash($ok ? 'ok' : 'loi', $tb);
    }
    chuyen_trang('don-hang-chi-tiet.php?id=' . $id);   // PRG: tải lại trang không gửi lại form
}

$st = $pdo->prepare(
    'SELECT d.*, k.email, m.ma_code
     FROM DonHang d
     LEFT JOIN KhachHang k ON k.id = d.id_khach_hang
     LEFT JOIN MaGiamGia m ON m.id = d.id_ma_giam_gia
     WHERE d.id = ?'
);
$st->execute([$id]);
$don = $st->fetch();
if (!$don) {
    flash('loi', 'Không tìm thấy đơn hàng.');
    chuyen_trang('don-hang.php');
}

$dong    = don_lay_dong_hang($id);
$tt      = (string)$don['trang_thai_don'];
$tiepTheo = DON_CHUYEN_HOP_LE[$tt] ?? [];
$anh     = don_anh_dinh_kem($don['ghi_chu']);
$ghiChu  = don_ghi_chu_sach($don['ghi_chu']);

// Nhãn nút cho từng bước chuyển
$nhanNut = [
    'dang_giao' => ['Xác nhận & giao hàng', 'btn--primary'],
    'hoan_tat'  => ['Hoàn tất đơn',          'btn--primary'],
    'huy'       => ['Huỷ đơn',               'btn--danger-soft'],
];

$tieuDe = 'Chi tiết đơn hàng';
$menu   = 'don-hang';
require __DIR__ . '/../includes/admin_header.php';
?>
<link rel="stylesheet" href="../assets/css/admin-don-hang.css">
<div class="dh">
  <div class="dh__head">
    <a class="dh-muted" href="don-hang.php">← Danh sách đơn hàng</a>
    <h2 class="dh__ma"><?= e(don_ma($id)) ?>
      <span class="<?= e(class_trang_thai_don($tt)) ?>"><?= e(don_nhan_trang_thai($tt)) ?></span></h2>
    <div class="dh__sub">Đặt lúc <?= e(date('H:i d/m/Y', strtotime((string)$don['ngay_dat']))) ?></div>
  </div>

  <div class="dh-grid">
    <div>
      <section class="ad-box">
        <h2 class="ad-box__title">Sản phẩm trong đơn</h2>
        <div class="ad-table-wrap">
          <table class="ad-table">
            <thead><tr><th>Sản phẩm</th><th class="num">Đơn giá</th><th class="num">SL</th><th class="num">Thành tiền</th></tr></thead>
            <tbody>
            <?php foreach ($dong as $c): ?>
              <tr>
                <td>
                  <?= e($c['ten_hien_thi']) ?>
                  <div class="dh-muted"><?= $c['id_thiet_ke'] ? 'Thiết kế tuỳ chỉnh' : 'Sản phẩm' ?></div>
                  <?php if ($c['id_thiet_ke'] && ($tk = lay_thiet_ke((int)$c['id_thiet_ke']))): ?>
                    <details class="dh-details">
                      <summary>Xem chi tiết thiết kế</summary>
                      <div>Size <?= e($tk['ten_size']) ?> · Dây <?= e($tk['ten_day']) ?> · <?= (int)$tk['so_charm_tuy_chinh'] ?> charm</div>
                      <ul>
                        <?php foreach ($tk['slots'] as $s): if ($s['id_charm'] === null) continue; ?>
                          <li>Vị trí <?= (int)$s['vi_tri_slot'] + 1 ?>: <?= e($s['ten_charm']) ?><?= ($s['kieu'] ?? '') === 'dai' ? ' (charm dài, chiếm 2 ô)' : '' ?></li>
                        <?php endforeach; ?>
                      </ul>
                    </details>
                  <?php endif; ?>
                </td>
                <td class="num"><?= tien($c['don_gia']) ?></td>
                <td class="num"><?= (int)$c['so_luong'] ?></td>
                <td class="num"><b><?= tien($c['thanh_tien']) ?></b></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div style="margin-top:14px">
          <div class="dh-sum"><span>Tạm tính</span><span><?= tien($don['tam_tinh']) ?></span></div>
          <div class="dh-sum"><span>Phí vận chuyển</span><span><?= tien($don['phi_van_chuyen']) ?></span></div>
          <div class="dh-sum"><span>Giảm giá<?= $don['ma_code'] ? ' (' . e($don['ma_code']) . ')' : '' ?></span><span>-<?= tien($don['giam_gia']) ?></span></div>
          <div class="dh-sum dh-sum--total"><span>Tổng cộng</span><span><?= tien($don['tong_tien']) ?></span></div>
        </div>
      </section>
    </div>

    <div>
      <section class="ad-box">
        <h2 class="ad-box__title">Cập nhật trạng thái</h2>
        <?php if ($tiepTheo): ?>
          <div class="dh-actions">
            <?php foreach ($tiepTheo as $moi): [$nhan, $lop] = $nhanNut[$moi]; ?>
              <form method="post" action="don-hang-chi-tiet.php"
                    <?= $moi === 'huy' ? 'onsubmit="return confirm(\'Huỷ đơn này? Tồn kho sẽ được hoàn lại và không thể khôi phục đơn.\');"' : '' ?>>
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int)$id ?>">
                <input type="hidden" name="trang_thai_moi" value="<?= e($moi) ?>">
                <button class="btn btn--sm <?= e($lop) ?>" type="submit"><?= e($nhan) ?></button>
              </form>
            <?php endforeach; ?>
          </div>
          <p class="dh-note">Luồng: Chờ xác nhận → Đang giao → Hoàn tất. Có thể huỷ khi đơn chưa hoàn tất.</p>
        <?php else: ?>
          <p class="dh-note" style="margin-top:0">
            <?= $tt === 'huy' ? 'Đơn đã huỷ, tồn kho đã được hoàn lại. Không thể đổi trạng thái nữa.' : 'Đơn đã hoàn tất, không thể đổi trạng thái nữa.' ?>
          </p>
        <?php endif; ?>
      </section>

      <section class="ad-box">
        <h2 class="ad-box__title">Thông tin nhận hàng</h2>
        <dl class="dh-info">
          <dt>Người nhận</dt><dd><?= e($don['ho_ten_nhan']) ?></dd>
          <dt>Điện thoại</dt><dd><?= e($don['sdt_nhan']) ?></dd>
          <?php if (!empty($don['email'])): ?><dt>Email</dt><dd><?= e($don['email']) ?></dd><?php endif; ?>
          <dt>Địa chỉ</dt><dd><?= e($don['dia_chi_giao']) ?></dd>
          <dt>Thanh toán</dt><dd><?= e(DON_PHUONG_THUC[$don['phuong_thuc_tt']] ?? $don['phuong_thuc_tt']) ?></dd>
          <?php if ($ghiChu !== ''): ?><dt>Ghi chú</dt><dd><?= e($ghiChu) ?></dd><?php endif; ?>
          <?php if ($anh): ?>
            <dt>Ảnh khách gửi</dt>
            <dd><a class="dh-img" href="../uploads/<?= e($anh) ?>" target="_blank" rel="noopener"><img src="../uploads/<?= e($anh) ?>" alt="Ảnh khách gửi kèm"></a></dd>
          <?php endif; ?>
        </dl>
      </section>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
