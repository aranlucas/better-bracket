<?= view('layouts/header', ['title' => 'Dashboard']) ?>
<?php $groupCount = count($groups); ?>
<section class="container page-heading">
    <div>
        <div class="eyebrow">Your tournament desk</div>
        <h1>Welcome back.</h1>
        <p class="lede">Everything you need for the next tip-off, in one view.</p>
    </div>
    <a class="button button-primary" href="<?= base_url('bracket') ?>">Make your picks <span aria-hidden="true">→</span></a>
</section>
<section class="container stat-grid" aria-label="Your bracket summary">
    <div class="stat-card card"><span class="stat-label">Groups</span><strong><?= $groupCount ?></strong><span class="stat-caption">Your active circles</span></div>
    <div class="stat-card card"><span class="stat-label">Picks</span><strong>—</strong><span class="stat-caption">Ready when you are</span></div>
    <div class="stat-card card"><span class="stat-label">Next move</span><strong>01</strong><span class="stat-caption">Choose a group</span></div>
</section>
<section class="container section-block">
    <div class="section-heading"><div><div class="eyebrow">Your circles</div><h2>Groups</h2></div><a class="text-link" href="<?= base_url('groups/new') ?>">Create a group <span aria-hidden="true">↗</span></a></div>
    <?php if ($groups === []): ?>
        <div class="empty-state card"><span class="empty-icon">＋</span><h3>Start your first group</h3><p>Give your crew a home before the games begin.</p><a class="button button-secondary" href="<?= base_url('groups/new') ?>">Create group</a></div>
    <?php else: ?>
        <div class="group-grid">
            <?php foreach ($groups as $group): ?>
                <a class="group-card card" href="<?= base_url('groups/' . (int) $group['id']) ?>">
                    <span class="group-card-kicker">Group</span>
                    <h3><?= e($group['name']) ?></h3>
                    <p><?= e($group['description'] ?: 'No description yet.') ?></p>
                    <span class="card-arrow" aria-hidden="true">↗</span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?= view('layouts/footer') ?>
