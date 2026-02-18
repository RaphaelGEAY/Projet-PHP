<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

require_login();

$values = [
    'title' => '',
    'description' => '',
    'price' => '',
    'stock_quantity' => '1',
    'image_url' => '',
];
$error = null;

if (is_post()) {
    $values['title'] = post_string('title');
    $values['description'] = post_string('description');
    $values['price'] = post_string('price');
    $values['stock_quantity'] = post_string('stock_quantity');
    $values['image_url'] = post_string('image_url');

    $price = (float) $values['price'];
    $stockQty = (int) $values['stock_quantity'];

    if ($values['title'] === '') {
        $error = 'Le nom de la voiture est obligatoire.';
    } elseif ($values['description'] === '') {
        $error = 'La description est obligatoire.';
    } elseif ($price <= 0) {
        $error = 'Le prix doit être supérieur à 0.';
    } elseif ($stockQty < 0) {
        $error = 'Le stock ne peut pas être négatif.';
    } else {
        db()->beginTransaction();
        try {
            $articleInsert = db()->prepare(
                'INSERT INTO articles (title, description, price, published_at, author_id, image_url)
                 VALUES (:title, :description, :price, NOW(), :author_id, :image_url)'
            );
            $articleInsert->execute([
                'title' => $values['title'],
                'description' => $values['description'],
                'price' => $price,
                'author_id' => current_user()['id'],
                'image_url' => $values['image_url'] !== '' ? $values['image_url'] : null,
            ]);

            $articleId = (int) db()->lastInsertId();

            $stockInsert = db()->prepare('INSERT INTO stock (article_id, quantity) VALUES (:article_id, :quantity)');
            $stockInsert->execute([
                'article_id' => $articleId,
                'quantity' => $stockQty,
            ]);

            db()->commit();

            set_flash('success', 'Article créé avec succès.');
            redirect('detail.php?id=' . $articleId);
        } catch (Throwable $exception) {
            db()->rollBack();
            $error = 'Impossible de créer l\'article: ' . $exception->getMessage();
        }
    }
}

render_header('Mettre en vente');
?>
<div class="form-card">
    <h1>Mettre une voiture en vente</h1>

    <?php if ($error): ?>
        <div class="flash flash-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <label for="title">Nom de la voiture</label>
        <input id="title" name="title" required value="<?= e($values['title']) ?>" placeholder="Ex: Bugatti Chiron Super Sport">

        <label for="description">Description</label>
        <textarea id="description" name="description" required><?= e($values['description']) ?></textarea>

        <label for="price">Prix (€)</label>
        <input id="price" name="price" type="number" min="0.01" step="0.01" required value="<?= e($values['price']) ?>">

        <label for="stock_quantity">Stock</label>
        <input id="stock_quantity" name="stock_quantity" type="number" min="0" step="1" required value="<?= e($values['stock_quantity']) ?>">

        <label for="image_url">Image (URL)</label>
        <input id="image_url" name="image_url" type="url" value="<?= e($values['image_url']) ?>" placeholder="https://...">

        <button type="submit">Publier l'article</button>
    </form>
</div>
<?php render_footer(); ?>
