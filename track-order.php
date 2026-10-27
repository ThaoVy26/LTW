<?php
/**
 * Khách tra cứu đơn hàng bằng SĐT + mã đơn, không cần tài khoản. (Mỹ Anh, B7)
 * - Phải khớp CẢ HAI điều kiện; không khớp thì chỉ báo một câu chung để không lộ mã đơn nào tồn tại.
 * - Sai liên tiếp 5 lần thì tạm khoá 5 phút (đếm trong SESSION, giống đăng nhập admin).
 */
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/don_hang.php';

const TRA_CUU_TOI_DA  = 5;
const TRA_CUU_KHOA_GIAY = 300;

$sdt   = '';
$maDon = '';
$loi   = '';
$don   = null;
$dong  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sdt   = trim((string)($_POST['sdt'] ?? ''));
    $maDon = trim((string)($_POST['ma_don'] ?? ''));
    $tc    = $_SESSION['tra_cuu'] ?? ['sai' => 0, 'khoa_den' => 0];

    if (!csrf_hop_le($_POST['csrf'] ?? null)) {
        $loi = 'Phiên làm việc đã hết hạn, vui lòng thử lại.';
    } elseif ($tc['khoa_den'] > time()) {
        $loi = 'Bạn đã tra cứu sai quá nhiều lần. Vui lòng thử lại sau ' . (int)ceil(($tc['khoa_den'] - time()) / 60) . ' phút.';
    } elseif (!preg_match('/^0\d{9}$/', $sdt)) {
        $loi = 'Số điện thoại gồm 10 số, bắt đầu bằng 0.';
    } elseif (!preg_match('/^#?(?:CA)?0*(\d{1,9})$/i', $maDon, $m) || (int)$m[1] < 1) {
        // Chấp nhận "12", "#12", "CA000012", "#CA000012"
        $loi = 'Mã đơn không hợp lệ. Ví dụ: #CA000012.';
    } else {
        $st = db()->prepare('SELECT * FROM DonHang WHERE id = ? AND sdt_nhan = ?');
        $st->execute([(int)$m[1], $sdt]);
        $don = $st->fetch() ?: null;

        if ($don) {
            unset($_SESSION['tra_cuu']);
            $dong = don_lay_dong_hang((int)$don['id']);
        } else {
            $tc['sai']++;
            if ($tc['sai'] >= TRA_CUU_TOI_DA) {
                $tc = ['sai' => 0, 'khoa_den' => time() + TRA_CUU_KHOA_GIAY];
            }
            $_SESSION['tra_cuu'] = $tc;
            $loi = 'Không tìm thấy đơn hàng.';   // cố ý dùng chung một câu
        }
    }
}

// Các bước hiển thị cho đơn bình thường
$cacBuoc = ['cho_xac_nhan' => 'Đã đặt hàng', 'dang_giao' => 'Đang giao', 'hoan_tat' => 'Hoàn tất'];
$viTri   = $don ? array_search($don['trang_thai_don'], array_keys($cacBuoc), true) : false;

$tieuDe = 'Tra cứu đơn hàng';
$menu   = 'tra-cuu';
require __DIR__ . '/includes/header.php';
?>
<style>
.track-form { padding: 32px; margin-bottom: 24px; }
.track-steps { display: flex; justify-content: space-between; list-style: none; margin: 8px 0 4px; padding: 0; position: relative; }
.track-steps::before { content: ""; position: absolute; left: 12%; right: 12%; top: 15px; height: 3px; background: var(--line); }
.track-steps li { flex: 1; text-align: center; font-size: 13px; color: var(--muted); position: relative; }
.track-steps li span { display: grid; place-items: center; width: 32px; height: 32px; margin: 0 auto 6px; border-radius: 50%; background: #fff; border: 2px solid var(--line); font-weight: 700; position: relative; z-index: 1; }
.track-steps li.is-done { color: var(--ink); font-weight: 600; }
.track-steps li.is-done span { background: var(--pink); border-color: var(--pink); color: #fff; }
.track-cancel { background: var(--pink-50); border: 1px solid var(--pink-200); color: var(--pink-dark); border-radius: 12px; padding: 12px 16px; text-align: center; font-weight: 600; }
.track-result { padding: 32px; }
.track-note { margin-top: 14px; font-size: 14px; }
@media (max-width: 520px) { .track-form, .track-result { padding: 20px; } .track-steps li { font-size: 12px; } }
</style>
<main class="page">
  <div class="container container--narrow">
    <h1 class="page-title page-title--lg" style="margin-bottom:8px">Tra cứu đơn hàng</h1>
    <p class="muted" style="margin-bottom:24px">Nhập số điện thoại đặt hàng và mã đơn (có trong trang đặt hàng thành công) để xem trạng thái đơn. Không cần tài khoản.</p>

    <form method="post" class="card track-form" novalidate>
      <?= csrf_field() ?>
      <div class="row2">
        <label class="field">
          <span>Số điện thoại *</span>
          <input name="sdt" value="<?= e($sdt) ?>" inputmode="tel" required pattern="0\d{9}" maxlength="10" autocomplete="tel" placeholder="0901234567">
        </label>
        <label class="field">
          <span>Mã đơn *</span>
          <input name="ma_don" value="<?= e($maDon) ?>" required maxlength="12" placeholder="#CA000012">
        </label>
      </div>
      <?php if ($loi !== ''): ?><div class="flash flash--loi" style="margin-bottom:16px" role="alert"><?= e($loi) ?></div><?php endif; ?>
      <button class="btn btn--primary btn--block" type="submit">Tra cứu</button>
    </form>

    <?php if ($don): $tt = $don['trang_thai_don']; ?>
    <div class="card success track-result" style="text-align:left">
      <h2 style="font-size:22px;margin-bottom:4px">Đơn hàng <span class="pink"><?= e(don_ma((int)$don['id'])) ?></span></h2>
      <p class="muted small" style="margin-bottom:20px">Đặt lúc <?= e(date('H:i d/m/Y', strtotime((string)$don['ngay_dat']))) ?></p>

      <?php if ($tt === 'huy'): ?>
        <div class="track-cancel">Đơn hàng đã được huỷ</div>
      <?php else: ?>
        <ol class="track-steps" aria-label="Tiến trình đơn hàng">
          <?php $i = 0; foreach ($cacBuoc as $ma => $ten): ?>
            <li class="<?= $viTri !== false && $i <= $viTri ? 'is-done' : '' ?>"><span><?= $viTri !== false && $i <= $viTri ? '✓' : $i + 1 ?></span><?= e($ten) ?></li>
          <?php $i++; endforeach; ?>
        </ol>
      <?php endif; ?>

      <div class="success__box">
        <div class="summary__row"><span>Trạng thái:</span><span class="badge <?= e(don_lop_badge($tt)) ?>"><?= e(don_nhan_trang_thai($tt)) ?></span></div>
        <div class="summary__row"><span>Người nhận:</span><span><?= e($don['ho_ten_nhan']) ?></span></div>
        <div class="summary__row"><span>Giao tới:</span><span><?= e($don['dia_chi_giao']) ?></span></div>
        <div class="summary__row"><span>Thanh toán:</span><span><?= e(DON_PHUONG_THUC[$don['phuong_thuc_tt']] ?? $don['phuong_thuc_tt']) ?></span></div>
        <hr>
        <?php foreach ($dong as $c): ?>
          <div class="summary__row summary__row--item"><span><?= e($c['ten_hien_thi']) ?> <small>× <?= (int)$c['so_luong'] ?></small></span><span><?= tien($c['thanh_tien']) ?></span></div>
        <?php endforeach; ?>
        <hr>
        <div class="summary__row"><span>Phí vận chuyển:</span><span><?= tien($don['phi_van_chuyen']) ?></span></div>
        <div class="summary__row"><span>Giảm giá:</span><span>-<?= tien($don['giam_gia']) ?></span></div>
        <div class="summary__total"><span>Tổng cộng:</span><span><?= tien($don['tong_tien']) ?></span></div>
      </div>
      <p class="muted track-note">Cần hỗ trợ? Liên hệ Charm Atelier qua email trong phần chân trang, nhớ kèm mã đơn.</p>
    </div>
    <?php endif; ?>
  </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
