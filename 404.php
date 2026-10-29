<?php
/**
 * Trang 404 thân thiện. Dùng được trực tiếp hoặc include từ trang khác khi không tìm thấy dữ liệu.
 */
require_once __DIR__ . '/includes/functions.php';

http_response_code(404);
$tieuDe = 'Không tìm thấy trang';
$menu   = '';
$moTa   = 'Trang bạn tìm không tồn tại hoặc đã được chuyển đi.';
require __DIR__ . '/includes/header.php';
?>
<main class="page">
  <div class="container container--narrow">
    <div class="card empty">
      <h1 class="page-title page-title--xl pink" style="margin-bottom:12px">404</h1>
      <p>Trang bạn tìm không tồn tại hoặc đã được chuyển đi.</p>
      <p style="margin-top:18px">
        <a class="btn btn--primary" href="index.php">Về trang chủ</a>
        <a class="btn btn--outline" href="shop.php">Xem sản phẩm</a>
      </p>
    </div>
  </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
