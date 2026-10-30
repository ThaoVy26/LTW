<?php
require_once __DIR__ . '/admin_functions.php';

$tieuDe ??= 'Tổng quan';
$menu   ??= '';
$admin   = admin_hien_tai();

$menuAdmin = [
    'tong-quan' => ['index.php',        'Tổng quan'],
    'san-pham'  => ['san-pham.php',     'Sản phẩm'],
    'danh-muc'  => ['danh-muc.php',     'Danh mục'],
    'charm'     => ['charm.php',        'Charm'],
    'don-hang'  => ['don-hang.php',     'Đơn hàng'],
    'khach-hang' => ['khach-hang.php',  'Khách hàng'],
    'ma-giam-gia' => ['ma-giam-gia.php', 'Mã giảm giá'],
    'thong-ke'  => ['thong-ke.php',     'Thống kê'],
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($tieuDe) ?> · Quản trị Charm Atelier</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../<?= phien_ban('assets/css/style.css') ?>">
<link rel="stylesheet" href="../<?= phien_ban('assets/css/admin.css') ?>">
</head>
<body class="admin">
<aside class="ad-side">
  <a class="logo" href="index.php">Charm Atelier.</a>
  <span class="ad-side__tag">Quản trị</span>
  <nav class="ad-menu">
    <?php foreach ($menuAdmin as $key => [$href, $ten]): ?>
      <a href="<?= $href ?>" class="ad-menu__link<?= $menu === $key ? ' is-active' : '' ?>"><?= $ten ?></a>
    <?php endforeach; ?>
  </nav>
  <a class="ad-side__shop" href="../index.php" target="_blank" rel="noopener">Xem trang bán hàng ↗</a>
</aside>

<div class="ad-main">
  <div class="ad-top">
    <h1 class="ad-top__title"><?= e($tieuDe) ?></h1>
    <div class="ad-user">
      <span class="ad-user__name"><?= e($admin['ho_ten'] ?? '') ?></span>
      <a class="ad-user__link" href="doi-mat-khau.php">Đổi mật khẩu</a>
      <form action="logout.php" method="post" class="inline">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn--light btn--sm">Đăng xuất</button>
      </form>
    </div>
  </div>

  <?php $flashes = lay_flash(); if ($flashes): ?>
  <div class="flash-wrap ad-flash">
    <?php foreach ($flashes as $f): ?>
      <div class="flash flash--<?= e($f['loai']) ?>"><?= e($f['noi_dung']) ?></div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <main class="ad-content">
