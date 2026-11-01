<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// api chat tư vấn cho admin: phải đăng nhập admin mới có dữ liệu
if (admin_hien_tai() === null) {
    tra_json(['ok' => false, 'message' => 'Vui lòng đăng nhập quản trị.'], 401);
}
$pdo = db();

// chỉ chat với tài khoản khách có thật
function khach_chat_hop_le(PDO $pdo, int $id): bool
{
    $st = $pdo->prepare("SELECT COUNT(*) FROM TaiKhoan WHERE id = ? AND vai_tro = 'khach'");
    $st->execute([$id]);
    return (int)$st->fetchColumn() === 1;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $idKh = (int)($_GET['khach'] ?? 0);
    if (!khach_chat_hop_le($pdo, $idKh)) {
        tra_json(['ok' => false, 'message' => 'Không tìm thấy khách.'], 404);
    }
    $st = $pdo->prepare('SELECT id, nguoi_gui, noi_dung, ngay_gui FROM TinNhanTuVan WHERE id_tai_khoan = ? AND id > ? ORDER BY id LIMIT 100');
    $st->execute([$idKh, max(0, (int)($_GET['sau'] ?? 0))]);
    $ds = $st->fetchAll();
    // admin đang mở cuộc chat này nên coi như đã đọc
    $pdo->prepare("UPDATE TinNhanTuVan SET da_doc = 1 WHERE id_tai_khoan = ? AND nguoi_gui = 'khach' AND da_doc = 0")->execute([$idKh]);
    tra_json(['ok' => true, 'tin' => $ds]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    tra_json(['ok' => false, 'message' => 'Phương thức không hợp lệ.'], 405);
}

$in = json_decode(file_get_contents('php://input'), true) ?: [];
if (!csrf_hop_le($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($in['csrf'] ?? null))) {
    tra_json(['ok' => false, 'message' => 'Phiên làm việc đã hết hạn, hãy tải lại trang.'], 403);
}
$idKh    = (int)($in['khach'] ?? 0);
$noiDung = trim((string)($in['noi_dung'] ?? ''));
if (!khach_chat_hop_le($pdo, $idKh)) {
    tra_json(['ok' => false, 'message' => 'Không tìm thấy khách.'], 404);
}
if (mb_strlen($noiDung) < 1 || mb_strlen($noiDung) > 500) {
    tra_json(['ok' => false, 'message' => 'Tin nhắn từ 1 đến 500 ký tự.'], 422);
}

$pdo->prepare("INSERT INTO TinNhanTuVan (id_tai_khoan, nguoi_gui, noi_dung) VALUES (?, 'shop', ?)")->execute([$idKh, $noiDung]);
$st = $pdo->prepare('SELECT id, nguoi_gui, noi_dung, ngay_gui FROM TinNhanTuVan WHERE id = ?');
$st->execute([(int)$pdo->lastInsertId()]);
tra_json(['ok' => true, 'tin' => $st->fetch()]);
