<?php
/**
 * Quản lý đơn hàng: danh sách, lọc theo trạng thái, tìm kiếm, phân trang. (Mỹ Anh, B6)
 */
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/don_hang.php';
yeu_cau_admin();

const DON_MOI_TRANG = 10;
$pdo = db();

// --- Đọc bộ lọc từ URL (chỉ nhận giá trị hợp lệ) ---
$tt = (string)($_GET['tt'] ?? '');
if (!isset(DON_TRANG_THAI[$tt])) {
    $tt = '';
}
$q     = mb_substr(trim((string)($_GET['q'] ?? '')), 0, 50);
$trang = max(1, (int)($_GET['trang'] ?? 1));

$where = [];
$tham  = [];   // mỗi phần tử: [giá trị, kiểu PDO]
if ($tt !== '') {
    $where[] = 'd.trang_thai_don = ?';
    $tham[]  = [$tt, PDO::PARAM_STR];
}
if ($q !== '') {
    $dk     = 'd.ho_ten_nhan LIKE ? OR d.sdt_nhan LIKE ?';
    $tham[] = ['%' . $q . '%', PDO::PARAM_STR];
    $tham[] = ['%' . $q . '%', PDO::PARAM_STR];
    // Gõ "#CA000012", "CA12" hoặc "12" thì tìm theo mã đơn
    if (preg_match('/^#?(?:CA)?0*(\d{1,9})$/i', $q, $m)) {
        $dk     .= ' OR d.id = ?';
        $tham[]  = [(int)$m[1], PDO::PARAM_INT];
    }
    $where[] = '(' . $dk . ')';
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// --- Tổng số đơn → số trang ---
$st = $pdo->prepare("SELECT COUNT(*) FROM DonHang d $whereSql");
foreach ($tham as $i => [$v, $kieu]) {
    $st->bindValue($i + 1, $v, $kieu);
}
$st->execute();
$tong    = (int)$st->fetchColumn();
$soTrang = max(1, (int)ceil($tong / DON_MOI_TRANG));
$trang   = min($trang, $soTrang);

// --- Lấy đúng 1 trang (LIMIT/OFFSET phải bind kiểu số nguyên) ---
$st = $pdo->prepare(
    "SELECT d.id, d.ho_ten_nhan, d.sdt_nhan, d.phuong_thuc_tt, d.tong_tien, d.trang_thai_don, d.ngay_dat,
            (SELECT COUNT(*) FROM ChiTietDonHang c WHERE c.id_don_hang = d.id) AS so_dong
     FROM DonHang d $whereSql
     ORDER BY d.ngay_dat DESC, d.id DESC
     LIMIT ? OFFSET ?"
);
foreach ($tham as $i => [$v, $kieu]) {
    $st->bindValue($i + 1, $v, $kieu);
}
$st->bindValue(count($tham) + 1, DON_MOI_TRANG, PDO::PARAM_INT);
$st->bindValue(count($tham) + 2, ($trang - 1) * DON_MOI_TRANG, PDO::PARAM_INT);
$st->execute();
$dsDon = $st->fetchAll();

// --- Số đơn theo từng trạng thái (cho các tab) ---
$demTt = array_fill_keys(array_keys(DON_TRANG_THAI), 0);
foreach ($pdo->query('SELECT trang_thai_don, COUNT(*) AS n FROM DonHang GROUP BY trang_thai_don') as $r) {
    $demTt[$r['trang_thai_don']] = (int)$r['n'];
}
$tatCa = array_sum($demTt);

/** Link giữ nguyên bộ lọc hiện tại, đổi/ghi đè vài tham số. */
function don_url(array $doi = []): string
{
    global $tt, $q, $trang;
    $p = array_merge(['tt' => $tt, 'q' => $q, 'trang' => $trang], $doi);
    $p = array_filter($p, fn($v) => $v !== '' && $v !== null && $v !== 1);
    return 'don-hang.php' . ($p ? '?' . http_build_query($p) : '');
}

$tieuDe = 'Đơn hàng';
$menu   = 'don-hang';
require __DIR__ . '/../includes/admin_header.php';
?>
<link rel="stylesheet" href="../assets/css/admin-don-hang.css">
<div class="dh">
  <p class="dh__sub"><?= $tong ?> đơn<?= ($tt !== '' || $q !== '') ? ' khớp bộ lọc' : '' ?></p>

  <nav class="dh-tabs" aria-label="Lọc theo trạng thái">
    <a class="dh-tab<?= $tt === '' ? ' is-active' : '' ?>" href="<?= e(don_url(['tt' => '', 'trang' => 1])) ?>">Tất cả<small><?= $tatCa ?></small></a>
    <?php foreach (DON_TRANG_THAI as $ma => $ten): ?>
      <a class="dh-tab<?= $tt === $ma ? ' is-active' : '' ?>" href="<?= e(don_url(['tt' => $ma, 'trang' => 1])) ?>"><?= e($ten) ?><small><?= $demTt[$ma] ?></small></a>
    <?php endforeach; ?>
  </nav>

  <form class="dh-search" method="get" action="don-hang.php">
    <?php if ($tt !== ''): ?><input type="hidden" name="tt" value="<?= e($tt) ?>"><?php endif; ?>
    <input type="search" name="q" value="<?= e($q) ?>" placeholder="Tìm theo mã đơn, tên hoặc số điện thoại…" maxlength="50">
    <button class="btn btn--primary btn--sm" type="submit">Tìm</button>
    <?php if ($q !== ''): ?><a class="btn btn--light btn--sm" href="<?= e(don_url(['q' => '', 'trang' => 1])) ?>">Xoá tìm kiếm</a><?php endif; ?>
  </form>

  <section class="ad-box">
    <?php if (!$dsDon): ?>
      <div class="ad-empty">Không có đơn hàng nào<?= ($tt !== '' || $q !== '') ? ' khớp bộ lọc' : '' ?>.</div>
    <?php else: ?>
    <div class="ad-table-wrap">
      <table class="ad-table">
        <thead>
          <tr>
            <th>Mã đơn</th><th>Khách nhận</th><th>Ngày đặt</th><th class="num">Số dòng</th>
            <th class="num">Tổng tiền</th><th>Thanh toán</th><th>Trạng thái</th><th></th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($dsDon as $d): ?>
          <tr>
            <td class="ma"><a href="don-hang-chi-tiet.php?id=<?= (int)$d['id'] ?>"><?= e(don_ma((int)$d['id'])) ?></a></td>
            <td><?= e($d['ho_ten_nhan']) ?><div class="dh-muted"><?= e($d['sdt_nhan']) ?></div></td>
            <td><?= e(date('d/m/Y H:i', strtotime((string)$d['ngay_dat']))) ?></td>
            <td class="num"><?= (int)$d['so_dong'] ?></td>
            <td class="num"><b><?= tien($d['tong_tien']) ?></b></td>
            <td><?= e(DON_PHUONG_THUC[$d['phuong_thuc_tt']] ?? $d['phuong_thuc_tt']) ?></td>
            <td><span class="<?= e(class_trang_thai_don($d['trang_thai_don'])) ?>"><?= e(don_nhan_trang_thai($d['trang_thai_don'])) ?></span></td>
            <td class="num"><a class="btn btn--light btn--sm" href="don-hang-chi-tiet.php?id=<?= (int)$d['id'] ?>">Xem</a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php if ($soTrang > 1): ?>
    <nav class="dh-pagination" aria-label="Phân trang">
      <?php if ($trang > 1): ?><a href="<?= e(don_url(['trang' => $trang - 1])) ?>">‹</a><?php else: ?><span class="is-disabled">‹</span><?php endif; ?>
      <?php for ($i = 1; $i <= $soTrang; $i++): ?>
        <?php if ($i === 1 || $i === $soTrang || abs($i - $trang) <= 2): ?>
          <a class="<?= $i === $trang ? 'is-active' : '' ?>" href="<?= e(don_url(['trang' => $i])) ?>"><?= $i ?></a>
        <?php elseif (abs($i - $trang) === 3): ?><span>…</span>
        <?php endif; ?>
      <?php endfor; ?>
      <?php if ($trang < $soTrang): ?><a href="<?= e(don_url(['trang' => $trang + 1])) ?>">›</a><?php else: ?><span class="is-disabled">›</span><?php endif; ?>
    </nav>
    <?php endif; ?>
    <?php endif; ?>
  </section>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
