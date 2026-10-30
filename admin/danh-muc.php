<?php
require_once __DIR__ . '/../includes/vy_catalog.php';
require_once __DIR__ . '/../includes/auth.php';
// phải đăng nhập admin mới vào được
yeu_cau_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_post();
    $act = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    try {
        if ($act === 'save') {
            $name = trim($_POST['ten_danh_muc'] ?? '');
            $desc = trim($_POST['mo_ta'] ?? '');
            if ($name === '' || mb_strlen($name) > 100 || mb_strlen($desc) > 255) throw new RuntimeException('Tên hoặc mô tả danh mục không hợp lệ.');
            if ($id) {
                $s = db()->prepare('UPDATE DanhMuc SET ten_danh_muc=?,mo_ta=? WHERE id=?');
                $s->execute([$name, $desc, $id]);
            } else {
                $s = db()->prepare('INSERT INTO DanhMuc(ten_danh_muc,mo_ta) VALUES(?,?)');
                $s->execute([$name, $desc]);
            }
            flash('ok', 'Đã lưu danh mục.');
        }
        elseif ($act === 'delete' && $id) {
            // danh mục còn sản phẩm thì không cho xoá
            $s = db()->prepare('SELECT COUNT(*) FROM SanPham WHERE id_danh_muc=?');
            $s->execute([$id]);
            if ($s->fetchColumn()) throw new RuntimeException('Danh mục đang có sản phẩm, không thể xoá.');
            $s = db()->prepare('DELETE FROM DanhMuc WHERE id=?');
            $s->execute([$id]);
            flash('ok', 'Đã xoá danh mục.');
        }
    } catch (Throwable $ex) {
        flash('loi', $ex instanceof PDOException ? 'Không thể thực hiện thao tác với CSDL.' : $ex->getMessage());
    }
    redirect('danh-muc.php');
}
$edit = null;
if (isset($_GET['edit'])) {
    $s = db()->prepare('SELECT * FROM DanhMuc WHERE id=?');
    $s->execute([(int) $_GET['edit']]);
    $edit = $s->fetch();
}
$rows = db()->query('SELECT d.*, (SELECT COUNT(*) FROM SanPham s WHERE s.id_danh_muc=d.id) so_sp FROM DanhMuc d ORDER BY id DESC')->fetchAll();
$tieuDe = 'Danh mục';
$menu = 'danh-muc';
require __DIR__ . '/../includes/admin_header.php'; ?>
<link rel="stylesheet" href="../<?= phien_ban('assets/css/admin-san-pham.css') ?>">
<div class="sp-admin">
<section class="ad-box">
  <h2 class="ad-box__title"><?= $edit ? 'Sửa danh mục' : 'Thêm danh mục' ?></h2>
  <form method="post">
    <?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
    <div class="sp-grid">
      <div class="ad-field"><label for="ten_danh_muc">Tên danh mục</label><input id="ten_danh_muc" name="ten_danh_muc" maxlength="100" value="<?= e($edit['ten_danh_muc'] ?? '') ?>" required></div>
      <div class="ad-field"><label for="mo_ta">Mô tả</label><input id="mo_ta" name="mo_ta" maxlength="255" value="<?= e($edit['mo_ta'] ?? '') ?>"></div>
    </div>
    <div class="sp-actions"><button class="btn btn--primary btn--sm">Lưu danh mục</button><?php if ($edit): ?><a class="btn btn--light btn--sm" href="danh-muc.php">Hủy</a><?php endif; ?></div>
  </form>
</section>
<section class="ad-box">
  <h2 class="ad-box__title">Danh sách danh mục <span class="sp-muted">(<?= count($rows) ?>)</span></h2>
  <div class="ad-table-wrap"><table class="ad-table sp-table">
    <thead><tr><th>ID</th><th>Tên danh mục</th><th>Mô tả</th><th class="num">Số sản phẩm</th><th>Thao tác</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr><td><?= (int)$r['id'] ?></td><td class="sp-name"><?= e($r['ten_danh_muc']) ?></td><td><?= e($r['mo_ta']) ?></td><td class="num"><?= (int)$r['so_sp'] ?></td>
        <td><div class="sp-actions"><a class="btn btn--light btn--sm" href="?edit=<?= (int)$r['id'] ?>">Sửa</a>
          <form method="post" onsubmit="return confirm('Xóa danh mục này?')"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><button class="btn btn--sm sp-delete" <?= $r['so_sp'] ? 'disabled title="Danh mục đang có sản phẩm"' : '' ?>>Xóa</button></form>
        </div></td></tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="5" class="ad-empty">Chưa có danh mục nào.</td></tr><?php endif; ?>
    </tbody>
  </table></div>
</section>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
