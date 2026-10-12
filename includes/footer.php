<footer class="footer">
  <div class="container">
    <div class="footer__grid">
      <div>
        <a class="logo logo--lg" href="index.php">Charm Atelier.</a>
        <p class="footer__slogan">Bạn đã sẵn sàng tạo nên "một khoảnh khắc riêng" qua những chiếc Charm cùng Charm Atelier chưa?</p>
      </div>
      <div>
        <h4 class="footer__title">Chính Sách &amp; Hỗ Trợ</h4>
        <ul class="footer__links">
          <?php foreach (ds_chinh_sach() as $slug => $ten): ?>
            <li><a href="policy.php?p=<?= $slug ?>"><?= e($ten) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div>
        <h4 class="footer__title">Liên Hệ &amp; Đặt Hàng</h4>
        <p class="footer__info"><b>Email:</b> <a href="mailto:charmatelierxx@gmail.com">charmatelierxx@gmail.com</a></p>
        <p class="footer__info"><b>Thời gian hỗ trợ:</b> 08:30 – 22:00 (Hàng ngày)</p>
        <div class="socials">
          <a href="https://instagram.com" target="_blank" rel="noopener" aria-label="Instagram">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor"/></svg>
          </a>
          <a href="https://tiktok.com" target="_blank" rel="noopener" aria-label="TikTok">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M16.6 5.8A4.3 4.3 0 0 1 15.5 3h-3.1v12.4a2.6 2.6 0 1 1-2.6-2.6c.3 0 .5 0 .8.1V9.7a5.7 5.7 0 1 0 4.9 5.7V9.1a7.3 7.3 0 0 0 4.3 1.4V7.4a4.3 4.3 0 0 1-3.2-1.6z"/></svg>
          </a>
        </div>
      </div>
    </div>
    <div class="footer__copy">© <?= date('Y') ?> Charm Atelier. All rights reserved.</div>
  </div>
</footer>
<script src="<?= phien_ban('assets/js/main.js') ?>"></script>
<?php foreach ($scripts ?? [] as $s): ?>
<script src="<?= e(phien_ban($s)) ?>"></script>
<?php endforeach; ?>
</body>
</html>
