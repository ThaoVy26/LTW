  </main>
</div>
<?php foreach ($scripts ?? [] as $s): ?>
<script src="<?= e($s) ?>"></script>
<?php endforeach; ?>
</body>
</html>
