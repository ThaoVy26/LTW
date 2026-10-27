<?php
require_once __DIR__ . '/../includes/vy_catalog.php';
require_once __DIR__ . '/../includes/auth.php';
yeu_cau_admin();
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
    redirect('charm.php');
}
$rows = db()->query('SELECT c.*,b.ten_bo_suu_tap FROM Charm c JOIN BoSuuTapCharm b ON b.id=c.id_bo_suu_tap ORDER BY c.id DESC')->fetchAll();
$tieuDe = 'Charm';
$menu = 'charm';
require __DIR__ . '/../includes/admin_header.php'; ?>
<h1>Quản lý charm</h1>
<p>Chỉnh sửa tên, giá, ảnh và trạng thái hiển thị.</p>
<div class="cards">
    <?php foreach ($rows as $r): ?>
    <form class="card" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <input type="hidden" name="id" value="<?= e($r['id']) ?>">
        <h3>#<?= e($r['id']) ?> • <?= e($r['ten_bo_suu_tap']) ?>
        </h3>
        <?php if ($r['hinh_anh']): ?>
        <img class="thumb" src="../<?= e($r['hinh_anh']) ?>" alt="">
        <?php endif; ?>
        <label>Tên
            <input name="ten_charm" maxlength="150" required value="<?= e($r['ten_charm']) ?>">
        </label>
        <label>Giá
            <input name="gia" type="number" min="0" step="1" required value="<?= e($r['gia']) ?>">
        </label>
        <label>Ảnh
            <input type="file" name="hinh_anh" accept="image/jpeg,image/png,image/webp">
        </label>
        <label>Hiển thị
            <select name="trang_thai">
                <option value="hien" <?= $r['trang_thai'] === 'hien' ? 'selected' : '' ?>>Hiện</option>
                <option value="an" <?= $r['trang_thai'] === 'an' ? 'selected' : '' ?>>Ẩn</option>
            </select>
        </label>
        <button>Lưu charm</button>
    </form>
    <?php endforeach; ?>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>