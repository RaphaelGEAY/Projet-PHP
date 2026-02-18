<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$q = trim((string) ($_GET['q'] ?? ''));
$sort = (string) ($_GET['sort'] ?? 'recent');

$orderBy = match ($sort) {
    'price_asc' => 'a.price ASC',
    'price_desc' => 'a.price DESC',
    default => 'a.published_at DESC',
};

$sql = "
    SELECT
        a.id,
        a.title,
        a.description,
        a.price,
        a.image_url,
        a.published_at,
        u.username,
        COALESCE(s.quantity, 0) AS stock_quantity
    FROM articles a
    INNER JOIN users u ON u.id = a.author_id
    LEFT JOIN stock s ON s.article_id = a.id
";

$params = [];
if ($q !== '') {
    $sql .= " WHERE a.title LIKE :search OR a.description LIKE :search ";
    $params['search'] = '%' . $q . '%';
}

$sql .= " ORDER BY {$orderBy}";

$stmt = db()->prepare($sql);
$stmt->execute($params);
$articles = $stmt->fetchAll();

render_header('Accueil');
?>
<div class="card">
    <h1>Voitures en vente</h1>
    <p class="muted">Des modèles pas chers aux hypercars de luxe à 1 000 000 000 €.</p>

    <form method="get" class="inline-form">
        <input type="text" name="q" placeholder="Rechercher une voiture" value="<?= e($q) ?>">
        <select name="sort">
            <option value="recent" <?= $sort === 'recent' ? 'selected' : '' ?>>Plus récent</option>
            <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Prix croissant</option>
            <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Prix décroissant</option>
        </select>
        <button type="submit">Appliquer</button>
    </form>
</div>

<?php if (empty($articles)): ?>
    <div class="card">
        <p>Aucun article trouvé.</p>
    </div>
<?php else: ?>
    <section class="grid">
        <?php foreach ($articles as $article): ?>
            <article class="card">
                <?php $imageSrc = media_src($article['image_url'] ?? ''); ?>
                <?php if ($imageSrc !== ''): ?>
                    <img
                        class="car-image"
                        src="<?= e($imageSrc) ?>"
                        alt="<?= e($article['title']) ?>"
                    >
                <?php endif; ?>
                <h2><?= e($article['title']) ?></h2>
                <p class="price"><?= e(format_price((float) $article['price'])) ?></p>
                <p class="muted">Vendeur: <?= e($article['username']) ?></p>
                <p class="muted">Stock: <?= e((string) $article['stock_quantity']) ?></p>
                <p><?= e(strlen($article['description']) > 120 ? substr($article['description'], 0, 117) . '...' : $article['description']) ?></p>
                <a class="btn" href="<?= e(url('detail.php?id=' . $article['id'])) ?>">Voir le détail</a>
            </article>
        <?php endforeach; ?>
    </section>
<?php endif; ?>
<?php render_footer(); ?>
