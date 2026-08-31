<?php declare(strict_types=1); ?>
<?= view('layouts/header', ['title' => $group['name']]) ?>
<section class="container narrow-content">
    <a class="back-link" href="<?= base_url('groups') ?>">← Back to groups</a>
    <div class="group-hero">
        <div><div class="eyebrow">Bracket group</div><h1><?= e($group['name']) ?></h1><p class="lede"><?= e($group['description'] ?: 'A place to compare picks and follow the tournament.') ?></p></div>
        <a class="button button-primary" href="<?= base_url('bracket') ?>">Make picks <span aria-hidden="true">→</span></a>
    </div>
    <div class="detail-grid">
        <section class="card detail-card"><div class="section-heading"><div><div class="eyebrow">The crew</div><h2><?= count($members) ?> member<?= count($members) === 1 ? '' : 's' ?></h2></div></div>
            <?php if ($members === []): ?><p class="muted">No members yet. You can be first.</p><?php else: ?><ul class="member-list"><?php foreach ($members as $member): ?><li><span class="avatar"><?= e(strtoupper(substr((string) ($member['first'] ?: $member['email']), 0, 1))) ?></span><span><strong><?= e(trim(($member['first'] ?? '') . ' ' . ($member['last'] ?? '')) ?: $member['email']) ?></strong><small><?= e($member['email']) ?></small></span></li><?php endforeach; ?></ul><?php endif; ?>
        </section>
        <section class="card detail-card"><div class="eyebrow"><?= $isMember ? 'Invite someone' : 'Join the group' ?></div><h2><?= $isMember ? 'Grow the crew' : 'Count me in' ?></h2><p class="muted"><?= $isMember ? 'Add a registered player by email.' : 'Join with your account, or invite yourself from this page.' ?></p>
            <form action="<?= base_url('groups/' . (int) $group['id'] . '/members') ?>" method="post" class="form-stack">
                <?= csrf_field() ?>
                <?php if ($isMember): ?><div class="field"><label for="member-email">Member email</label><input id="member-email" name="email" type="email" autocomplete="email" placeholder="friend@example.com" required></div><?php endif; ?>
                <button class="button button-secondary" type="submit"><?= $isMember ? 'Add member' : 'Join group' ?> <span aria-hidden="true">→</span></button>
            </form>
        </section>
    </div>
</section>
<?= view('layouts/footer') ?>
