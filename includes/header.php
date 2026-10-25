<?php
/**
 * Header dùng chung. Trước khi include, trang đặt:
 *   $tieuDe  = 'Sản phẩm';     // tiêu đề tab
 *   $menu    = 'san-pham';     // trang-chu | san-pham | tu-thiet-ke | gio-hang
 */
$tieuDe ??= 'Trang chủ';
$menu   ??= '';
$soLuongGio = gio_dem();
$menuItems = [
    'trang-chu'   => ['index.php', 'Trang chủ'],
    'san-pham'    => ['shop.php', 'Sản phẩm'],
    'tu-thiet-ke' => ['customizer.php', 'Tự thiết kế'],
    'tra-cuu'     => ['track-order.php', 'Tra cứu đơn'],
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($tieuDe) ?> · Charm Atelier</title>
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
