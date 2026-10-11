<?php
require_once __DIR__ . '/includes/functions.php';

$dsTrang = ds_chinh_sach();
$p = (string)($_GET['p'] ?? 'giao-hang');
if (!isset($dsTrang[$p])) {
    $p = 'giao-hang';
}

// Nội dung tĩnh — sau này có thể chuyển vào bảng CSDL để admin sửa
$noiDung = [
    'giao-hang' => [
        ['Phạm vi & thời gian', 'Giao toàn quốc. Nội thành TP.HCM 1–2 ngày, tỉnh thành khác 3–5 ngày làm việc.'],
        ['Phí vận chuyển', 'Đồng giá 30.000đ/đơn. Dùng mã FREESHIP để được miễn phí vận chuyển.'],
        ['Đồng kiểm', 'Bạn được mở hộp kiểm tra sản phẩm trước khi thanh toán cho shipper.'],
    ],
    'doi-tra' => [
        ['Điều kiện đổi', 'Đổi trong 7 ngày nếu sản phẩm lỗi do sản xuất, sai mẫu hoặc sai size.'],
        ['Vòng tự thiết kế', 'Được đổi vị trí charm miễn phí 1 lần trong 30 ngày. Không hoàn tiền với charm khắc tên.'],
        ['Bảo hành', 'Bảo hành mắt nối và khoá vòng 6 tháng.'],
    ],
    'thanh-toan' => [
        ['Bước 1–3', 'Chọn size tay, chọn dây và bộ sưu tập charm ở trang Tự thiết kế.'],
        ['Bước 4–5', 'Bấm hoặc kéo thả charm vào khung, xem trước và kiểm tra bảng tạm tính.'],
        ['Bước 6', 'Thêm vào giỏ → Thanh toán bằng COD, chuyển khoản hoặc ví điện tử.'],
    ],
    'bao-mat' => [
        ['Thông tin thu thập', 'Họ tên, số điện thoại, địa chỉ và email chỉ dùng để giao hàng và chăm sóc khách hàng.'],
        ['Cam kết', 'Charm Atelier không chia sẻ thông tin của bạn cho bên thứ ba ngoài đơn vị vận chuyển.'],
    ],
    'faq' => [
        ['Size tay của tôi là bao nhiêu?', 'Đo vòng cổ tay bằng thước dây rồi cộng thêm 1 cm. Trang Tự thiết kế sẽ gợi ý số mắt charm phù hợp.'],
        ['Charm có bị đen không?', 'Charm làm từ thép không gỉ 316L nên không đen, không gỉ khi tiếp xúc nước.'],
        ['Tôi muốn khắc chữ riêng?', 'Tải ảnh hoặc ghi chú yêu cầu ở bước thanh toán, shop sẽ liên hệ xác nhận.'],
    ],
];

$tieuDe = $dsTrang[$p];
require __DIR__ . '/includes/header.php';
?>
<main class="page">
  <div class="container">
    <h1 class="page-title page-title--xl"><?= e($dsTrang[$p]) ?></h1>
    <div class="policy">
      <aside class="card">
        <ul class="cat-list">
          <?php foreach ($dsTrang as $slug => $ten): ?>
            <li><a class="<?= $slug === $p ? 'is-active' : '' ?>" href="policy.php?p=<?= $slug ?>"><?= e($ten) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </aside>
      <article class="card policy__body">
        <?php foreach ($noiDung[$p] as [$h, $txt]): ?>
          <h3><?= e($h) ?></h3>
          <p><?= e($txt) ?></p>
        <?php endforeach; ?>
      </article>
    </div>
  </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
