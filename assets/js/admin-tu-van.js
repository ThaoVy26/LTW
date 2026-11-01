document.addEventListener("DOMContentLoaded", () => {
  const chat = document.getElementById("tv-chat");
  if (!chat) return;
  const body = document.getElementById("tv-body");
  const form = document.getElementById("tv-form");
  const input = document.getElementById("tv-input");
  const err = document.getElementById("tv-err");
  const API = chat.dataset.api, CSRF = chat.dataset.csrf, KHACH = Number(chat.dataset.khach);
  let lichSu = [];
  try { lichSu = JSON.parse(chat.dataset.lichSu || "[]"); } catch (e) { lichSu = []; }

  let sauId = 0;
  const daHien = new Set();
  let dangGui = false;
  let timer = null;

  function cuonXuong() { body.scrollTop = body.scrollHeight; }

  // dựng bong bóng, nội dung chỉ gán bằng textContent (khách bên trái, shop bên phải)
  function themTin(t) {
    const id = Number(t.id);
    if (daHien.has(id)) return false;
    daHien.add(id);
    if (id > sauId) sauId = id;
    const row = document.createElement("div");
    row.className = "tv-msg tv-msg--" + (t.nguoi_gui === "shop" ? "shop" : "khach");
    const bub = document.createElement("div");
    bub.className = "tv-msg__bub";
    const text = document.createElement("span");
    text.textContent = t.noi_dung;
    const gio = document.createElement("small");
    gio.className = "tv-msg__time";
    gio.textContent = String(t.ngay_gui || "").slice(11, 16);
    bub.append(text, gio);
    row.appendChild(bub);
    body.appendChild(row);
    return true;
  }

  function hienLoi(msg) { err.textContent = msg; err.hidden = !msg; }

  async function lamMoi() {
    try {
      const r = await fetch(API + "?khach=" + KHACH + "&sau=" + sauId, { headers: { Accept: "application/json" } });
      if (!r.ok) return;
      const d = await r.json();
      let moi = false;
      (d.tin || []).forEach((t) => { if (themTin(t)) moi = true; });
      if (moi) cuonXuong();
    } catch (e) { /* mất mạng thì lần sau thử lại */ }
  }

  function batDauLamMoi() {
    clearInterval(timer);
    timer = setInterval(lamMoi, 6000);
  }
  document.addEventListener("visibilitychange", () => {
    if (document.hidden) { clearInterval(timer); timer = null; }
    else { lamMoi(); batDauLamMoi(); }
  });

  // Enter gửi, Shift+Enter xuống dòng
  input.addEventListener("keydown", (e) => {
    if (e.key === "Enter" && !e.shiftKey && !e.isComposing) {
      e.preventDefault();
      form.requestSubmit();
    }
  });

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    const noiDung = input.value.trim();
    if (!noiDung || dangGui) return;
    dangGui = true; hienLoi("");
    try {
      const r = await fetch(API, {
        method: "POST",
        headers: { "Content-Type": "application/json", "X-CSRF-Token": CSRF },
        body: JSON.stringify({ khach: KHACH, noi_dung: noiDung }),
      });
      const d = await r.json().catch(() => ({}));
      if (!r.ok || !d.ok) { hienLoi(d.message || "Không gửi được tin nhắn."); return; }
      themTin(d.tin);
      input.value = ""; cuonXuong();
    } catch (ex) {
      hienLoi("Mất kết nối, vui lòng thử lại.");
    } finally {
      dangGui = false; input.focus();
    }
  });

  lichSu.forEach(themTin);
  cuonXuong();
  batDauLamMoi();
});
