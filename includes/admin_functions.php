<?php
declare(strict_types=1);

require_once __DIR__ . '/don_hang.php';

// danh sách trạng thái đơn cho ô lọc
function ds_trang_thai_don(): array
{
    return DON_TRANG_THAI;
}

// tên tiếng việt của trạng thái
function ten_trang_thai_don(?string $ma): string
{
    return ds_trang_thai_don()[$ma] ?? (string)$ma;
}

// class màu của trạng thái
function class_trang_thai_don(?string $ma): string
{
    return 'tt tt--' . preg_replace('/[^a-z_]/', '', (string)$ma);
}
