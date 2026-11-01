<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/khach.php';

// api chat tư vấn của khách: chỉ đọc và gửi trong luồng chat của chính tài khoản đang đăng nhập
$khach = khach_hien_tai();
if ($khach === null) {
    tra_json(['ok' => false, 'message' => 'Vui lòng đăng nhập.'], 401);
}
$idTk = (int)$khach['id'];
$pdo  = db();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // lấy các tin mới hơn id khách đang có
    $st = $pdo->prepare('SELECT id, nguoi_gui, noi_dung, ngay_gui FROM TinNhanTuVan WHERE id_tai_khoan = ? AND id > ? ORDER BY id LIMIT 100');
    $st->execute([$idTk, max(0, (int)($_GET['sau'] ?? 0))]);
    $ds = $st->fetchAll();
    // khách đang mở chat nên coi như đã đọc tin của shop
    $pdo->prepare("UPDATE TinNhanTuVan SET da_doc = 1 WHERE id_tai_khoan = ? AND nguoi_gui = 'shop' AND da_doc = 0")->execute([$idTk]);
    tra_json(['ok' => true, 'tin' => $ds]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    tra_json(['ok' => false, 'message' => 'Phương thức không hợp lệ.'], 405);
}

$in = json_decode(file_get_contents('php://input'), true) ?: [];
if (!csrf_hop_le($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($in['csrf'] ?? null))) {
    tra_json(['ok' => false, 'message' => 'Phiên làm việc đã hết hạn, hãy tải lại trang.'], 403);
}

$noiDung = trim((string)($in['noi_dung'] ?? ''));
$dai     = mb_strlen($noiDung);
if ($dai < 1 || $dai > 500) {
    tra_json(['ok' => false, 'message' => 'Tin nhắn từ 1 đến 500 ký tự.'], 422);
}

// chặn spam: tối đa 20 tin mỗi giờ
$st = $pdo->prepare("SELECT COUNT(*) FROM TinNhanTuVan WHERE id_tai_khoan = ? AND nguoi_gui = 'khach' AND ngay_gui > NOW() - INTERVAL 1 HOUR");
$st->execute([$idTk]);
if ((int)$st->fetchColumn() >= 20) {
    tra_json(['ok' => false, 'message' => 'Bạn gửi hơi nhanh, vui lòng thử lại sau'], 429);
}

$pdo->prepare("INSERT INTO TinNhanTuVan (id_tai_khoan, nguoi_gui, noi_dung) VALUES (?, 'khach', ?)")->execute([$idTk, $noiDung]);
$id = (int)$pdo->lastInsertId();
$st = $pdo->prepare('SELECT id, nguoi_gui, noi_dung, ngay_gui FROM TinNhanTuVan WHERE id = ?');
$st->execute([$id]);
tra_json(['ok' => true, 'tin' => $st->fetch()]);
