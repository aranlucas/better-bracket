<?php declare(strict_types=1); ?>
<?php if ($message = session()->getFlashdata('success')): ?>
    <div class="container"><div class="flash flash-success" role="status"><?= e($message) ?></div></div>
<?php endif; ?>
<?php if ($message = session()->getFlashdata('error')): ?>
    <div class="container"><div class="flash flash-error" role="alert"><?= e($message) ?></div></div>
<?php endif; ?>
