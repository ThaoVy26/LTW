(() => {
  // dữ liệu php in ra thẻ script (size, dây, bộ sưu tập, charm)
  const DATA = JSON.parse(document.getElementById("cust-data").textContent);
  const $ = (id) => document.getElementById(id);
  const tien = (n) => Number(n || 0).toLocaleString("vi-VN") + "đ";
  const esc = (t) => String(t ?? "").replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));

  // tra charm theo id cho nhanh
  const charmById = new Map();
  DATA.collections.forEach((c) => c.charms.forEach((ch) => charmById.set(ch.id, { ...ch, bst: c.id })));

  // trạng thái hiện tại của vòng đang thiết kế
  const state = {
    sizeId: DATA.sizes[0].id,
    chainId: DATA.chains[0].id,
    collectionId: DATA.collections[0]?.id,
    slots: Array(DATA.sizes[0].so).fill(null),
    selected: 0,
    reached: 1,
    history: [], future: [],
    editKey: null,
  };

  if (DATA.start) state.collectionId = DATA.start;
  // mở lại thiết kế đã lưu thì nạp lại các ô
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

  // -1 là ô thứ 2 của charm dài
  const NOI = -1;
  const laCharm = (v) => v > 0;
  const laDai = (v) => laCharm(v) && charmById.get(v).kieu === "dai";
  const chu = (i) => (state.slots[i] === NOI ? i - 1 : i);
  const soCharm = () => state.slots.filter(laCharm).length;
  const soTrong = () => state.slots.filter((v) => v === null).length;

  // gỡ charm khỏi ô (charm dài thì gỡ cả 2 ô)
  function goTai(i) {
    if (i < 0 || i >= state.slots.length || state.slots[i] === null) return null;
    const c = chu(i), id = state.slots[c];
    state.slots[c] = null;
    if (laDai(id) && state.slots[c + 1] === NOI) state.slots[c + 1] = null;
    return id;
  }
  const vuaCho = (id, i) => state.slots[i] === null && (!laDai(id) || state.slots[i + 1] === null);
  // đặt charm vào ô (charm dài chiếm thêm ô kế bên)
  function datTai(id, i) {
    state.slots[i] = id;
    if (laDai(id)) state.slots[i + 1] = NOI;
  }
  const matBac = () => `<img class="slot__base" src="assets/img/link-${chain().ten.toLowerCase().includes("hồng") ? "hong" : "bac"}.webp?v=2" alt="" draggable="false">`;
  const hinh = (ch) => (ch.anh ? `<img src="${esc(ch.anh)}" alt="" draggable="false">` : `${matBac()}<span class="slot__face">${ch.icon}</span>`);

  let msgTimer;
  // hiện thông báo nhỏ dưới bảng tạm tính
  function msg(text, type = "ok", html = false) {
    const el = $("msg");
    el[html ? "innerHTML" : "textContent"] = text;
    el.className = "cust-msg is-" + type;
    clearTimeout(msgTimer);
    if (!html) msgTimer = setTimeout(() => (el.textContent = ""), 4000);
  }

  const snap = () => JSON.stringify({ sizeId: state.sizeId, slots: state.slots });
  // nhớ trạng thái hiện tại để hoàn tác
  function remember() { state.history.push(snap()); state.future = []; if (state.history.length > 50) state.history.shift(); }
  // quay về trạng thái đã nhớ
  function restore(json) { const s = JSON.parse(json); state.sizeId = s.sizeId; state.slots = s.slots; $("size").value = s.sizeId; }
  // hoàn tác
  function undo() {
    if (!state.history.length) return msg("Không còn gì để hoàn tác.", "warn");
    state.future.push(snap()); restore(state.history.pop()); renderAll(); msg("Đã hoàn tác.");
  }
  // làm lại
  function redo() {
    if (!state.future.length) return msg("Không còn gì để làm lại.", "warn");
    state.history.push(snap()); restore(state.future.pop()); renderAll(); msg("Đã làm lại.");
  }

  // ghi nhận bước đã làm cho thanh 5 bước
  function reach(step) { state.reached = Math.max(state.reached, step); renderSteps(); }

  // vẽ thanh 5 bước
  function renderSteps() {
    document.querySelectorAll("#steps li").forEach((li) => {
      const n = +li.dataset.step;
      li.classList.toggle("is-active", n === Math.min(state.reached, 5));
      li.classList.toggle("is-done", n < state.reached);
    });
  }

  // vẽ ô chọn size
  function renderSize() {
    $("size").innerHTML = DATA.sizes.map((s) => `<option value="${s.id}">${esc(s.ten)}</option>`).join("");
    $("size").value = state.sizeId;
  }

  // vẽ các chấm màu dây, đổi màu mắt xích theo dây
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

  // vẽ ô chọn bộ sưu tập
  function renderCollections() {
    const nhom = [...new Set(DATA.collections.map((c) => c.nhom))];
    $("collection").innerHTML = nhom.map((n) => `<optgroup label="${esc(n)}">` +
      DATA.collections.filter((c) => c.nhom === n)
        .map((c) => `<option value="${c.id}">${esc(c.ten)} (${c.charms.length})</option>`).join("") + "</optgroup>").join("");
    $("collection").value = state.collectionId;
  }

  // vẽ lưới charm của bộ đang chọn
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

  // vẽ khung kéo thả, mỗi ô là 1 mắt xích
  function renderBracelet() {
    const n = state.slots.length;
    const b = $("bracelet");
    b.style.setProperty("--n", n);
    const sel = chu(state.selected);
    b.innerHTML = state.slots.map((id, i) => {
      if (id === NOI) {
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

  // tính tiền tạm tính: tiền dây mỗi ô + tiền charm
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

  // vẽ lại khung, giá và thanh bước
  function renderAll() { renderBracelet(); renderPrice(); renderSteps(); }

  // tìm ô trống vừa cho charm (charm dài cần 2 ô liền nhau)
  function oTrongCho(id) {
    if (vuaCho(id, state.selected)) return state.selected;
    return state.slots.findIndex((_, i) => vuaCho(id, i));
  }

  // đặt charm vào vòng: ô đang chọn hoặc ô trống đầu tiên
  function placeCharm(charmId, index) {
    const n = state.slots.length;
    let i = index != null ? chu(index) : oTrongCho(charmId);
    if (i === -1) {
      return msg(laDai(charmId) ? "Không còn 2 ô trống liền nhau cho charm dài. Gỡ bớt charm hoặc kéo thả vào vị trí muốn thay."
                                : "Khung đã đầy! Gỡ bớt charm hoặc kéo thả để thay.", "warn");
    }
    if (laDai(charmId) && i >= n - 1) i = n - 2;
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

  // gỡ charm khỏi ô
  function removeCharm(i) {
    if (state.slots[i] == null) return;
    remember();
    const c = chu(i), id = goTai(i);
    state.selected = c;
    renderAll();
    msg(`Đã gỡ "${charmById.get(id).ten}" khỏi ô ${c + 1}.`);
  }

  // kéo charm sang ô khác, ô đích có charm thì đổi chỗ
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

  // đổi size: giữ lại charm cũ nếu còn vừa
  function changeSize(newId) {
    const s = DATA.sizes.find((x) => x.id === newId);
    const lost = state.slots.slice(s.so).filter(laCharm).length + (laDai(state.slots[s.so - 1]) ? 1 : 0);
    if (lost && !confirm(`Size mới chỉ có ${s.so} ô, ${lost} charm ở cuối sẽ bị gỡ. Tiếp tục?`)) {
      $("size").value = state.sizeId; return;
    }
    remember();
    state.sizeId = newId;
    state.slots = Array.from({ length: s.so }, (_, i) => state.slots[i] ?? null);
    if (laDai(state.slots[s.so - 1])) state.slots[s.so - 1] = null;
    state.selected = Math.min(state.selected, s.so - 1);
    reach(2);
    renderAll();
    msg(`Khung đã đổi thành ${s.so} ô theo size ${s.ten}.`);
  }

  // gọi api để lưu thiết kế hoặc thêm vào giỏ
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

  $("size").addEventListener("change", (e) => changeSize(+e.target.value));

  $("chains").addEventListener("click", (e) => {
    const d = e.target.closest("[data-id]"); if (!d) return;
    state.chainId = +d.dataset.id; reach(3); renderChains(); renderBracelet(); renderPrice();
  });

  $("collection").addEventListener("change", (e) => {
    state.collectionId = +e.target.value; reach(4); renderCharmGrid();
  });

  $("charm-grid").addEventListener("click", (e) => {
    const t = e.target.closest(".ctile"); if (t) placeCharm(+t.dataset.id);
  });

  $("bracelet").addEventListener("click", (e) => {
    const x = e.target.closest("[data-remove]");
    if (x) return removeCharm(+x.dataset.remove);
    const s = e.target.closest(".slot"); if (!s) return;
    state.selected = chu(+s.dataset.i); renderBracelet();
    if (state.slots[state.selected] === null) msg(`Đã chọn ô ${state.selected + 1} — bấm 1 charm bên trái để đặt vào.`, "info");
  });

  // kéo thả charm bằng chuột (máy tính)
  let drag = null;
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
  $("charm-grid").addEventListener("dragover", (e) => { if (drag?.type === "slot") e.preventDefault(); });
  $("charm-grid").addEventListener("drop", (e) => { if (drag?.type === "slot") { e.preventDefault(); drag.dropped = true; removeCharm(drag.i); } });

  // kéo thả charm bằng ngón tay (điện thoại)
  let cham = null;
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

  // nút Ngang, Dọc, Xoay 360
  document.querySelectorAll(".seg button").forEach((b) => b.addEventListener("click", () => {
    const mode = b.dataset.mode, stage = $("stage");
    if (mode === "xoay") return open360();
    document.querySelectorAll(".seg button").forEach((x) => x.classList.toggle("is-active", x === b));
    stage.classList.toggle("is-vertical", mode === "doc");
    fitVertical();
  }));

  // chế độ dọc: chỉnh chiều cao khung cho vừa
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

  $("steps").addEventListener("click", (e) => {
    const li = e.target.closest("li"); if (!li) return;
    $("block-" + li.dataset.step)?.scrollIntoView({ behavior: "smooth", block: "center" });
  });

  // phím tắt: Ctrl+Z, Ctrl+Y, Delete, mũi tên
  document.addEventListener("keydown", (e) => {
    if (["INPUT", "SELECT", "TEXTAREA"].includes(document.activeElement.tagName)) return;
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === "z") { e.preventDefault(); e.shiftKey ? redo() : undo(); }
    else if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === "y") { e.preventDefault(); redo(); }
    else if (e.key === "Delete" || e.key === "Backspace") removeCharm(state.selected);
    else if (e.key === "ArrowRight") { state.selected = Math.min(state.slots.length - 1, state.selected + (laDai(state.slots[state.selected]) ? 2 : 1)); renderBracelet(); }
    else if (e.key === "ArrowLeft") { state.selected = Math.max(0, state.selected - 1); renderBracelet(); }
  });

  // khung xem vòng 3d
  const v360 = { raf: 0, ang: 0, tilt: -22, auto: true, drag: null, last: 0, gl: null };
  const scripts = {};
  const loadScript = (src) => (scripts[src] ||= new Promise((ok, fail) => {
    const s = document.createElement("script"); s.src = src; s.onload = ok; s.onerror = fail; document.head.appendChild(s);
  }));

  // đổi các ô trên vòng thành danh sách cho bản 3d
  function items3d() {
    return state.slots.map((v, i) => {
      if (v === NOI) { const ch = charmById.get(state.slots[i - 1]); return ch.anh ? { type: "img", url: ch.anh, kind: "dai", part: 1 } : { type: "empty" }; }
      const ch = laCharm(v) ? charmById.get(v) : null;
      if (!ch) return { type: "empty" };
      return ch.anh ? { type: "img", url: ch.anh, kind: ch.kieu, part: 0 } : { type: "icon", icon: ch.icon };
    });
  }
  // chọn bộ ảnh mắt xích theo màu dây
  function tint3d() {
    const t = chain().ten.toLowerCase();
    return t.includes("vàng") ? "vang" : t.includes("đen") ? "den" : t.includes("hồng") ? "hong" : "bac";
  }

  // mỗi khung hình: tự xoay rồi vẽ lại
  function tick360(t) {
    if (v360.auto && !v360.drag) v360.ang -= ((t - (v360.last || t)) / 1000) * 28;
    v360.last = t;
    v360.gl.pose(v360.ang, v360.tilt); v360.gl.render();
    v360.raf = requestAnimationFrame(tick360);
  }

  // bật tắt tự xoay
  function setAuto(on) {
    v360.auto = on;
    $("v360-toggle").textContent = on ? "⏸ Dừng xoay" : "▶ Tiếp tục xoay";
  }

  // mở khung 3d (chỉ tải three.js lúc bấm)
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

  // đóng khung 3d và dọn bộ nhớ
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

  // chạy lần đầu khi mở trang
  renderSize(); renderChains(); renderCollections(); renderCharmGrid(); renderAll();
})();
