<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/don_hang.php';
// phải đăng nhập admin mới vào được
yeu_cau_admin();

const TK_MOC_NHANH   = ['hom_nay' => 'Hôm nay', 'hom_qua' => 'Hôm qua', '7_ngay' => '7 ngày qua', 'thang' => 'Tháng này'];
const TK_TOI_DA_NGAY = 366;
const TK_NGAY_THEO_NGAY = 62;
const TK_DON_MOI_TRANG = 10;

$pdo     = db();
$homNay  = new DateTimeImmutable('today');
$huy     = 'huy';

// đọc ngày từ url, sai định dạng thì bỏ
function tk_doc_ngay(?string $s): ?DateTimeImmutable
{
    $d = DateTimeImmutable::createFromFormat('!Y-m-d', (string)$s);
    return ($d && $d->format('Y-m-d') === $s) ? $d : null;
}

// làm tròn mốc trên của trục tung cho đẹp
function tk_so_tron(float $max): float
{
    if ($max <= 0) {
        return 1;
    }
    $m = 10 ** floor(log10($max));
    foreach ([1, 2, 2.5, 5, 10] as $f) {
        if ($max <= $f * $m) {
            return $f * $m;
        }
    }
    return 10 * $m;
}

// rút gọn số tiền: 1,2tr, 450k
function tk_rut_gon(float $n): string
{
    if ($n >= 1000000) {
        return rtrim(rtrim(number_format($n / 1000000, 1, ',', ''), '0'), ',') . 'tr';
    }
    if ($n >= 1000) {
        return rtrim(rtrim(number_format($n / 1000, 1, ',', ''), '0'), ',') . 'k';
    }
    return (string)(int)$n;
}

// vẽ biểu đồ cột doanh thu bằng svg
function tk_ve_cot(array $nhan, array $doanhThu, array $soDon): string
{
    $n = count($nhan);
    if ($n === 0 || max($doanhThu) <= 0) {
        return '<p class="ad-empty">Chưa có doanh thu trong khoảng thời gian này.</p>';
    }
    $w = 760; $h = 260; $trai = 56; $phai = 12; $tren = 14; $duoi = 34;
    $rongVe = $w - $trai - $phai;
    $caoVe  = $h - $tren - $duoi;
    $tran   = tk_so_tron((float)max($doanhThu));
    $o      = $rongVe / $n;
    $rongCot = min(46, $o * 0.62);
    $buocNhan = max(1, (int)ceil($n / 12));

    $svg = '<svg class="tk-chart" viewBox="0 0 ' . $w . ' ' . $h . '" role="img" aria-label="Doanh thu theo thời gian">';
    for ($i = 0; $i <= 4; $i++) {
        $y = $tren + $caoVe - $caoVe * $i / 4;
        $svg .= '<line class="tk-grid" x1="' . $trai . '" x2="' . ($w - $phai) . '" y1="' . $y . '" y2="' . $y . '"/>';
        $svg .= '<text class="tk-txt" x="' . ($trai - 8) . '" y="' . ($y + 4) . '" text-anchor="end">' . e(tk_rut_gon($tran * $i / 4)) . '</text>';
    }
    foreach ($nhan as $i => $nh) {
        $cao = $caoVe * $doanhThu[$i] / $tran;
        $x   = $trai + $o * $i + ($o - $rongCot) / 2;
        $y   = $tren + $caoVe - $cao;
        $mo  = $nh . ': ' . tien($doanhThu[$i]) . ' · ' . $soDon[$i] . ' đơn';
        $svg .= '<g><title>' . e($mo) . '</title>';
        $svg .= '<rect class="tk-bar" x="' . round($x, 1) . '" y="' . round($y, 1) . '" width="' . round($rongCot, 1) . '" height="' . round(max($cao, $doanhThu[$i] > 0 ? 2 : 0), 1) . '" rx="4"/></g>';
        if ($i % $buocNhan === 0) {
            $svg .= '<text class="tk-txt" x="' . round($x + $rongCot / 2, 1) . '" y="' . ($h - 12) . '" text-anchor="middle">' . e($nh) . '</text>';
        }
    }
    return $svg . '</svg>';
}

$moc     = (string)($_GET['moc'] ?? '7_ngay');
$loiLoc  = '';
$tu = $den = null;

if ($moc === 'tu_chon') {
    $a = tk_doc_ngay($_GET['tu'] ?? null);
    $b = tk_doc_ngay($_GET['den'] ?? null);
    if (!$a || !$b) {
        $loiLoc = 'Vui lòng chọn đủ ngày bắt đầu và ngày kết thúc.';
    } elseif ($a > $b) {
        $loiLoc = 'Ngày bắt đầu phải trước hoặc bằng ngày kết thúc.';
    } elseif ($a->diff($b)->days >= TK_TOI_DA_NGAY) {
        $loiLoc = 'Chỉ xem được tối đa ' . TK_TOI_DA_NGAY . ' ngày mỗi lần.';
    } else {
        $tu  = $a;
        $den = $b->modify('+1 day');
    }
}
if ($tu === null) {
    if ($moc === 'tu_chon' || !isset(TK_MOC_NHANH[$moc])) {
        $moc = '7_ngay';
    }
    switch ($moc) {
        case 'hom_nay': $tu = $homNay;                                  $den = $homNay->modify('+1 day'); break;
        case 'hom_qua': $tu = $homNay->modify('-1 day');                $den = $homNay;                   break;
        case 'thang':   $tu = $homNay->modify('first day of this month'); $den = $homNay->modify('+1 day'); break;
        default:        $tu = $homNay->modify('-6 day');                $den = $homNay->modify('+1 day');
    }
}
$cuoi  = $den->modify('-1 day');
$soNgay = (int)$tu->diff($den)->days;
$kyTu  = $tu->format('Y-m-d');
$kyDen = $den->format('Y-m-d');
$trang = max(1, (int)($_GET['trang'] ?? 1));

$st = $pdo->prepare('SELECT COUNT(*) AS so_don, COALESCE(SUM(tong_tien), 0) AS doanh_thu
                     FROM DonHang WHERE ngay_dat >= ? AND ngay_dat < ? AND trang_thai_don <> ?');
$st->execute([$kyTu, $kyDen, $huy]);
$tong = $st->fetch();

$st = $pdo->prepare('SELECT COALESCE(SUM(c.so_luong), 0)
                     FROM ChiTietDonHang c JOIN DonHang d ON d.id = c.id_don_hang
                     WHERE d.ngay_dat >= ? AND d.ngay_dat < ? AND d.trang_thai_don <> ?');
$st->execute([$kyTu, $kyDen, $huy]);
$soSanPham = (int)$st->fetchColumn();

$loiNhuan = null;
try {
    // lợi nhuận = (giá bán - giá nhập) x số lượng
    $st = $pdo->prepare('SELECT COALESCE(SUM((c.don_gia - s.gia_nhap) * c.so_luong), 0)
                         FROM ChiTietDonHang c
                         JOIN DonHang d ON d.id = c.id_don_hang
                         JOIN SanPham s ON s.id = c.id_san_pham
                         WHERE d.ngay_dat >= ? AND d.ngay_dat < ? AND d.trang_thai_don <> ? AND s.gia_nhap > 0');
    $st->execute([$kyTu, $kyDen, $huy]);
    $loiNhuan = (float)$st->fetchColumn();
} catch (PDOException $ex) {
    $loiNhuan = null;
}

$theoThang = $soNgay > TK_NGAY_THEO_NGAY;
$dangKey   = $theoThang ? '%Y-%m' : '%Y-%m-%d';
$st = $pdo->prepare("SELECT DATE_FORMAT(ngay_dat, '$dangKey') AS k, COUNT(*) AS so_don, COALESCE(SUM(tong_tien), 0) AS dt
                     FROM DonHang WHERE ngay_dat >= ? AND ngay_dat < ? AND trang_thai_don <> ?
                     GROUP BY k");
$st->execute([$kyTu, $kyDen, $huy]);
$theoKy = [];
foreach ($st->fetchAll() as $r) {
    $theoKy[$r['k']] = $r;
}
$nhan = $dt = $sd = [];
if ($theoThang) {
    for ($d = $tu->modify('first day of this month'); $d < $den; $d = $d->modify('+1 month')) {
        $nhan[] = $d->format('m/Y');
        $dt[]   = (float)($theoKy[$d->format('Y-m')]['dt'] ?? 0);
        $sd[]   = (int)($theoKy[$d->format('Y-m')]['so_don'] ?? 0);
    }
} else {
    for ($d = $tu; $d < $den; $d = $d->modify('+1 day')) {
        $nhan[] = $d->format('d/m');
        $dt[]   = (float)($theoKy[$d->format('Y-m-d')]['dt'] ?? 0);
        $sd[]   = (int)($theoKy[$d->format('Y-m-d')]['so_don'] ?? 0);
    }
}

$st = $pdo->prepare(
    'SELECT ch.id, ch.ten_charm, ch.hinh_anh, SUM(c.so_luong) AS luot
     FROM DonHang d
     JOIN ChiTietDonHang c ON c.id_don_hang = d.id AND c.id_thiet_ke IS NOT NULL
     JOIN ChiTietThietKe t ON t.id_thiet_ke = c.id_thiet_ke AND t.id_charm IS NOT NULL
     JOIN Charm ch ON ch.id = t.id_charm
     WHERE d.ngay_dat >= ? AND d.ngay_dat < ? AND d.trang_thai_don <> ?
     GROUP BY ch.id, ch.ten_charm, ch.hinh_anh
     ORDER BY luot DESC, ch.id LIMIT 5'
);
$st->execute([$kyTu, $kyDen, $huy]);
$topCharm = $st->fetchAll();

$st = $pdo->prepare(
    'SELECT c.id_san_pham, MIN(c.ten_hien_thi) AS ten, SUM(c.so_luong) AS sl, SUM(c.thanh_tien) AS dt
     FROM DonHang d JOIN ChiTietDonHang c ON c.id_don_hang = d.id AND c.id_san_pham IS NOT NULL
     WHERE d.ngay_dat >= ? AND d.ngay_dat < ? AND d.trang_thai_don <> ?
     GROUP BY c.id_san_pham ORDER BY sl DESC, dt DESC LIMIT 5'
);
$st->execute([$kyTu, $kyDen, $huy]);
$topSp = $st->fetchAll();

$st = $pdo->prepare('SELECT COUNT(*) FROM DonHang WHERE ngay_dat >= ? AND ngay_dat < ?');
$st->execute([$kyTu, $kyDen]);
$tongDon = (int)$st->fetchColumn();
$soTrang = max(1, (int)ceil($tongDon / TK_DON_MOI_TRANG));
$trang   = min($trang, $soTrang);

$st = $pdo->prepare('SELECT id, ho_ten_nhan, ngay_dat, tong_tien, trang_thai_don FROM DonHang
                     WHERE ngay_dat >= ? AND ngay_dat < ?
                     ORDER BY ngay_dat DESC, id DESC LIMIT ? OFFSET ?');
$st->bindValue(1, $kyTu);
$st->bindValue(2, $kyDen);
$st->bindValue(3, TK_DON_MOI_TRANG, PDO::PARAM_INT);
$st->bindValue(4, ($trang - 1) * TK_DON_MOI_TRANG, PDO::PARAM_INT);
$st->execute();
$dsDon = $st->fetchAll();

// tạo link giữ nguyên khoảng ngày đang chọn
function tk_url(array $them = []): string
{
    global $moc, $tu, $cuoi;
    $p = ['moc' => $moc];
    if ($moc === 'tu_chon') {
        $p['tu']  = $tu->format('Y-m-d');
        $p['den'] = $cuoi->format('Y-m-d');
    }
    return 'thong-ke.php?' . http_build_query(array_merge($p, $them));
}

$tieuDe = 'Thống kê';
$menu   = 'thong-ke';
require __DIR__ . '/../includes/admin_header.php';
?>

<nav class="tk-tabs" aria-label="Chọn khoảng thời gian">
  <?php foreach (TK_MOC_NHANH as $ma => $ten): ?>
    <a class="tk-tab<?= $moc === $ma ? ' is-active' : '' ?>" href="thong-ke.php?moc=<?= e($ma) ?>"><?= e($ten) ?></a>
  <?php endforeach; ?>
</nav>

<form class="tk-range" method="get" action="thong-ke.php">
  <input type="hidden" name="moc" value="tu_chon">
  <label>Từ ngày <input type="date" name="tu" value="<?= e($tu->format('Y-m-d')) ?>" required></label>
  <label>Đến ngày <input type="date" name="den" value="<?= e($cuoi->format('Y-m-d')) ?>" required></label>
  <button type="submit" class="btn btn--light btn--sm">Xem</button>
</form>
<?php if ($loiLoc !== ''): ?>
  <div class="flash flash--loi" style="margin-bottom:16px"><?= e($loiLoc) ?></div>
<?php endif; ?>
<p class="tk-ky">Số liệu từ <b><?= e($tu->format('d/m/Y')) ?></b> đến <b><?= e($cuoi->format('d/m/Y')) ?></b></p>

<div class="ad-cards">
  <div class="ad-card">
    <div class="ad-card__label">Tổng tiền đã nhận</div>
    <div class="ad-card__value ad-card__value--pink"><?= tien($tong['doanh_thu']) ?></div>
    <div class="ad-card__note">Không tính đơn đã huỷ</div>
  </div>
  <div class="ad-card">
    <div class="ad-card__label">Số đơn hàng</div>
    <div class="ad-card__value"><?= (int)$tong['so_don'] ?></div>
  </div>
  <div class="ad-card">
    <div class="ad-card__label">Số sản phẩm đã bán</div>
    <div class="ad-card__value"><?= $soSanPham ?></div>
  </div>
  <div class="ad-card">
    <div class="ad-card__label">Lợi nhuận (ước tính)</div>
    <div class="ad-card__value"><?= $loiNhuan === null ? '—' : tien($loiNhuan) ?></div>
    <div class="ad-card__note">Chỉ tính sản phẩm bán sẵn có giá nhập, chưa trừ mã giảm giá</div>
  </div>
</div>

<section class="ad-box">
  <div class="ad-box__head">
    <h2 class="ad-box__title">Doanh thu theo <?= $theoThang ? 'tháng' : 'ngày' ?></h2>
  </div>
  <?= tk_ve_cot($nhan, $dt, $sd) ?>
</section>

<div class="tk-hai-cot">
  <section class="ad-box">
    <h2 class="ad-box__title" style="margin-bottom:12px">Charm được chọn nhiều nhất</h2>
    <?php if (!$topCharm): ?>
      <p class="ad-empty">Chưa có thiết kế nào được bán.</p>
    <?php else: ?>
      <table class="ad-table">
        <thead><tr><th>Charm</th><th class="num">Lượt dùng</th></tr></thead>
        <tbody>
        <?php foreach ($topCharm as $c): ?>
          <tr>
            <td>
              <?php if (la_anh($c['hinh_anh'])): ?><img class="tk-thumb" src="../<?= e($c['hinh_anh']) ?>" alt=""><?php endif; ?>
              <?= e($c['ten_charm']) ?>
            </td>
            <td class="num"><b><?= (int)$c['luot'] ?></b></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </section>

  <section class="ad-box">
    <h2 class="ad-box__title" style="margin-bottom:12px">Sản phẩm bán chạy</h2>
    <?php if (!$topSp): ?>
      <p class="ad-empty">Chưa bán được sản phẩm nào.</p>
    <?php else: ?>
      <table class="ad-table">
        <thead><tr><th>Sản phẩm</th><th class="num">Số lượng</th><th class="num">Doanh thu</th></tr></thead>
        <tbody>
        <?php foreach ($topSp as $s): ?>
          <tr>
            <td><?= e($s['ten']) ?></td>
            <td class="num"><b><?= (int)$s['sl'] ?></b></td>
            <td class="num"><?= tien($s['dt']) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </section>
</div>

<section class="ad-box">
  <div class="ad-box__head">
    <h2 class="ad-box__title">Đơn hàng trong khoảng này (<?= $tongDon ?>)</h2>
  </div>
  <?php if (!$dsDon): ?>
    <p class="ad-empty">Không có đơn hàng nào trong khoảng thời gian này.</p>
  <?php else: ?>
    <div class="ad-table-wrap">
      <table class="ad-table">
        <thead><tr><th>Mã đơn</th><th>Người nhận</th><th>Ngày đặt</th><th class="num">Tổng tiền</th><th>Trạng thái</th></tr></thead>
        <tbody>
        <?php foreach ($dsDon as $d): ?>
          <tr>
            <td><a href="don-hang-chi-tiet.php?id=<?= (int)$d['id'] ?>"><?= e(don_ma((int)$d['id'])) ?></a></td>
            <td><?= e($d['ho_ten_nhan']) ?></td>
            <td><?= e(date('d/m/Y H:i', strtotime((string)$d['ngay_dat']))) ?></td>
            <td class="num"><?= tien($d['tong_tien']) ?></td>
            <td><span class="<?= e(class_trang_thai_don($d['trang_thai_don'])) ?>"><?= e(don_nhan_trang_thai($d['trang_thai_don'])) ?></span></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <p class="ad-field__hint" style="margin-top:10px">Danh sách gồm cả đơn đã huỷ; các đơn này không được tính vào số liệu phía trên.</p>
    <?php if ($soTrang > 1): ?>
      <nav class="tk-trang" aria-label="Phân trang">
        <?php if ($trang > 1): ?><a href="<?= e(tk_url(['trang' => $trang - 1])) ?>">‹ Trước</a><?php endif; ?>
        <span>Trang <?= $trang ?> / <?= $soTrang ?></span>
        <?php if ($trang < $soTrang): ?><a href="<?= e(tk_url(['trang' => $trang + 1])) ?>">Sau ›</a><?php endif; ?>
      </nav>
    <?php endif; ?>
  <?php endif; ?>
</section>

<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
