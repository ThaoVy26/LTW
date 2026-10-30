<?php
require_once __DIR__ . '/includes/functions.php';

$dong = gio_chi_tiet();
if (!$dong) {
    flash('loi', 'Giỏ hàng đang trống.');
    chuyen_trang('cart.php');
}

// các cách thanh toán
$dsPhuongThuc = [
    'COD'    => ['Thanh toán khi nhận hàng (COD)', 'Trả tiền mặt cho shipper'],
    'BANK'   => ['Chuyển khoản ngân hàng', 'Shop gửi số tài khoản qua email/SĐT'],
    'WALLET' => ['Ví điện tử', 'Momo / ZaloPay'],
];

$du    = ['ho_ten' => '', 'sdt' => '', 'email' => '', 'dia_chi' => '', 'ghi_chu' => '', 'pttt' => 'COD'];
$loi   = [];

// khách bấm đặt hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($du as $k => $_) {
        $du[$k] = trim((string)($_POST[$k] ?? ''));
    }

    // kiểm tra thông tin khách nhập
    if (!csrf_hop_le($_POST['csrf'] ?? null))           $loi['chung']   = 'Phiên làm việc đã hết hạn, vui lòng thử lại.';
    if (mb_strlen($du['ho_ten']) < 2)                    $loi['ho_ten']  = 'Vui lòng nhập họ và tên.';
    if (!preg_match('/^0\d{9}$/', $du['sdt']))           $loi['sdt']     = 'Số điện thoại gồm 10 số, bắt đầu bằng 0.';
    if ($du['email'] !== '' && !filter_var($du['email'], FILTER_VALIDATE_EMAIL)) $loi['email'] = 'Email không hợp lệ.';
    if (mb_strlen($du['dia_chi']) < 5)                   $loi['dia_chi'] = 'Vui lòng nhập địa chỉ nhận hàng.';
    if (!isset($dsPhuongThuc[$du['pttt']]))              $loi['pttt']    = 'Vui lòng chọn phương thức thanh toán.';

    // ảnh khách đính kèm (nếu có), chỉ nhận jpg png webp tối đa 3MB
    $tenAnh = null;
    if (!empty($_FILES['anh']['name']) && $_FILES['anh']['error'] !== UPLOAD_ERR_NO_FILE) {
        $f    = $_FILES['anh'];
        $mime = $f['error'] === UPLOAD_ERR_OK ? mime_content_type($f['tmp_name']) : '';
        $duoi = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime] ?? null;
        if (!$duoi)                       $loi['anh'] = 'Chỉ nhận ảnh JPG, PNG hoặc WEBP.';
        elseif ($f['size'] > 3 * 1024 * 1024) $loi['anh'] = 'Ảnh tối đa 3MB.';
        else                              $tenAnh = 'yc_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3)) . '.' . $duoi;
    }

    // kiểm tra còn đủ hàng không
    foreach ($dong as $d) {
        if ($d['loai'] === 'san_pham' && $d['so_luong'] > $d['ton']) {
            $loi['chung'] = '"' . $d['ten'] . '" chỉ còn ' . $d['ton'] . ' sản phẩm.';
        }
    }

    // không có lỗi thì lưu đơn
    if (!$loi) {
        $tien = gio_tinh_tien($dong);
        $pdo  = db();
        try {
            // lưu đơn trong 1 transaction, lỗi thì huỷ hết
            $pdo->beginTransaction();

            if ($tenAnh) {
                move_uploaded_file($_FILES['anh']['tmp_name'], __DIR__ . '/uploads/' . $tenAnh);
            }
            $ghiChu = $du['ghi_chu'] . ($tenAnh ? ($du['ghi_chu'] ? ' | ' : '') . 'Ảnh: uploads/' . $tenAnh : '');

            // khách cũ (cùng số điện thoại) thì cập nhật, chưa có thì tạo mới
            $tim = $pdo->prepare('SELECT id FROM KhachHang WHERE so_dien_thoai = ? ORDER BY id DESC LIMIT 1');
            $tim->execute([$du['sdt']]);
            $idKh = (int)$tim->fetchColumn();
            if ($idKh) {
                $pdo->prepare('UPDATE KhachHang SET ho_ten = ?, email = COALESCE(?, email), dia_chi = ? WHERE id = ?')
                    ->execute([$du['ho_ten'], $du['email'] ?: null, $du['dia_chi'], $idKh]);
            } else {
                $pdo->prepare('INSERT INTO KhachHang (ho_ten, so_dien_thoai, email, dia_chi) VALUES (?, ?, ?, ?)')
                    ->execute([$du['ho_ten'], $du['sdt'], $du['email'] ?: null, $du['dia_chi']]);
                $idKh = (int)$pdo->lastInsertId();
            }

            // tạo đơn hàng
            $pdo->prepare(
                'INSERT INTO DonHang (id_khach_hang, id_ma_giam_gia, ho_ten_nhan, sdt_nhan, dia_chi_giao,
                     phuong_thuc_tt, ghi_chu, tam_tinh, phi_van_chuyen, giam_gia, tong_tien)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            )->execute([
                $idKh, $tien['ma']['id'] ?? null, $du['ho_ten'], $du['sdt'], $du['dia_chi'],
                $du['pttt'], mb_substr($ghiChu, 0, 500) ?: null,
                $tien['tam'], $tien['ship'], $tien['giam'], $tien['tong'],
            ]);
            $idDon = (int)$pdo->lastInsertId();

            $insCt  = $pdo->prepare(
                'INSERT INTO ChiTietDonHang (id_don_hang, id_san_pham, id_thiet_ke, ten_hien_thi, don_gia, so_luong, thanh_tien)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $truKho = $pdo->prepare('UPDATE SanPham SET so_luong_ton = so_luong_ton - ? WHERE id = ? AND so_luong_ton >= ?');
            $ganKh  = $pdo->prepare('UPDATE ThietKeTuyChinh SET id_khach_hang = ? WHERE id = ?');

            // lưu từng dòng hàng và trừ kho, hết hàng thì báo lỗi
            foreach ($dong as $d) {
                $laSp = $d['loai'] === 'san_pham';
                $insCt->execute([
                    $idDon, $laSp ? $d['id'] : null, $laSp ? null : $d['id'],
                    $d['ten'], $d['don_gia'], $d['so_luong'], $d['don_gia'] * $d['so_luong'],
                ]);
                if ($laSp) {
                    $truKho->execute([$d['so_luong'], $d['id'], $d['so_luong']]);
                    if ($truKho->rowCount() === 0) {
                        throw new RuntimeException('"' . $d['ten'] . '" vừa hết hàng.');
                    }
                } else {
                    $ganKh->execute([$idKh, $d['id']]);
                }
            }

            // xong thì xoá giỏ, chuyển sang trang cảm ơn
            $pdo->commit();
            unset($_SESSION['gio'], $_SESSION['ma_giam_gia']);
            $_SESSION['don_cua_toi'][] = $idDon;
            chuyen_trang('order-success.php?id=' . $idDon);
        // lỗi thì huỷ hết và xoá luôn ảnh đã up
        } catch (Throwable $ex) {
            $pdo->rollBack();
            if ($tenAnh && is_file(__DIR__ . '/uploads/' . $tenAnh)) {
                unlink(__DIR__ . '/uploads/' . $tenAnh);
            }
            $loi['chung'] = $ex instanceof RuntimeException ? $ex->getMessage() : 'Có lỗi khi lưu đơn hàng, vui lòng thử lại.';
        }
    }
}

$tien = gio_tinh_tien($dong);

$tieuDe  = 'Thanh toán';
$menu    = 'gio-hang';
$scripts = ['assets/js/checkout.js'];
require __DIR__ . '/includes/header.php';

// lấy câu lỗi của 1 ô nhập
function o_loi(array $loi, string $k): string
{
    return isset($loi[$k]) ? '<div class="field-error">' . e($loi[$k]) . '</div>' : '';
}
?>
<main class="page">
  <div class="container">
    <h1 class="page-title page-title--xl">Thanh toán</h1>
    <ol class="checkout-steps">
      <li class="is-done">Giỏ hàng</li><li class="is-active">Thông tin &amp; thanh toán</li><li>Hoàn tất</li>
    </ol>

    <form method="post" enctype="multipart/form-data" id="checkout-form" novalidate>
      <?= csrf_field() ?>
      <div class="two-col">
        <section>
          <div class="card form-card">
            <h3 class="summary__title">Thông tin nhận hàng</h3>
            <div class="row2">
              <label class="field<?= isset($loi['ho_ten']) ? ' has-error' : '' ?>">
                <span>Họ và tên *</span>
                <input name="ho_ten" value="<?= e($du['ho_ten']) ?>" required autocomplete="name">
                <?= o_loi($loi, 'ho_ten') ?>
              </label>
              <label class="field<?= isset($loi['sdt']) ? ' has-error' : '' ?>">
                <span>Số điện thoại *</span>
                <input name="sdt" value="<?= e($du['sdt']) ?>" inputmode="tel" required pattern="0\d{9}" autocomplete="tel">
                <?= o_loi($loi, 'sdt') ?>
              </label>
            </div>
            <label class="field<?= isset($loi['email']) ? ' has-error' : '' ?>">
              <span>Email (tuỳ chọn)</span>
              <input name="email" type="email" value="<?= e($du['email']) ?>" autocomplete="email">
              <?= o_loi($loi, 'email') ?>
            </label>
            <label class="field<?= isset($loi['dia_chi']) ? ' has-error' : '' ?>">
              <span>Địa chỉ nhận hàng *</span>
              <input name="dia_chi" value="<?= e($du['dia_chi']) ?>" required autocomplete="street-address">
              <?= o_loi($loi, 'dia_chi') ?>
            </label>
            <label class="field">
              <span>Ghi chú cho shop</span>
              <textarea name="ghi_chu" rows="2" maxlength="400"><?= e($du['ghi_chu']) ?></textarea>
            </label>
          </div>

          <div class="card form-card">
            <h3 class="summary__title">Phương thức thanh toán</h3>
            <div class="pay-options">
              <?php foreach ($dsPhuongThuc as $k => [$ten, $moTa]): ?>
                <label class="pay-option">
                  <input type="radio" name="pttt" value="<?= $k ?>" <?= $du['pttt'] === $k ? 'checked' : '' ?>>
                  <span><b><?= $ten ?></b><small><?= $moTa ?></small></span>
                </label>
              <?php endforeach; ?>
            </div>
            <?= o_loi($loi, 'pttt') ?>
          </div>

          <div class="card form-card">
            <h3 class="summary__title">Ảnh yêu cầu riêng <small class="muted">(không bắt buộc)</small></h3>
            <label class="upload" id="upload">
              <input type="file" name="anh" id="anh" accept="image/png,image/jpeg,image/webp">
              <span id="upload-text">Kéo thả hoặc bấm để tải ảnh (charm khắc / in ảnh theo yêu cầu) · tối đa 3MB</span>
              <img id="upload-preview" alt="" hidden>
            </label>
            <?= o_loi($loi, 'anh') ?>
          </div>
        </section>

        <aside class="card summary">
          <h3 class="summary__title">Đơn hàng (<?= gio_dem() ?> sản phẩm)</h3>
          <?php foreach ($dong as $d): ?>
            <div class="summary__row summary__row--item">
              <span><?= e($d['ten']) ?> <small>× <?= $d['so_luong'] ?></small></span>
              <span><?= tien($d['don_gia'] * $d['so_luong']) ?></span>
            </div>
          <?php endforeach; ?>
          <hr>
          <div class="summary__row"><span>Tạm tính:</span><span><?= tien($tien['tam']) ?></span></div>
          <div class="summary__row"><span>Phí vận chuyển:</span><span><?= tien($tien['ship']) ?></span></div>
          <div class="summary__row"><span>Giảm giá<?= $tien['ma'] ? ' (' . e($tien['ma']['ma_code']) . ')' : '' ?>:</span><span>-<?= tien($tien['giam']) ?></span></div>
          <div class="summary__total"><span>Tổng cộng:</span><span><?= tien($tien['tong']) ?></span></div>
          <?= o_loi($loi, 'chung') ?>
          <button class="btn btn--primary btn--block btn--lg" type="submit">Đặt hàng</button>
          <a class="link-muted center-text" href="cart.php">← Quay lại giỏ hàng</a>
        </aside>
      </div>
    </form>
  </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
