
<?php

require_once __DIR__ . '/functions.php';

function require_post(): void
{
    if (
        ($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' ||
        !csrf_hop_le($_POST['csrf'] ?? null)
    ) {
        http_response_code(403);
        exit('Yêu cầu không hợp lệ (CSRF).');
    }
}

function redirect(string $url): never
{
    chuyen_trang($url);
}

function upload_anh(string $field, string $old = ''): string
{
    if (
        !isset($_FILES[$field]) ||
        $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE
    ) {
        return $old;
    }

    $file = $_FILES[$field];

    if (
        $file['error'] !== UPLOAD_ERR_OK ||
        $file['size'] > 3 * 1024 * 1024
    ) {
        throw new RuntimeException('Ảnh không hợp lệ hoặc vượt quá 3MB.');
    }

    $types = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    $mime = (new finfo(FILEINFO_MIME_TYPE))
        ->file($file['tmp_name']);

    if (
        !isset($types[$mime]) ||
        getimagesize($file['tmp_name']) === false
    ) {
        throw new RuntimeException('Chỉ chấp nhận JPG, PNG, WEBP.');
    }

    $folder = __DIR__ . '/../uploads/';

    if (!is_dir($folder)) {
        mkdir($folder, 0755, true);
    }

    $name = bin2hex(random_bytes(12)) . '.' . $types[$mime];

    if (!move_uploaded_file($file['tmp_name'], $folder . $name)) {
        throw new RuntimeException('Không thể lưu ảnh.');
    }

    return $name;
}