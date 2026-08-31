<?= view('layouts/header', ['title' => 'All groups']) ?>
<section class="container page-heading compact-heading"><div><div class="eyebrow">Find your people</div><h1>All groups</h1><p class="lede">Browse the bracket communities on this board.</p></div></section>
<section class="container section-block">
    <?php if ($groups === []): ?>
        <div class="empty-state card"><h2>The board is quiet.</h2><p>Be the first to create a group.</p></div>
    <?php else: ?>
        <div class="group-grid">
            <?php foreach ($groups as $group): ?>
                <a class="group-card card" href="<?= base_url('groups/' . (int) $group['id']) ?>"><span class="group-card-kicker">Open group</span><h2><?= e($group['name']) ?></h2><p><?= e($group['description'] ?: 'No description yet.') ?></p><span class="card-arrow" aria-hidden="true">↗</span></a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?= view('layouts/footer') ?>
