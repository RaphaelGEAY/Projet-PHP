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
        <title><?= e($title) ?> | AutoMarket</title>
        <link rel="stylesheet" href="<?= e(url('assets/style.css')) ?>">
    </head>
    <body>
        <header class="site-header">
            <div class="container nav-wrap">
                <a class="logo" href="<?= e(url('index.php')) ?>">AutoMarket</a>
                <nav class="main-nav">
                    <a href="<?= e(url('index.php')) ?>">Accueil</a>
                    <?php if ($user): ?>
                        <a href="<?= e(url('sell.php')) ?>">Vendre</a>
                        <a href="<?= e(url('cart/')) ?>">Panier</a>
                        <a href="<?= e(url('account.php')) ?>">Mon compte</a>
                        <?php if ($user['role'] === 'admin'): ?>
                            <a href="<?= e(url('admin/')) ?>">Admin</a>
                        <?php endif; ?>
                        <a href="<?= e(url('logout.php')) ?>">Déconnexion</a>
                    <?php else: ?>
                        <a href="<?= e(url('login.php')) ?>">Connexion</a>
                        <a href="<?= e(url('register.php')) ?>">Inscription</a>
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
                AutoMarket - Projet PHP E-Commerce Voitures
            </div>
        </footer>
    </body>
    </html>
    <?php
}
