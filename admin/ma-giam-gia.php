<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/don_hang.php';
require_once __DIR__ . '/../includes/admin_ui.php';
// phải đăng nhập admin mới vào được
yeu_cau_admin();

const MG_MOI_TRANG = 10;
const MG_LOAI = ['so_tien' => 'Giảm số tiền (đ)', 'phan_tram' => 'Giảm theo phần trăm (%)'];
const MG_TINH_TRANG = [
    'dang_chay' => ['Đang áp dụng',  'tt tt--hoan_tat'],
    'chua'      => ['Chưa bắt đầu',  'tt tt--dang_giao'],
    'het_han'   => ['Hết hạn',       'tt tt--cho_xac_nhan'],
    'tat'       => ['Đã tắt',        'tt tt--huy'],
];
const MG_SQL_TINH_TRANG = "CASE
    WHEN m.trang_thai <> 'hoat_dong' THEN 'tat'
    WHEN m.ngay_het_han IS NOT NULL AND m.ngay_het_han < CURDATE() THEN 'het_han'
    WHEN m.ngay_bat_dau IS NOT NULL AND m.ngay_bat_dau > CURDATE() THEN 'chua'
    ELSE 'dang_chay' END";

$pdo = db();

// đếm số đơn đã dùng mã này
function mg_so_don_da_dung(int $id): int
{
    $st = db()->prepare('SELECT COUNT(*) FROM DonHang WHERE id_ma_giam_gia = ?');
    $st->execute([$id]);
    return (int)$st->fetchColumn();
}

$loi = [];
$du  = ['id' => 0, 'ma_code' => '', 'loai_giam' => 'so_tien', 'gia_tri' => '', 'ngay_bat_dau' => '', 'ngay_het_han' => '', 'bat' => '1',
       'don_toi_thieu' => '', 'giam_toi_da' => '', 'luot_toi_da' => ''];
$hienForm = false;

// bật tắt, xoá, thêm, sửa mã giảm giá
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hanhDong = (string)($_POST['hanh_dong'] ?? '');
    $id = (int)($_POST['id'] ?? 0);

    if (!csrf_hop_le($_POST['csrf'] ?? null)) {
        flash('loi', 'Phiên làm việc đã hết hạn, vui lòng thử lại.');
        chuyen_trang('ma-giam-gia.php');
    }

    if ($hanhDong === 'bat_tat') {
        $st = $pdo->prepare("UPDATE MaGiamGia SET trang_thai = IF(trang_thai = 'hoat_dong', 'tam_tat', 'hoat_dong') WHERE id = ?");
        $st->execute([$id]);
        $st = $pdo->prepare('SELECT ma_code, trang_thai FROM MaGiamGia WHERE id = ?');
        $st->execute([$id]);
        if ($m = $st->fetch()) {
            flash('ok', 'Mã ' . $m['ma_code'] . ($m['trang_thai'] === 'hoat_dong' ? ' đã được bật.' : ' đã được tắt, khách không áp dụng được nữa.'));
        } else {
            flash('loi', 'Không tìm thấy mã giảm giá.');
        }
        chuyen_trang(url_quay_lai($_POST['back'] ?? null, 'ma-giam-gia.php'));
    }

    if ($hanhDong === 'xoa') {
        $st = $pdo->prepare('SELECT ma_code FROM MaGiamGia WHERE id = ?');
        $st->execute([$id]);
        $code = $st->fetchColumn();
        if ($code === false) {
            flash('loi', 'Không tìm thấy mã giảm giá.');
        } elseif (mg_so_don_da_dung($id) > 0) {
            flash('loi', 'Mã ' . $code . ' đã có đơn hàng sử dụng nên không thể xoá. Hãy tắt mã thay vì xoá.');
        } else {
            $pdo->prepare('DELETE FROM MaGiamGia WHERE id = ?')->execute([$id]);
            flash('ok', 'Đã xoá mã ' . $code . '.');
        }
        chuyen_trang('ma-giam-gia.php');
    }

    if ($hanhDong === 'luu') {
        $hienForm = true;
        $du = [
            'id'           => $id,
            'ma_code'      => strtoupper(trim((string)($_POST['ma_code'] ?? ''))),
            'loai_giam'    => (string)($_POST['loai_giam'] ?? ''),
            'gia_tri'      => trim((string)($_POST['gia_tri'] ?? '')),
            'ngay_bat_dau' => trim((string)($_POST['ngay_bat_dau'] ?? '')),
            'ngay_het_han' => trim((string)($_POST['ngay_het_han'] ?? '')),
            'bat'          => isset($_POST['bat']) ? '1' : '',
            'don_toi_thieu' => trim((string)($_POST['don_toi_thieu'] ?? '')),
            'giam_toi_da'  => trim((string)($_POST['giam_toi_da'] ?? '')),
            'luot_toi_da'  => trim((string)($_POST['luot_toi_da'] ?? '')),
        ];

        if ($id > 0) {
            $st = $pdo->prepare('SELECT ma_code FROM MaGiamGia WHERE id = ?');
            $st->execute([$id]);
            $codeCu = $st->fetchColumn();
            if ($codeCu === false) {
                flash('loi', 'Không tìm thấy mã giảm giá.');
                chuyen_trang('ma-giam-gia.php');
            }
            if (mg_so_don_da_dung($id) > 0) {
                $du['ma_code'] = (string)$codeCu;
            }
        }

        if (!preg_match('/^[A-Z0-9_-]{3,30}$/', $du['ma_code'])) {
            $loi['ma_code'] = 'Mã gồm 3–30 ký tự: chữ không dấu, số, dấu gạch ngang hoặc gạch dưới.';
        } else {
            $st = $pdo->prepare('SELECT COUNT(*) FROM MaGiamGia WHERE ma_code = ? AND id <> ?');
            $st->execute([$du['ma_code'], $id]);
            if ((int)$st->fetchColumn() > 0) {
                $loi['ma_code'] = 'Mã "' . $du['ma_code'] . '" đã tồn tại.';
            }
        }

        if (!isset(MG_LOAI[$du['loai_giam']])) {
            $loi['loai_giam'] = 'Vui lòng chọn loại giảm giá.';
        }

        $giaTri = ad_so_nguyen($du['gia_tri']);
        if ($giaTri === null || $giaTri < 1) {
            $loi['gia_tri'] = 'Giá trị phải là số nguyên lớn hơn 0.';
        } elseif ($du['loai_giam'] === 'phan_tram' && $giaTri > 100) {
            $loi['gia_tri'] = 'Giảm theo phần trăm tối đa là 100.';
        } elseif ($du['loai_giam'] === 'so_tien' && $giaTri > 10000000) {
            $loi['gia_tri'] = 'Số tiền giảm tối đa 10.000.000đ.';
        }

        $toiThieu = 0; $giamToiDa = null; $luotToiDa = null;
        if ($du['don_toi_thieu'] !== '') {
            $toiThieu = ad_so_nguyen($du['don_toi_thieu']);
            if ($toiThieu === null) { $loi['don_toi_thieu'] = 'Đơn tối thiểu phải là số nguyên không âm.'; $toiThieu = 0; }
        }
        if ($du['giam_toi_da'] !== '') {
            $giamToiDa = ad_so_nguyen($du['giam_toi_da']);
            if ($giamToiDa === null || $giamToiDa < 1) $loi['giam_toi_da'] = 'Mức giảm tối đa phải là số nguyên lớn hơn 0.';
        }
        if ($du['luot_toi_da'] !== '') {
            $luotToiDa = ad_so_nguyen($du['luot_toi_da']);
            if ($luotToiDa === null || $luotToiDa < 1) $loi['luot_toi_da'] = 'Số lượt dùng tối đa phải là số nguyên lớn hơn 0.';
        }

        $bd = $du['ngay_bat_dau'] === '' ? null : ad_ngay($du['ngay_bat_dau']);
        $hh = $du['ngay_het_han'] === '' ? null : ad_ngay($du['ngay_het_han']);
        if ($du['ngay_bat_dau'] !== '' && $bd === null) $loi['ngay_bat_dau'] = 'Ngày bắt đầu không hợp lệ.';
        if ($du['ngay_het_han'] !== '' && $hh === null) $loi['ngay_het_han'] = 'Ngày hết hạn không hợp lệ.';
        if ($bd !== null && $hh !== null && $hh < $bd) {
            $loi['ngay_het_han'] = 'Ngày hết hạn không được trước ngày bắt đầu.';
        }

        if (!$loi) {
            $trangThai = $du['bat'] === '1' ? 'hoat_dong' : 'tam_tat';
            try {
                if ($id > 0) {
                    $pdo->prepare(
                        'UPDATE MaGiamGia SET ma_code = ?, loai_giam = ?, gia_tri = ?, ngay_bat_dau = ?, ngay_het_han = ?, trang_thai = ?,
                            don_toi_thieu = ?, giam_toi_da = ?, luot_toi_da = ? WHERE id = ?'
                    )->execute([$du['ma_code'], $du['loai_giam'], $giaTri, $bd, $hh, $trangThai, $toiThieu, $giamToiDa, $luotToiDa, $id]);
                    flash('ok', 'Đã cập nhật mã ' . $du['ma_code'] . '.');
                } else {
                    $pdo->prepare(
                        'INSERT INTO MaGiamGia (ma_code, loai_giam, gia_tri, ngay_bat_dau, ngay_het_han, trang_thai, don_toi_thieu, giam_toi_da, luot_toi_da)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
                    )->execute([$du['ma_code'], $du['loai_giam'], $giaTri, $bd, $hh, $trangThai, $toiThieu, $giamToiDa, $luotToiDa]);
                    flash('ok', 'Đã tạo mã ' . $du['ma_code'] . '.');
                }
                chuyen_trang('ma-giam-gia.php');
            } catch (PDOException $ex) {
                error_log('ma-giam-gia luu: ' . $ex->getMessage());
                $loi['ma_code'] = $ex->getCode() === '23000' ? 'Mã "' . $du['ma_code'] . '" đã tồn tại.' : 'Có lỗi khi lưu mã, vui lòng thử lại.';
            }
        }
    }
}

if (!$hienForm) {
    if (isset($_GET['them'])) {
        $hienForm = true;
    } elseif (($suaId = (int)($_GET['sua'] ?? 0)) > 0) {
        $st = $pdo->prepare('SELECT * FROM MaGiamGia WHERE id = ?');
        $st->execute([$suaId]);
        if ($m = $st->fetch()) {
            $hienForm = true;
            $du = [
                'id' => (int)$m['id'], 'ma_code' => $m['ma_code'], 'loai_giam' => $m['loai_giam'],
                'gia_tri' => (string)(int)$m['gia_tri'], 'ngay_bat_dau' => (string)$m['ngay_bat_dau'],
                'ngay_het_han' => (string)$m['ngay_het_han'], 'bat' => $m['trang_thai'] === 'hoat_dong' ? '1' : '',
                'don_toi_thieu' => (int)($m['don_toi_thieu'] ?? 0) > 0 ? (string)(int)$m['don_toi_thieu'] : '',
                'giam_toi_da' => ($m['giam_toi_da'] ?? null) !== null ? (string)(int)$m['giam_toi_da'] : '',
                'luot_toi_da' => ($m['luot_toi_da'] ?? null) !== null ? (string)(int)$m['luot_toi_da'] : '',
            ];
        } else {
            flash('loi', 'Không tìm thấy mã giảm giá.');
            chuyen_trang('ma-giam-gia.php');
        }
    }
}
$maKhoa = $hienForm && $du['id'] > 0 && mg_so_don_da_dung((int)$du['id']) > 0;

$tt = (string)($_GET['tt'] ?? '');
if (!isset(MG_TINH_TRANG[$tt])) {
    $tt = '';
}
$q     = mb_substr(trim((string)($_GET['q'] ?? '')), 0, 30);
$trang = max(1, (int)($_GET['trang'] ?? 1));

$goc = 'SELECT m.*, ' . MG_SQL_TINH_TRANG . ' AS tinh_trang,
               COALESCE(u.tong_don, 0) AS tong_don, COALESCE(u.don_dung, 0) AS don_dung, COALESCE(u.da_giam, 0) AS da_giam
        FROM MaGiamGia m
        LEFT JOIN (
            SELECT id_ma_giam_gia,
                   COUNT(*) AS tong_don,
                   SUM(trang_thai_don <> \'huy\') AS don_dung,
                   COALESCE(SUM(CASE WHEN trang_thai_don <> \'huy\' THEN giam_gia END), 0) AS da_giam
            FROM DonHang WHERE id_ma_giam_gia IS NOT NULL GROUP BY id_ma_giam_gia
        ) u ON u.id_ma_giam_gia = m.id';

$where = [];
$tham  = [];
if ($tt !== '') { $where[] = 't.tinh_trang = ?'; $tham[] = [$tt, PDO::PARAM_STR]; }
if ($q !== '')  { $where[] = 't.ma_code LIKE ?'; $tham[] = ['%' . $q . '%', PDO::PARAM_STR]; }
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$st = $pdo->prepare("SELECT COUNT(*) FROM ($goc) t $whereSql");
foreach ($tham as $i => [$v, $k]) { $st->bindValue($i + 1, $v, $k); }
$st->execute();
$tong    = (int)$st->fetchColumn();
$soTrang = max(1, (int)ceil($tong / MG_MOI_TRANG));
$trang   = min($trang, $soTrang);

$st = $pdo->prepare("SELECT t.* FROM ($goc) t $whereSql ORDER BY t.id DESC LIMIT ? OFFSET ?");
foreach ($tham as $i => [$v, $k]) { $st->bindValue($i + 1, $v, $k); }
$st->bindValue(count($tham) + 1, MG_MOI_TRANG, PDO::PARAM_INT);
$st->bindValue(count($tham) + 2, ($trang - 1) * MG_MOI_TRANG, PDO::PARAM_INT);
$st->execute();
$dsMa = $st->fetchAll();

$dem = array_fill_keys(array_keys(MG_TINH_TRANG), 0);
foreach ($pdo->query("SELECT " . MG_SQL_TINH_TRANG . " AS tinh_trang, COUNT(*) AS n FROM MaGiamGia m GROUP BY tinh_trang") as $r) {
    $dem[$r['tinh_trang']] = (int)$r['n'];
}

// tạo link giữ nguyên bộ lọc mã giảm giá
function mg_url(array $doi = []): string
{
    global $tt, $q, $trang;
    $p = array_merge(['tt' => $tt, 'q' => $q, 'trang' => $trang], $doi);
    $p = array_filter($p, fn($v) => $v !== '' && $v !== null && $v !== 1);
    return 'ma-giam-gia.php' . ($p ? '?' . http_build_query($p) : '');
}

// lấy câu lỗi của 1 ô nhập
function mg_o_loi(array $loi, string $k): string
{
    return isset($loi[$k]) ? '<div class="mg-err">' . e($loi[$k]) . '</div>' : '';
}

$tieuDe  = 'Mã giảm giá';
$menu    = 'ma-giam-gia';
$scripts = ['../assets/js/admin-ma-giam-gia.js'];
require __DIR__ . '/../includes/admin_header.php';
?>
<link rel="stylesheet" href="../<?= phien_ban('assets/css/admin-don-hang.css') ?>">
<link rel="stylesheet" href="../<?= phien_ban('assets/css/admin-ma-giam-gia.css') ?>">
<div class="dh">

  <?php if ($hienForm): ?>
  <section class="ad-box" id="form-ma">
    <h2 class="ad-box__title"><?= $du['id'] > 0 ? 'Sửa mã giảm giá' : 'Tạo mã giảm giá mới' ?></h2>
    <form method="post" action="ma-giam-gia.php" class="mg-form" novalidate>
      <?= csrf_field() ?>
      <input type="hidden" name="hanh_dong" value="luu">
      <input type="hidden" name="id" value="<?= (int)$du['id'] ?>">

      <div class="mg-field<?= isset($loi['ma_code']) ? ' has-error' : '' ?>">
        <label for="ma_code">Mã *</label>
        <input id="ma_code" name="ma_code" value="<?= e($du['ma_code']) ?>" maxlength="30" required
               placeholder="VD: SALE20" autocomplete="off"<?= $maKhoa ? ' readonly' : '' ?>>
        <?= mg_o_loi($loi, 'ma_code') ?>
        <div class="ad-field__hint"><?= $maKhoa ? 'Mã đã có đơn hàng sử dụng nên không đổi tên được.' : 'Tự chuyển thành chữ HOA. Khách nhập mã này ở giỏ hàng.' ?></div>
      </div>

      <div class="mg-row">
        <div class="mg-field<?= isset($loi['loai_giam']) ? ' has-error' : '' ?>">
          <label for="loai_giam">Loại giảm *</label>
          <select id="loai_giam" name="loai_giam">
            <?php foreach (MG_LOAI as $k => $ten): ?>
              <option value="<?= e($k) ?>"<?= $du['loai_giam'] === $k ? ' selected' : '' ?>><?= e($ten) ?></option>
            <?php endforeach; ?>
          </select>
          <?= mg_o_loi($loi, 'loai_giam') ?>
        </div>
        <div class="mg-field<?= isset($loi['gia_tri']) ? ' has-error' : '' ?>">
          <label for="gia_tri">Giá trị * <span id="gia-tri-don-vi" class="mg-unit"></span></label>
          <input id="gia_tri" name="gia_tri" value="<?= e($du['gia_tri']) ?>" inputmode="numeric" required placeholder="VD: 15">
          <?= mg_o_loi($loi, 'gia_tri') ?>
        </div>
      </div>

      <div class="mg-row">
        <div class="mg-field<?= isset($loi['ngay_bat_dau']) ? ' has-error' : '' ?>">
          <label for="ngay_bat_dau">Ngày bắt đầu</label>
          <input id="ngay_bat_dau" type="date" name="ngay_bat_dau" value="<?= e($du['ngay_bat_dau']) ?>">
          <?= mg_o_loi($loi, 'ngay_bat_dau') ?>
          <div class="ad-field__hint">Để trống nếu áp dụng ngay.</div>
        </div>
        <div class="mg-field<?= isset($loi['ngay_het_han']) ? ' has-error' : '' ?>">
          <label for="ngay_het_han">Ngày hết hạn</label>
          <input id="ngay_het_han" type="date" name="ngay_het_han" value="<?= e($du['ngay_het_han']) ?>">
          <?= mg_o_loi($loi, 'ngay_het_han') ?>
          <div class="ad-field__hint">Để trống nếu không giới hạn. Dùng được đến hết ngày này.</div>
        </div>
      </div>

      <div class="mg-row">
        <div class="mg-field<?= isset($loi['don_toi_thieu']) ? ' has-error' : '' ?>">
          <label for="don_toi_thieu">Đơn tối thiểu (đ)</label>
          <input id="don_toi_thieu" name="don_toi_thieu" value="<?= e($du['don_toi_thieu']) ?>" inputmode="numeric" placeholder="VD: 300000">
          <?= mg_o_loi($loi, 'don_toi_thieu') ?>
          <div class="ad-field__hint">Để trống nếu áp dụng cho mọi đơn.</div>
        </div>
        <div class="mg-field<?= isset($loi['giam_toi_da']) ? ' has-error' : '' ?>">
          <label for="giam_toi_da">Giảm tối đa (đ), cho mã %</label>
          <input id="giam_toi_da" name="giam_toi_da" value="<?= e($du['giam_toi_da']) ?>" inputmode="numeric" placeholder="VD: 50000">
          <?= mg_o_loi($loi, 'giam_toi_da') ?>
          <div class="ad-field__hint">Để trống nếu không giới hạn.</div>
        </div>
      </div>
      <div class="mg-row">
        <div class="mg-field<?= isset($loi['luot_toi_da']) ? ' has-error' : '' ?>">
          <label for="luot_toi_da">Số lượt dùng tối đa</label>
          <input id="luot_toi_da" name="luot_toi_da" value="<?= e($du['luot_toi_da']) ?>" inputmode="numeric" placeholder="VD: 100">
          <?= mg_o_loi($loi, 'luot_toi_da') ?>
          <div class="ad-field__hint">Chỉ tính đơn chưa huỷ. Để trống nếu không giới hạn.</div>
        </div>
      </div>

      <label class="mg-check">
        <input type="checkbox" name="bat" value="1"<?= $du['bat'] === '1' ? ' checked' : '' ?>>
        Đang bật (khách áp dụng được trong khoảng ngày trên)
      </label>

      <div class="dh-actions">
        <button class="btn btn--primary btn--sm" type="submit"><?= $du['id'] > 0 ? 'Lưu thay đổi' : 'Tạo mã' ?></button>
        <a class="btn btn--light btn--sm" href="ma-giam-gia.php">Huỷ</a>
      </div>
    </form>
  </section>
  <?php endif; ?>

  <div class="mg-head">
    <p class="dh__sub"><?= $tong ?> mã<?= ($tt !== '' || $q !== '') ? ' khớp bộ lọc' : '' ?>. Doanh thu và số đơn dùng mã không tính đơn đã huỷ.</p>
    <?php if (!$hienForm): ?><a class="btn btn--primary btn--sm" href="ma-giam-gia.php?them=1">+ Tạo mã mới</a><?php endif; ?>
  </div>

  <nav class="dh-tabs" aria-label="Lọc theo tình trạng">
    <a class="dh-tab<?= $tt === '' ? ' is-active' : '' ?>" href="<?= e(mg_url(['tt' => '', 'trang' => 1])) ?>">Tất cả<small><?= array_sum($dem) ?></small></a>
    <?php foreach (MG_TINH_TRANG as $ma => [$ten]): ?>
      <a class="dh-tab<?= $tt === $ma ? ' is-active' : '' ?>" href="<?= e(mg_url(['tt' => $ma, 'trang' => 1])) ?>"><?= e($ten) ?><small><?= $dem[$ma] ?></small></a>
    <?php endforeach; ?>
  </nav>

  <form class="dh-search" method="get" action="ma-giam-gia.php">
    <?php if ($tt !== ''): ?><input type="hidden" name="tt" value="<?= e($tt) ?>"><?php endif; ?>
    <input type="search" name="q" value="<?= e($q) ?>" placeholder="Tìm theo mã…" maxlength="30">
    <button class="btn btn--primary btn--sm" type="submit">Tìm</button>
    <?php if ($q !== ''): ?><a class="btn btn--light btn--sm" href="<?= e(mg_url(['q' => '', 'trang' => 1])) ?>">Xoá tìm kiếm</a><?php endif; ?>
  </form>

  <section class="ad-box">
    <?php if (!$dsMa): ?>
      <div class="ad-empty"><?= ($tt !== '' || $q !== '') ? 'Không có mã nào khớp bộ lọc.' : 'Chưa có mã giảm giá nào. Bấm "Tạo mã mới" để thêm.' ?></div>
    <?php else: ?>
    <div class="ad-table-wrap">
      <table class="ad-table">
        <thead>
          <tr><th>Mã</th><th>Mức giảm</th><th>Thời hạn</th><th class="num">Số đơn dùng</th><th class="num">Tổng đã giảm</th><th>Tình trạng</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach ($dsMa as $m): [$nhan, $lop] = MG_TINH_TRANG[$m['tinh_trang']]; ?>
          <tr>
            <td class="ma"><?= e($m['ma_code']) ?></td>
            <td><b><?= $m['loai_giam'] === 'phan_tram' ? (int)$m['gia_tri'] . '%' : '-' . tien($m['gia_tri']) ?></b>
              <?php
                $dk = [];
                if ((int)($m['don_toi_thieu'] ?? 0) > 0) $dk[] = 'đơn từ ' . tien($m['don_toi_thieu']);
                if (($m['giam_toi_da'] ?? null) !== null && $m['loai_giam'] === 'phan_tram') $dk[] = 'giảm tối đa ' . tien($m['giam_toi_da']);
                if (($m['luot_toi_da'] ?? null) !== null) $dk[] = 'tối đa ' . (int)$m['luot_toi_da'] . ' lượt';
              ?>
              <?php if ($dk): ?><div class="dh-muted"><?= e(implode(' · ', $dk)) ?></div><?php endif; ?>
            </td>
            <td>
              <?php if ($m['ngay_bat_dau'] === null && $m['ngay_het_han'] === null): ?>
                <span class="dh-muted">Không giới hạn</span>
              <?php else: ?>
                <?= $m['ngay_bat_dau'] ? e(date('d/m/Y', strtotime($m['ngay_bat_dau']))) : '…' ?> → <?= $m['ngay_het_han'] ? e(date('d/m/Y', strtotime($m['ngay_het_han']))) : '…' ?>
              <?php endif; ?>
            </td>
            <td class="num"><?= (int)$m['don_dung'] ?></td>
            <td class="num"><?= tien($m['da_giam']) ?></td>
            <td><span class="<?= e($lop) ?>"><?= e($nhan) ?></span></td>
            <td class="num mg-actions">
              <a class="btn btn--light btn--sm" href="ma-giam-gia.php?sua=<?= (int)$m['id'] ?>#form-ma">Sửa</a>
              <form method="post" action="ma-giam-gia.php">
                <?= csrf_field() ?>
                <input type="hidden" name="hanh_dong" value="bat_tat">
                <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                <input type="hidden" name="back" value="<?= e(mg_url()) ?>">
                <button class="btn btn--light btn--sm" type="submit"><?= $m['trang_thai'] === 'hoat_dong' ? 'Tắt' : 'Bật' ?></button>
              </form>
              <?php if ((int)$m['tong_don'] === 0): ?>
              <form method="post" action="ma-giam-gia.php" onsubmit="return confirm('Xoá mã này? Không thể khôi phục.');">
                <?= csrf_field() ?>
                <input type="hidden" name="hanh_dong" value="xoa">
                <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                <button class="btn btn--danger-soft btn--sm" type="submit">Xoá</button>
              </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?= ad_phan_trang($trang, $soTrang, fn(int $p): string => mg_url(['trang' => $p])) ?>
    <?php endif; ?>
  </section>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
