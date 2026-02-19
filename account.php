<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

require_login();

$self = current_user();
$targetUserId = isset($_GET['user_id']) ? (int) $_GET['user_id'] : (int) $self['id'];

$targetStmt = db()->prepare('SELECT id, username, email, balance, profile_photo, role, created_at FROM users WHERE id = :id LIMIT 1');
$targetStmt->execute(['id' => $targetUserId]);
$target = $targetStmt->fetch();

if (!$target) {
    set_flash('error', 'Utilisateur introuvable.');
    redirect('account.php');
}

$isSelf = (int) $target['id'] === (int) $self['id'];
$error = null;

if ($isSelf && is_post()) {
    if (isset($_POST['update_profile'])) {
        $username = post_string('username');
        $email = post_string('email');
        $newPassword = (string) ($_POST['new_password'] ?? '');
        $currentProfilePhotoPath = (string) ($self['profile_photo'] ?? '');
        $uploadedProfilePhotoPath = null;
        $removeProfilePhoto = isset($_POST['remove_profile_photo']);
        $nextProfilePhotoPath = $removeProfilePhoto ? '' : $currentProfilePhotoPath;

        if ($username === '' || strlen($username) < 3) {
            $error = 'Le username doit contenir au moins 3 caractères.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Adresse e-mail invalide.';
        } elseif ($newPassword !== '' && strlen($newPassword) < 6) {
            $error = 'Le nouveau mot de passe doit contenir au moins 6 caractères.';
        } else {
            $uploadResult = store_uploaded_profile_image('profile_photo_file');
            if ($uploadResult['error'] !== null) {
                $error = (string) $uploadResult['error'];
            } else {
                $uploadedProfilePhotoPath = $uploadResult['path'];
                if ($uploadedProfilePhotoPath !== null) {
                    $nextProfilePhotoPath = (string) $uploadedProfilePhotoPath;
                }
            }
        }

        if ($error === null) {
            $uniqueStmt = db()->prepare('SELECT id FROM users WHERE (username = :username OR email = :email) AND id <> :id LIMIT 1');
            $uniqueStmt->execute([
                'username' => $username,
                'email' => $email,
                'id' => $self['id'],
            ]);
            if ($uniqueStmt->fetch()) {
                $error = 'Username ou e-mail déjà utilisé.';
            } else {
                if ($newPassword !== '') {
                    $update = db()->prepare(
                        'UPDATE users
                         SET username = :username,
                             email = :email,
                             profile_photo = :profile_photo,
                             password = :password
                         WHERE id = :id'
                    );
                    $update->execute([
                        'username' => $username,
                        'email' => $email,
                        'profile_photo' => $nextProfilePhotoPath !== '' ? $nextProfilePhotoPath : null,
                        'password' => password_hash($newPassword, PASSWORD_BCRYPT),
                        'id' => $self['id'],
                    ]);
                } else {
                    $update = db()->prepare(
                        'UPDATE users
                         SET username = :username,
                             email = :email,
                             profile_photo = :profile_photo
                         WHERE id = :id'
                    );
                    $update->execute([
                        'username' => $username,
                        'email' => $email,
                        'profile_photo' => $nextProfilePhotoPath !== '' ? $nextProfilePhotoPath : null,
                        'id' => $self['id'],
                    ]);
                }

                if ($nextProfilePhotoPath !== $currentProfilePhotoPath) {
                    delete_uploaded_profile_image($currentProfilePhotoPath);
                }

                current_user(true);
                set_flash('success', 'Profil mis à jour.');
                redirect('account.php');
            }
        }

        if ($error !== null && $uploadedProfilePhotoPath !== null) {
            delete_uploaded_profile_image($uploadedProfilePhotoPath);
        }
    }

    if (isset($_POST['add_balance'])) {
        $amount = post_float('amount', 0.0);
        if ($amount <= 0) {
            $error = 'Le montant doit être supérieur à 0.';
        } else {
            $add = db()->prepare('UPDATE users SET balance = balance + :amount WHERE id = :id');
            $add->execute([
                'amount' => $amount,
                'id' => $self['id'],
            ]);
            current_user(true);
            set_flash('success', 'Solde crédité de ' . format_price($amount) . '.');
            redirect('account.php');
        }
    }
}

$articleStmt = db()->prepare(
    'SELECT a.id, a.title, a.price, a.published_at, COALESCE(s.quantity, 0) AS stock_quantity
     FROM articles a
     LEFT JOIN stock s ON s.article_id = a.id
     WHERE a.author_id = :author_id
     ORDER BY a.published_at DESC'
);
$articleStmt->execute(['author_id' => $target['id']]);
$postedArticles = $articleStmt->fetchAll();

$invoices = [];
$purchasedArticles = [];
if ($isSelf) {
    $invoiceStmt = db()->prepare(
        'SELECT id, transaction_date, amount, billing_address, billing_city, billing_postal_code
         FROM invoice
         WHERE user_id = :user_id
         ORDER BY transaction_date DESC'
    );
    $invoiceStmt->execute(['user_id' => $self['id']]);
    $invoices = $invoiceStmt->fetchAll();

    $purchasedStmt = db()->prepare(
        'SELECT
            ii.article_name,
            SUM(ii.quantity) AS total_quantity,
            MAX(ii.unit_price) AS last_unit_price,
            MAX(i.transaction_date) AS last_purchase_date
         FROM invoice_items ii
         INNER JOIN invoice i ON i.id = ii.invoice_id
         WHERE i.user_id = :user_id
         GROUP BY ii.article_name
         ORDER BY last_purchase_date DESC'
    );
    $purchasedStmt->execute(['user_id' => $self['id']]);
    $purchasedArticles = $purchasedStmt->fetchAll();
}

render_header('Compte');
?>
<div class="card">
    <h1>Compte de <?= e($target['username']) ?></h1>
    <?php $profileImageSrc = media_src((string) ($target['profile_photo'] ?? '')); ?>
    <?php if ($profileImageSrc !== ''): ?>
        <img class="car-image" style="max-width:220px;height:220px;" src="<?= e($profileImageSrc) ?>" alt="Photo de profil">
    <?php endif; ?>
    <p><strong>Email:</strong> <?= e($target['email']) ?></p>
    <p><strong>Rôle:</strong> <?= e($target['role']) ?></p>
    <p><strong>Membre depuis:</strong> <?= e(date('d/m/Y', strtotime($target['created_at']))) ?></p>

    <?php if ($isSelf): ?>
        <p><strong>Solde:</strong> <?= e(format_price((float) $target['balance'])) ?></p>
    <?php endif; ?>
</div>

<?php if ($error): ?>
    <div class="flash flash-error"><?= e($error) ?></div>
<?php endif; ?>

<?php if ($isSelf): ?>
    <div class="form-card">
        <h2>Modifier mes informations</h2>
        <form method="post" enctype="multipart/form-data">
            <label for="username">Username</label>
            <input id="username" name="username" required value="<?= e($self['username']) ?>">

            <label for="email">E-mail</label>
            <input id="email" type="email" name="email" required value="<?= e($self['email']) ?>">

            <label for="profile_photo_file">Remplacer la photo (JPG, PNG, WEBP ou GIF, max 5 Mo)</label>
            <input id="profile_photo_file" name="profile_photo_file" type="file" accept="image/jpeg,image/png,image/webp,image/gif">

            <label class="checkbox-label">
                <input type="checkbox" name="remove_profile_photo" value="1">
                Supprimer la photo actuelle
            </label>

            <label for="new_password">Nouveau mot de passe (optionnel)</label>
            <input id="new_password" type="password" name="new_password">

            <button type="submit" name="update_profile" value="1">Sauvegarder</button>
        </form>
    </div>

    <div class="form-card">
        <h2>Ajouter de l'argent</h2>
        <form method="post" class="inline-form">
            <input type="number" min="0.01" step="0.01" name="amount" required placeholder="Montant en €">
            <button type="submit" name="add_balance" value="1">Créditer le solde</button>
        </form>
    </div>
<?php endif; ?>

<div class="table-card">
    <h2>Articles publiés par <?= e($target['username']) ?></h2>
    <?php if (empty($postedArticles)): ?>
        <p>Aucun article publié.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Article</th>
                    <th>Prix</th>
                    <th>Stock</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($postedArticles as $article): ?>
                    <tr>
                        <td><a href="<?= e(url('detail.php?id=' . $article['id'])) ?>"><?= e($article['title']) ?></a></td>
                        <td><?= e(format_price((float) $article['price'])) ?></td>
                        <td><?= e((string) $article['stock_quantity']) ?></td>
                        <td><?= e(date('d/m/Y H:i', strtotime($article['published_at']))) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php if ($isSelf): ?>
    <div class="table-card">
        <h2>Articles achetés</h2>
        <?php if (empty($purchasedArticles)): ?>
            <p>Vous n'avez pas encore d'achats.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Article</th>
                        <th>Quantité totale</th>
                        <th>Dernier prix unitaire</th>
                        <th>Dernier achat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($purchasedArticles as $purchase): ?>
                        <tr>
                            <td><?= e($purchase['article_name']) ?></td>
                            <td><?= e((string) $purchase['total_quantity']) ?></td>
                            <td><?= e(format_price((float) $purchase['last_unit_price'])) ?></td>
                            <td><?= e(date('d/m/Y H:i', strtotime($purchase['last_purchase_date']))) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="table-card">
        <h2>Factures</h2>
        <?php if (empty($invoices)): ?>
            <p>Aucune facture pour le moment.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID Facture</th>
                        <th>Date</th>
                        <th>Montant</th>
                        <th>Adresse de facturation</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invoices as $invoice): ?>
                        <tr>
                            <td>#<?= e((string) $invoice['id']) ?></td>
                            <td><?= e(date('d/m/Y H:i', strtotime($invoice['transaction_date']))) ?></td>
                            <td><?= e(format_price((float) $invoice['amount'])) ?></td>
                            <td>
                                <?= e($invoice['billing_address']) ?>,
                                <?= e($invoice['billing_city']) ?>
                                <?= e($invoice['billing_postal_code']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
<?php endif; ?>
<?php render_footer(); ?>
