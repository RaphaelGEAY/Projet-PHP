<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$articleId = (int) ($_GET['id'] ?? 0);
if ($articleId <= 0) {
    set_flash('error', 'Article invalide.');
    redirect('index.php');
}

$stmt = db()->prepare(
    'SELECT a.*, u.username, COALESCE(s.quantity, 0) AS stock_quantity
     FROM articles a
     INNER JOIN users u ON u.id = a.author_id
     LEFT JOIN stock s ON s.article_id = a.id
     WHERE a.id = :id LIMIT 1'
);
$stmt->execute(['id' => $articleId]);
$article = $stmt->fetch();

if (!$article) {
    set_flash('error', 'Article introuvable.');
    redirect('index.php');
}

if (is_post() && isset($_POST['add_to_cart'])) {
    require_login();

    $wantedQty = max(1, post_int('quantity', 1));
    $stock = (int) $article['stock_quantity'];

    if ($stock <= 0) {
        set_flash('error', 'Cet article est en rupture de stock.');
        redirect('detail.php?id=' . $articleId);
    }

    $cartStmt = db()->prepare('SELECT id, quantity FROM cart WHERE user_id = :user_id AND article_id = :article_id LIMIT 1');
    $cartStmt->execute([
        'user_id' => current_user()['id'],
        'article_id' => $articleId,
    ]);
    $existing = $cartStmt->fetch();

    $newQty = $wantedQty;
    if ($existing) {
        $newQty += (int) $existing['quantity'];
    }

    if ($newQty > $stock) {
        set_flash('error', 'Quantité demandée supérieure au stock disponible.');
        redirect('detail.php?id=' . $articleId);
    }

    if ($existing) {
        $update = db()->prepare('UPDATE cart SET quantity = :quantity WHERE id = :id');
        $update->execute([
            'quantity' => $newQty,
            'id' => $existing['id'],
        ]);
    } else {
        $insert = db()->prepare('INSERT INTO cart (user_id, article_id, quantity) VALUES (:user_id, :article_id, :quantity)');
        $insert->execute([
            'user_id' => current_user()['id'],
            'article_id' => $articleId,
            'quantity' => $wantedQty,
        ]);
    }

    set_flash('success', 'Article ajouté au panier.');
    redirect('cart/');
}

$user = current_user();
$canEdit = $user && ($user['role'] === 'admin' || (int) $user['id'] === (int) $article['author_id']);

render_header('Détail article');
?>
<article class="card">
    <img
        class="car-image"
        src="<?= e($article['image_url'] ?: 'https://picsum.photos/900/450') ?>"
        alt="<?= e($article['title']) ?>"
    >

    <h1><?= e($article['title']) ?></h1>
    <p class="price"><?= e(format_price((float) $article['price'])) ?></p>
    <p class="muted">Vendeur: <?= e($article['username']) ?></p>
    <p class="muted">Publié le: <?= e(date('d/m/Y H:i', strtotime($article['published_at']))) ?></p>
    <p class="muted">Stock disponible: <?= e((string) $article['stock_quantity']) ?></p>

    <p><?= nl2br(e($article['description'])) ?></p>
</article>

<?php if (is_logged_in()): ?>
    <div class="form-card">
        <h2>Ajouter au panier</h2>
        <?php if ((int) $article['stock_quantity'] > 0): ?>
            <form method="post" class="inline-form">
                <input type="number" name="quantity" min="1" max="<?= e((string) $article['stock_quantity']) ?>" value="1" required>
                <button type="submit" name="add_to_cart" value="1">Ajouter</button>
            </form>
        <?php else: ?>
            <p class="flash flash-warning">Rupture de stock.</p>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="card">
        <p>Connectez-vous pour ajouter cet article au panier.</p>
        <a class="btn" href="<?= e(url('login.php')) ?>">Se connecter</a>
    </div>
<?php endif; ?>

<?php if ($canEdit): ?>
    <div class="form-card">
        <h2>Gestion de votre article</h2>
        <form method="post" action="<?= e(url('edit.php')) ?>">
            <input type="hidden" name="article_id" value="<?= e((string) $article['id']) ?>">
            <button type="submit" name="open_edit" value="1">Modifier / Supprimer</button>
        </form>
    </div>
<?php endif; ?>
<?php render_footer(); ?>
