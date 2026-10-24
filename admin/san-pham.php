<?php
require_once __DIR__ . '/../includes/vy_catalog.php';
require_once __DIR__ . '/../includes/auth.php';
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
require __DIR__ . '/../includes/admin_header.php'; ?>
<h1>Sản phẩm</h1>
<p>
    <a class="button" href="san-pham-form.php">+ Thêm sản phẩm</a>
</p>
<form method="get" class="filters">

    <input name="q" placeholder="Tìm theo tên" value="<?= e($q) ?>">

    <select name="cat">
        <option value="0">Tất cả danh mục</option>
        <?php foreach ($cats as $c): ?>
        <option value="<?= e($c['id']) ?>" <?= $cat == (int) $c['id'] ? 'selected' : '' ?>>
            <?= e($c['ten_danh_muc']) ?>
        </option>
        <?php endforeach; ?>
    </select>
    <button>Tìm kiếm</button>
</form>
<table>
    <tr>
        <th>Ảnh</th>
        <th>Tên</th>
        <th>Danh mục</th>
        <th>Giá</th>
        <th>Tồn</th>
        <th>Trạng thái</th>
        <th>Thao tác</th>
    </tr>
    <?php foreach ($rows as $r): ?>
    <tr>
        <td>
            <?php if ($r['hinh_anh']): ?>
            <img class="thumb" src="../uploads/<?= e(basename($r['hinh_anh'])) ?>" alt="">
            <?php endif; ?>
        </td>
        <td>
            <?= e($r['ten_sp']) ?>
        </td>
        <td>
            <?= e($r['ten_danh_muc']) ?>
        </td>
        <td>
            <?= tien($r['gia']) ?>
        </td>
        <td>
            <?= e($r['so_luong_ton']) ?>
        </td>
        <td>
            <?= e($r['trang_thai']) ?>
        </td>
        <td>
            <a href="san-pham-form.php?id=<?= e($r['id']) ?>">Sửa</a>
            <form class="inline" method="post" onsubmit="return confirm('Xoá sản phẩm?')">
                <?= csrf_field() ?>

                <input type="hidden" name="action" value="delete">

                <input type="hidden" name="id" value="<?= e($r['id']) ?>">
                <button class="danger" <?= $r['da_ban'] ? 'disabled' : '' ?>>Xoá</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<p>Trang <?= e($page) ?> / <?= e($pages) ?> • <?= e($total) ?> sản phẩm</p>
<div class="pagination">
    <?php for ($j = 1; $j <= $pages; $j++): ?>
    <a href="?<?= e(http_build_query(['q' => $q, 'cat' => $cat, 'page' => $j])) ?>">
        <?= e($j) ?>
    </a>
    <?php endfor; ?>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>