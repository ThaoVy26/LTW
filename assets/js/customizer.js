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
    slots: Array(DATA.sizes[0].so).fill(null), // mỗi phần tử: id charm hoặc null
    selected: 0,          // ô đang chọn (bấm charm sẽ đặt vào ô này)
    reached: 1,           // bước xa nhất đã làm (cho thanh 5 bước)
    history: [], future: [],
    editKey: null,
  };

  if (DATA.edit) {
    state.sizeId = DATA.edit.size_id;
    state.chainId = DATA.edit.chain_id;
    state.slots = DATA.edit.slots.slice();
    state.editKey = DATA.edit.cart_key;
    state.reached = 5;
    const first = state.slots.find((v) => v !== null);
    if (first) state.collectionId = charmById.get(first)?.bst ?? state.collectionId;
  }

  const size = () => DATA.sizes.find((s) => s.id === state.sizeId);
  const chain = () => DATA.chains.find((c) => c.id === state.chainId);
  const collection = () => DATA.collections.find((c) => c.id === state.collectionId);

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
  }

  function renderCollections() {
    $("collection").innerHTML = DATA.collections.map((c) => `<option value="${c.id}">${esc(c.ten)} (${c.charms.length})</option>`).join("");
    $("collection").value = state.collectionId;
  }

  function renderCharmGrid() {
    const col = collection();
    $("collection-name").textContent = "— " + col.ten;
    $("charm-grid").innerHTML = col.charms.map((ch, i) => `
      <button type="button" class="ctile" draggable="true" data-id="${ch.id}" title="${esc(ch.ten)} · ${tien(ch.gia)}">
        <span class="ctile__link"><span class="ctile__face">${ch.icon}</span></span>
        <span class="ctile__num">${i + 1}</span>
        <span class="ctile__price">${tien(ch.gia)}</span>
      </button>`).join("");
  }

  function renderBracelet() {
    const n = state.slots.length;
    const b = $("bracelet");
    b.style.setProperty("--n", n);
    b.innerHTML = state.slots.map((id, i) => {
      const ch = id ? charmById.get(id) : null;
      return `<div class="slot${ch ? " is-filled" : ""}${i === state.selected ? " is-selected" : ""}" data-i="${i}"
                  ${ch ? 'draggable="true"' : ""} title="${ch ? esc(ch.ten) : "Ô trống " + (i + 1)}">
          <span class="slot__link">${ch ? `<span class="slot__face">${ch.icon}</span>` : ""}</span>
          ${ch ? `<button type="button" class="slot__x" data-remove="${i}" aria-label="Gỡ charm">×</button>` : ""}
          <span class="slot__num">${i + 1}</span>
        </div>`;
    }).join("");
    const filled = state.slots.filter(Boolean).length;
    $("filled").textContent = filled;
    $("total").textContent = n;
    $("remain").textContent = n - filled;
    $("size-hint").textContent = "Gợi ý: " + size().so + " charm";
  }

  function renderPrice() {
    const n = state.slots.length, c = chain();
    const used = state.slots.filter(Boolean).map((id) => charmById.get(id));
    const chainCost = n * c.gia, charmCost = used.reduce((s, ch) => s + ch.gia, 0);
    $("chain-line").textContent = `Dây (${n} × ${tien(c.gia)})`;
    $("chain-cost").textContent = tien(chainCost);
    $("charm-line").textContent = `Charm × ${used.length}`;
    $("charm-cost").textContent = tien(charmCost);
    $("total-price").textContent = tien(chainCost + charmCost);
  }

  function renderAll() { renderBracelet(); renderPrice(); renderSteps(); }

  /* ---------- Thao tác với ô ---------- */
  function placeCharm(charmId, index) {
    let i = index ?? (state.slots[state.selected] === null ? state.selected : state.slots.indexOf(null));
    if (i === -1 || i == null) return msg("Khung đã đầy! Gỡ bớt charm hoặc chọn 1 ô để thay.", "warn");
    remember();
    state.slots[i] = charmId;
    const next = state.slots.indexOf(null, i + 1);
    state.selected = next === -1 ? i : next;
    reach(5);
    renderAll();
    msg(`Đã đặt "${charmById.get(charmId).ten}" vào ô ${i + 1}.`);
  }

  function removeCharm(i) {
    if (state.slots[i] === null) return;
    remember();
    const ten = charmById.get(state.slots[i]).ten;
    state.slots[i] = null;
    state.selected = i;
    renderAll();
    msg(`Đã gỡ "${ten}" khỏi ô ${i + 1}.`);
  }

  function moveCharm(from, to) {
    if (from === to) return;
    remember();
    [state.slots[from], state.slots[to]] = [state.slots[to], state.slots[from]];
    state.selected = to;
    renderAll();
  }

  function changeSize(newId) {
    const s = DATA.sizes.find((x) => x.id === newId);
    const lost = state.slots.slice(s.so).filter(Boolean).length;
    if (lost && !confirm(`Size mới chỉ có ${s.so} ô, ${lost} charm ở cuối sẽ bị gỡ. Tiếp tục?`)) {
      $("size").value = state.sizeId; return;
    }
    remember();
    state.sizeId = newId;
    state.slots = Array.from({ length: s.so }, (_, i) => state.slots[i] ?? null);
    state.selected = Math.min(state.selected, s.so - 1);
    reach(2);
    renderAll();
    msg(`Khung đã đổi thành ${s.so} ô theo size ${s.ten}.`);
  }

  /* ---------- Gọi API lưu / thêm giỏ ---------- */
  async function send(action) {
    if (action === "cart" && !state.slots.some(Boolean)) return msg("Bạn chưa chọn charm nào!", "err");
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
        msg(`${esc(data.message)} <a href="customizer.php?edit=${data.id}">Mở lại thiết kế</a>`, "ok", true);
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
    state.chainId = +d.dataset.id; reach(3); renderChains(); renderPrice();
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
    state.selected = +s.dataset.i; renderBracelet();
    if (!state.slots[state.selected]) msg(`Đã chọn ô ${state.selected + 1} — bấm 1 charm bên trái để đặt vào.`, "info");
  });

  // Kéo thả (HTML5 Drag & Drop)
  let drag = null; // {type:'charm', id} | {type:'slot', i}
  document.addEventListener("dragstart", (e) => {
    const tile = e.target.closest?.(".ctile");
    const slot = e.target.closest?.(".slot.is-filled");
    if (tile) drag = { type: "charm", id: +tile.dataset.id };
    else if (slot) drag = { type: "slot", i: +slot.dataset.i };
    else return;
    e.dataTransfer.effectAllowed = "move";
    e.dataTransfer.setData("text/plain", "charm");
  });
  document.addEventListener("dragend", () => { drag = null; document.querySelectorAll(".slot.is-over").forEach((s) => s.classList.remove("is-over")); });
  $("bracelet").addEventListener("dragover", (e) => {
    const s = e.target.closest(".slot"); if (!s || !drag) return;
    e.preventDefault();
    document.querySelectorAll(".slot.is-over").forEach((x) => x !== s && x.classList.remove("is-over"));
    s.classList.add("is-over");
  });
  $("bracelet").addEventListener("drop", (e) => {
    const s = e.target.closest(".slot"); if (!s || !drag) return;
    e.preventDefault();
    const to = +s.dataset.i;
    if (drag.type === "charm") placeCharm(drag.id, to);
    else moveCharm(drag.i, to);
  });
  // Kéo charm từ khung ra lưới = gỡ charm
  $("charm-grid").addEventListener("dragover", (e) => { if (drag?.type === "slot") e.preventDefault(); });
  $("charm-grid").addEventListener("drop", (e) => { if (drag?.type === "slot") { e.preventDefault(); removeCharm(drag.i); } });

  // Ngang / Dọc / Xoay 360
  document.querySelectorAll(".seg button").forEach((b) => b.addEventListener("click", () => {
    const mode = b.dataset.mode, stage = $("stage");
    if (mode === "xoay") {
      stage.classList.remove("is-spinning"); void stage.offsetWidth; stage.classList.add("is-spinning");
      setTimeout(() => stage.classList.remove("is-spinning"), 1800);
      return;
    }
    document.querySelectorAll(".seg button").forEach((x) => x.classList.toggle("is-active", x === b));
    stage.classList.toggle("is-vertical", mode === "doc");
  }));

  $("undo").addEventListener("click", undo);
  $("redo").addEventListener("click", redo);
  $("clear").addEventListener("click", () => {
    if (!state.slots.some(Boolean)) return msg("Khung đang trống.", "warn");
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
    else if (e.key === "ArrowRight") { state.selected = Math.min(state.slots.length - 1, state.selected + 1); renderBracelet(); }
    else if (e.key === "ArrowLeft") { state.selected = Math.max(0, state.selected - 1); renderBracelet(); }
  });

  /* ---------- Khởi tạo ---------- */
  renderSize(); renderChains(); renderCollections(); renderCharmGrid(); renderAll();
})();
