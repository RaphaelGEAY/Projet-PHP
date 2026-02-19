<?php
declare(strict_types=1);

function render_header(string $title): void
{
    $user = current_user();
    $flashes = consume_flashes();
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= e($title) ?> | VoitiBox</title>
        <link rel="stylesheet" href="<?= e(asset_url('assets/style.css')) ?>">
    </head>
    <body>
        <header class="site-header">
            <div class="container nav-wrap">
                <a class="logo" href="<?= e(url('')) ?>">VoitiBox</a>
                <nav class="main-nav">
                    <a href="<?= e(url('')) ?>">Accueil</a>
                    <?php if ($user): ?>
                        <?php
                        $headerAvatarSrc = media_src((string) ($user['profile_photo'] ?? ''));
                        $headerInitial = strtoupper(substr((string) ($user['username'] ?? ''), 0, 1));
                        ?>
                        <a href="<?= e(url('sell/')) ?>">Vendre</a>
                        <a href="<?= e(url('cart/')) ?>">Panier</a>
                        <?php if ($user['role'] === 'admin'): ?>
                            <a href="<?= e(url('admin/')) ?>">Admin</a>
                        <?php endif; ?>
                        <a href="<?= e(url('logout/')) ?>">Déconnexion</a>
                        <a class="nav-account" href="<?= e(url('account/')) ?>" aria-label="Mon compte" title="Mon compte">
                            <?php if ($headerAvatarSrc !== ''): ?>
                                <img class="header-avatar" src="<?= e($headerAvatarSrc) ?>" alt="Profil de <?= e($user['username']) ?>">
                            <?php else: ?>
                                <span class="header-avatar header-avatar-fallback"><?= e($headerInitial !== '' ? $headerInitial : '?') ?></span>
                            <?php endif; ?>
                        </a>
                    <?php else: ?>
                        <a href="<?= e(url('login/')) ?>">Connexion</a>
                        <a href="<?= e(url('register/')) ?>">Inscription</a>
                    <?php endif; ?>
                </nav>
            </div>
        </header>

        <main class="container">
            <?php foreach ($flashes as $flash): ?>
                <div class="flash flash-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
            <?php endforeach; ?>
    <?php
}

function render_footer(): void
{
    ?>
        </main>
        <footer class="site-footer">
            <div class="container">
                VoitiBox - Projet PHP E-Commerce Voitures
            </div>
        </footer>
    </body>
    </html>
    <?php
}
