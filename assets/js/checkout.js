document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("checkout-form");
  // luật kiểm tra từng ô trước khi gửi form
  const rules = {
    ho_ten: (v) => v.trim().length >= 2 || "Vui lòng nhập họ và tên.",
    sdt: (v) => /^0\d{9}$/.test(v.trim()) || "Số điện thoại gồm 10 số, bắt đầu bằng 0.",
    email: (v) => !v.trim() || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v.trim()) || "Email không hợp lệ.",
    dia_chi: (v) => v.trim().length >= 5 || "Vui lòng nhập địa chỉ nhận hàng.",
  };

  // kiểm tra 1 ô, sai thì hiện lỗi ngay dưới ô
  function check(input) {
    const rule = rules[input.name]; if (!rule) return true;
    const result = rule(input.value);
    const field = input.closest(".field");
    let err = field.querySelector(".field-error");
    if (result === true) { field.classList.remove("has-error"); err?.remove(); return true; }
    if (!err) { err = document.createElement("div"); err.className = "field-error"; field.appendChild(err); }
    err.textContent = result; field.classList.add("has-error");
    return false;
  }

  Object.keys(rules).forEach((name) => form[name].addEventListener("blur", (e) => check(e.target)));
  // còn ô sai thì chặn không cho gửi
  form.addEventListener("submit", (e) => {
    const ok = Object.keys(rules).map((n) => check(form[n])).every(Boolean);
    if (!ok) { e.preventDefault(); form.querySelector(".has-error input")?.focus(); }
  });

  const box = document.getElementById("upload"), file = document.getElementById("anh");
  const text = document.getElementById("upload-text"), img = document.getElementById("upload-preview");
  // xem trước ảnh khách đính kèm
  function preview() {
    const f = file.files[0]; if (!f) return;
    if (!f.type.startsWith("image/")) { text.textContent = "Vui lòng chọn file ảnh."; return; }
    text.textContent = f.name + " · " + Math.round(f.size / 1024) + " KB";
    img.src = URL.createObjectURL(f); img.hidden = false;
  }
  file.addEventListener("change", preview);
  // kéo thả ảnh vào khung upload
  box.addEventListener("dragover", (e) => { e.preventDefault(); box.classList.add("is-over"); });
  box.addEventListener("dragleave", () => box.classList.remove("is-over"));
  box.addEventListener("drop", (e) => {
    e.preventDefault(); box.classList.remove("is-over");
    file.files = e.dataTransfer.files; preview();
  });
});
