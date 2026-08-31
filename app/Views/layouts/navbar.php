<header class="site-header">
    <div class="container nav-wrap">
        <a class="brand" href="<?= base_url(is_authenticated() ? 'dashboard' : 'login') ?>" aria-label="Better Bracket home">
            <span class="brand-mark">BB</span>
            <span>Better Bracket</span>
        </a>
        <?php if (is_authenticated()): ?>
            <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="main-nav">
                <span class="sr-only">Toggle navigation</span>
                <span></span><span></span><span></span>
            </button>
            <nav id="main-nav" class="main-nav" aria-label="Primary navigation">
                <a href="<?= base_url('dashboard') ?>">Dashboard</a>
                <a href="<?= base_url('groups') ?>">My groups</a>
                <a href="<?= base_url('bracket') ?>">Make picks</a>
                <a href="<?= base_url('games') ?>">Past games</a>
                <a href="<?= base_url('profile') ?>">Profile</a>
                <form action="<?= base_url('logout') ?>" method="post" class="logout-form">
                    <?= csrf_field() ?>
                    <button type="submit" class="nav-link-button">Sign out</button>
                </form>
            </nav>
        <?php endif; ?>
    </div>
</header>
