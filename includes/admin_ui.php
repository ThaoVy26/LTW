<?php
declare(strict_types=1);

// vẽ thanh phân trang cho bảng admin
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

// đọc số nguyên từ ô nhập, bỏ dấu chấm dấu phẩy
function ad_so_nguyen(string $s): ?int
{
    $s = str_replace(['.', ',', ' '], '', trim($s));
    return ($s !== '' && ctype_digit($s) && strlen($s) <= 9) ? (int)$s : null;
}

// kiểm tra ngày có đúng dạng năm-tháng-ngày không
function ad_ngay(string $s): ?string
{
    $d = DateTime::createFromFormat('!Y-m-d', $s);
    return ($d && $d->format('Y-m-d') === $s) ? $s : null;
}
