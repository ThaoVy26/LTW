/* story.js — khung story ở trang chủ
 * - Tự chạy từ trái sang phải, hết slide cuối thì quay lại slide đầu
 * - Mũi tên 2 bên / bấm thanh tiến trình để chuyển slide
 * - Slide ảnh & HTML: hiện trong data-interval ms; slide video: chạy hết video mới chuyển
 * - Rê chuột vào thì tạm dừng; vuốt trái/phải trên điện thoại; phím ← → khi đang focus
 */
document.addEventListener("DOMContentLoaded", () => {
  const story = document.getElementById("story");
  if (!story) return;

  const track = story.querySelector(".story__track");
  const slides = [...story.querySelectorAll(".story__slide")];
  const bars = [...story.querySelectorAll(".story__bar")];
  const INTERVAL = +story.dataset.interval || 5000;
  const MAX_VIDEO = 30000; // video dài hơn 30s thì chỉ chiếu 30s
  let current = 0;

  if (slides.length < 2) {           // chỉ 1 slide thì không cần chạy
    bars.forEach((b) => (b.hidden = true));
    return;
  }

  function thoiLuong(slide) {
    const v = slide.querySelector("video");
    if (v && v.duration && isFinite(v.duration)) return Math.min(v.duration * 1000, MAX_VIDEO);
    return INTERVAL;
  }

  function go(i) {
    current = (i + slides.length) % slides.length; // vòng lặp: sau slide cuối là slide đầu
    track.style.transform = `translateX(-${current * 100}%)`;

    // Video: chỉ slide đang hiện được chạy, chạy lại từ đầu
    slides.forEach((s, k) => {
      const v = s.querySelector("video");
      if (!v) return;
      if (k === current) { v.currentTime = 0; v.play().catch(() => {}); }
      else v.pause();
    });

    // Thanh tiến trình: trước = đầy, hiện tại = đang chạy, sau = rỗng
    bars.forEach((b, k) => {
      b.classList.toggle("is-done", k < current);
      b.classList.remove("is-active");
      b.setAttribute("aria-current", k === current ? "true" : "false");
    });
    const bar = bars[current];
    void bar.offsetWidth; // reset animation
    bar.style.setProperty("--dur", thoiLuong(slides[current]) + "ms");
    bar.classList.add("is-active");
  }

  // Thanh hiện tại chạy đầy → sang slide sau
  bars.forEach((b) => b.querySelector("span").addEventListener("animationend", () => go(current + 1)));

  // Video tải xong mới biết thời lượng → cập nhật lại nếu nó đang là slide hiện tại
  slides.forEach((s, k) => {
    const v = s.querySelector("video");
    v?.addEventListener("loadedmetadata", () => { if (k === current) go(current); });
    v?.addEventListener("error", () => { if (k === current) go(current + 1); });
  });

  // Nút mũi tên & thanh tiến trình
  story.querySelector(".story__nav--prev")?.addEventListener("click", () => go(current - 1));
  story.querySelector(".story__nav--next")?.addEventListener("click", () => go(current + 1));
  bars.forEach((b) => b.addEventListener("click", () => go(+b.dataset.go)));

  // Tạm dừng khi rê chuột / chuyển tab khác
  function pause(on) {
    story.classList.toggle("is-paused", on);
    const v = slides[current].querySelector("video");
    if (v) on ? v.pause() : v.play().catch(() => {});
  }
  story.addEventListener("mouseenter", () => pause(true));
  story.addEventListener("mouseleave", () => pause(false));
  document.addEventListener("visibilitychange", () => pause(document.hidden));

  // Vuốt trên điện thoại
  let startX = null;
  story.addEventListener("touchstart", (e) => { startX = e.touches[0].clientX; pause(true); }, { passive: true });
  story.addEventListener("touchend", (e) => {
    const dx = e.changedTouches[0].clientX - startX;
    pause(false);
    if (Math.abs(dx) > 40) go(current + (dx < 0 ? 1 : -1));
    startX = null;
  });

  // Phím mũi tên
  story.tabIndex = 0;
  story.addEventListener("keydown", (e) => {
    if (e.key === "ArrowLeft") go(current - 1);
    if (e.key === "ArrowRight") go(current + 1);
  });

  go(0);
});
