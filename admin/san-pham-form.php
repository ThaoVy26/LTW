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
<link rel="stylesheet" href="../<?= phien_ban('assets/css/admin-san-pham.css') ?>">
<div class="sp-admin">
<section class="ad-box">
  <?php if ($error !== ''): ?><div class="flash flash--loi" role="alert"><?= e($error) ?></div><?php endif; ?>
  <form method="post" enctype="multipart/form-data" class="sp-editor">
    <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$id ?>">
    <div class="sp-grid">
      <div class="ad-field"><label for="ten_sp">Tên sản phẩm</label><input id="ten_sp" name="ten_sp" maxlength="200" required value="<?= e($_POST['ten_sp'] ?? $item['ten_sp'] ?? '') ?>"></div>
      <div class="ad-field"><label for="id_danh_muc">Danh mục</label><select id="id_danh_muc" name="id_danh_muc" required>
        <option value="">Chọn danh mục</option>
        <?php foreach ($cats as $c): ?><option value="<?= (int)$c['id'] ?>" <?= (int)($_POST['id_danh_muc'] ?? $item['id_danh_muc'] ?? 0) === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['ten_danh_muc']) ?></option><?php endforeach; ?>
      </select></div>
      <?php foreach (['gia' => 'Giá bán (đ)', 'gia_nhap' => 'Giá nhập (đ)', 'so_luong_ton' => 'Số lượng tồn'] as $k => $v): ?>
        <div class="ad-field"><label for="<?= e($k) ?>"><?= e($v) ?></label><input id="<?= e($k) ?>" type="number" min="0" step="1" max="<?= $k === 'so_luong_ton' ? '2147483647' : '999999999999' ?>" name="<?= e($k) ?>" required value="<?= e($_POST[$k] ?? $item[$k] ?? 0) ?>"></div>
      <?php endforeach; ?>
      <div class="ad-field"><label for="trang_thai">Trạng thái</label><select id="trang_thai" name="trang_thai">
        <option value="dang_ban">Đang bán</option><option value="ngung_ban" <?= ($_POST['trang_thai'] ?? $item['trang_thai'] ?? '') === 'ngung_ban' ? 'selected' : '' ?>>Ngừng bán</option>
      </select></div>
      <div class="ad-field sp-full"><label for="mo_ta">Mô tả</label><textarea id="mo_ta" name="mo_ta" maxlength="500" rows="4"><?= e($_POST['mo_ta'] ?? $item['mo_ta'] ?? '') ?></textarea></div>
      <div class="ad-field"><label for="hinh_anh">Ảnh sản phẩm</label><input id="hinh_anh" type="file" name="hinh_anh" accept="image/jpeg,image/png,image/webp"><p class="ad-field__hint">JPG, PNG hoặc WEBP, tối đa 3MB. Để trống để giữ ảnh hiện tại.</p></div>
      <div class="sp-preview"><p class="sp-muted">Ảnh xem trước</p><img id="sp-preview" <?= !empty($item['hinh_anh']) ? 'src="../' . e($item['hinh_anh']) . '"' : 'hidden' ?> alt="Ảnh sản phẩm xem trước"><span id="sp-preview-empty" <?= !empty($item['hinh_anh']) ? 'hidden' : '' ?>>Chưa chọn ảnh</span><p id="sp-preview-error" role="alert"></p></div>
    </div>
    <div class="sp-actions sp-form-actions"><button class="btn btn--primary">Lưu sản phẩm</button><a class="btn btn--light" href="san-pham.php">Hủy</a></div>
  </form>
</section>
</div>
<script>
(() => {
  const input = document.getElementById('hinh_anh');
  const preview = document.getElementById('sp-preview');
  const empty = document.getElementById('sp-preview-empty');
  const error = document.getElementById('sp-preview-error');
  const original = preview.getAttribute('src');
  let objectUrl;
  input.addEventListener('change', () => {
    if (objectUrl) URL.revokeObjectURL(objectUrl);
    const file = input.files[0];
    const valid = !file || (['image/jpeg','image/png','image/webp'].includes(file.type) && file.size <= 3 * 1024 * 1024);
    input.setCustomValidity(valid ? '' : 'Chọn ảnh JPG, PNG, WEBP tối đa 3MB.');
    error.textContent = input.validationMessage;
    const src = file && valid ? (objectUrl = URL.createObjectURL(file)) : original;
    if (src) preview.src = src; else preview.removeAttribute('src');
    preview.hidden = !src;
    empty.hidden = !!src;
  });
})();
</script>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
