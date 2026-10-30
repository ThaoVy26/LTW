<?php
require_once __DIR__ . '/../includes/functions.php';

// api lưu thiết kế vòng: chỉ nhận POST có csrf
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    tra_json(['ok' => false, 'message' => 'Chỉ chấp nhận POST.'], 405);
}
if (!csrf_hop_le($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
    tra_json(['ok' => false, 'message' => 'Phiên làm việc đã hết hạn, hãy tải lại trang.'], 403);
}

// đọc dữ liệu js gửi lên (json)
$in     = json_decode(file_get_contents('php://input'), true) ?: [];
$action = $in['action'] ?? '';
$slots  = $in['slots'] ?? null;

$st = db()->prepare('SELECT * FROM KichThuocTay WHERE id = ?');
$st->execute([(int)($in['size_id'] ?? 0)]);
$size = $st->fetch();

$st = db()->prepare('SELECT * FROM DayVong WHERE id = ?');
$st->execute([(int)($in['chain_id'] ?? 0)]);
$day = $st->fetch();

// kiểm tra size, dây và số ô có đúng không
if (!in_array($action, ['save', 'cart'], true) || !$size || !$day || !is_array($slots)) {
    tra_json(['ok' => false, 'message' => 'Dữ liệu thiết kế không hợp lệ.'], 422);
}
if (count($slots) !== (int)$size['so_charm_toi_da']) {
    tra_json(['ok' => false, 'message' => 'Số ô không khớp với size đã chọn.'], 422);
}

// -1 là ô thứ 2 của charm dài
const O_NOI = -1;
$slots   = array_map(fn($v) => $v === null ? null : (int)$v, array_values($slots));
$idDung  = array_values(array_unique(array_filter($slots, fn($v) => $v !== null && $v !== O_NOI)));
// lấy giá charm từ csdl, không tin giá js gửi lên
$charm   = [];
if ($idDung) {
    $st = db()->prepare('SELECT id, gia, kieu FROM Charm WHERE trang_thai = "hien" AND id IN (' . implode(',', array_fill(0, count($idDung), '?')) . ')');
    $st->execute($idDung);
    foreach ($st->fetchAll() as $c) {
        $charm[(int)$c['id']] = $c;
    }
    if (count($charm) !== count($idDung)) {
        tra_json(['ok' => false, 'message' => 'Có charm không còn được bán.'], 422);
    }
}
if ($action === 'cart' && !$idDung) {
    tra_json(['ok' => false, 'message' => 'Bạn chưa chọn charm nào!'], 422);
}

// charm dài phải chiếm đúng 2 ô liền nhau
foreach ($slots as $i => $v) {
    $laDai   = $v !== null && $v !== O_NOI && $charm[$v]['kieu'] === 'dai';
    $sauLaNoi = ($slots[$i + 1] ?? null) === O_NOI;
    $truocLaDai = $i > 0 && $slots[$i - 1] !== null && $slots[$i - 1] !== O_NOI && $charm[$slots[$i - 1]]['kieu'] === 'dai';
    if (($laDai && !$sauLaNoi) || ($v === O_NOI && !$truocLaDai)) {
        tra_json(['ok' => false, 'message' => 'Charm dài phải chiếm 2 ô liền nhau.'], 422);
    }
}

// tính tổng giá = tiền dây mỗi ô + tiền các charm
$soO        = count($slots);
$soCharmBac = count(array_filter($slots, fn($v) => $v === null));
$tongGia    = $soO * (int)$day['gia_charm_bac'];
foreach ($slots as $v) {
    if ($v !== null && $v !== O_NOI) {
        $tongGia += (int)$charm[$v]['gia'];
    }
}

// lưu thiết kế và từng ô vào csdl
$pdo = db();
$pdo->beginTransaction();
$pdo->prepare('INSERT INTO ThietKeTuyChinh (id_kich_thuoc, id_day_vong, so_charm_bac, tong_gia) VALUES (?, ?, ?, ?)')
    ->execute([$size['id'], $day['id'], $soCharmBac, $tongGia]);
$idTk = (int)$pdo->lastInsertId();
$ins = $pdo->prepare('INSERT INTO ChiTietThietKe (id_thiet_ke, vi_tri_slot, id_charm) VALUES (?, ?, ?)');
foreach ($slots as $viTri => $idCharm) {
    if ($idCharm !== O_NOI) {
        $ins->execute([$idTk, $viTri, $idCharm]);
    }
}
$pdo->commit();

// bấm thêm vào giỏ thì cho vào giỏ, còn lại chỉ lưu
if ($action === 'cart') {
    $thay = $in['replace_key'] ?? null;
    gio_them_thiet_ke($idTk, is_string($thay) && str_starts_with($thay, 'tk_') ? $thay : null);
    $msg = 'Đã thêm vòng tự thiết kế vào giỏ hàng!';
} else {
    $_SESSION['thiet_ke_da_luu'][] = $idTk;
    $msg = 'Đã lưu thiết kế #' . $idTk . '.';
}

tra_json(['ok' => true, 'message' => $msg, 'id' => $idTk, 'tong_gia' => $tongGia, 'cart_count' => gio_dem()]);
