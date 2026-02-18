<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

require_login();

if (!is_post()) {
    set_flash('error', 'La page édition doit être ouverte depuis un formulaire POST.');
    redirect('index.php');
}

$articleId = post_int('article_id');
if ($articleId <= 0) {
    set_flash('error', 'Article invalide.');
    redirect('index.php');
}

$stmt = db()->prepare(
    'SELECT a.*, COALESCE(s.quantity, 0) AS stock_quantity
     FROM articles a
     LEFT JOIN stock s ON s.article_id = a.id
     WHERE a.id = :id
     LIMIT 1'
);
$stmt->execute(['id' => $articleId]);
$article = $stmt->fetch();

if (!$article) {
    set_flash('error', 'Article introuvable.');
    redirect('index.php');
}

$user = current_user();
$canEdit = $user['role'] === 'admin' || (int) $user['id'] === (int) $article['author_id'];

if (!$canEdit) {
    set_flash('error', 'Vous ne pouvez pas modifier cet article.');
    redirect('detail.php?id=' . $articleId);
}

$error = null;
$values = [
    'title' => $article['title'],
    'description' => $article['description'],
    'price' => (string) $article['price'],
    'stock_quantity' => (string) $article['stock_quantity'],
    'image_url' => $article['image_url'] ?? '',
];

if (isset($_POST['update_article'])) {
    $values['title'] = post_string('title');
    $values['description'] = post_string('description');
    $values['price'] = post_string('price');
    $values['stock_quantity'] = post_string('stock_quantity');
    $values['image_url'] = post_string('image_url');

    $price = (float) $values['price'];
    $stock = (int) $values['stock_quantity'];

    if ($values['title'] === '') {
        $error = 'Le titre est obligatoire.';
    } elseif ($values['description'] === '') {
        $error = 'La description est obligatoire.';
    } elseif ($price <= 0) {
        $error = 'Le prix doit être supérieur à 0.';
    } elseif ($stock < 0) {
        $error = 'Le stock ne peut pas être négatif.';
    } else {
        db()->beginTransaction();
        try {
            $updateArticle = db()->prepare(
                'UPDATE articles
                 SET title = :title,
                     description = :description,
                     price = :price,
                     image_url = :image_url
                 WHERE id = :id'
            );
            $updateArticle->execute([
                'title' => $values['title'],
                'description' => $values['description'],
                'price' => $price,
                'image_url' => $values['image_url'] !== '' ? $values['image_url'] : null,
                'id' => $articleId,
            ]);

            $updateStock = db()->prepare('UPDATE stock SET quantity = :quantity WHERE article_id = :article_id');
            $updateStock->execute([
                'quantity' => $stock,
                'article_id' => $articleId,
            ]);

            db()->commit();
            set_flash('success', 'Article mis à jour.');
            redirect('detail.php?id=' . $articleId);
        } catch (Throwable $exception) {
            db()->rollBack();
            $error = 'Erreur pendant la mise à jour: ' . $exception->getMessage();
        }
    }
}

if (isset($_POST['delete_article'])) {
    db()->beginTransaction();
    try {
        $deleteCart = db()->prepare('DELETE FROM cart WHERE article_id = :article_id');
        $deleteCart->execute(['article_id' => $articleId]);

        $deleteStock = db()->prepare('DELETE FROM stock WHERE article_id = :article_id');
        $deleteStock->execute(['article_id' => $articleId]);

        $deleteArticle = db()->prepare('DELETE FROM articles WHERE id = :id');
        $deleteArticle->execute(['id' => $articleId]);

        db()->commit();
        set_flash('success', 'Article supprimé.');
        redirect('index.php');
    } catch (Throwable $exception) {
        db()->rollBack();
        $error = 'Erreur pendant la suppression: ' . $exception->getMessage();
    }
}

render_header('Modifier article');
?>
<div class="form-card">
    <h1>Modifier l'article</h1>

    <?php if ($error): ?>
        <div class="flash flash-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="article_id" value="<?= e((string) $articleId) ?>">

        <label for="title">Titre</label>
        <input id="title" name="title" required value="<?= e($values['title']) ?>">

        <label for="description">Description</label>
        <textarea id="description" name="description" required><?= e($values['description']) ?></textarea>

        <label for="price">Prix (€)</label>
        <input id="price" name="price" type="number" min="0.01" step="0.01" required value="<?= e($values['price']) ?>">

        <label for="stock_quantity">Stock</label>
        <input id="stock_quantity" name="stock_quantity" type="number" min="0" step="1" required value="<?= e($values['stock_quantity']) ?>">

        <label for="image_url">Image (URL)</label>
        <input id="image_url" name="image_url" type="url" value="<?= e($values['image_url']) ?>" placeholder="https://...">

        <button type="submit" name="update_article" value="1">Sauvegarder</button>
    </form>
</div>

<div class="form-card">
    <h2>Supprimer l'article</h2>
    <form method="post" onsubmit="return confirm('Supprimer cet article ?');">
        <input type="hidden" name="article_id" value="<?= e((string) $articleId) ?>">
        <button class="btn-danger" type="submit" name="delete_article" value="1">Supprimer définitivement</button>
    </form>
</div>
<?php render_footer(); ?>
