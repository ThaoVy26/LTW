<?php
/**
 * Trang Tự thiết kế: PHP lấy size / dây / bộ sưu tập / charm từ CSDL,
 * đổ ra JSON cho customizer.js xử lý tương tác (kéo thả, hoàn tác, tính giá).
 * Lưu thiết kế & thêm giỏ gọi api/design.php (server tính lại giá).
 */
require_once __DIR__ . '/includes/functions.php';

$sizes = db()->query('SELECT id, ten_size, so_charm_toi_da FROM KichThuocTay ORDER BY so_charm_toi_da')->fetchAll();
// Dây đen xếp cuối cho giống thứ tự trên Figma (bạc, vàng, trắng, đen)
$days  = db()->query("SELECT id, ten_day, gia_charm_bac FROM DayVong ORDER BY ten_day LIKE '%Đen%', id")->fetchAll();
$bsts  = db()->query(
    'SELECT b.id, b.ten_bo_suu_tap, b.slug, b.hinh_anh, n.ten_nhom, n.slug AS slug_nhom
     FROM BoSuuTapCharm b LEFT JOIN NhomCharm n ON n.id = b.id_nhom
     ORDER BY n.thu_tu, b.id'
)->fetchAll();
$charms = db()->query('SELECT id, id_bo_suu_tap, ten_charm, gia, kieu, hinh_anh FROM Charm ORDER BY thu_tu, id')->fetchAll();

$data = [
    'sizes'  => array_map(fn($s) => ['id' => (int)$s['id'], 'ten' => $s['ten_size'], 'so' => (int)$s['so_charm_toi_da']], $sizes),
    'chains' => array_map(fn($d) => ['id' => (int)$d['id'], 'ten' => $d['ten_day'], 'gia' => (int)$d['gia_charm_bac'], 'mau' => mau_day($d['ten_day'])], $days),
    'collections' => [],
    'start'  => null,
    'edit'   => null,
    'csrf'   => csrf_token(),
];
// Mở sẵn 1 bộ sưu tập: customizer.php?bst=tone-hong hoặc ?nhom=charm-hinh
$moBst  = (string)($_GET['bst'] ?? '');
$moNhom = (string)($_GET['nhom'] ?? '');
foreach ($bsts as $b) {
    $ds = array_values(array_filter($charms, fn($c) => (int)$c['id_bo_suu_tap'] === (int)$b['id']));
    $data['collections'][] = [
        'id' => (int)$b['id'], 'ten' => $b['ten_bo_suu_tap'], 'nhom' => $b['ten_nhom'] ?? 'Khác', 'bia' => $b['hinh_anh'],
        'charms' => array_map(fn($c) => [
            'id'   => (int)$c['id'], 'ten' => $c['ten_charm'], 'gia' => (int)$c['gia'], 'kieu' => $c['kieu'],
            'anh'  => la_anh($c['hinh_anh']) ? $c['hinh_anh'] : null,
            'icon' => icon_charm($c['hinh_anh']),
        ], $ds),
    ];
    if ($data['start'] === null && (($moBst && $b['slug'] === $moBst) || ($moNhom && $b['slug_nhom'] === $moNhom))) {
        $data['start'] = (int)$b['id'];
    }
}

// Sửa thiết kế đã có trong giỏ: customizer.php?edit=ID
$editId = (int)($_GET['edit'] ?? 0);
if ($editId && ($tk = lay_thiet_ke($editId))) {
    $slots = array_fill(0, (int)$tk['so_charm_toi_da'], null);
    foreach ($tk['slots'] as $s) {
        if (array_key_exists($s['vi_tri_slot'], $slots)) {
            $slots[$s['vi_tri_slot']] = $s['id_charm'] !== null ? (int)$s['id_charm'] : null;
        }
    }
    // Charm dài chiếm thêm ô kế tiếp (đánh dấu -1)
    foreach ($tk['slots'] as $s) {
        if ($s['kieu'] === 'dai' && array_key_exists($s['vi_tri_slot'] + 1, $slots)) {
            $slots[$s['vi_tri_slot'] + 1] = -1;
        }
    }
    $data['edit'] = [
        'id'       => $editId,
        'cart_key' => isset($_SESSION['gio']['tk_' . $editId]) ? 'tk_' . $editId : null,
        'size_id'  => (int)$tk['id_kich_thuoc'],
        'chain_id' => (int)$tk['id_day_vong'],
        'slots'    => $slots,
    ];
}

$tieuDe  = 'Tự thiết kế';
$menu    = 'tu-thiet-ke';
$scripts = ['assets/js/customizer.js'];
require __DIR__ . '/includes/header.php';
?>
<main class="page">
  <div class="container">
    <div class="cust-head">
      <h1 class="page-title page-title--lg">Thiết kế vòng Italian Charm của bạn</h1>
      <p class="muted">Chọn size, chọn dây và những chiếc charm yêu thích để tạo một chiếc vòng mang dấu ấn riêng.</p>
      <?php if ($data['edit']): ?>
        <p class="flash flash--info">Bạn đang sửa thiết kế #<?= $editId ?>. Bấm "Thêm vào giỏ" để cập nhật giỏ hàng.</p>
      <?php endif; ?>
    </div>

    <ol class="steps card" id="steps">
      <?php foreach (['Size', 'Dây', 'Bộ sưu tập', 'Charm', 'Kéo thả'] as $i => $buoc): ?>
        <li data-step="<?= $i + 1 ?>"><span class="steps__num"><?= $i + 1 ?></span><?= $buoc ?></li>
      <?php endforeach; ?>
    </ol>

    <details class="guide" open>
      <summary>ⓘ HƯỚNG DẪN SỬ DỤNG</summary>
      <ol>
        <li>Chọn size tay để hệ thống gợi ý số hạt charm.</li>
        <li>Chọn loại dây vòng phù hợp về màu sắc và chất liệu.</li>
        <li>Chọn bộ sưu tập và mẫu charm bạn muốn dùng.</li>
        <li>Kéo thả charm vào đúng vị trí bạn muốn trên vòng (hoặc bấm 1 ô rồi bấm charm).</li>
        <li><b>Charm dài</b> chiếm 2 ô liền nhau · <b>Charm yêu cầu cá nhân</b>: gửi ảnh / chữ muốn in ở bước thanh toán.</li>
      </ol>
      <p><i>Mẹo: Kéo charm giữa các ô để đổi chỗ · Bấm × hoặc phím Delete để gỡ charm · Ctrl+Z để hoàn tác.</i></p>
    </details>

    <section class="card cust-block cust-stage-card" id="block-5">
      <div class="cust-block__head">
        <h3 class="h-icon">✨ Khung kéo thả</h3>
        <div class="seg">
          <button type="button" class="is-active" data-mode="ngang">Ngang</button>
          <button type="button" data-mode="doc">Dọc</button>
          <button type="button" data-mode="xoay">Xoay 360</button>
        </div>
      </div>
      <p class="counter">Charm tùy chỉnh: <b id="filled">0</b>/<span id="total">0</span> · <span class="muted">Còn <span id="remain">0</span> vị trí</span></p>
      <p class="muted small stage-hint">Kéo charm giữa các ô để đổi chỗ · kéo charm ra ngoài khung để gỡ</p>
      <div class="stage" id="stage">
        <div class="bracelet" id="bracelet"></div>
      </div>
      <div class="cust-actions">
        <button type="button" class="btn btn--light" id="undo">Hoàn tác ↶</button>
        <button type="button" class="btn btn--light" id="redo">Làm lại ↷</button>
        <button type="button" class="btn btn--danger-soft" id="clear">🗑 Xóa hết</button>
      </div>
    </section>

    <div class="cust-grid">
      <div class="cust-left">
        <section class="card cust-block" id="block-1">
          <h3>Chọn size tay</h3>
          <div class="size-row">
            <select id="size" class="input" aria-label="Size tay"></select>
            <span class="tag" id="size-hint"></span>
          </div>
        </section>

        <section class="card cust-block" id="block-2">
          <div class="cust-block__head">
            <h3>Chọn dây</h3>
            <span class="muted small">Giá dây nền: <b id="chain-price"></b></span>
          </div>
          <div class="chain-dots" id="chains" role="radiogroup" aria-label="Loại dây"></div>
          <p class="muted small" id="chain-name"></p>
        </section>

        <section class="card cust-block" id="block-3">
          <h3>Bộ sưu tập</h3>
          <select id="collection" class="input" aria-label="Bộ sưu tập"></select>
          <a class="cover" id="cover" href="#" target="_blank" rel="noopener" hidden>
            <img id="cover-img" alt="Bảng mẫu bộ sưu tập">
            <span>Xem bảng mẫu đầy đủ ↗</span>
          </a>
        </section>

        <section class="card cust-block" id="block-4">
          <h3>Lưới charm <span class="muted" id="collection-name"></span></h3>
          <div class="charm-grid" id="charm-grid"></div>
          <p class="muted small center-text">Lưới charm (bấm hoặc kéo vào khung)</p>
        </section>
      </div>

      <div class="cust-right">
        <section class="card cust-block">
          <h3>Bảng tạm tính</h3>
          <div class="summary__row"><span id="chain-line">Dây</span><span id="chain-cost">0đ</span></div>
          <div class="summary__row"><span id="charm-line">Charm × 0</span><span id="charm-cost">0đ</span></div>
          <div class="summary__total summary__total--pink"><span>Tổng cộng:</span><span id="total-price">0đ</span></div>
          <div class="cust-buttons">
            <button type="button" class="btn btn--outline btn--lg" id="save">💾 Lưu thiết kế</button>
            <button type="button" class="btn btn--primary btn--lg" id="add-cart">🛍 Thêm vào giỏ</button>
          </div>
          <p class="cust-msg" id="msg" role="status" aria-live="polite"></p>
        </section>
      </div>
    </div>
  </div>
</main>
<script id="cust-data" type="application/json"><?= json_encode($data, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
<?php require __DIR__ . '/includes/footer.php'; ?>
