<?php
require_once __DIR__ . '/includes/functions.php';

const GIA_TRAN = 1000000;

/* ---- Đọc bộ lọc từ URL ---- */
$dm     = (int)($_GET['dm'] ?? 0);
$tuKhoa = trim((string)($_GET['q'] ?? ''));
$giaMax = (int)($_GET['max'] ?? GIA_TRAN);
$giaMax = max(0, min(GIA_TRAN, $giaMax));
$sapXep = (string)($_GET['sort'] ?? 'pho-bien');
$trang  = max(1, (int)($_GET['page'] ?? 1));

$dsSapXep = [
    'pho-bien' => ['Phổ biến',      'sp.id ASC'],
    'moi'      => ['Mới nhất',      'sp.id DESC'],
    'gia-tang' => ['Giá tăng dần',  'sp.gia ASC'],
    'gia-giam' => ['Giá giảm dần',  'sp.gia DESC'],
    'ten'      => ['Tên A → Z',     'sp.ten_sp ASC'],
];
if (!isset($dsSapXep[$sapXep])) {
    $sapXep = 'pho-bien';
}

/* ---- Danh mục kèm số lượng sản phẩm ---- */
$danhMuc = db()->query(
    "SELECT dm.id, dm.ten_danh_muc, COUNT(sp.id) AS so_sp
     FROM DanhMuc dm LEFT JOIN SanPham sp ON sp.id_danh_muc = dm.id AND sp.trang_thai = 'dang_ban'
     GROUP BY dm.id ORDER BY dm.id"
)->fetchAll();
$tongSp = array_sum(array_column($danhMuc, 'so_sp'));

/* ---- Truy vấn sản phẩm (prepared statement) ---- */
$where  = ["sp.trang_thai = 'dang_ban'", 'sp.gia <= ?'];
$params = [$giaMax];
if ($dm) {
    $where[]  = 'sp.id_danh_muc = ?';
    $params[] = $dm;
}
if ($tuKhoa !== '') {
    $where[]  = 'sp.ten_sp LIKE ?';
    $params[] = '%' . $tuKhoa . '%';
}
$whereSql = implode(' AND ', $where);

$st = db()->prepare("SELECT COUNT(*) FROM SanPham sp WHERE $whereSql");
$st->execute($params);
$tongKetQua = (int)$st->fetchColumn();
$soTrang    = max(1, (int)ceil($tongKetQua / SP_MOI_TRANG));
$trang      = min($trang, $soTrang);
$offset     = ($trang - 1) * SP_MOI_TRANG;

$st = db()->prepare(
    "SELECT sp.*, dm.ten_danh_muc FROM SanPham sp JOIN DanhMuc dm ON dm.id = sp.id_danh_muc
     WHERE $whereSql ORDER BY {$dsSapXep[$sapXep][1]} LIMIT " . SP_MOI_TRANG . " OFFSET $offset"
);
$st->execute($params);
$sanPham = $st->fetchAll();

/** Tạo URL giữ nguyên bộ lọc, chỉ đổi vài tham số. */
function url_loc(array $doi): string
{
    $q = array_merge($_GET, $doi);
    $q = array_filter($q, fn($v) => $v !== '' && $v !== null && $v !== 0);
    return 'shop.php' . ($q ? '?' . http_build_query($q) : '');
}

$tieuDe  = 'Sản phẩm';
$menu    = 'san-pham';
$scripts = ['assets/js/shop.js'];
require __DIR__ . '/includes/header.php';
?>
<main class="page">
  <div class="container">

    <form class="shop-head" method="get" action="shop.php" id="filter-form">
      <h1 class="page-title">Sản phẩm</h1>
      <div class="shop-tools">
        <?php if ($dm): ?><input type="hidden" name="dm" value="<?= $dm ?>"><?php endif; ?>
        <label class="search">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
          <input type="search" name="q" value="<?= e($tuKhoa) ?>" placeholder="Tìm kiếm sản phẩm..." aria-label="Tìm kiếm sản phẩm">
        </label>
        <label class="select">
          <span>Sắp xếp:</span>
          <select name="sort" data-autosubmit aria-label="Sắp xếp">
            <?php foreach ($dsSapXep as $k => [$ten]): ?>
              <option value="<?= $k ?>" <?= $k === $sapXep ? 'selected' : '' ?>><?= $ten ?></option>
            <?php endforeach; ?>
          </select>
        </label>
      </div>

      <div class="shop-layout">
        <aside class="card filter">
          <div class="filter__head">
            <h3>Danh mục</h3>
            <a href="<?= url_loc(['dm' => null, 'page' => null]) ?>">Tất cả (<?= $tongSp ?>)</a>
          </div>
          <ul class="cat-list">
            <li><a class="<?= !$dm ? 'is-active' : '' ?>" href="<?= url_loc(['dm' => null, 'page' => null]) ?>">Tất cả <span><?= $tongSp ?></span></a></li>
            <?php foreach ($danhMuc as $d): ?>
              <li><a class="<?= $dm === (int)$d['id'] ? 'is-active' : '' ?>" href="<?= url_loc(['dm' => $d['id'], 'page' => null]) ?>">
                <?= e($d['ten_danh_muc']) ?> <span><?= $d['so_sp'] ?></span></a></li>
            <?php endforeach; ?>
          </ul>

          <h3 class="filter__sub">Khoảng giá</h3>
          <input class="range" type="range" name="max" id="gia-max" min="0" max="<?= GIA_TRAN ?>" step="10000" value="<?= $giaMax ?>">
          <div class="range-labels">
            <span>0đ</span><b id="gia-text">0đ – <?= tien($giaMax) ?></b><span><?= tien(GIA_TRAN) ?></span>
          </div>
          <a class="btn btn--dashed btn--block" href="shop.php">Đặt lại bộ lọc</a>
        </aside>

        <section>
          <?php if ($tuKhoa !== ''): ?>
            <p class="result-note">Tìm thấy <b><?= $tongKetQua ?></b> sản phẩm cho "<?= e($tuKhoa) ?>"</p>
          <?php endif; ?>

          <?php if (!$sanPham): ?>
            <div class="card empty">
              <p>Không tìm thấy sản phẩm phù hợp.</p>
              <a class="btn btn--outline" href="shop.php">Xem tất cả sản phẩm</a>
            </div>
          <?php else: ?>
            <div class="grid grid--3">
              <?php foreach ($sanPham as $sp): ?>
                <article class="product-card">
                  <div class="product-card__media">
                    <?php if ($sp['nhan']): ?><span class="badge <?= class_nhan($sp['nhan']) ?>"><?= e($sp['nhan']) ?></span><?php endif; ?>
                    <button type="button" class="wish" data-wish="<?= $sp['id'] ?>" aria-label="Yêu thích">
                      <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20s-7-4.4-7-9.5A4 4 0 0 1 12 8a4 4 0 0 1 7 2.5C19 15.6 12 20 12 20z"/></svg>
                    </button>
                    <a href="product.php?id=<?= $sp['id'] ?>"><?= anh_san_pham($sp) ?></a>
                  </div>
                  <div class="product-card__body">
                    <h3><a href="product.php?id=<?= $sp['id'] ?>"><?= e($sp['ten_sp']) ?></a></h3>
                    <div class="price"><?= tien($sp['gia']) ?></div>
                    <?php if ($sp['so_luong_ton'] > 0): ?>
                      <button type="button" class="btn btn--outline btn--block js-add" data-id="<?= $sp['id'] ?>">Thêm giỏ</button>
                    <?php else: ?>
                      <button type="button" class="btn btn--outline btn--block" disabled>Hết hàng</button>
                    <?php endif; ?>
                  </div>
                </article>
              <?php endforeach; ?>
            </div>

            <?php if ($soTrang > 1): ?>
              <nav class="pagination" aria-label="Phân trang">
                <a class="<?= $trang === 1 ? 'is-disabled' : '' ?>" href="<?= url_loc(['page' => max(1, $trang - 1)]) ?>" aria-label="Trang trước">‹</a>
                <?php for ($i = 1; $i <= $soTrang; $i++): ?>
                  <a class="<?= $i === $trang ? 'is-active' : '' ?>" href="<?= url_loc(['page' => $i]) ?>"><?= $i ?></a>
                <?php endfor; ?>
                <a class="<?= $trang === $soTrang ? 'is-disabled' : '' ?>" href="<?= url_loc(['page' => min($soTrang, $trang + 1)]) ?>" aria-label="Trang sau">›</a>
              </nav>
            <?php endif; ?>
          <?php endif; ?>
        </section>
      </div>
    </form>

    <!-- Form dùng chung cho các nút "Thêm giỏ" (shop.js gửi bằng fetch) -->
    <form id="add-form" method="post" action="cart_action.php" hidden>
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="add">
      <input type="hidden" name="id" value="">
      <input type="hidden" name="back" value="<?= e($_SERVER['REQUEST_URI'] ?? 'shop.php') ?>">
    </form>
  </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
