<?php
require_once __DIR__ . '/khach.php';
$tieuDe ??= 'Trang chủ';
$menu   ??= '';
$moTa   ??= 'Charm Atelier: bán và tự thiết kế vòng tay Italian Charm. Chọn size, chọn dây, kéo thả charm theo cách của riêng bạn.';
$goc    = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')
        . rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/') . '/';
$soLuongGio = gio_dem();
$menuItems = [
    'trang-chu'   => ['index.php', 'Trang chủ'],
    'san-pham'    => ['shop.php', 'Sản phẩm'],
    'tu-thiet-ke' => ['customizer.php', 'Tự thiết kế'],
];
$khach     = khach_hien_tai();
$tvChuaDoc = $khach ? tu_van_chua_doc_khach($khach['id']) : 0;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($tieuDe) ?> · Charm Atelier</title>
<meta name="description" content="<?= e($moTa) ?>">
<meta property="og:type" content="website">
<meta property="og:title" content="<?= e($tieuDe) ?> · Charm Atelier">
<meta property="og:description" content="<?= e($moTa) ?>">
<meta property="og:image" content="<?= e($goc) ?>assets/hero/chao-mung.webp">
<link rel="icon" type="image/svg+xml" href="favicon.svg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= phien_ban('assets/css/style.css') ?>">
</head>
<body>
<header class="header">
  <div class="container header__bar">
    <a class="logo" href="index.php">Charm Atelier.</a>
    <button class="nav-toggle" id="nav-toggle" aria-label="Mở menu" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>
    <nav class="nav" id="nav">
      <?php foreach ($menuItems as $key => [$href, $ten]): ?>
        <a href="<?= $href ?>" class="nav__link<?= $menu === $key ? ' is-active' : '' ?>"><?= $ten ?></a>
      <?php endforeach; ?>
      <a href="cart.php" class="nav__link nav__cart<?= $menu === 'gio-hang' ? ' is-active' : '' ?>">
        Giỏ hàng <span class="cart-count" id="cart-count"><?= $soLuongGio ?></span>
      </a>
      <?php if ($khach): ?>
        <details class="nav__user">
          <summary class="nav__link"><?= e($khach['ten']) ?><?php if ($tvChuaDoc > 0): ?><span class="nav__badge"><?= $tvChuaDoc ?></span><?php endif; ?></summary>
          <div class="nav__menu">
            <a href="don-cua-toi.php">Đơn của tôi</a>
            <a href="tu-van.php">Tư vấn<?php if ($tvChuaDoc > 0): ?><span class="nav__badge"><?= $tvChuaDoc ?></span><?php endif; ?></a>
            <form method="post" action="dang-xuat.php">
              <?= csrf_field() ?>
              <button type="submit">Đăng xuất</button>
            </form>
          </div>
        </details>
      <?php else: ?>
        <a href="dang-nhap.php" class="nav__link<?= $menu === 'dang-nhap' ? ' is-active' : '' ?>">Đăng nhập</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<?php $flashes = lay_flash(); if ($flashes): ?>
<div class="container flash-wrap">
  <?php foreach ($flashes as $f): ?>
    <div class="flash flash--<?= e($f['loai']) ?>"><?= e($f['noi_dung']) ?></div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
