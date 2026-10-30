document.addEventListener("DOMContentLoaded", () => {
  // nút mở menu trên điện thoại
  const toggle = document.getElementById("nav-toggle");
  const nav = document.getElementById("nav");
  toggle?.addEventListener("click", () => {
    const open = nav.classList.toggle("is-open");
    toggle.setAttribute("aria-expanded", open);
  });

  // nút + - chỉnh số lượng
  document.querySelectorAll("[data-qty]").forEach((box) => {
    const input = box.querySelector("input");
    box.addEventListener("click", (e) => {
      const b = e.target.closest("[data-step]"); if (!b) return;
      const max = +input.max || 99;
      input.value = Math.min(max, Math.max(1, (+input.value || 1) + +b.dataset.step));
    });
  });

  // thông báo tự mờ đi sau 4 giây
  document.querySelectorAll(".flash-wrap .flash").forEach((f) =>
    setTimeout(() => { f.style.opacity = 0; setTimeout(() => f.remove(), 300); }, 4000));
});

// hiện thông báo nhỏ ở góc màn hình
function toast(text, type = "ok") {
  const t = document.createElement("div");
  t.className = "toast toast--" + type;
  t.textContent = text;
  document.body.appendChild(t);
  requestAnimationFrame(() => t.classList.add("is-show"));
  setTimeout(() => { t.classList.remove("is-show"); setTimeout(() => t.remove(), 300); }, 2600);
}
