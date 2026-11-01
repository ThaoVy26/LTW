<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/khach.php';

yeu_cau_khach('tu-van.php');
$khach = khach_hien_tai();
$pdo   = db();

// khách mở chat thì coi như đã đọc tin của shop
$pdo->prepare("UPDATE TinNhanTuVan SET da_doc = 1 WHERE id_tai_khoan = ? AND nguoi_gui = 'shop' AND da_doc = 0")->execute([$khach['id']]);

// lịch sử chat của chính tài khoản này
$st = $pdo->prepare('SELECT id, nguoi_gui, noi_dung, ngay_gui FROM TinNhanTuVan WHERE id_tai_khoan = ? ORDER BY id LIMIT 200');
$st->execute([$khach['id']]);
$lichSu = $st->fetchAll();

$tieuDe  = 'Tư vấn';
$menu    = 'tu-van';
$scripts = ['assets/js/tu-van.js'];
require __DIR__ . '/includes/header.php';
?>
<main class="page">
  <div class="container container--narrow">
    <section class="card chat" id="chat"
             data-api="api/tu-van.php"
             data-csrf="<?= e(csrf_token()) ?>"
             data-lich-su="<?= e(json_encode($lichSu, JSON_UNESCAPED_UNICODE)) ?>">
      <header class="chat__head">
        <h1>Tư vấn cùng Charm Atelier</h1>
        <small>Shop phản hồi 08:30 đến 22:00 hằng ngày</small>
      </header>

      <div class="chat__body" id="chat-body" aria-live="polite"></div>

      <form class="chat__form" id="chat-form" autocomplete="off">
        <div class="chat__box">
          <textarea id="chat-input" rows="1" maxlength="500" placeholder="Nhập tin nhắn…" aria-label="Tin nhắn"></textarea>
          <small class="chat__count" id="chat-count" hidden></small>
        </div>
        <button type="submit" class="chat__send" aria-label="Gửi">
          <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M2.5 11.3 20.6 3.4c.8-.3 1.5.4 1.2 1.2l-7.9 18.1c-.4.9-1.7.8-2-.1l-2.2-6.6-6.6-2.2c-.9-.3-1-1.6-.1-2z"/><path d="m9.7 14.3 4.6-4.6" stroke="#fff" stroke-width="1.6" stroke-linecap="round" fill="none"/></svg>
        </button>
      </form>
      <div class="chat__err" id="chat-err" role="alert" hidden></div>
    </section>
  </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
