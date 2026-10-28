/* Trang mã giảm giá: đổi đơn vị gợi ý (đ / %) theo loại giảm đang chọn. */
(function () {
  var loai = document.getElementById('loai_giam');
  var unit = document.getElementById('gia-tri-don-vi');
  var gt   = document.getElementById('gia_tri');
  if (!loai || !unit || !gt) return;
  function capNhat() {
    var phanTram = loai.value === 'phan_tram';
    unit.textContent = phanTram ? '(từ 1 đến 100 %)' : '(số tiền, đơn vị đồng)';
    gt.placeholder = phanTram ? 'VD: 15' : 'VD: 20000';
  }
  loai.addEventListener('change', capNhat);
  capNhat();
})();
