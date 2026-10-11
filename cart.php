<?php
require_once __DIR__ . '/includes/functions.php';

$dong = gio_chi_tiet();
$tien = gio_tinh_tien($dong);

$tieuDe = 'Giỏ hàng';
$menu   = 'gio-hang';
require __DIR__ . '/includes/header.php';

/** Nút nhỏ gửi 1 thao tác giỏ hàng. */
function nut_gio(string $action, string $key, string $nhan, string $class = '', string $aria = ''): string
{
    return '<form method="post" action="cart_action.php" class="inline">' . csrf_field()
        . '<input type="hidden" name="action" value="' . $action . '">'
        . '<input type="hidden" name="key" value="' . e($key) . '">'
        . '<button type="submit" class="' . $class . '"' . ($aria ? ' aria-label="' . e($aria) . '"' : '') . '>' . $nhan . '</button></form>';
}
?>
<main class="page">
  <div class="container">
    <h1 class="page-title page-title--xl">Giỏ hàng</h1>

    <div class="two-col">
      <section class="card cart-list">
        <?php if (!$dong): ?>
          <div class="empty">
            <p>Giỏ hàng của bạn đang trống.</p>
            <a class="btn btn--outline" href="shop.php">Đi mua sắm</a>
            <a class="btn btn--primary" href="customizer.php">Tự thiết kế vòng</a>
          </div>
        <?php endif; ?>

        <?php foreach ($dong as $d): ?>
          <div class="cart-item">
            <div class="cart-item__img">
              <?php if ($d['loai'] === 'san_pham'): ?>
                <?= anh_san_pham($d['sp'], 'Ảnh vòng') ?>
              <?php else: ?>
                <div class="mini-bracelet">
                  <?php foreach (array_slice($d['thiet_ke']['slots'], 0, 8) as $s): ?>
                    <span><?= $s['id_charm'] ? icon_charm($s['hinh_anh']) : '' ?></span>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
            <div class="cart-item__info">
              <h3>
                <?php if ($d['loai'] === 'san_pham'): ?>
                  <a href="product.php?id=<?= $d['id'] ?>"><?= e($d['ten']) ?></a>
                <?php else: ?>
                  <?= e($d['ten']) ?>
                <?php endif; ?>
              </h3>
              <p class="muted"><?= e($d['mo_ta']) ?></p>
              <?php if ($d['loai'] === 'thiet_ke'): ?>
                <a class="link-pink" href="customizer.php?edit=<?= $d['id'] ?>">Sửa thiết kế</a>
              <?php endif; ?>
            </div>
            <div class="cart-item__side">
              <div class="qty qty--sm">
                <?= nut_gio('dec', $d['key'], '−', '', 'Giảm số lượng') ?>
                <span><?= $d['so_luong'] ?></span>
                <?= nut_gio('inc', $d['key'], '+', '', 'Tăng số lượng') ?>
              </div>
              <strong class="cart-item__price"><?= tien($d['don_gia'] * $d['so_luong']) ?></strong>
              <?= nut_gio('remove', $d['key'], 'Xóa', 'link-del') ?>
            </div>
          </div>
        <?php endforeach; ?>
      </section>

      <aside class="card summary">
        <h3 class="summary__title">Tóm tắt đơn</h3>

        <?php if ($tien['ma']): ?>
          <div class="coupon coupon--applied">
            <span>Mã <b><?= e($tien['ma']['ma_code']) ?></b> đã áp dụng</span>
            <form method="post" action="cart_action.php" class="inline">
              <?= csrf_field() ?><input type="hidden" name="action" value="coupon_remove">
              <button type="submit">Bỏ mã</button>
            </form>
          </div>
        <?php else: ?>
          <form method="post" action="cart_action.php" class="coupon">
            <?= csrf_field() ?><input type="hidden" name="action" value="coupon">
            <input type="text" name="ma" placeholder="Nhập mã giảm giá" autocomplete="off" aria-label="Mã giảm giá">
            <button type="submit">Áp dụng</button>
          </form>
          <p class="hint">Thử mã: CHARM10, FREESHIP, SALE15</p>
        <?php endif; ?>

        <div class="summary__row"><span>Tạm tính:</span><span><?= tien($tien['tam']) ?></span></div>
        <div class="summary__row"><span>Phí vận chuyển:</span><span><?= tien($tien['ship']) ?></span></div>
        <div class="summary__row"><span>Giảm giá:</span><span>-<?= tien($tien['giam']) ?></span></div>
        <div class="summary__total"><span>Tổng cộng:</span><span><?= tien($tien['tong']) ?></span></div>

        <?php if ($dong): ?>
          <a class="btn btn--soft btn--block btn--lg" href="checkout.php">Thanh toán</a>
        <?php else: ?>
          <span class="btn btn--soft btn--block btn--lg is-disabled">Thanh toán</span>
        <?php endif; ?>
      </aside>
    </div>
  </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
