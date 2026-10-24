<?php
require_once __DIR__ . '/../includes/vy_catalog.php';
require_once __DIR__ . '/../includes/auth.php';
yeu_cau_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_post();
    $act = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    try {
        if ($act === 'save') {
            $name = trim($_POST['ten_danh_muc'] ?? '');
            $desc = trim($_POST['mo_ta'] ?? '');
            if ($name === '' || mb_strlen($name) > 100) throw new RuntimeException('Tên danh mục không hợp lệ.');
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
require __DIR__ . '/../includes/admin_header.php'; ?>
<h1>Danh mục</h1>
<section>
    <h2>
        <?= $edit ? 'Sửa' : 'Thêm' ?> danh mục</h2>
        <form method="post">
            <?= csrf_field() ?>

            <input type="hidden" name="action" value="save">

            <input type="hidden" name="id" value="<?= e($edit['id'] ?? 0) ?>">
            <label>Tên danh mục
                <input name="ten_danh_muc" maxlength="100" value="<?= e($edit['ten_danh_muc'] ?? '') ?>" required>
            </label>
            <label>Mô tả
                <input name="mo_ta" maxlength="255" value="<?= e($edit['mo_ta'] ?? '') ?>">
            </label>
            <button>Lưu</button>
        </form>
    </section>
    <section>
        <h2>Danh sách</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Tên</th>
                <th>Mô tả</th>
                <th>Số sản phẩm</th>
                <th>Thao tác</th>
            </tr>
            <?php foreach ($rows as $r): ?>
            <tr>
                <td>
                    <?= e($r['id']) ?>
                </td>
                <td>
                    <?= e($r['ten_danh_muc']) ?>
                </td>
                <td>
                    <?= e($r['mo_ta']) ?>
                </td>
                <td>
                    <?= e($r['so_sp']) ?>
                </td>
                <td>
                    <a href="?edit=<?= e($r['id']) ?>">Sửa</a>
                    <form class="inline" method="post" onsubmit="return confirm('Xoá danh mục?')">
                        <?= csrf_field() ?>

                        <input type="hidden" name="action" value="delete">

                        <input type="hidden" name="id" value="<?= e($r['id']) ?>">
                        <button class="danger" <?= $r['so_sp'] ? 'disabled' : '' ?>>Xoá</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </section>
    <?php require __DIR__ . '/../includes/admin_footer.php'; ?>