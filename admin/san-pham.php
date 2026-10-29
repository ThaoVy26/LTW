<?php
require_once __DIR__ . '/../includes/vy_catalog.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/admin_ui.php';
yeu_cau_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_post();
    $id = (int)($_POST['id'] ?? 0);
    if (($_POST['action'] ?? '') === 'delete' && $id) {
        try {
            $s = db()->prepare('SELECT COUNT(*) FROM ChiTietDonHang WHERE id_san_pham=?');
            $s->execute([$id]);
            if ($s->fetchColumn()) throw new RuntimeException('Sản phẩm đã có trong đơn hàng, không thể xoá.');
            $s = db()->prepare('DELETE FROM SanPham WHERE id=?');
            $s->execute([$id]);
            flash('ok', 'Đã xoá sản phẩm.');
        } catch (Throwable $ex) {
            flash('loi', $ex instanceof PDOException ? 'Không thể xoá sản phẩm đang được tham chiếu.' : $ex->getMessage());
        }
    }
    redirect('san-pham.php');
}
$q = trim($_GET['q'] ?? '');
$cat = (int)($_GET['cat'] ?? 0);
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10;
$where = ['1=1'];
$params = [];
if ($q !== '') {
    $where[] = 's.ten_sp LIKE ?';
    $params[] = '%' . $q . '%';
}
if ($cat) {
    $where[] = 's.id_danh_muc=?';
    $params[] = $cat;
}
$filter = implode(' AND ', $where);
$s = db()->prepare('SELECT COUNT(*) FROM SanPham s WHERE ' . $filter);
$s->execute($params);
$total = (int) $s->fetchColumn();
$pages = max(1, (int) ceil($total / $limit));
$page = min($page, $pages);
$s = db()->prepare('SELECT s.*,d.ten_danh_muc,(SELECT COUNT(*) FROM ChiTietDonHang c WHERE c.id_san_pham=s.id) da_ban FROM SanPham s JOIN DanhMuc d ON d.id=s.id_danh_muc WHERE ' . $filter . ' ORDER BY s.id DESC LIMIT ? OFFSET ?');
$i = 1;
foreach ($params as $v) $s->bindValue($i++, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
$s->bindValue($i++, $limit, PDO::PARAM_INT);
$s->bindValue($i, ($page - 1) * $limit, PDO::PARAM_INT);
$s->execute();
$rows = $s->fetchAll();
$cats = db()->query('SELECT id,ten_danh_muc FROM DanhMuc ORDER BY ten_danh_muc')->fetchAll();
$tieuDe = 'Sản phẩm';
$menu = 'san-pham';
require __DIR__ . '/../includes/admin_header.php'; ?>
<link rel="stylesheet" href="../assets/css/admin-san-pham.css">
<div class="sp-admin">
<section class="ad-box">
  <div class="sp-toolbar">
    <form method="get" class="sp-filters">
      <div class="ad-field"><label for="q">Tìm sản phẩm</label><input id="q" type="search" name="q" maxlength="200" placeholder="Nhập tên sản phẩm" value="<?= e($q) ?>"></div>
      <div class="ad-field"><label for="cat">Danh mục</label><select id="cat" name="cat"><option value="0">Tất cả danh mục</option>
        <?php foreach ($cats as $c): ?><option value="<?= (int)$c['id'] ?>" <?= $cat === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['ten_danh_muc']) ?></option><?php endforeach; ?>
      </select></div>
      <button class="btn btn--primary btn--sm">Tìm kiếm</button>
      <?php if ($q !== '' || $cat): ?><a class="btn btn--light btn--sm" href="san-pham.php">Xóa bộ lọc</a><?php endif; ?>
    </form>
    <a class="btn btn--primary btn--sm" href="san-pham-form.php">+ Thêm sản phẩm</a>
  </div>
  <p class="sp-muted"><?= (int)$total ?> sản phẩm · Trang <?= $page ?> / <?= $pages ?></p>
  <div class="ad-table-wrap"><table class="ad-table sp-table">
    <thead><tr><th>Ảnh</th><th>Tên sản phẩm</th><th>Danh mục</th><th class="num">Giá bán</th><th class="num">Tồn kho</th><th>Trạng thái</th><th>Thao tác</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?php if ($r['hinh_anh']): ?><img class="sp-thumb" src="../<?= e($r['hinh_anh']) ?>" alt="<?= e($r['ten_sp']) ?>" loading="lazy"><?php else: ?><span class="sp-no-image">Chưa có ảnh</span><?php endif; ?></td>
        <td class="sp-name"><?= e($r['ten_sp']) ?></td><td><?= e($r['ten_danh_muc']) ?></td>
        <td class="num"><?= tien($r['gia']) ?></td><td class="num"><?= (int)$r['so_luong_ton'] ?></td>
        <td><span class="sp-badge <?= $r['trang_thai'] === 'dang_ban' ? 'sp-badge--on' : 'sp-badge--off' ?>"><?= $r['trang_thai'] === 'dang_ban' ? 'Đang bán' : 'Ngừng bán' ?></span></td>
        <td><div class="sp-actions"><a class="btn btn--light btn--sm" href="san-pham-form.php?id=<?= (int)$r['id'] ?>">Sửa</a>
          <form method="post" onsubmit="return confirm('Xóa sản phẩm này?')">
            <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <button class="btn btn--sm sp-delete" <?= $r['da_ban'] ? 'disabled title="Sản phẩm đã có trong đơn hàng"' : '' ?>>Xóa</button>
          </form></div></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="7" class="ad-empty">Không có sản phẩm phù hợp.</td></tr><?php endif; ?>
    </tbody>
  </table></div>
  <?= ad_phan_trang($page, $pages, fn(int $p): string => '?' . http_build_query(['q' => $q, 'cat' => $cat, 'page' => $p])) ?>
</section>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
