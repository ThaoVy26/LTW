<?php
/**
 * Hàm dùng chung cho các trang quản trị (nhãn trạng thái đơn, v.v.).
 */
declare(strict_types=1);

require_once __DIR__ . '/don_hang.php';   // DON_TRANG_THAI: nguồn duy nhất của danh sách trạng thái đơn

/** Trạng thái đơn hàng: mã lưu trong CSDL => tên hiển thị. */
function ds_trang_thai_don(): array
{
    return DON_TRANG_THAI;
}

function ten_trang_thai_don(?string $ma): string
{
    return ds_trang_thai_don()[$ma] ?? (string)$ma;
}

/** Tên class màu cho nhãn trạng thái (xem admin.css). */
function class_trang_thai_don(?string $ma): string
{
    return 'tt tt--' . preg_replace('/[^a-z_]/', '', (string)$ma);
}
