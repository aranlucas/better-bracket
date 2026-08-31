<?php declare(strict_types=1); ?>
<?= view('layouts/header', ['title' => 'Create a group']) ?>
<section class="container narrow-content">
    <a class="back-link" href="<?= base_url('groups') ?>">← Back to groups</a>
    <div class="page-heading stacked-heading"><div><div class="eyebrow">Build your circle</div><h1>Create a group</h1><p class="lede">Start a shared space for your bracket crew.</p></div></div>
    <form action="<?= base_url('groups') ?>" method="post" class="card form-card">
        <?= csrf_field() ?>
        <div class="field"><label for="name">Group name</label><input id="name" name="name" type="text" value="<?= old_input('name') ?>" maxlength="125" placeholder="e.g. The Office Pool" required></div>
        <div class="field"><label for="caption">Short label <span class="field-hint">Optional</span></label><input id="caption" name="caption" type="text" value="<?= old_input('caption') ?>" maxlength="125" placeholder="e.g. 2026 March Madness"></div>
        <div class="field"><label for="description">Description <span class="field-hint">Optional</span></label><textarea id="description" name="description" rows="5" maxlength="2000" placeholder="What is this group about?"><?= old_input('description') ?></textarea></div>
        <div class="form-actions"><a class="button button-ghost" href="<?= base_url('groups') ?>">Cancel</a><button class="button button-primary" type="submit">Create group <span aria-hidden="true">→</span></button></div>
    </form>
</section>
<?= view('layouts/footer') ?>
