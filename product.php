<?php
require_once __DIR__ . '/includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
$st = db()->prepare(
    "SELECT sp.*, dm.ten_danh_muc, c.kieu AS kieu_charm, b.slug AS slug_bst, b.ten_bo_suu_tap
     FROM SanPham sp JOIN DanhMuc dm ON dm.id = sp.id_danh_muc
     LEFT JOIN Charm c ON c.id = sp.id_charm
     LEFT JOIN BoSuuTapCharm b ON b.id = c.id_bo_suu_tap
     WHERE sp.id = ? AND sp.trang_thai = 'dang_ban'"
);
$st->execute([$id]);
$sp = $st->fetch();
if (!$sp) {
    http_response_code(404);
    flash('loi', 'Không tìm thấy sản phẩm.');
    chuyen_trang('shop.php');
}

$st = db()->prepare(
    "SELECT sp.*, dm.ten_danh_muc FROM SanPham sp JOIN DanhMuc dm ON dm.id = sp.id_danh_muc
     WHERE sp.id_danh_muc = ? AND sp.id <> ? AND sp.trang_thai = 'dang_ban' ORDER BY RAND() LIMIT 4"
);
$st->execute([$sp['id_danh_muc'], $sp['id']]);
$lienQuan = $st->fetchAll();

$tieuDe = $sp['ten_sp'];
$menu   = 'san-pham';
require __DIR__ . '/includes/header.php';
?>
<main class="page">
  <div class="container">
    <nav class="breadcrumb">
      <a href="index.php">Trang chủ</a> › <a href="shop.php">Sản phẩm</a> ›
      <a href="shop.php?dm=<?= $sp['id_danh_muc'] ?>"><?= e($sp['ten_danh_muc']) ?></a> › <span><?= e($sp['ten_sp']) ?></span>
    </nav>

    <div class="detail">
      <div class="detail__media card<?= $sp['id_charm'] ? ' detail__media--charm' : '' ?>">
        <?php if ($sp['nhan']): ?><span class="badge <?= class_nhan($sp['nhan']) ?>"><?= e($sp['nhan']) ?></span><?php endif; ?>
        <?= anh_san_pham($sp) ?>
      </div>
      <div class="detail__info card">
        <span class="eyebrow"><?= e($sp['ten_danh_muc']) ?></span>
        <h1><?= e($sp['ten_sp']) ?></h1>
        <div class="price price--lg"><?= tien($sp['gia']) ?></div>
        <?php if ($sp['mo_ta']): ?><p class="muted"><?= e($sp['mo_ta']) ?></p><?php endif; ?>
        <ul class="ticks">
          <li>Thép không gỉ 316L, không đen, không gỉ</li>
          <li>Tháo lắp charm dễ dàng, đổi phong cách mỗi ngày</li>
          <li>Đóng hộp quà miễn phí</li>
        </ul>
        <p class="stock"><?= $sp['so_luong_ton'] > 0 ? 'Còn ' . (int)$sp['so_luong_ton'] . ' sản phẩm' : 'Tạm hết hàng' ?></p>

        <?php if ($sp['so_luong_ton'] > 0): ?>
          <form method="post" action="cart_action.php" class="detail__buy">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="id" value="<?= $sp['id'] ?>">
            <input type="hidden" name="back" value="cart.php">
            <div class="qty" data-qty>
              <button type="button" data-step="-1" aria-label="Giảm">−</button>
              <input type="number" name="so_luong" value="1" min="1" max="<?= (int)$sp['so_luong_ton'] ?>" aria-label="Số lượng">
              <button type="button" data-step="1" aria-label="Tăng">+</button>
            </div>
            <button class="btn btn--primary btn--lg" type="submit">Thêm vào giỏ</button>
          </form>
        <?php endif; ?>
        <?php if ($sp['slug_bst']): ?>
          <a class="btn btn--soft" href="customizer.php?bst=<?= e($sp['slug_bst']) ?>">✨ Gắn charm này lên vòng (bộ <?= e($sp['ten_bo_suu_tap']) ?>)</a>
        <?php else: ?>
          <a class="link-muted" href="customizer.php">Hoặc tự thiết kế vòng của riêng bạn →</a>
        <?php endif; ?>
      </div>
    </div>

    <?php if ($lienQuan): ?>
      <section class="section">
        <div class="section__head"><h2>Có thể bạn cũng thích</h2></div>
        <div class="grid grid--4">
          <?php foreach ($lienQuan as $c): ?>
            <article class="combo-card">
              <a class="combo-card__img" href="product.php?id=<?= $c['id'] ?>"><?= anh_san_pham($c) ?></a>
              <h3 class="combo-card__name"><a href="product.php?id=<?= $c['id'] ?>"><?= e($c['ten_sp']) ?></a></h3>
              <p class="combo-card__desc"><?= e($c['mo_ta']) ?></p>
              <div class="combo-card__foot">
                <span class="price"><?= tien($c['gia']) ?></span>
                <a class="chip-link" href="product.php?id=<?= $c['id'] ?>">Chi tiết</a>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>
  </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
