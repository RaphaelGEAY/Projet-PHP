<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

require_login();

$userId = (int) current_user()['id'];

if (is_post()) {
    if (isset($_POST['remove_item'])) {
        $cartId = post_int('cart_id');
        if ($cartId > 0) {
            $delete = db()->prepare('DELETE FROM cart WHERE id = :id AND user_id = :user_id');
            $delete->execute([
                'id' => $cartId,
                'user_id' => $userId,
            ]);
            set_flash('success', 'Article retiré du panier.');
        }
        redirect('cart/');
    }

    if (isset($_POST['update_qty'])) {
        $cartId = post_int('cart_id');
        $newQty = post_int('quantity', 1);

        $stmt = db()->prepare(
            'SELECT c.id, c.quantity, COALESCE(s.quantity, 0) AS stock_quantity
             FROM cart c
             LEFT JOIN stock s ON s.article_id = c.article_id
             WHERE c.id = :id AND c.user_id = :user_id
             LIMIT 1'
        );
        $stmt->execute([
            'id' => $cartId,
            'user_id' => $userId,
        ]);
        $row = $stmt->fetch();

        if (!$row) {
            set_flash('error', 'Ligne de panier introuvable.');
            redirect('cart/');
        }

        if ($newQty <= 0) {
            $delete = db()->prepare('DELETE FROM cart WHERE id = :id AND user_id = :user_id');
            $delete->execute([
                'id' => $cartId,
                'user_id' => $userId,
            ]);
            set_flash('success', 'Article retiré du panier.');
            redirect('cart/');
        }

        if ($newQty > (int) $row['stock_quantity']) {
            set_flash('error', 'Quantité supérieure au stock disponible.');
            redirect('cart/');
        }

        $update = db()->prepare('UPDATE cart SET quantity = :quantity WHERE id = :id AND user_id = :user_id');
        $update->execute([
            'quantity' => $newQty,
            'id' => $cartId,
            'user_id' => $userId,
        ]);

        set_flash('success', 'Quantité mise à jour.');
        redirect('cart/');
    }
}

$itemsStmt = db()->prepare(
    'SELECT
        c.id AS cart_id,
        c.quantity,
        a.id AS article_id,
        a.title,
        a.price,
        a.image_url,
        COALESCE(s.quantity, 0) AS stock_quantity
     FROM cart c
     INNER JOIN articles a ON a.id = c.article_id
     LEFT JOIN stock s ON s.article_id = a.id
     WHERE c.user_id = :user_id
     ORDER BY c.id DESC'
);
$itemsStmt->execute(['user_id' => $userId]);
$items = $itemsStmt->fetchAll();

$total = 0.0;
$stockError = false;
foreach ($items as $item) {
    $total += (float) $item['price'] * (int) $item['quantity'];
    if ((int) $item['quantity'] > (int) $item['stock_quantity']) {
        $stockError = true;
    }
}

$balance = (float) current_user()['balance'];

render_header('Panier');
?>
<div class="table-card">
    <h1>Votre panier</h1>

    <?php if (empty($items)): ?>
        <p>Votre panier est vide.</p>
        <a class="btn" href="<?= e(url('')) ?>">Voir les voitures</a>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Article</th>
                    <th>Prix</th>
                    <th>Stock</th>
                    <th>Quantité</th>
                    <th>Sous-total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <?php
                    $lineTotal = (float) $item['price'] * (int) $item['quantity'];
                    $invalidQty = (int) $item['quantity'] > (int) $item['stock_quantity'];
                    ?>
                    <tr>
                        <td>
                            <a href="<?= e(url('detail/?id=' . $item['article_id'])) ?>"><?= e($item['title']) ?></a>
                            <?php if ($invalidQty): ?>
                                <div class="muted" style="color:#b42318;">Stock insuffisant pour cette quantité.</div>
                            <?php endif; ?>
                        </td>
                        <td><?= e(format_price((float) $item['price'])) ?></td>
                        <td><?= e((string) $item['stock_quantity']) ?></td>
                        <td>
                            <form method="post" class="inline-form">
                                <input type="hidden" name="cart_id" value="<?= e((string) $item['cart_id']) ?>">
                                <input type="number" name="quantity" min="0" step="1" value="<?= e((string) $item['quantity']) ?>" required>
                                <button type="submit" name="update_qty" value="1">Mettre à jour</button>
                            </form>
                        </td>
                        <td><?= e(format_price($lineTotal)) ?></td>
                        <td>
                            <form method="post" onsubmit="return confirm('Supprimer cet élément du panier ?');">
                                <input type="hidden" name="cart_id" value="<?= e((string) $item['cart_id']) ?>">
                                <button class="btn-danger" type="submit" name="remove_item" value="1">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="card">
            <p><strong>Total:</strong> <?= e(format_price($total)) ?></p>
            <p><strong>Votre solde:</strong> <?= e(format_price($balance)) ?></p>

            <?php if ($stockError): ?>
                <p class="flash flash-error">Certaines quantités dépassent le stock disponible. Corrigez le panier avant validation.</p>
            <?php endif; ?>

            <?php if ($total > $balance): ?>
                <p class="flash flash-warning">Solde insuffisant pour cette commande.</p>
            <?php endif; ?>

            <a class="btn" href="<?= e(url('cart/validate/')) ?>">Passer à la confirmation</a>
        </div>
    <?php endif; ?>
</div>
<?php render_footer(); ?>
