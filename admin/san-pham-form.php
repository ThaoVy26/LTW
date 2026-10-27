<?php
require_once __DIR__ . '/../includes/vy_catalog.php';
require_once __DIR__ . '/../includes/auth.php';
yeu_cau_admin();
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$item = null;
if ($id) {
    $s = db()->prepare('SELECT * FROM SanPham WHERE id=?');
    $s->execute([$id]);
    $item = $s->fetch();
    if (!$item) {
        http_response_code(404);
        exit('Không tìm thấy sản phẩm.');
    }
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_post();
    try {
        $name = trim($_POST['ten_sp'] ?? '');
        $cat = (int)($_POST['id_danh_muc'] ?? 0);
        $price = filter_var($_POST['gia'] ?? null, FILTER_VALIDATE_INT);
        $cost = filter_var($_POST['gia_nhap'] ?? null, FILTER_VALIDATE_INT);
        $stock = filter_var($_POST['so_luong_ton'] ?? null, FILTER_VALIDATE_INT);
        $desc = trim($_POST['mo_ta'] ?? '');
        $status = $_POST['trang_thai'] ?? '';
        if ($name === '' || mb_strlen($name) > 200 || mb_strlen($desc) > 500 || !$cat || $price === false || $cost === false || $stock === false || min($price, $cost, $stock) < 0 || !in_array($status, ['dang_ban', 'ngung_ban'], true)) throw new RuntimeException('Dữ liệu sản phẩm không hợp lệ.');
        $s = db()->prepare('SELECT COUNT(*) FROM DanhMuc WHERE id=?');
        $s->execute([$cat]);
        if (!$s->fetchColumn()) throw new RuntimeException('Danh mục không tồn tại.');
        $image = upload_anh('hinh_anh', $item['hinh_anh'] ?? '');
        if ($id) {
            $s = db()->prepare('UPDATE SanPham SET ten_sp=?,id_danh_muc=?,gia=?,gia_nhap=?,so_luong_ton=?,hinh_anh=?,mo_ta=?,trang_thai=? WHERE id=?');
            $s->execute([$name, $cat, $price, $cost, $stock, $image, $desc, $status, $id]);
        } else {
            $s = db()->prepare('INSERT INTO SanPham(ten_sp,id_danh_muc,gia,gia_nhap,so_luong_ton,hinh_anh,mo_ta,trang_thai) VALUES(?,?,?,?,?,?,?,?)');
            $s->execute([$name, $cat, $price, $cost, $stock, $image, $desc, $status]);
        }
        flash('ok', 'Đã lưu sản phẩm.');
        redirect('san-pham.php');
    } catch (Throwable $ex) {
        $error = $ex instanceof PDOException ? 'Lỗi lưu dữ liệu.' : $ex->getMessage();
    }
}
$cats = db()->query('SELECT id,ten_danh_muc FROM DanhMuc ORDER BY ten_danh_muc')->fetchAll();
$tieuDe = ($id ? 'Sửa sản phẩm' : 'Thêm sản phẩm');
$menu = 'san-pham';
require __DIR__ . '/../includes/admin_header.php'; ?>
<h1>
    <?= $id ? 'Sửa' : 'Thêm' ?> sản phẩm</h1>
    <p>
        <?= e($error) ?>
    </p>
    <form method="post" enctype="multipart/form-data" class="editor">
        <?= csrf_field() ?>

        <input type="hidden" name="id" value="<?= e($id) ?>">
        <label>Tên sản phẩm
            <input name="ten_sp" maxlength="200" required value="<?= e($_POST['ten_sp'] ?? $item['ten_sp'] ?? '') ?>">
        </label>
        <label>Danh mục
            <select name="id_danh_muc" required>
                <?php foreach ($cats as $c): ?>
                <option value="<?= e($c['id']) ?>" <?= (int)($_POST['id_danh_muc'] ?? $item['id_danh_muc'] ?? 0) == (int) $c['id'] ? 'selected' : '' ?>>
                    <?= e($c['ten_danh_muc']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </label>
        <?php foreach (['gia' => 'Giá bán', 'gia_nhap' => 'Giá nhập', 'so_luong_ton' => 'Số lượng tồn'] as $k => $v): ?>
        <label>
            <?= e($v) ?>

            <input type="number" min="0" step="1" name="<?= e($k) ?>" required value="<?= e($_POST[$k] ?? $item[$k] ?? 0) ?>">
        </label>
        <?php endforeach; ?>
        <label>Mô tả
            <textarea name="mo_ta" maxlength="500">
                <?= e($_POST['mo_ta'] ?? $item['mo_ta'] ?? '') ?>
            </textarea>
        </label>
        <label>Trạng thái
            <select name="trang_thai">
                <option value="dang_ban">Đang bán</option>
                <option value="ngung_ban" <?= ($_POST['trang_thai'] ?? $item['trang_thai'] ?? '') === 'ngung_ban' ? 'selected' : '' ?>>Ngừng bán</option>
            </select>
        </label>
        <label>Ảnh JPG/PNG/WEBP, tối đa 3MB
            <input type="file" name="hinh_anh" accept="image/jpeg,image/png,image/webp">
        </label>
        <?php if (!empty($item['hinh_anh'])): ?>
        <img class="thumb" src="../<?= e($item['hinh_anh']) ?>" alt="Ảnh hiện tại">
        <?php endif; ?>
        <button>Lưu sản phẩm</button>
    </form>
    <?php require __DIR__ . '/../includes/admin_footer.php'; ?>