<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

if (is_logged_in()) {
    redirect('index.php');
}

$error = null;
$email = '';

if (is_post()) {
    $email = post_string('email');
    $password = (string) ($_POST['password'] ?? '');
    $emailLower = strtolower($email);
    $altEmail = $email;

    if (str_ends_with($emailLower, '@voitibox.local')) {
        $altEmail = substr($email, 0, -strlen('@voitibox.local')) . '@automarket.local';
    } elseif (str_ends_with($emailLower, '@automarket.local')) {
        $altEmail = substr($email, 0, -strlen('@automarket.local')) . '@voitibox.local';
    }

    $stmt = db()->prepare('SELECT * FROM users WHERE email = :email OR email = :alt_email LIMIT 1');
    $stmt->execute([
        'email' => $email,
        'alt_email' => $altEmail,
    ]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = (int) $user['id'];
        current_user(true);
        set_flash('success', 'Connexion réussie.');
        redirect('index.php');
    }

    $error = 'Identifiants invalides.';
}

render_header('Connexion');
?>
<div class="form-card">
    <h1>Connexion</h1>

    <?php if ($error): ?>
        <div class="flash flash-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" class="auth-form">
        <label for="email">E-mail</label>
        <input id="email" type="email" name="email" required value="<?= e($email) ?>">

        <label for="password">Mot de passe</label>
        <input id="password" type="password" name="password" required>

        <button type="submit">Se connecter</button>
    </form>

    <p>Pas encore de compte ? <a href="<?= e(url('register.php')) ?>">Inscrivez-vous</a>.</p>
</div>
<?php render_footer(); ?>
