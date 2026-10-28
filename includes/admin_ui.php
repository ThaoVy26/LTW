<?php
/**
 * Thành phần giao diện dùng chung cho các trang danh sách trong admin (Mỹ Anh).
 * Cần include SAU includes/functions.php (dùng e()).
 */
declare(strict_types=1);

/**
 * Thanh phân trang. $url là hàm nhận số trang và trả về URL (đã giữ nguyên bộ lọc).
 * Trả về chuỗi rỗng khi chỉ có 1 trang. Class .dh-pagination nằm trong assets/css/admin-don-hang.css.
 */
function ad_phan_trang(int $trang, int $soTrang, callable $url): string
{
    if ($soTrang <= 1) {
        return '';
    }
    $h = '<nav class="dh-pagination" aria-label="Phân trang">';
    $h .= $trang > 1
        ? '<a href="' . e($url($trang - 1)) . '">‹</a>'
        : '<span class="is-disabled">‹</span>';
    for ($i = 1; $i <= $soTrang; $i++) {
        if ($i === 1 || $i === $soTrang || abs($i - $trang) <= 2) {
            $h .= '<a class="' . ($i === $trang ? 'is-active' : '') . '" href="' . e($url($i)) . '">' . $i . '</a>';
        } elseif (abs($i - $trang) === 3) {
            $h .= '<span>…</span>';
        }
    }
    $h .= $trang < $soTrang
        ? '<a href="' . e($url($trang + 1)) . '">›</a>'
        : '<span class="is-disabled">›</span>';
    return $h . '</nav>';
}

/** Số nguyên dương từ chuỗi người dùng nhập ("10.000", "10,000", "10000"); không hợp lệ thì null. */
function ad_so_nguyen(string $s): ?int
{
    $s = str_replace(['.', ',', ' '], '', trim($s));
    return ($s !== '' && ctype_digit($s) && strlen($s) <= 9) ? (int)$s : null;
}

/** Ngày dạng Y-m-d hợp lệ thì trả lại chính chuỗi đó, sai thì null. */
function ad_ngay(string $s): ?string
{
    $d = DateTime::createFromFormat('!Y-m-d', $s);
    return ($d && $d->format('Y-m-d') === $s) ? $s : null;
}
