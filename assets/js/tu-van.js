document.addEventListener("DOMContentLoaded", () => {
  const chat = document.getElementById("chat");
  const body = document.getElementById("chat-body");
  const form = document.getElementById("chat-form");
  const input = document.getElementById("chat-input");
  const count = document.getElementById("chat-count");
  const err = document.getElementById("chat-err");
  const API = chat.dataset.api, CSRF = chat.dataset.csrf;
  let lichSu = [];
  try { lichSu = JSON.parse(chat.dataset.lichSu || "[]"); } catch (e) { lichSu = []; }

  let sauId = 0;        // id tin cuối đã hiện
  let daHien = new Set();
  let dangGui = false;
  let timer = null;

  function cuonXuong() { body.scrollTop = body.scrollHeight; }

  // dựng 1 dòng tin nhắn, nội dung chỉ gán bằng textContent
  function dongTin(nguoi, noiDung, gio) {
    const row = document.createElement("div");
    row.className = "msg msg--" + nguoi;
    if (nguoi === "shop") {
      const av = document.createElement("span");
      av.className = "msg__av";
      row.appendChild(av);
    }
    const bub = document.createElement("div");
    bub.className = "msg__bub";
    const text = document.createElement("span");
    text.textContent = noiDung;
    bub.appendChild(text);
    if (gio) {
      const t = document.createElement("small");
      t.className = "msg__time"; t.textContent = gio;
      bub.appendChild(t);
    }
    row.appendChild(bub);
    return row;
  }

  function themTin(t) {
    const id = Number(t.id);
    if (daHien.has(id)) return false;
    daHien.add(id);
    if (id > sauId) sauId = id;
    body.appendChild(dongTin(t.nguoi_gui === "shop" ? "shop" : "khach", t.noi_dung, String(t.ngay_gui || "").slice(11, 16)));
    return true;
  }

  // lời chào của shop: hiện ba chấm khoảng 0,7 giây rồi mới ra chữ
  function loiChao() {
    return new Promise((xong) => {
      const row = document.createElement("div");
      row.className = "msg msg--shop";
      const av = document.createElement("span");
      av.className = "msg__av";
      const dots = document.createElement("div");
      dots.className = "msg__bub msg__bub--typing";
      for (let i = 0; i < 3; i++) dots.appendChild(document.createElement("i"));
      row.append(av, dots);
      body.appendChild(row);
      cuonXuong();
      setTimeout(() => {
        row.replaceWith(dongTin("shop", "Xin chào, bạn cần hỗ trợ gì ạ?", ""));
        cuonXuong();
        xong();
      }, 700);
    });
  }

  function hienLoi(msg) { err.textContent = msg; err.hidden = !msg; }

  // lấy tin mới của shop
  async function lamMoi() {
    try {
      const r = await fetch(API + "?sau=" + sauId, { headers: { Accept: "application/json" } });
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

  // đếm ký tự khi gần đầy, ô nhập tự cao lên theo chữ
  function capNhatO() {
    const n = input.value.length;
    count.hidden = n < 400;
    count.textContent = n + "/500";
    input.style.height = "auto";
    input.style.height = Math.min(input.scrollHeight, 120) + "px";
  }
  input.addEventListener("input", capNhatO);

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
        body: JSON.stringify({ noi_dung: noiDung }),
      });
      const d = await r.json().catch(() => ({}));
      if (!r.ok || !d.ok) { hienLoi(d.message || "Không gửi được tin nhắn, vui lòng thử lại."); return; }
      themTin(d.tin);
      input.value = ""; capNhatO(); cuonXuong();
    } catch (ex) {
      hienLoi("Mất kết nối, vui lòng thử lại.");
    } finally {
      dangGui = false; input.focus();
    }
  });

  // vào trang: lời chào trước, rồi tới lịch sử thật
  loiChao().then(() => {
    lichSu.forEach(themTin);
    cuonXuong();
    batDauLamMoi();
    lamMoi();
  });
});
