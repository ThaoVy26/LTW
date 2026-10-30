<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/don_hang.php';
require_once __DIR__ . '/../includes/admin_ui.php';
// phải đăng nhập admin mới vào được
yeu_cau_admin();

const KH_MOI_TRANG = 10;
const KH_SAP_XEP = [
    'moi' => ['Mua gần đây nhất', 'lan_cuoi DESC'],
    'chi' => ['Chi tiêu nhiều nhất', 'tong_tien DESC, so_don DESC'],
    'don' => ['Nhiều đơn nhất', 'so_don DESC, tong_tien DESC'],
];

$pdo = db();

$sdt = (string)($_GET['sdt'] ?? '');
if ($sdt !== '') {
    if (!preg_match('/^0\d{9}$/', $sdt)) {
        flash('loi', 'Số điện thoại không hợp lệ.');
        chuyen_trang('khach-hang.php');
    }
    $st = $pdo->prepare(
        'SELECT d.id, d.ho_ten_nhan, d.dia_chi_giao, d.phuong_thuc_tt, d.tong_tien, d.trang_thai_don, d.ngay_dat,
                (SELECT COUNT(*) FROM ChiTietDonHang c WHERE c.id_don_hang = d.id) AS so_dong
         FROM DonHang d WHERE d.sdt_nhan = ? ORDER BY d.ngay_dat DESC, d.id DESC'
    );
    $st->execute([$sdt]);
    $dsDon = $st->fetchAll();
    if (!$dsDon) {
        flash('loi', 'Không tìm thấy khách hàng có số điện thoại ' . $sdt . '.');
        chuyen_trang('khach-hang.php');
    }

    $soDon = 0; $tongTien = 0; $soHuy = 0; $dsTen = [];
    foreach ($dsDon as $d) {
        if ($d['trang_thai_don'] === 'huy') { $soHuy++; continue; }
        $soDon++;
        $tongTien += (int)$d['tong_tien'];
    }
    foreach ($dsDon as $d) { $dsTen[$d['ho_ten_nhan']] = true; }
    $dsTen = array_keys($dsTen);

    $st = $pdo->prepare('SELECT DISTINCT email FROM KhachHang WHERE so_dien_thoai = ? AND email IS NOT NULL AND email <> "" ORDER BY email');
    $st->execute([$sdt]);
    $emails = $st->fetchAll(PDO::FETCH_COLUMN);

    $tieuDe = 'Khách hàng ' . $dsDon[0]['ho_ten_nhan'];
    $menu   = 'khach-hang';
    require __DIR__ . '/../includes/admin_header.php';
    ?>
<link rel="stylesheet" href="../<?= phien_ban('assets/css/admin-don-hang.css') ?>">
<link rel="stylesheet" href="../<?= phien_ban('assets/css/admin-khach-hang.css') ?>">
<div class="dh">
  <a class="dh-muted" href="khach-hang.php">← Danh sách khách hàng</a>

  <section class="ad-box kh-profile">
    <div>
      <h2 class="kh-name"><?= e($dsDon[0]['ho_ten_nhan']) ?></h2>
      <dl class="dh-info">
        <dt>Điện thoại</dt><dd><?= e($sdt) ?></dd>
        <?php if ($emails): ?><dt>Email</dt><dd><?= e(implode(', ', $emails)) ?></dd><?php endif; ?>
        <dt>Địa chỉ gần nhất</dt><dd><?= e($dsDon[0]['dia_chi_giao']) ?></dd>
        <?php if (count($dsTen) > 1): ?><dt>Từng dùng tên</dt><dd><?= e(implode(', ', $dsTen)) ?></dd><?php endif; ?>
        <dt>Khách từ</dt><dd><?= e(date('d/m/Y', strtotime((string)end($dsDon)['ngay_dat']))) ?></dd>
      </dl>
    </div>
  </section>

  <div class="ad-cards kh-cards">
    <div class="ad-card"><div class="ad-card__label">Số đơn</div><div class="ad-card__value"><?= $soDon ?></div><div class="ad-card__note">Không tính đơn đã huỷ</div></div>
    <div class="ad-card"><div class="ad-card__label">Tổng đã mua</div><div class="ad-card__value ad-card__value--pink"><?= tien($tongTien) ?></div></div>
    <div class="ad-card"><div class="ad-card__label">Giá trị trung bình / đơn</div><div class="ad-card__value"><?= tien($soDon > 0 ? (int)round($tongTien / $soDon) : 0) ?></div></div>
    <div class="ad-card"><div class="ad-card__label">Đơn đã huỷ</div><div class="ad-card__value"><?= $soHuy ?></div></div>
  </div>

  <section class="ad-box">
    <div class="ad-box__head"><h2 class="ad-box__title">Lịch sử đơn hàng (<?= count($dsDon) ?>)</h2></div>
    <div class="ad-table-wrap">
      <table class="ad-table">
        <thead><tr><th>Mã đơn</th><th>Ngày đặt</th><th>Người nhận</th><th class="num">Số dòng</th><th class="num">Tổng tiền</th><th>Thanh toán</th><th>Trạng thái</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($dsDon as $d): ?>
          <tr>
            <td class="ma"><a href="don-hang-chi-tiet.php?id=<?= (int)$d['id'] ?>"><?= e(don_ma((int)$d['id'])) ?></a></td>
            <td><?= e(date('d/m/Y H:i', strtotime((string)$d['ngay_dat']))) ?></td>
            <td><?= e($d['ho_ten_nhan']) ?></td>
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
  </section>
</div>
<?php
    require __DIR__ . '/../includes/admin_footer.php';
    exit;
}

$q     = mb_substr(trim((string)($_GET['q'] ?? '')), 0, 50);
$sx    = (string)($_GET['sx'] ?? 'moi');
if (!isset(KH_SAP_XEP[$sx])) { $sx = 'moi'; }
$trang = max(1, (int)($_GET['trang'] ?? 1));

$where = '';
$tham  = [];
if ($q !== '') {
    $where  = 'WHERE d.sdt_nhan IN (SELECT x.sdt_nhan FROM DonHang x WHERE x.ho_ten_nhan LIKE ? OR x.sdt_nhan LIKE ?)';
    $tham[] = '%' . $q . '%';
    $tham[] = '%' . $q . '%';
}

$st = $pdo->prepare("SELECT COUNT(DISTINCT d.sdt_nhan) FROM DonHang d $where");
$st->execute($tham);
$tong    = (int)$st->fetchColumn();
$soTrang = max(1, (int)ceil($tong / KH_MOI_TRANG));
$trang   = min($trang, $soTrang);

$st = $pdo->prepare(
    "SELECT d.sdt_nhan,
            COUNT(*) AS so_don_tat_ca,
            COALESCE(SUM(d.trang_thai_don <> 'huy'), 0) AS so_don,
            COALESCE(SUM(CASE WHEN d.trang_thai_don <> 'huy' THEN d.tong_tien END), 0) AS tong_tien,
            MAX(d.ngay_dat) AS lan_cuoi,
            (SELECT d2.ho_ten_nhan FROM DonHang d2 WHERE d2.sdt_nhan = d.sdt_nhan ORDER BY d2.ngay_dat DESC, d2.id DESC LIMIT 1) AS ho_ten
     FROM DonHang d $where
     GROUP BY d.sdt_nhan
     ORDER BY " . KH_SAP_XEP[$sx][1] . "
     LIMIT ? OFFSET ?"
);
foreach ($tham as $i => $v) { $st->bindValue($i + 1, $v, PDO::PARAM_STR); }
$st->bindValue(count($tham) + 1, KH_MOI_TRANG, PDO::PARAM_INT);
$st->bindValue(count($tham) + 2, ($trang - 1) * KH_MOI_TRANG, PDO::PARAM_INT);
$st->execute();
$dsKhach = $st->fetchAll();

$tomTat = $pdo->query(
    "SELECT COUNT(*) AS khach, COALESCE(SUM(n >= 2), 0) AS quen, COALESCE(SUM(tien), 0) AS doanh_thu
     FROM (SELECT sdt_nhan,
                  SUM(trang_thai_don <> 'huy') AS n,
                  COALESCE(SUM(CASE WHEN trang_thai_don <> 'huy' THEN tong_tien END), 0) AS tien
           FROM DonHang GROUP BY sdt_nhan) t"
)->fetch();

// tạo link giữ nguyên bộ lọc khách hàng
function kh_url(array $doi = []): string
{
    global $q, $sx, $trang;
    $p = array_merge(['q' => $q, 'sx' => $sx, 'trang' => $trang], $doi);
    $p = array_filter($p, fn($v) => $v !== '' && $v !== null && $v !== 1 && $v !== 'moi');
    return 'khach-hang.php' . ($p ? '?' . http_build_query($p) : '');
}

$tieuDe = 'Khách hàng';
$menu   = 'khach-hang';
require __DIR__ . '/../includes/admin_header.php';
?>
<link rel="stylesheet" href="../<?= phien_ban('assets/css/admin-don-hang.css') ?>">
<link rel="stylesheet" href="../<?= phien_ban('assets/css/admin-khach-hang.css') ?>">
<div class="dh">
  <div class="ad-cards kh-cards">
    <div class="ad-card"><div class="ad-card__label">Tổng số khách</div><div class="ad-card__value"><?= (int)$tomTat['khach'] ?></div><div class="ad-card__note">Tính theo số điện thoại</div></div>
    <div class="ad-card"><div class="ad-card__label">Khách quen (từ 2 đơn)</div><div class="ad-card__value"><?= (int)$tomTat['quen'] ?></div></div>
    <div class="ad-card"><div class="ad-card__label">Tổng đã mua</div><div class="ad-card__value ad-card__value--pink"><?= tien($tomTat['doanh_thu']) ?></div><div class="ad-card__note">Không tính đơn đã huỷ</div></div>
  </div>

  <form class="dh-search" method="get" action="khach-hang.php">
    <input type="search" name="q" value="<?= e($q) ?>" placeholder="Tìm theo tên hoặc số điện thoại…" maxlength="50">
    <select name="sx" class="kh-select" aria-label="Sắp xếp">
      <?php foreach (KH_SAP_XEP as $k => [$ten]): ?>
        <option value="<?= e($k) ?>"<?= $sx === $k ? ' selected' : '' ?>><?= e($ten) ?></option>
      <?php endforeach; ?>
    </select>
    <button class="btn btn--primary btn--sm" type="submit">Lọc</button>
    <?php if ($q !== '' || $sx !== 'moi'): ?><a class="btn btn--light btn--sm" href="khach-hang.php">Xoá lọc</a><?php endif; ?>
  </form>
  <p class="dh__sub"><?= $tong ?> khách<?= $q !== '' ? ' khớp "' . e($q) . '"' : '' ?></p>

  <section class="ad-box">
    <?php if (!$dsKhach): ?>
      <div class="ad-empty"><?= $q !== '' ? 'Không có khách nào khớp tìm kiếm.' : 'Chưa có khách hàng nào. Khách xuất hiện sau khi đặt đơn đầu tiên.' ?></div>
    <?php else: ?>
    <div class="ad-table-wrap">
      <table class="ad-table">
        <thead><tr><th>Khách hàng</th><th>Điện thoại</th><th class="num">Số đơn</th><th class="num">Tổng đã mua</th><th>Mua gần nhất</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($dsKhach as $k): ?>
          <tr>
            <td><a class="kh-link" href="khach-hang.php?sdt=<?= e($k['sdt_nhan']) ?>"><?= e($k['ho_ten']) ?></a>
              <?php if ((int)$k['so_don'] >= 2): ?><span class="badge badge--purple">Khách quen</span><?php endif; ?></td>
            <td><?= e($k['sdt_nhan']) ?></td>
            <td class="num"><?= (int)$k['so_don'] ?><?php if ((int)$k['so_don_tat_ca'] > (int)$k['so_don']): ?><div class="dh-muted"><?= (int)$k['so_don_tat_ca'] - (int)$k['so_don'] ?> đơn huỷ</div><?php endif; ?></td>
            <td class="num"><b><?= tien($k['tong_tien']) ?></b></td>
            <td><?= e(date('d/m/Y H:i', strtotime((string)$k['lan_cuoi']))) ?></td>
            <td class="num"><a class="btn btn--light btn--sm" href="khach-hang.php?sdt=<?= e($k['sdt_nhan']) ?>">Lịch sử</a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?= ad_phan_trang($trang, $soTrang, fn(int $p): string => kh_url(['trang' => $p])) ?>
    <?php endif; ?>
  </section>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
