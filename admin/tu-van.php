<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
// phải đăng nhập admin mới vào được
yeu_cau_admin();

$pdo  = db();
$idKh = (int)($_GET['khach'] ?? $_POST['khach'] ?? 0);

// gửi tin trả lời khi trình duyệt không chạy js (có js thì gửi qua api)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $noiDung = trim((string)($_POST['noi_dung'] ?? ''));
    $st = $pdo->prepare("SELECT COUNT(*) FROM TaiKhoan WHERE id = ? AND vai_tro = 'khach'");
    $st->execute([$idKh]);
    if (!csrf_hop_le($_POST['csrf'] ?? null)) {
        flash('loi', 'Phiên làm việc đã hết hạn, vui lòng thử lại.');
    } elseif ((int)$st->fetchColumn() !== 1) {
        flash('loi', 'Không tìm thấy khách.');
    } elseif (mb_strlen($noiDung) < 1 || mb_strlen($noiDung) > 500) {
        flash('loi', 'Tin nhắn từ 1 đến 500 ký tự.');
    } else {
        $pdo->prepare("INSERT INTO TinNhanTuVan (id_tai_khoan, nguoi_gui, noi_dung) VALUES (?, 'shop', ?)")->execute([$idKh, $noiDung]);
    }
    chuyen_trang('tu-van.php?khach=' . $idKh);
}

// khách đang chọn (phải là tài khoản khách có thật)
$khachChon = null;
if ($idKh > 0) {
    $st = $pdo->prepare("SELECT id, ho_ten, email FROM TaiKhoan WHERE id = ? AND vai_tro = 'khach'");
    $st->execute([$idKh]);
    $khachChon = $st->fetch() ?: null;
}

$lichSu = [];
if ($khachChon) {
    // mở cuộc chat thì coi như đã đọc tin của khách
    $pdo->prepare("UPDATE TinNhanTuVan SET da_doc = 1 WHERE id_tai_khoan = ? AND nguoi_gui = 'khach' AND da_doc = 0")->execute([$idKh]);
    $st = $pdo->prepare('SELECT id, nguoi_gui, noi_dung, ngay_gui FROM TinNhanTuVan WHERE id_tai_khoan = ? ORDER BY id LIMIT 300');
    $st->execute([$idKh]);
    $lichSu = $st->fetchAll();
}

// danh sách khách đã nhắn, cuộc chat mới nhất lên đầu
$dsKhach = $pdo->query(
    "SELECT t.id, t.ho_ten, t.email, m.noi_dung, m.ngay_gui,
            (SELECT COUNT(*) FROM TinNhanTuVan x WHERE x.id_tai_khoan = t.id AND x.nguoi_gui = 'khach' AND x.da_doc = 0) AS chua_doc
     FROM TaiKhoan t
     JOIN (SELECT id_tai_khoan, MAX(id) AS mid FROM TinNhanTuVan GROUP BY id_tai_khoan) l ON l.id_tai_khoan = t.id
     JOIN TinNhanTuVan m ON m.id = l.mid
     ORDER BY m.id DESC"
)->fetchAll();

$tieuDe  = 'Tư vấn';
$menu    = 'tu-van';
$scripts = ['../' . phien_ban('assets/js/admin-tu-van.js')];
require __DIR__ . '/../includes/admin_header.php';
?>
<link rel="stylesheet" href="../<?= phien_ban('assets/css/admin-tu-van.css') ?>">
<div class="tv">
  <section class="ad-box tv__list">
    <h2 class="ad-box__title">Khách đã nhắn</h2>
    <?php if (!$dsKhach): ?>
      <p class="ad-empty">Chưa có khách nào nhắn tin.</p>
    <?php endif; ?>
    <?php foreach ($dsKhach as $k): ?>
      <a class="tv-kh<?= (int)$k['id'] === $idKh ? ' is-active' : '' ?>" href="tu-van.php?khach=<?= (int)$k['id'] ?>">
        <span class="tv-kh__top">
          <b><?= e($k['ho_ten'] ?: $k['email']) ?></b>
          <?php if ((int)$k['chua_doc'] > 0): ?><span class="ad-menu__badge"><?= (int)$k['chua_doc'] ?></span><?php endif; ?>
        </span>
        <span class="tv-kh__mail"><?= e($k['email']) ?></span>
        <span class="tv-kh__tin"><?= e(mb_strimwidth((string)$k['noi_dung'], 0, 60, '…')) ?></span>
        <span class="tv-kh__gio"><?= e(date('H:i d/m/Y', strtotime((string)$k['ngay_gui']))) ?></span>
      </a>
    <?php endforeach; ?>
  </section>

  <section class="ad-box tv__chat">
    <?php if (!$khachChon): ?>
      <p class="ad-empty">Chọn một khách ở danh sách để xem cuộc trò chuyện.</p>
    <?php else: ?>
      <div id="tv-chat"
           data-api="../api/admin-tu-van.php"
           data-csrf="<?= e(csrf_token()) ?>"
           data-khach="<?= (int)$khachChon['id'] ?>"
           data-lich-su="<?= e(json_encode($lichSu, JSON_UNESCAPED_UNICODE)) ?>">
        <h2 class="ad-box__title"><?= e($khachChon['ho_ten'] ?: $khachChon['email']) ?> <small class="tv__mail"><?= e($khachChon['email']) ?></small></h2>
        <div class="tv__body" id="tv-body"></div>
        <form method="post" class="tv__form" id="tv-form">
          <?= csrf_field() ?>
          <input type="hidden" name="khach" value="<?= (int)$khachChon['id'] ?>">
          <div class="ad-field">
            <textarea name="noi_dung" id="tv-input" rows="2" maxlength="500" placeholder="Nhập câu trả lời…" required></textarea>
          </div>
          <button type="submit" class="btn btn--primary btn--sm">Gửi</button>
        </form>
        <div class="tv__err" id="tv-err" role="alert" hidden></div>
      </div>
    <?php endif; ?>
  </section>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
