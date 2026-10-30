/* customizer.js — trang Tự thiết kế
 * Dữ liệu lấy từ thẻ <script id="cust-data"> do PHP in ra (đọc từ CSDL).
 * Tính năng: chọn size/dây/bộ sưu tập, bấm hoặc kéo thả charm vào ô,
 * đổi chỗ giữa các ô, hoàn tác/làm lại, Ngang/Dọc/Xoay 360, lưu & thêm giỏ qua API.
 */
(() => {
  const DATA = JSON.parse(document.getElementById("cust-data").textContent);
  const $ = (id) => document.getElementById(id);
  const tien = (n) => Number(n || 0).toLocaleString("vi-VN") + "đ";
  const esc = (t) => String(t ?? "").replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));

  const charmById = new Map();
  DATA.collections.forEach((c) => c.charms.forEach((ch) => charmById.set(ch.id, { ...ch, bst: c.id })));

  const state = {
    sizeId: DATA.sizes[0].id,
    chainId: DATA.chains[0].id,
    collectionId: DATA.collections[0]?.id,
    slots: Array(DATA.sizes[0].so).fill(null), // mỗi ô: id charm | null (bạc trơn) | NOI (phần sau của charm dài)
    selected: 0,          // ô đang chọn (bấm charm sẽ đặt vào ô này)
    reached: 1,           // bước xa nhất đã làm (cho thanh 5 bước)
    history: [], future: [],
    editKey: null,
  };

  if (DATA.start) state.collectionId = DATA.start;
  if (DATA.edit) {
    state.sizeId = DATA.edit.size_id;
    state.chainId = DATA.edit.chain_id;
    state.slots = DATA.edit.slots.slice();
    state.editKey = DATA.edit.cart_key;
    state.reached = 5;
    const first = state.slots.find((v) => v > 0);
    if (first) state.collectionId = charmById.get(first)?.bst ?? state.collectionId;
  }

  const size = () => DATA.sizes.find((s) => s.id === state.sizeId);
  const chain = () => DATA.chains.find((c) => c.id === state.chainId);
  const collection = () => DATA.collections.find((c) => c.id === state.collectionId);

  /* ---------- Ô & charm dài ---------- */
  const NOI = -1;                                          // ô thứ 2 của charm dài
  const laCharm = (v) => v > 0;
  const laDai = (v) => laCharm(v) && charmById.get(v).kieu === "dai";
  const chu = (i) => (state.slots[i] === NOI ? i - 1 : i); // ô "chủ" của 1 vị trí
  const soCharm = () => state.slots.filter(laCharm).length;
  const soTrong = () => state.slots.filter((v) => v === null).length;

  /** Gỡ charm đang chiếm vị trí i (kể cả khi i là nửa sau của charm dài). Trả về id đã gỡ. */
  function goTai(i) {
    if (i < 0 || i >= state.slots.length || state.slots[i] === null) return null;
    const c = chu(i), id = state.slots[c];
    state.slots[c] = null;
    if (laDai(id) && state.slots[c + 1] === NOI) state.slots[c + 1] = null;
    return id;
  }
  /** Ô i có đặt được charm id không (các ô cần dùng đang trống). */
  const vuaCho = (id, i) => state.slots[i] === null && (!laDai(id) || state.slots[i + 1] === null);
  /** Đặt charm vào i (giả định đã trống). */
  function datTai(id, i) {
    state.slots[i] = id;
    if (laDai(id)) state.slots[i + 1] = NOI;
  }
  /** Hình charm: ảnh thật nếu có, không thì emoji/chữ. */
  // mắt xích trống: ảnh bạc, hoặc ảnh hồng khi chọn dây Trơn Hồng
  const matBac = () => `<img class="slot__base" src="assets/img/link-${chain().ten.toLowerCase().includes("hồng") ? "hong" : "bac"}.webp?v=2" alt="" draggable="false">`;
  const hinh = (ch) => (ch.anh ? `<img src="${esc(ch.anh)}" alt="" draggable="false">` : `${matBac()}<span class="slot__face">${ch.icon}</span>`);

  /* ---------- Thông báo ---------- */
  let msgTimer;
  function msg(text, type = "ok", html = false) {
    const el = $("msg");
    el[html ? "innerHTML" : "textContent"] = text;
    el.className = "cust-msg is-" + type;
    clearTimeout(msgTimer);
    if (!html) msgTimer = setTimeout(() => (el.textContent = ""), 4000);
  }

  /* ---------- Lịch sử (hoàn tác / làm lại) ---------- */
  const snap = () => JSON.stringify({ sizeId: state.sizeId, slots: state.slots });
  function remember() { state.history.push(snap()); state.future = []; if (state.history.length > 50) state.history.shift(); }
  function restore(json) { const s = JSON.parse(json); state.sizeId = s.sizeId; state.slots = s.slots; $("size").value = s.sizeId; }
  function undo() {
    if (!state.history.length) return msg("Không còn gì để hoàn tác.", "warn");
    state.future.push(snap()); restore(state.history.pop()); renderAll(); msg("Đã hoàn tác.");
  }
  function redo() {
    if (!state.future.length) return msg("Không còn gì để làm lại.", "warn");
    state.history.push(snap()); restore(state.future.pop()); renderAll(); msg("Đã làm lại.");
  }

  function reach(step) { state.reached = Math.max(state.reached, step); renderSteps(); }

  /* ---------- Render ---------- */
  function renderSteps() {
    document.querySelectorAll("#steps li").forEach((li) => {
      const n = +li.dataset.step;
      li.classList.toggle("is-active", n === Math.min(state.reached, 5));
      li.classList.toggle("is-done", n < state.reached);
    });
  }

  function renderSize() {
    $("size").innerHTML = DATA.sizes.map((s) => `<option value="${s.id}">${esc(s.ten)}</option>`).join("");
    $("size").value = state.sizeId;
  }

  function renderChains() {
    $("chains").innerHTML = DATA.chains.map((c) => `
      <button type="button" role="radio" aria-checked="${c.id === state.chainId}" aria-label="${esc(c.ten)}" title="${esc(c.ten)}"
        class="chain-dot${c.id === state.chainId ? " is-active" : ""}" data-id="${c.id}" style="background:${c.mau}"></button>`).join("");
    $("chain-price").textContent = tien(chain().gia);
    $("chain-name").textContent = "Đang chọn: " + chain().ten;
    $("bracelet").style.setProperty("--chain", chain().mau);
    const ten = chain().ten.toLowerCase();
    $("bracelet").style.setProperty("--link-filter",
      ten.includes("vàng") ? "sepia(.9) saturate(2.6) hue-rotate(-12deg) brightness(1.03)" :
      ten.includes("đen") ? "grayscale(1) brightness(.38) contrast(1.6)" :
      ten.includes("trắng") ? "grayscale(1) brightness(1.16) contrast(.85)" : "none");
  }

  function renderCollections() {
    const nhom = [...new Set(DATA.collections.map((c) => c.nhom))];
    $("collection").innerHTML = nhom.map((n) => `<optgroup label="${esc(n)}">` +
      DATA.collections.filter((c) => c.nhom === n)
        .map((c) => `<option value="${c.id}">${esc(c.ten)} (${c.charms.length})</option>`).join("") + "</optgroup>").join("");
    $("collection").value = state.collectionId;
  }

  function renderCharmGrid() {
    const col = collection();
    const nhanKieu = { dai: "Dài · 2 ô", day: "Dây treo" };
    $("charm-grid").innerHTML = col.charms.map((ch, i) => `
      <button type="button" class="ctile ctile--${ch.kieu}${ch.anh ? " has-img" : ""}" draggable="true" data-id="${ch.id}" title="${esc(ch.ten)} · ${tien(ch.gia)}">
        <span class="ctile__link">${ch.anh ? `<img src="${esc(ch.anh)}" alt="" draggable="false">` : `<span class="ctile__face">${ch.icon}</span>`}</span>
        <span class="ctile__num">${nhanKieu[ch.kieu] || i + 1}</span>
        <span class="ctile__price">${tien(ch.gia)}</span>
      </button>`).join("");
  }

  function renderBracelet() {
    const n = state.slots.length;
    const b = $("bracelet");
    b.style.setProperty("--n", n);
    const sel = chu(state.selected);
    b.innerHTML = state.slots.map((id, i) => {
      if (id === NOI) {   // nửa sau của charm dài: chỉ giữ chỗ
        return `<div class="slot is-cont${sel === i - 1 ? " is-selected" : ""}" data-i="${i}" title="${esc(charmById.get(state.slots[i - 1]).ten)}">
            <span class="slot__link"></span><span class="slot__num">${i + 1}</span></div>`;
      }
      const ch = laCharm(id) ? charmById.get(id) : null;
      const cls = ch ? ` is-filled kieu-${ch.kieu}${ch.anh ? " has-img" : ""}` : "";
      return `<div class="slot${cls}${i === sel ? " is-selected" : ""}" data-i="${i}"
                  ${ch ? 'draggable="true"' : ""} title="${ch ? esc(ch.ten) : "Ô trống " + (i + 1)}">
          <span class="slot__link">${ch ? hinh(ch) : matBac()}</span>
          ${ch ? `<button type="button" class="slot__x" data-remove="${i}" aria-label="Gỡ charm">×</button>` : ""}
          <span class="slot__num">${i + 1}</span>
        </div>`;
    }).join("");
    b.classList.toggle("has-day", state.slots.some((v) => laCharm(v) && charmById.get(v).kieu === "day"));
    $("filled").textContent = soCharm();
    $("total").textContent = n;
    $("remain").textContent = soTrong();
    fitVertical();
    $("size-hint").textContent = "Gợi ý: " + size().so + " charm";
  }

  function renderPrice() {
    const n = state.slots.length, c = chain();
    const used = state.slots.filter(laCharm).map((id) => charmById.get(id));
    const chainCost = n * c.gia, charmCost = used.reduce((s, ch) => s + ch.gia, 0);
    $("chain-line").textContent = `Dây (${n} × ${tien(c.gia)})`;
    $("chain-cost").textContent = tien(chainCost);
    $("charm-line").textContent = `Charm × ${used.length}`;
    $("charm-cost").textContent = tien(charmCost);
    $("total-price").textContent = tien(chainCost + charmCost);
  }

  function renderAll() { renderBracelet(); renderPrice(); renderSteps(); }

  /* ---------- Thao tác với ô ---------- */
  /** Tìm chỗ trống phù hợp: ưu tiên ô đang chọn, rồi ô trống đầu tiên. */
  function oTrongCho(id) {
    if (vuaCho(id, state.selected)) return state.selected;
    return state.slots.findIndex((_, i) => vuaCho(id, i));
  }

  function placeCharm(charmId, index) {
    const n = state.slots.length;
    let i = index != null ? chu(index) : oTrongCho(charmId);
    if (i === -1) {
      return msg(laDai(charmId) ? "Không còn 2 ô trống liền nhau cho charm dài. Gỡ bớt charm hoặc kéo thả vào vị trí muốn thay."
                                : "Khung đã đầy! Gỡ bớt charm hoặc kéo thả để thay.", "warn");
    }
    if (laDai(charmId) && i >= n - 1) i = n - 2;   // charm dài thả vào ô cuối → lùi 1 ô
    remember();
    goTai(i);
    if (laDai(charmId)) goTai(i + 1);
    datTai(charmId, i);
    const next = state.slots.indexOf(null, i + 1);
    state.selected = next === -1 ? i : next;
    reach(5);
    renderAll();
    msg(`Đã đặt "${charmById.get(charmId).ten}" vào ô ${i + 1}${laDai(charmId) ? "–" + (i + 2) : ""}.`);
  }

  function removeCharm(i) {
    if (state.slots[i] == null) return;
    remember();
    const c = chu(i), id = goTai(i);
    state.selected = c;
    renderAll();
    msg(`Đã gỡ "${charmById.get(id).ten}" khỏi ô ${c + 1}.`);
  }

  /** Kéo charm sang ô khác: charm ở đích (nếu có) chuyển về chỗ cũ nếu vừa. */
  function moveCharm(from, to) {
    from = chu(from); to = chu(to);
    if (from === to) return;
    const truoc = snap();
    const backup = state.slots.slice();
    const id = goTai(from);
    if (laDai(id) && to >= state.slots.length - 1) to = state.slots.length - 2;
    const biThay = [goTai(to), laDai(id) ? goTai(to + 1) : null].filter(Boolean);
    datTai(id, to);
    const khongVua = biThay.filter((x) => { if (vuaCho(x, from)) { datTai(x, from); return false; } return true; });
    if (khongVua.length && !confirm(`Không đủ chỗ để đổi vị trí, ${khongVua.length} charm sẽ bị gỡ. Tiếp tục?`)) {
      state.slots = backup; renderAll(); return;
    }
    state.history.push(truoc); state.future = [];
    state.selected = to;
    renderAll();
  }

  function changeSize(newId) {
    const s = DATA.sizes.find((x) => x.id === newId);
    const lost = state.slots.slice(s.so).filter(laCharm).length + (laDai(state.slots[s.so - 1]) ? 1 : 0);
    if (lost && !confirm(`Size mới chỉ có ${s.so} ô, ${lost} charm ở cuối sẽ bị gỡ. Tiếp tục?`)) {
      $("size").value = state.sizeId; return;
    }
    remember();
    state.sizeId = newId;
    state.slots = Array.from({ length: s.so }, (_, i) => state.slots[i] ?? null);
    if (laDai(state.slots[s.so - 1])) state.slots[s.so - 1] = null; // charm dài bị cắt đôi
    state.selected = Math.min(state.selected, s.so - 1);
    reach(2);
    renderAll();
    msg(`Khung đã đổi thành ${s.so} ô theo size ${s.ten}.`);
  }

  /* ---------- Gọi API lưu / thêm giỏ ---------- */
  async function send(action) {
    if (action === "cart" && !state.slots.some(laCharm)) return msg("Bạn chưa chọn charm nào!", "err");
    const btn = $(action === "cart" ? "add-cart" : "save");
    btn.disabled = true;
    try {
      const res = await fetch("api/design.php", {
        method: "POST",
        headers: { "Content-Type": "application/json", "X-CSRF-Token": DATA.csrf },
        body: JSON.stringify({ action, size_id: state.sizeId, chain_id: state.chainId, slots: state.slots, replace_key: state.editKey }),
      });
      const data = await res.json();
      if (!data.ok) return msg(data.message, "err");
      if (action === "cart") {
        state.editKey = "tk_" + data.id;
        const cnt = $("cart-count"); if (cnt) cnt.textContent = data.cart_count;
        msg(`${esc(data.message)} <a href="cart.php">Xem giỏ hàng →</a>`, "ok", true);
      } else {
        msg(esc(data.message));
      }
    } catch (e) {
      msg("Không kết nối được máy chủ, vui lòng thử lại.", "err");
    } finally {
      btn.disabled = false;
    }
  }

  /* ---------- Sự kiện ---------- */
  $("size").addEventListener("change", (e) => changeSize(+e.target.value));

  $("chains").addEventListener("click", (e) => {
    const d = e.target.closest("[data-id]"); if (!d) return;
    state.chainId = +d.dataset.id; reach(3); renderChains(); renderBracelet(); renderPrice();
  });

  $("collection").addEventListener("change", (e) => {
    state.collectionId = +e.target.value; reach(4); renderCharmGrid();
  });

  // Bấm charm trong lưới
  $("charm-grid").addEventListener("click", (e) => {
    const t = e.target.closest(".ctile"); if (t) placeCharm(+t.dataset.id);
  });

  // Bấm ô: chọn ô / gỡ charm
  $("bracelet").addEventListener("click", (e) => {
    const x = e.target.closest("[data-remove]");
    if (x) return removeCharm(+x.dataset.remove);
    const s = e.target.closest(".slot"); if (!s) return;
    state.selected = chu(+s.dataset.i); renderBracelet();
    if (state.slots[state.selected] === null) msg(`Đã chọn ô ${state.selected + 1} — bấm 1 charm bên trái để đặt vào.`, "info");
  });

  // Kéo thả (HTML5 Drag & Drop)
  let drag = null; // {type:'charm', id} | {type:'slot', i}
  document.addEventListener("dragstart", (e) => {
    const tile = e.target.closest?.(".ctile");
    const slot = e.target.closest?.(".slot.is-filled");
    const cont = e.target.closest?.(".slot.is-cont");
    if (tile) drag = { type: "charm", id: +tile.dataset.id };
    else if (slot || cont) { drag = { type: "slot", i: chu(+(slot || cont).dataset.i), dropped: false }; (slot || cont).classList.add("is-dragging"); }
    else return;
    e.dataTransfer.effectAllowed = "move";
    e.dataTransfer.setData("text/plain", "charm");
  });
  document.addEventListener("dragend", () => {
    // Kéo charm từ vòng rồi thả ra ngoài (không vào ô nào) → gỡ charm
    if (drag?.type === "slot" && !drag.dropped && !$("stage").matches(":hover")) removeCharm(drag.i);
    drag = null;
    document.querySelectorAll(".slot.is-over, .slot.is-dragging").forEach((s) => s.classList.remove("is-over", "is-dragging"));
  });
  $("bracelet").addEventListener("dragover", (e) => {
    const s = e.target.closest(".slot"); if (!s || !drag) return;
    e.preventDefault();
    document.querySelectorAll(".slot.is-over").forEach((x) => x !== s && x.classList.remove("is-over"));
    s.classList.add("is-over");
  });
  $("bracelet").addEventListener("drop", (e) => {
    const s = e.target.closest(".slot"); if (!s || !drag) return;
    e.preventDefault();
    drag.dropped = true;
    const to = +s.dataset.i;
    if (drag.type === "charm") placeCharm(drag.id, to);
    else moveCharm(drag.i, to);
  });
  // Kéo charm từ khung ra lưới = gỡ charm
  $("charm-grid").addEventListener("dragover", (e) => { if (drag?.type === "slot") e.preventDefault(); });
  $("charm-grid").addEventListener("drop", (e) => { if (drag?.type === "slot") { e.preventDefault(); drag.dropped = true; removeCharm(drag.i); } });

  // Kéo đổi chỗ bằng tay trên điện thoại (kéo thả HTML5 không chạy trên màn hình cảm ứng)
  let cham = null; // {from, ghost, x0, y0, started}
  $("bracelet").addEventListener("pointerdown", (e) => {
    if (e.pointerType === "mouse") return;
    const s = e.target.closest(".slot.is-filled, .slot.is-cont");
    if (!s || e.target.closest("[data-remove]")) return;
    cham = { from: chu(+s.dataset.i), x0: e.clientX, y0: e.clientY, started: false, el: s };
  });
  document.addEventListener("pointermove", (e) => {
    if (!cham) return;
    if (!cham.started) {
      if (Math.hypot(e.clientX - cham.x0, e.clientY - cham.y0) < 8) return;
      cham.started = true;
      const img = cham.el.querySelector(".slot__link img:not(.slot__base)") || cham.el.querySelector("img");
      cham.ghost = img.cloneNode();
      cham.ghost.className = "drag-ghost";
      cham.ghost.style.width = cham.el.offsetWidth * 1.2 + "px";
      document.body.appendChild(cham.ghost);
      cham.el.classList.add("is-dragging");
    }
    e.preventDefault();
    cham.ghost.style.transform = `translate(${e.clientX}px, ${e.clientY}px) translate(-50%, -60%)`;
    const over = document.elementFromPoint(e.clientX, e.clientY)?.closest(".slot");
    document.querySelectorAll(".slot.is-over").forEach((x) => x !== over && x.classList.remove("is-over"));
    over?.classList.add("is-over");
  }, { passive: false });
  document.addEventListener("pointerup", (e) => {
    if (!cham) return;
    const c = cham; cham = null;
    if (!c.started) return;
    c.ghost.remove();
    document.querySelectorAll(".slot.is-over, .slot.is-dragging").forEach((x) => x.classList.remove("is-over", "is-dragging"));
    const over = document.elementFromPoint(e.clientX, e.clientY);
    const slot = over?.closest(".slot");
    if (slot) moveCharm(c.from, +slot.dataset.i);
    else if (!over?.closest("#stage")) removeCharm(c.from);
  });
  document.addEventListener("pointercancel", () => { if (cham?.ghost) cham.ghost.remove(); cham = null; });

  // Ngang / Dọc / Xoay 360
  document.querySelectorAll(".seg button").forEach((b) => b.addEventListener("click", () => {
    const mode = b.dataset.mode, stage = $("stage");
    if (mode === "xoay") return open360();
    document.querySelectorAll(".seg button").forEach((x) => x.classList.toggle("is-active", x === b));
    stage.classList.toggle("is-vertical", mode === "doc");
    fitVertical();
  }));

  /** Chế độ Dọc: xoay cả vòng 90°, tăng chiều cao khung cho vừa. */
  function fitVertical() {
    const stage = $("stage"), b = $("bracelet");
    stage.style.minHeight = stage.classList.contains("is-vertical") ? b.offsetWidth + 80 + "px" : "";
  }

  $("undo").addEventListener("click", undo);
  $("redo").addEventListener("click", redo);
  $("clear").addEventListener("click", () => {
    if (!state.slots.some(laCharm)) return msg("Khung đang trống.", "warn");
    if (!confirm("Gỡ tất cả charm khỏi vòng?")) return;
    remember(); state.slots = state.slots.map(() => null); state.selected = 0; renderAll(); msg("Đã xoá hết charm.");
  });
  $("save").addEventListener("click", () => send("save"));
  $("add-cart").addEventListener("click", () => send("cart"));

  // Thanh bước: bấm để cuộn tới khối tương ứng
  $("steps").addEventListener("click", (e) => {
    const li = e.target.closest("li"); if (!li) return;
    $("block-" + li.dataset.step)?.scrollIntoView({ behavior: "smooth", block: "center" });
  });

  // Phím tắt
  document.addEventListener("keydown", (e) => {
    if (["INPUT", "SELECT", "TEXTAREA"].includes(document.activeElement.tagName)) return;
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === "z") { e.preventDefault(); e.shiftKey ? redo() : undo(); }
    else if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === "y") { e.preventDefault(); redo(); }
    else if (e.key === "Delete" || e.key === "Backspace") removeCharm(state.selected);
    else if (e.key === "ArrowRight") { state.selected = Math.min(state.slots.length - 1, state.selected + (laDai(state.slots[state.selected]) ? 2 : 1)); renderBracelet(); }
    else if (e.key === "ArrowLeft") { state.selected = Math.max(0, state.selected - 1); renderBracelet(); }
  });

  /* ---------- Xem vòng 360° (3D, tự xoay, kéo để xoay tay) ---------- */
  const v360 = { raf: 0, ang: 0, tilt: -22, auto: true, drag: null, last: 0, gl: null };
  const scripts = {};
  const loadScript = (src) => (scripts[src] ||= new Promise((ok, fail) => {
    const s = document.createElement("script"); s.src = src; s.onload = ok; s.onerror = fail; document.head.appendChild(s);
  }));

  function items3d() {
    return state.slots.map((v, i) => {
      if (v === NOI) { const ch = charmById.get(state.slots[i - 1]); return ch.anh ? { type: "img", url: ch.anh, kind: "dai", part: 1 } : { type: "empty" }; }
      const ch = laCharm(v) ? charmById.get(v) : null;
      if (!ch) return { type: "empty" };
      return ch.anh ? { type: "img", url: ch.anh, kind: ch.kieu, part: 0 } : { type: "icon", icon: ch.icon };
    });
  }
  function tint3d() {
    const t = chain().ten.toLowerCase();
    return t.includes("vàng") ? "vang" : t.includes("đen") ? "den" : t.includes("hồng") ? "hong" : t.includes("trắng") ? "trang" : "bac";
  }

  function tick360(t) {
    if (v360.auto && !v360.drag) v360.ang -= ((t - (v360.last || t)) / 1000) * 28;   // ~28°/giây
    v360.last = t;
    v360.gl.pose(v360.ang, v360.tilt); v360.gl.render();
    v360.raf = requestAnimationFrame(tick360);
  }

  function setAuto(on) {
    v360.auto = on;
    $("v360-toggle").textContent = on ? "⏸ Dừng xoay" : "▶ Tiếp tục xoay";
  }

  async function open360() {
    if (!state.slots.some(laCharm)) return msg("Bạn chưa chọn charm nào để xem!", "warn");
    $("v360").hidden = false;
    document.body.classList.add("v360-open");
    try {
      await loadScript("assets/js/vendor/three.min.js");
      await loadScript("assets/js/ring3d.js?v=9");
      v360.gl = window.Ring3D.create($("v360-scene"), { items: items3d(), tint: tint3d() });
    } catch (err) {
      close360();
      return msg("Trình duyệt không hiển thị được vòng 3D.", "warn");
    }
    v360.ang = 0; v360.tilt = -22; v360.last = 0;
    setAuto(!matchMedia("(prefers-reduced-motion: reduce)").matches);
    cancelAnimationFrame(v360.raf);
    v360.raf = requestAnimationFrame(tick360);
    $("v360-close").focus();
  }

  function close360() {
    $("v360").hidden = true;
    document.body.classList.remove("v360-open");
    cancelAnimationFrame(v360.raf);
    if (v360.gl) { v360.gl.dispose(); v360.gl = null; }
  }

  $("v360-close").addEventListener("click", close360);
  $("v360-toggle").addEventListener("click", () => setAuto(!v360.auto));
  $("v360").addEventListener("click", (e) => { if (e.target === $("v360")) close360(); });
  document.addEventListener("keydown", (e) => { if (e.key === "Escape" && !$("v360").hidden) close360(); });
  $("v360-scene").addEventListener("pointerdown", (e) => { v360.drag = { x: e.clientX, y: e.clientY }; $("v360-scene").setPointerCapture(e.pointerId); });
  $("v360-scene").addEventListener("pointermove", (e) => {
    if (!v360.drag) return;
    v360.ang += (e.clientX - v360.drag.x) * 0.6;
    v360.tilt = Math.max(-70, Math.min(0, v360.tilt + (e.clientY - v360.drag.y) * 0.3));
    v360.drag = { x: e.clientX, y: e.clientY };
  });
  const endDrag = () => { v360.drag = null; };
  $("v360-scene").addEventListener("pointerup", endDrag);
  $("v360-scene").addEventListener("pointercancel", endDrag);

  /* ---------- Khởi tạo ---------- */
  renderSize(); renderChains(); renderCollections(); renderCharmGrid(); renderAll();
})();
