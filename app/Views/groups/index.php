<?php declare(strict_types=1); ?>
<?= view('layouts/header', ['title' => 'My groups']) ?>
<section class="container page-heading compact-heading">
    <div><div class="eyebrow">Your circles</div><h1>My groups</h1><p class="lede">Keep your people and your picks together.</p></div>
    <a class="button button-primary" href="<?= base_url('groups/new') ?>">New group <span aria-hidden="true">＋</span></a>
</section>
<section class="container section-block">
    <?php if ($groups === []): ?>
        <div class="empty-state card"><span class="empty-icon">◎</span><h2>No groups yet</h2><p>Create one for your office, family, or friends.</p><a class="button button-secondary" href="<?= base_url('groups/new') ?>">Create your first group</a></div>
    <?php else: ?>
        <div class="group-grid">
            <?php foreach ($groups as $group): ?>
                <a class="group-card card" href="<?= base_url('groups/' . (int) $group['id']) ?>">
                    <span class="group-card-kicker"><?= e($group['caption'] ?: 'Bracket group') ?></span>
                    <h2><?= e($group['name']) ?></h2>
                    <p><?= e($group['description'] ?: 'No description yet.') ?></p>
                    <span class="card-arrow" aria-hidden="true">↗</span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?= view('layouts/footer') ?>
