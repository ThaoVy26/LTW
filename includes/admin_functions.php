<?php
/**
 * Hàm dùng chung cho các trang quản trị (nhãn trạng thái đơn, v.v.).
 */
declare(strict_types=1);

/** Trạng thái đơn hàng: mã lưu trong CSDL => tên hiển thị. */
function ds_trang_thai_don(): array
{
    return [
        'cho_xac_nhan' => 'Chờ xác nhận',
        'dang_giao'    => 'Đang giao',
        'hoan_tat'     => 'Hoàn tất',
        'huy'          => 'Đã huỷ',
    ];
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
