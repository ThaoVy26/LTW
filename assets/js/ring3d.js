/* ring3d.js — vòng tay Italian Charm 3D (three.js).
 * Mỗi mắt xích là một khối hộp dán ảnh THẬT của mắt xích thép (mặt trước, mặt trong có lò xo,
 * mặt trên/dưới, hai đầu — ảnh trong assets/img/link3d/). Charm dán lên mặt trước, mặt phồng nhẹ + bóng.
 * Dùng: const r = Ring3D.create(khungDiv, { items, tint }); r.pose(gocXoay, gocNghieng); r.render(); r.dispose();
 */
(() => {
  const IMG = "assets/img/link3d/";
  const W = 1, H = 0.82, D = 0.32;          // rộng · cao · dày của 1 mắt xích (tỉ lệ lấy từ ảnh thật)
  const SUFFIX = { vang: "-vang", den: "-den", hong: "-hong" };           // dây vàng / đen dùng ảnh thép đã đổi màu sẵn (không nhân màu → không bị ám xanh bẩn)

  /** Ảnh môi trường kiểu phòng chụp: chỉ để tạo vệt bóng nhẹ trên mặt charm và mắt xích. */
  function makeEnv(THREE, renderer) {
    const c = document.createElement("canvas"); c.width = 1024; c.height = 512;
    const g = c.getContext("2d");
    const grad = g.createLinearGradient(0, 0, 0, 512);
    grad.addColorStop(0, "#f2f4f8"); grad.addColorStop(.45, "#9aa1ad"); grad.addColorStop(.5, "#4a4f5a"); grad.addColorStop(1, "#1a1c22");
    g.fillStyle = grad; g.fillRect(0, 0, 1024, 512);
    g.filter = "blur(10px)"; g.fillStyle = "#fff";
    [[60, 80, 110, 150], [300, 50, 70, 190], [520, 100, 150, 80], [780, 60, 90, 170]].forEach(([x, y, w, h]) => g.fillRect(x, y, w, h));
    const tex = new THREE.CanvasTexture(c); tex.mapping = THREE.EquirectangularReflectionMapping;
    const pm = new THREE.PMREMGenerator(renderer), env = pm.fromEquirectangular(tex).texture;
    tex.dispose(); pm.dispose();
    return env;
  }

  /** Mặt charm phồng nhẹ như giọt nhựa epoxy → vệt sáng trượt qua khi xoay. */
  function domeGeometry(THREE, pw, ph) {
    const g = new THREE.PlaneGeometry(pw, ph, 16, 8), p = g.attributes.position;
    for (let i = 0; i < p.count; i++) {
      const nx = (p.getX(i) / (pw / 2)) ** 2, ny = (p.getY(i) / (ph / 2)) ** 2;
      p.setZ(i, 0.04 * Math.max(0, 1 - nx) * Math.max(0, 1 - ny));
    }
    g.computeVertexNormals();
    return g;
  }

  function create(host, cfg) {
    const THREE = window.THREE, items = cfg.items, n = items.length;
    const w = host.clientWidth || 520, h = host.clientHeight || 340;
    const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
    renderer.setSize(w, h);
    renderer.outputEncoding = THREE.sRGBEncoding;
    renderer.domElement.className = "v360__gl";
    host.appendChild(renderer.domElement);

    const scene = new THREE.Scene();
    scene.environment = makeEnv(THREE, renderer);
    const key = new THREE.DirectionalLight(0xffffff, 0.5); key.position.set(3, 6, 5); scene.add(key);
    scene.add(new THREE.AmbientLight(0xffffff, 0.45));

    const R = W / (2 * Math.tan(Math.PI / n));
    const camera = new THREE.PerspectiveCamera(32, w / h, 0.1, 100);
    camera.position.set(0, 0, ((R + 1.4) / Math.tan((camera.fov * Math.PI) / 360)) * 0.66);
    camera.lookAt(0, -0.35, 0);
    const tilt = new THREE.Group(), spin = new THREE.Group();
    tilt.add(spin); scene.add(tilt); tilt.position.y = -0.1;

    const aniso = renderer.capabilities.getMaxAnisotropy();
    const disposables = [];
    const loader = new THREE.TextureLoader();
    const photo = (name) => {
      const t = loader.load(IMG + name + (SUFFIX[cfg.tint] || "") + ".webp?v=5");
      t.encoding = THREE.sRGBEncoding; t.anisotropy = aniso;
      disposables.push(t);
      return t;
    };
    const tex = { front: photo("front"), back: photo("back"), top: photo("top"), bottom: photo("bottom"), left: photo("left"), right: photo("right") };
    const steel = (t) => {
      const m = new THREE.MeshStandardMaterial({ map: t, metalness: 0.3, roughness: 0.5, envMapIntensity: 0.55 });
      disposables.push(m);
      return m;
    };
    // thứ tự mặt của BoxGeometry: +x, -x, +y, -y, +z (trước), -z (trong vòng)
    const mats = [steel(tex.right), steel(tex.left), steel(tex.top), steel(tex.bottom), steel(tex.front), steel(tex.back)];
    const box = new THREE.BoxGeometry(W, H, D);
    // charm dài: một mắt xích dài thật (rộng 2 ô), mặt trên/dưới/trong lấy từ ảnh mắt xích dài
    const boxDai = new THREE.BoxGeometry(2 * W, H, D);
    const matsDai = [mats[0], mats[1], steel(photo("long-top")), steel(photo("long-bottom")), mats[4], steel(photo("long-back"))];
    disposables.push(box, boxDai);

    const front = D / 2 + 0.003;
    const faceMat = (t) => {
      t.encoding = THREE.sRGBEncoding; t.anisotropy = aniso;
      const m = new THREE.MeshPhysicalMaterial({
        map: t, transparent: true, alphaTest: 0.04, metalness: 0.1, roughness: 0.5, envMapIntensity: 0.3,
        clearcoat: 0.45, clearcoatRoughness: 0.12,
      });
      disposables.push(t, m);
      return m;
    };

    items.forEach((it, i) => {
      const dai = it.type === "img" && it.kind === "dai";
      if (dai && it.part === 1) return;                                      // ô thứ 2 của charm dài: đã gộp vào mắt xích dài
      const a = (Math.PI * 2 * (i + (dai ? 0.5 : 0))) / n;
      // khối dài là thanh thẳng: lùi vào để hai đầu mặt trước chạm đúng góc của mắt xích bên cạnh, không lồi ra khỏi vòng
      const rc = (R + D / 2) / Math.cos(Math.PI / n);
      const rr = dai ? Math.sqrt(rc * rc - W * W) - D / 2 : R;
      const link = new THREE.Group();
      link.position.set(Math.sin(a) * rr, 0, Math.cos(a) * rr);
      link.rotation.y = a;
      link.add(new THREE.Mesh(dai ? boxDai : box, dai ? matsDai : mats));
      spin.add(link);

      const plane = (t, pw, ph, top, dome) => {
        const g = dome ? domeGeometry(THREE, pw, ph) : new THREE.PlaneGeometry(pw, ph);
        const m = new THREE.Mesh(g, faceMat(t));
        m.position.set(0, top ? H / 2 - ph / 2 : 0, front);
        disposables.push(g);
        link.add(m);
      };
      if (it.type === "img") {
        loader.load(it.url, (t) => {
          const asp = t.image.width / t.image.height;
          if (it.kind === "day") plane(t, W, W / asp, true, false);   // charm treo: thòng xuống dưới
          else plane(t, dai ? 2 * W : W, H, false, true);
        });
      } else if (it.type === "icon") {                                // charm chỉ có biểu tượng: vẽ lên canvas
        const c = document.createElement("canvas"); c.width = 480; c.height = 394;
        const g = c.getContext("2d");
        g.font = "170px serif"; g.textAlign = "center"; g.textBaseline = "middle"; g.fillText(it.icon, 240, 205);
        plane(new THREE.CanvasTexture(c), W * 0.8, H * 0.8, false, true);
      }
    });

    const sc = document.createElement("canvas"); sc.width = sc.height = 128;
    const sg = sc.getContext("2d"), rg = sg.createRadialGradient(64, 64, 4, 64, 64, 64);
    rg.addColorStop(0, "rgba(40,20,30,.28)"); rg.addColorStop(1, "rgba(74,13,34,0)");
    sg.fillStyle = rg; sg.fillRect(0, 0, 128, 128);
    const shadow = new THREE.Mesh(new THREE.PlaneGeometry(R * 2.5, R * 2.5),
      new THREE.MeshBasicMaterial({ map: new THREE.CanvasTexture(sc), transparent: true, depthWrite: false }));
    shadow.rotation.x = -Math.PI / 2; shadow.position.y = -(H / 2 + 1.1);
    scene.add(shadow);

    const ro = typeof ResizeObserver === "function" ? new ResizeObserver(() => {
      const nw = host.clientWidth, nh = host.clientHeight;
      if (!nw || !nh) return;
      renderer.setSize(nw, nh); camera.aspect = nw / nh; camera.updateProjectionMatrix();
    }) : null;
    ro && ro.observe(host);

    return {
      pose(angDeg, tiltDeg) {
        spin.rotation.y = (angDeg * Math.PI) / 180;
        tilt.rotation.x = (-tiltDeg * Math.PI) / 180;
      },
      render() { renderer.render(scene, camera); },
      dispose() {
        ro && ro.disconnect();
        disposables.forEach((o) => o.dispose && o.dispose());
        scene.environment && scene.environment.dispose();
        renderer.dispose();
        renderer.domElement.remove();
      },
    };
  }

  window.Ring3D = { create };
})();
