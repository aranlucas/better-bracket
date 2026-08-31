<?php declare(strict_types=1); ?>
<?= view('layouts/header', ['title' => 'Welcome']) ?>
<section class="auth-shell container">
    <div class="auth-intro">
        <div class="eyebrow">Tournament day, organized</div>
        <h1>Bring your bracket crew together.</h1>
        <p class="lede">Make picks, create private groups, and keep every game in one place.</p>
        <div class="feature-list">
            <div><span class="feature-icon">✓</span><span>One clean bracket for every tournament</span></div>
            <div><span class="feature-icon">✓</span><span>Groups for friends, family, and coworkers</span></div>
            <div><span class="feature-icon">✓</span><span>Your account stays yours</span></div>
        </div>
    </div>
    <div class="auth-card card">
        <div class="auth-card-heading">
            <div>
                <div class="eyebrow">Get started</div>
                <h2>Sign in or join</h2>
            </div>
            <span class="status-dot" aria-hidden="true"></span>
        </div>
        <form action="<?= base_url('login') ?>" method="post" class="form-stack">
            <?= csrf_field() ?>
            <div class="field">
                <label for="login-email">Email</label>
                <input id="login-email" name="email" type="email" value="<?= old_input('email') ?>" autocomplete="email" required>
            </div>
            <div class="field">
                <label for="login-password">Password</label>
                <input id="login-password" name="password" type="password" autocomplete="current-password" required>
            </div>
            <button class="button button-primary" type="submit">Sign in <span aria-hidden="true">→</span></button>
        </form>
        <div class="form-divider"><span>New here?</span></div>
        <form action="<?= base_url('register') ?>" method="post" class="form-stack">
            <?= csrf_field() ?>
            <div class="form-grid">
                <div class="field">
                    <label for="first">First name</label>
                    <input id="first" name="first" type="text" value="<?= old_input('first') ?>" autocomplete="given-name" required>
                </div>
                <div class="field">
                    <label for="last">Last name</label>
                    <input id="last" name="last" type="text" value="<?= old_input('last') ?>" autocomplete="family-name" required>
                </div>
            </div>
            <div class="field">
                <label for="register-email">Email</label>
                <input id="register-email" name="email" type="email" value="<?= old_input('email') ?>" autocomplete="email" required>
            </div>
            <div class="field">
                <label for="register-password">Password <span class="field-hint">8+ characters</span></label>
                <input id="register-password" name="password" type="password" autocomplete="new-password" minlength="8" required>
            </div>
            <button class="button button-secondary" type="submit">Create account</button>
        </form>
    </div>
</section>
<?= view('layouts/footer') ?>
