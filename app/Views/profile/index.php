<?= view('layouts/header', ['title' => 'Your profile']) ?>
<section class="container page-heading compact-heading"><div><div class="eyebrow">Your account</div><h1>Profile</h1><p class="lede">The details connected to your Better Bracket account.</p></div></section>
<section class="container narrow-content">
    <div class="card profile-card"><div class="profile-avatar"><?= e(strtoupper(substr((string) ($profile['first'] ?: $profile['email']), 0, 1))) ?></div><div><h2><?= e(trim(($profile['first'] ?? '') . ' ' . ($profile['last'] ?? '')) ?: 'Bracket player') ?></h2><p class="muted"><?= e($profile['email']) ?></p><dl class="profile-details"><div><dt>Member since</dt><dd><?= e(date('F j, Y', strtotime((string) $profile['date_joined']))) ?></dd></div><div><dt>About</dt><dd><?= e($profile['description'] ?: 'No profile description yet.') ?></dd></div></dl></div></div>
</section>
<?= view('layouts/footer') ?>
