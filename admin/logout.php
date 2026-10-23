<?php
/**
 * Đăng xuất (chỉ nhận POST từ nút trên thanh trên).
 */
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_hop_le($_POST['csrf'] ?? null)) {
    dang_xuat();
}
chuyen_trang('login.php');
