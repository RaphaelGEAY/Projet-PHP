<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

if (is_logged_in()) {
    redirect('');
}

$values = [
    'username' => '',
    'email' => '',
];
$error = null;

if (is_post()) {
    $values['username'] = post_string('username');
    $values['email'] = post_string('email');
    $password = (string) ($_POST['password'] ?? '');
    $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');
    $uploadedProfilePhotoPath = null;

    if ($values['username'] === '' || strlen($values['username']) < 3) {
        $error = 'Le username doit contenir au moins 3 caractères.';
    } elseif (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresse e-mail invalide.';
    } elseif (strlen($password) < 6) {
        $error = 'Le mot de passe doit contenir au moins 6 caractères.';
    } elseif ($password !== $passwordConfirm) {
        $error = 'La confirmation du mot de passe ne correspond pas.';
    } else {
        $stmt = db()->prepare('SELECT id, username, email FROM users WHERE username = :username OR email = :email LIMIT 1');
        $stmt->execute([
            'username' => $values['username'],
            'email' => $values['email'],
        ]);
        $existing = $stmt->fetch();

        if ($existing) {
            if ($existing['username'] === $values['username']) {
                $error = 'Ce username est déjà utilisé.';
            } else {
                $error = 'Cette adresse e-mail est déjà utilisée.';
            }
        } else {
            $uploadResult = store_uploaded_profile_image('profile_photo_file');
            if ($uploadResult['error'] !== null) {
                $error = (string) $uploadResult['error'];
            } else {
                $uploadedProfilePhotoPath = $uploadResult['path'];
            }
        }
    }

    if ($error === null) {
        try {
            $insert = db()->prepare(
                'INSERT INTO users (username, email, password, balance, profile_photo, role, created_at)
                 VALUES (:username, :email, :password, 0, :profile_photo, :role, NOW())'
            );

            $insert->execute([
                'username' => $values['username'],
                'email' => $values['email'],
                'password' => password_hash($password, PASSWORD_BCRYPT),
                'profile_photo' => $uploadedProfilePhotoPath,
                'role' => 'user',
            ]);

            $_SESSION['user_id'] = (int) db()->lastInsertId();
            current_user(true);
            set_flash('success', 'Compte créé avec succès. Vous êtes connecté.');
            redirect('');
        } catch (Throwable) {
            if ($uploadedProfilePhotoPath !== null) {
                delete_uploaded_profile_image($uploadedProfilePhotoPath);
            }
            $error = 'Impossible de créer le compte pour le moment. Veuillez réessayer.';
        }
    }
}

render_header('Inscription');
?>
<div class="form-card">
    <h1>Créer un compte</h1>
    <p class="muted">Un username et un e-mail uniques sont obligatoires.</p>

    <?php if ($error): ?>
        <div class="flash flash-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="auth-form">
        <label for="username">Username</label>
        <input id="username" name="username" required value="<?= e($values['username']) ?>">

        <label for="email">E-mail</label>
        <input id="email" type="email" name="email" required value="<?= e($values['email']) ?>">

        <label for="password">Mot de passe</label>
        <input id="password" type="password" name="password" required>

        <label for="password_confirm">Confirmer le mot de passe</label>
        <input id="password_confirm" type="password" name="password_confirm" required>

        <label for="profile_photo_file">Photo de profil (optionnel: JPG, PNG, WEBP ou GIF, max 5 Mo)</label>
        <input id="profile_photo_file" name="profile_photo_file" type="file" accept="image/jpeg,image/png,image/webp,image/gif">

        <button type="submit">S'inscrire</button>
    </form>

    <p>Déjà inscrit ? <a href="<?= e(url('login/')) ?>">Connectez-vous</a>.</p>
</div>
<?php render_footer(); ?>
