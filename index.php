<?php
require_once __DIR__ . '/includes/functions.php';

$idCombo = (int)db()->query("SELECT id FROM DanhMuc WHERE ten_danh_muc = 'Combo' LIMIT 1")->fetchColumn();

// 4 combo mới nhất (đảo lại để hiện theo thứ tự thêm vào)
$st = db()->prepare(
    "SELECT sp.*, dm.ten_danh_muc FROM SanPham sp JOIN DanhMuc dm ON dm.id = sp.id_danh_muc
     WHERE sp.id_danh_muc = ? AND sp.trang_thai = 'dang_ban' ORDER BY sp.id DESC LIMIT 4"
);
$st->execute([$idCombo]);
$combos = array_reverse($st->fetchAll());

// Giá bộ khởi điểm = size nhỏ nhất × dây rẻ nhất
$giaKhoiDiem = (int)db()->query(
    'SELECT MIN(kt.so_charm_toi_da) * MIN(dv.gia_charm_bac) FROM KichThuocTay kt, DayVong dv'
)->fetchColumn();

/*
 * Các slide của khung story ở hero.
 *   loai = 'mau'        → vòng mẫu Sweet Pink Star (HTML)
 *   loai = 'khuyen_mai' → banner mã giảm giá (HTML)
 *   loai = 'anh'        → ảnh   (bỏ file vào assets/hero/)
 *   loai = 'video'      → video ngắn .mp4 (bỏ file vào assets/hero/), chạy hết video mới sang slide sau
 * Slide ảnh/video nào chưa có file thì tự ẩn.
 */
$heroSlides = [
    ['loai' => 'mau'],
    ['loai' => 'anh',   'src' => 'assets/hero/slide-1.jpg', 'tieu_de' => 'BST Mùa Xuân ' . date('Y'), 'mo_ta' => 'Những chiếc charm pastel cho ngày nắng nhẹ', 'link' => 'shop.php'],
    ['loai' => 'video', 'src' => 'assets/hero/slide-2.mp4', 'tieu_de' => 'Ghép charm trong 30 giây', 'mo_ta' => 'Tháo lắp dễ dàng, đổi phong cách mỗi ngày', 'link' => 'customizer.php'],
    ['loai' => 'anh',   'src' => 'assets/hero/slide-3.jpg', 'tieu_de' => 'Combo Love Story', 'mo_ta' => 'Quà tặng cho người thương', 'link' => 'shop.php?dm=' . $idCombo],
    ['loai' => 'khuyen_mai'],
];
$heroSlides = array_values(array_filter(
    $heroSlides,
    fn($s) => !isset($s['src']) || is_file(__DIR__ . '/' . $s['src'])
));

$tieuDe  = 'Trang chủ';
$menu    = 'trang-chu';
$scripts = ['assets/js/story.js'];
require __DIR__ . '/includes/header.php';
?>
<main class="page">
  <div class="container">

    <section class="hero">
      <div class="hero__text">
        <span class="eyebrow">✨ ITALIAN CHARM BRACELET</span>
        <h1 class="hero__title">Mỗi chiếc charm, một câu chuyện của riêng bạn</h1>
        <p class="hero__desc">Chọn dây, chọn size, ghép từng chiếc charm theo cách bạn muốn — vòng tay Italian Charm không đâu giống đâu.</p>
        <a class="btn btn--outline btn--lg" href="customizer.php">Tự thiết kế ngay</a>
      </div>

      <div class="story" id="story" data-interval="5000" aria-roledescription="carousel" aria-label="Bộ sưu tập nổi bật">
        <!-- Thanh tiến trình kiểu story -->
        <div class="story__bars">
          <?php foreach ($heroSlides as $i => $_): ?>
            <button type="button" class="story__bar" data-go="<?= $i ?>" aria-label="Xem slide <?= $i + 1 ?>"><span></span></button>
          <?php endforeach; ?>
        </div>

        <div class="story__track">
          <?php foreach ($heroSlides as $i => $s): ?>
            <div class="story__slide" role="group" aria-label="Slide <?= $i + 1 ?>/<?= count($heroSlides) ?>">

              <?php if ($s['loai'] === 'mau'): ?>
                <div class="hero-card">
                  <div class="hero-card__top">
                    <span class="pill">✨ BST Mùa Xuân <?= date('Y') ?></span>
                    <span class="pill pill--solid">18 Modules</span>
                  </div>
                  <div class="sample">
                    <div class="sample__head">
                      <span>Vòng mẫu: <b>Sweet Pink Star</b></span>
                      <span class="tag">9mm Standard</span>
                    </div>
                    <div class="sample__links">
                      <?php foreach (['C', '❤️', 'A', '⭐', '🎀', 'R', 'M'] as $j => $m): ?>
                        <span class="link-chip<?= $j % 2 ? ' link-chip--pink' : '' ?>"><?= $m ?></span>
                      <?php endforeach; ?>
                    </div>
                    <p class="sample__note"><span class="dot"></span> Thép không gỉ 316L cao cấp chống gỉ &amp; sáng bóng</p>
                  </div>
                  <div class="hero-card__bottom">
                    <div>
                      <small>Giá bộ khởi điểm từ</small>
                      <strong><?= tien($giaKhoiDiem) ?></strong>
                    </div>
                    <a class="btn btn--white btn--sm" href="customizer.php">Tự phối ngay →</a>
                  </div>
                </div>

              <?php elseif ($s['loai'] === 'khuyen_mai'): ?>
                <div class="hero-card">
                  <div class="hero-card__top">
                    <span class="pill">🎁 Ưu đãi tháng này</span>
                    <span class="pill pill--solid">-15%</span>
                  </div>
                  <div class="promo">
                    <p class="promo__label">Nhập mã khi thanh toán</p>
                    <p class="promo__code">SALE15</p>
                    <p class="promo__desc">Giảm 15% cho mọi đơn hàng · Dùng <b>FREESHIP</b> để miễn phí vận chuyển</p>
                  </div>
                  <div class="hero-card__bottom">
                    <div><small>Áp dụng cho</small><strong>Toàn bộ sản phẩm</strong></div>
                    <a class="btn btn--white btn--sm" href="shop.php">Mua ngay →</a>
                  </div>
                </div>

              <?php else: ?>
                <div class="media-slide">
                  <?php if ($s['loai'] === 'video'): ?>
                    <video src="<?= e($s['src']) ?>" muted playsinline preload="metadata"></video>
                  <?php else: ?>
                    <img src="<?= e($s['src']) ?>" alt="<?= e($s['tieu_de']) ?>">
                  <?php endif; ?>
                  <div class="media-slide__caption">
                    <h3><?= e($s['tieu_de']) ?></h3>
                    <p><?= e($s['mo_ta']) ?></p>
                    <a class="btn btn--white btn--sm" href="<?= e($s['link']) ?>">Khám phá →</a>
                  </div>
                </div>
              <?php endif; ?>

            </div>
          <?php endforeach; ?>
        </div>

        <?php if (count($heroSlides) > 1): ?>
          <button type="button" class="story__nav story__nav--prev" aria-label="Slide trước">‹</button>
          <button type="button" class="story__nav story__nav--next" aria-label="Slide sau">›</button>
        <?php endif; ?>
      </div>
    </section>

    <section class="section">
      <div class="section__head">
        <h2>Combo nổi bật</h2>
        <p>Gợi ý những set vòng được yêu thích nhất</p>
      </div>
      <div class="grid grid--4">
        <?php foreach ($combos as $c): ?>
          <article class="combo-card">
            <a class="combo-card__img" href="product.php?id=<?= $c['id'] ?>"><?= anh_san_pham($c, 'Ảnh combo') ?></a>
            <h3 class="combo-card__name"><a href="product.php?id=<?= $c['id'] ?>"><?= e($c['ten_sp']) ?></a></h3>
            <p class="combo-card__desc"><?= e($c['mo_ta']) ?></p>
            <div class="combo-card__foot">
              <span class="price"><?= tien($c['gia']) ?></span>
              <a class="chip-link" href="product.php?id=<?= $c['id'] ?>">Chi tiết</a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
      <div class="center">
        <a class="btn btn--ghost" href="shop.php?dm=<?= $idCombo ?>">Xem tất cả <span aria-hidden="true">›</span></a>
      </div>
    </section>

    <section class="section">
      <h2 class="section__title-caps">Thông tin mua hàng</h2>
      <div class="grid grid--3">
        <?php
        $info = [
            ['bag',    'Hướng dẫn đặt hàng',   'Thiết kế dễ dàng với luồng 6 bước độc quyền',          'Xem hướng dẫn', 'policy.php?p=thanh-toan'],
            ['shield', 'Bảo hành & đồng kiểm', 'Chính sách minh bạch, an tâm tuyệt đối khi nhận hàng', 'Xem chính sách', 'policy.php?p=doi-tra'],
            ['help',   'Hỗ trợ & xử lý sự cố', 'Đội ngũ CSKH chuyên nghiệp luôn sẵn sàng 24/7',       'Liên hệ ngay',   'policy.php?p=faq'],
        ];
        $svg = [
            'bag'    => '<path d="M6 8h12l-1 12H7L6 8z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/>',
            'shield' => '<path d="M12 3 4 6v6c0 5 3.5 8 8 9 4.5-1 8-4 8-9V6l-8-3z"/><path d="m9 12 2 2 4-4"/>',
            'help'   => '<ellipse cx="12" cy="6" rx="7" ry="3"/><path d="M5 6v6c0 1.7 3.1 3 7 3s7-1.3 7-3V6"/><path d="M5 12v6c0 1.7 3.1 3 7 3s7-1.3 7-3v-6"/>',
        ];
        foreach ($info as [$icon, $tieuDeInfo, $moTa, $nut, $link]): ?>
          <div class="info-card">
            <span class="info-card__icon">
              <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><?= $svg[$icon] ?></svg>
            </span>
            <h3><?= e($tieuDeInfo) ?></h3>
            <p><?= e($moTa) ?></p>
            <a href="<?= $link ?>"><?= e($nut) ?> →</a>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

  </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
