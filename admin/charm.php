<?php
require_once __DIR__ . '/../includes/vy_catalog.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/admin_ui.php';
yeu_cau_admin();
$bst = max(0, (int)($_GET['bst'] ?? 0));
$page = max(1, (int)($_GET['page'] ?? 1));
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_post();
    try {
        $id = (int)($_POST['id'] ?? 0);
        $s = db()->prepare('SELECT * FROM Charm WHERE id=?');
        $s->execute([$id]);
        $old = $s->fetch();
        if (!$old) throw new RuntimeException('Không tìm thấy charm.');
        $name = trim($_POST['ten_charm'] ?? '');
        $price = filter_var($_POST['gia'] ?? null, FILTER_VALIDATE_INT);
        $status = $_POST['trang_thai'] ?? '';
        if ($name === '' || mb_strlen($name) > 150 || $price === false || $price < 0 || !in_array($status, ['hien', 'an'], true)) throw new RuntimeException('Dữ liệu charm không hợp lệ.');
        $image = upload_anh('hinh_anh', $old['hinh_anh'] ?? '');
        $s = db()->prepare('UPDATE Charm SET ten_charm=?,gia=?,hinh_anh=?,trang_thai=? WHERE id=?');
        $s->execute([$name, $price, $image, $status, $id]);
        flash('ok', 'Đã cập nhật charm.');
    } catch (Throwable $ex) {
        flash('loi', $ex instanceof PDOException ? 'Không thể lưu charm.' : $ex->getMessage());
    }
    redirect('charm.php?' . http_build_query(['bst' => $bst, 'page' => $page]));
}
$limit = 20;
$where = $bst ? ' WHERE c.id_bo_suu_tap = ?' : '';
$params = $bst ? [$bst] : [];
$s = db()->prepare('SELECT COUNT(*) FROM Charm c' . $where);
$s->execute($params);
$total = (int)$s->fetchColumn();
$pages = max(1, (int)ceil($total / $limit));
$page = min($page, $pages);
$s = db()->prepare('SELECT c.*, b.ten_bo_suu_tap FROM Charm c JOIN BoSuuTapCharm b ON b.id=c.id_bo_suu_tap' . $where . ' ORDER BY c.id DESC LIMIT ? OFFSET ?');
$i = 1;
foreach ($params as $v) $s->bindValue($i++, $v, PDO::PARAM_INT);
$s->bindValue($i++, $limit, PDO::PARAM_INT);
$s->bindValue($i, ($page - 1) * $limit, PDO::PARAM_INT);
$s->execute();
$rows = $s->fetchAll();
$collections = db()->query('SELECT id, ten_bo_suu_tap FROM BoSuuTapCharm ORDER BY ten_bo_suu_tap')->fetchAll();
$tieuDe = 'Charm';
$menu = 'charm';
require __DIR__ . '/../includes/admin_header.php'; ?>
<link rel="stylesheet" href="../assets/css/admin-san-pham.css">
<div class="sp-admin">
<section class="ad-box">
  <form method="get" class="sp-filters">
    <div class="ad-field"><label for="bst">Bộ sưu tập</label><select id="bst" name="bst"><option value="0">Tất cả bộ sưu tập</option>
      <?php foreach ($collections as $b): ?><option value="<?= (int)$b['id'] ?>" <?= $bst === (int)$b['id'] ? 'selected' : '' ?>><?= e($b['ten_bo_suu_tap']) ?></option><?php endforeach; ?>
    </select></div>
    <button class="btn btn--primary btn--sm">Lọc charm</button>
    <?php if ($bst): ?><a class="btn btn--light btn--sm" href="charm.php">Xóa bộ lọc</a><?php endif; ?>
  </form>
  <p class="sp-muted"><?= $total ?> charm · Trang <?= $page ?> / <?= $pages ?> · Tối đa 20 dòng mỗi trang. Lưu riêng từng dòng sau khi sửa.</p>
  <div class="ad-table-wrap"><table class="ad-table sp-table sp-charm-table">
    <thead><tr><th>Ảnh</th><th>Tên charm</th><th>Bộ sưu tập</th><th class="num">Giá (đ)</th><th>Hiển thị</th><th>Đổi ảnh</th><th>Thao tác</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): $formId = 'charm-' . (int)$r['id']; ?>
      <tr>
        <td><?php if ($r['hinh_anh']): ?><img class="sp-thumb sp-thumb--charm" src="../<?= e($r['hinh_anh']) ?>" alt="<?= e($r['ten_charm']) ?>" loading="lazy"><?php else: ?><span class="sp-no-image">Chưa có ảnh</span><?php endif; ?></td>
        <td><input class="sp-inline-name" form="<?= $formId ?>" name="ten_charm" aria-label="Tên charm #<?= (int)$r['id'] ?>" maxlength="150" required value="<?= e($r['ten_charm']) ?>"></td>
        <td><?= e($r['ten_bo_suu_tap']) ?></td>
        <td><input class="sp-inline-price" form="<?= $formId ?>" name="gia" aria-label="Giá charm #<?= (int)$r['id'] ?>" type="number" min="0" max="999999999999" step="1" required value="<?= e($r['gia']) ?>"></td>
        <td><select form="<?= $formId ?>" name="trang_thai" aria-label="Trạng thái charm #<?= (int)$r['id'] ?>"><option value="hien" <?= $r['trang_thai'] === 'hien' ? 'selected' : '' ?>>Hiện</option><option value="an" <?= $r['trang_thai'] === 'an' ? 'selected' : '' ?>>Ẩn</option></select></td>
        <td><input class="sp-inline-file" form="<?= $formId ?>" type="file" name="hinh_anh" aria-label="Đổi ảnh charm #<?= (int)$r['id'] ?>" accept="image/jpeg,image/png,image/webp"><span class="ad-field__hint">JPG/PNG/WEBP, tối đa 3MB</span></td>
        <td><form id="<?= $formId ?>" method="post" enctype="multipart/form-data" action="?<?= e(http_build_query(['bst' => $bst, 'page' => $page])) ?>"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><button class="btn btn--primary btn--sm">Lưu</button></form></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="7" class="ad-empty">Không có charm trong bộ sưu tập này.</td></tr><?php endif; ?>
    </tbody>
  </table></div>
  <?= ad_phan_trang($page, $pages, fn(int $p): string => '?' . http_build_query(['bst' => $bst, 'page' => $p])) ?>
</section>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
