/* shop.js — trang Sản phẩm: tự submit bộ lọc, thanh giá, thêm giỏ bằng fetch, bộ lọc thu gọn trên điện thoại */
document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("filter-form");
  const range = document.getElementById("gia-max");
  const giaText = document.getElementById("gia-text");
  const tien = (n) => Number(n).toLocaleString("vi-VN") + "đ";

  // Điện thoại / máy tính bảng: bộ lọc thu gọn sẵn để thấy sản phẩm ngay
  const hop = document.getElementById("filter-toggle");
  if (hop && window.matchMedia("(max-width: 960px)").matches) hop.removeAttribute("open");

  // Đổi sắp xếp → lọc lại ngay
  form.querySelectorAll("[data-autosubmit]").forEach((el) => el.addEventListener("change", () => form.submit()));

  // Kéo thanh giá: cập nhật chữ ngay, thả tay mới lọc
  range.addEventListener("input", () => {
    giaText.textContent = "0đ – " + tien(range.value);
    range.style.setProperty("--p", (range.value / range.max) * 100 + "%");
  });
  range.style.setProperty("--p", (range.value / range.max) * 100 + "%");
  range.addEventListener("change", () => {
    form.querySelector("[name=page]")?.remove();
    form.submit();
  });

  // Thêm giỏ không tải lại trang
  const addForm = document.getElementById("add-form");
  document.querySelectorAll(".js-add").forEach((btn) => btn.addEventListener("click", async () => {
    addForm.elements.id.value = btn.dataset.id;
    btn.disabled = true;
    try {
      const res = await fetch(addForm.getAttribute("action"), {
        method: "POST", body: new FormData(addForm), headers: { "X-Requested-With": "XMLHttpRequest" },
      });
      const data = await res.json();
      toast(data.message, data.ok ? "ok" : "err");
      if (data.ok) {
        document.getElementById("cart-count").textContent = data.cart_count;
        btn.textContent = "Đã thêm ✓";
        setTimeout(() => (btn.textContent = "Thêm giỏ"), 1200);
      }
    } catch (e) {
      addForm.submit(); // lỗi mạng → gửi form bình thường
    } finally {
      btn.disabled = false;
    }
  }));

});
