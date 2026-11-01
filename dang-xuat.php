<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/khach.php';

// đăng xuất chỉ nhận POST có csrf, gõ link trực tiếp thì về trang chủ
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    chuyen_trang('index.php');
}
if (!csrf_hop_le($_POST['csrf'] ?? null)) {
    flash('loi', 'Phiên làm việc đã hết hạn, vui lòng thử lại.');
    chuyen_trang('index.php');
}

khach_dang_xuat();
flash('ok', 'Đã đăng xuất.');
chuyen_trang('index.php');
