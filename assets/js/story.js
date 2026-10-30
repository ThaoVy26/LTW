document.addEventListener("DOMContentLoaded", () => {
  const story = document.getElementById("story");
  if (!story) return;

  const track = story.querySelector(".story__track");
  const slides = [...story.querySelectorAll(".story__slide")];
  const bars = [...story.querySelectorAll(".story__bar")];
  const INTERVAL = +story.dataset.interval || 5000;
  const MAX_VIDEO = 30000;
  let current = 0;

  if (slides.length < 2) {
    bars.forEach((b) => (b.hidden = true));
    return;
  }

  // ảnh hiện 5 giây, video chạy hết (tối đa 30 giây)
  function thoiLuong(slide) {
    const v = slide.querySelector("video");
    if (v && v.duration && isFinite(v.duration)) return Math.min(v.duration * 1000, MAX_VIDEO);
    return INTERVAL;
  }

  // chuyển tới slide số i rồi chạy lại thanh tiến trình
  function go(i) {
    current = (i + slides.length) % slides.length;
    track.style.transform = `translateX(-${current * 100}%)`;

    slides.forEach((s, k) => {
      const v = s.querySelector("video");
      if (!v) return;
      if (k === current) { v.currentTime = 0; v.play().catch(() => {}); }
      else v.pause();
    });

    bars.forEach((b, k) => {
      b.classList.toggle("is-done", k < current);
      b.classList.remove("is-active");
      b.setAttribute("aria-current", k === current ? "true" : "false");
    });
    const bar = bars[current];
    void bar.offsetWidth;
    bar.style.setProperty("--dur", thoiLuong(slides[current]) + "ms");
    bar.classList.add("is-active");
  }

  bars.forEach((b) => b.querySelector("span").addEventListener("animationend", () => go(current + 1)));

  slides.forEach((s, k) => {
    const v = s.querySelector("video");
    v?.addEventListener("loadedmetadata", () => { if (k === current) go(current); });
    v?.addEventListener("error", () => { if (k === current) go(current + 1); });
  });

  story.querySelector(".story__nav--prev")?.addEventListener("click", () => go(current - 1));
  story.querySelector(".story__nav--next")?.addEventListener("click", () => go(current + 1));
  bars.forEach((b) => b.addEventListener("click", () => go(+b.dataset.go)));

  // rê chuột vào thì dừng
  function pause(on) {
    story.classList.toggle("is-paused", on);
    const v = slides[current].querySelector("video");
    if (v) on ? v.pause() : v.play().catch(() => {});
  }
  story.addEventListener("mouseenter", () => pause(true));
  story.addEventListener("mouseleave", () => pause(false));
  document.addEventListener("visibilitychange", () => pause(document.hidden));

  // vuốt trái phải trên điện thoại
  let startX = null;
  story.addEventListener("touchstart", (e) => { startX = e.touches[0].clientX; pause(true); }, { passive: true });
  story.addEventListener("touchend", (e) => {
    const dx = e.changedTouches[0].clientX - startX;
    pause(false);
    if (Math.abs(dx) > 40) go(current + (dx < 0 ? 1 : -1));
    startX = null;
  });

  story.tabIndex = 0;
  story.addEventListener("keydown", (e) => {
    if (e.key === "ArrowLeft") go(current - 1);
    if (e.key === "ArrowRight") go(current + 1);
  });

  go(0);
});
