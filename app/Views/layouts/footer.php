<?php declare(strict_types=1); ?>
</main>
<footer class="site-footer">
    <div class="container footer-wrap">
        <span>Better Bracket</span>
        <span>Make every pick count.</span>
    </div>
</footer>
<script src="<?= base_url('assets/js/app.js') ?>" defer></script>
<?php foreach (($scripts ?? []) as $script): ?>
    <script src="<?= base_url($script) ?>" defer></script>
<?php endforeach; ?>
</body>
</html>
