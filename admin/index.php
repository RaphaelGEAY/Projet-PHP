<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

require_admin();

$currentAdmin = current_user();
$error = null;

if (is_post()) {
    try {
        if (isset($_POST['delete_article'])) {
            $articleId = post_int('article_id');
            if ($articleId > 0) {
                db()->beginTransaction();

                $deleteCart = db()->prepare('DELETE FROM cart WHERE article_id = :article_id');
                $deleteCart->execute(['article_id' => $articleId]);

                $deleteStock = db()->prepare('DELETE FROM stock WHERE article_id = :article_id');
                $deleteStock->execute(['article_id' => $articleId]);

                $deleteArticle = db()->prepare('DELETE FROM articles WHERE id = :id');
                $deleteArticle->execute(['id' => $articleId]);

                db()->commit();
                set_flash('success', 'Article supprimé par l\'administrateur.');
                redirect('admin/index.php');
            }
        }

        if (isset($_POST['update_article'])) {
            $articleId = post_int('article_id');
            $title = post_string('title');
            $price = post_float('price', 0.0);
            $stock = post_int('stock_quantity', 0);

            if ($articleId <= 0 || $title === '' || $price <= 0 || $stock < 0) {
                throw new RuntimeException('Données article invalides.');
            }

            db()->beginTransaction();

            $articleUpdate = db()->prepare('UPDATE articles SET title = :title, price = :price WHERE id = :id');
            $articleUpdate->execute([
                'title' => $title,
                'price' => $price,
                'id' => $articleId,
            ]);

            $stockUpdate = db()->prepare('UPDATE stock SET quantity = :quantity WHERE article_id = :article_id');
            $stockUpdate->execute([
                'quantity' => $stock,
                'article_id' => $articleId,
            ]);

            db()->commit();
            set_flash('success', 'Article mis à jour.');
            redirect('admin/index.php');
        }

        if (isset($_POST['delete_user'])) {
            $userId = post_int('user_id');
            if ($userId <= 0) {
                throw new RuntimeException('Utilisateur invalide.');
            }

            if ($userId === (int) $currentAdmin['id']) {
                throw new RuntimeException('Vous ne pouvez pas supprimer votre propre compte admin.');
            }

            db()->beginTransaction();

            $userArticleIdsStmt = db()->prepare('SELECT id FROM articles WHERE author_id = :author_id');
            $userArticleIdsStmt->execute(['author_id' => $userId]);
            $articleIds = $userArticleIdsStmt->fetchAll();

            foreach ($articleIds as $article) {
                $deleteCart = db()->prepare('DELETE FROM cart WHERE article_id = :article_id');
                $deleteCart->execute(['article_id' => $article['id']]);

                $deleteStock = db()->prepare('DELETE FROM stock WHERE article_id = :article_id');
                $deleteStock->execute(['article_id' => $article['id']]);
            }

            $deleteUserArticles = db()->prepare('DELETE FROM articles WHERE author_id = :author_id');
            $deleteUserArticles->execute(['author_id' => $userId]);

            $deleteUserCart = db()->prepare('DELETE FROM cart WHERE user_id = :user_id');
            $deleteUserCart->execute(['user_id' => $userId]);

            $deleteInvoices = db()->prepare('DELETE FROM invoice WHERE user_id = :user_id');
            $deleteInvoices->execute(['user_id' => $userId]);

            $deleteUser = db()->prepare('DELETE FROM users WHERE id = :id');
            $deleteUser->execute(['id' => $userId]);

            db()->commit();
            set_flash('success', 'Utilisateur supprimé.');
            redirect('admin/index.php');
        }

        if (isset($_POST['update_user'])) {
            $userId = post_int('user_id');
            $username = post_string('username');
            $email = post_string('email');
            $role = post_string('role');
            $balance = post_float('balance', 0.0);

            if ($userId <= 0 || $username === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('Données utilisateur invalides.');
            }

            if (!in_array($role, ['user', 'admin'], true)) {
                throw new RuntimeException('Rôle invalide.');
            }

            $uniqueStmt = db()->prepare('SELECT id FROM users WHERE (username = :username OR email = :email) AND id <> :id LIMIT 1');
            $uniqueStmt->execute([
                'username' => $username,
                'email' => $email,
                'id' => $userId,
            ]);

            if ($uniqueStmt->fetch()) {
                throw new RuntimeException('Username ou e-mail déjà pris.');
            }

            $updateUser = db()->prepare(
                'UPDATE users
                 SET username = :username,
                     email = :email,
                     role = :role,
                     balance = :balance
                 WHERE id = :id'
            );
            $updateUser->execute([
                'username' => $username,
                'email' => $email,
                'role' => $role,
                'balance' => max(0.0, $balance),
                'id' => $userId,
            ]);

            if ($userId === (int) $currentAdmin['id']) {
                current_user(true);
            }

            set_flash('success', 'Utilisateur mis à jour.');
            redirect('admin/index.php');
        }
    } catch (Throwable $exception) {
        if (db()->inTransaction()) {
            db()->rollBack();
        }
        $error = $exception->getMessage();
    }
}

$usersStmt = db()->query('SELECT id, username, email, role, balance, created_at FROM users ORDER BY created_at DESC');
$users = $usersStmt->fetchAll();

$articlesStmt = db()->query(
    'SELECT
        a.id,
        a.title,
        a.price,
        a.published_at,
        u.username AS author_username,
        COALESCE(s.quantity, 0) AS stock_quantity
     FROM articles a
     INNER JOIN users u ON u.id = a.author_id
     LEFT JOIN stock s ON s.article_id = a.id
     ORDER BY a.published_at DESC'
);
$articles = $articlesStmt->fetchAll();

render_header('Administration');
?>
<div class="card">
    <h1>Tableau Administrateur</h1>
    <p class="muted">Gestion globale des utilisateurs et des articles.</p>
</div>

<?php if ($error): ?>
    <div class="flash flash-error"><?= e($error) ?></div>
<?php endif; ?>

<div class="table-card">
    <h2>Utilisateurs</h2>
    <?php if (empty($users)): ?>
        <p>Aucun utilisateur.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>E-mail</th>
                    <th>Rôle</th>
                    <th>Solde</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <?php $formId = 'user-form-' . $user['id']; ?>
                    <tr>
                        <td><?= e((string) $user['id']) ?></td>
                        <td>
                            <input type="text" name="username" value="<?= e($user['username']) ?>" required form="<?= e($formId) ?>">
                        </td>
                        <td>
                            <input type="email" name="email" value="<?= e($user['email']) ?>" required form="<?= e($formId) ?>">
                        </td>
                        <td>
                            <select name="role" form="<?= e($formId) ?>">
                                <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>user</option>
                                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>admin</option>
                            </select>
                        </td>
                        <td>
                            <input type="number" step="0.01" min="0" name="balance" value="<?= e((string) $user['balance']) ?>" required form="<?= e($formId) ?>">
                        </td>
                        <td>
                            <form id="<?= e($formId) ?>" method="post">
                                <input type="hidden" name="user_id" value="<?= e((string) $user['id']) ?>">
                                <button type="submit" name="update_user" value="1">Mettre à jour</button>
                            </form>
                            <form method="post" onsubmit="return confirm('Supprimer cet utilisateur ?');">
                                <input type="hidden" name="user_id" value="<?= e((string) $user['id']) ?>">
                                <button class="btn-danger" type="submit" name="delete_user" value="1">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<div class="table-card">
    <h2>Articles</h2>
    <?php if (empty($articles)): ?>
        <p>Aucun article.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Article</th>
                    <th>Auteur</th>
                    <th>Prix</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($articles as $article): ?>
                    <?php $formId = 'article-form-' . $article['id']; ?>
                    <tr>
                        <td><?= e((string) $article['id']) ?></td>
                        <td>
                            <input type="text" name="title" value="<?= e($article['title']) ?>" required form="<?= e($formId) ?>">
                        </td>
                        <td><?= e($article['author_username']) ?></td>
                        <td>
                            <input type="number" name="price" min="0.01" step="0.01" value="<?= e((string) $article['price']) ?>" required form="<?= e($formId) ?>">
                        </td>
                        <td>
                            <input type="number" name="stock_quantity" min="0" step="1" value="<?= e((string) $article['stock_quantity']) ?>" required form="<?= e($formId) ?>">
                        </td>
                        <td>
                            <form id="<?= e($formId) ?>" method="post">
                                <input type="hidden" name="article_id" value="<?= e((string) $article['id']) ?>">
                                <button type="submit" name="update_article" value="1">Mettre à jour</button>
                            </form>
                            <form method="post" onsubmit="return confirm('Supprimer cet article ?');">
                                <input type="hidden" name="article_id" value="<?= e((string) $article['id']) ?>">
                                <button class="btn-danger" type="submit" name="delete_article" value="1">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php render_footer(); ?>
