<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

require_login();

$userId = (int) current_user()['id'];

$cartStmt = db()->prepare(
    'SELECT
        c.article_id,
        c.quantity,
        a.title,
        a.price,
        COALESCE(s.quantity, 0) AS stock_quantity
     FROM cart c
     INNER JOIN articles a ON a.id = c.article_id
     LEFT JOIN stock s ON s.article_id = a.id
     WHERE c.user_id = :user_id
     ORDER BY c.id ASC'
);
$cartStmt->execute(['user_id' => $userId]);
$cartItems = $cartStmt->fetchAll();

if (empty($cartItems)) {
    set_flash('warning', 'Votre panier est vide.');
    redirect('cart/');
}

$total = 0.0;
$stockError = false;
foreach ($cartItems as $item) {
    $total += (float) $item['price'] * (int) $item['quantity'];
    if ((int) $item['quantity'] > (int) $item['stock_quantity']) {
        $stockError = true;
    }
}

$balance = (float) current_user()['balance'];
$error = null;

$billing = [
    'address' => '',
    'city' => '',
    'postal_code' => '',
];

if (is_post() && isset($_POST['confirm_order'])) {
    $billing['address'] = post_string('billing_address');
    $billing['city'] = post_string('billing_city');
    $billing['postal_code'] = post_string('billing_postal_code');

    if ($billing['address'] === '' || $billing['city'] === '' || $billing['postal_code'] === '') {
        $error = 'Toutes les informations de facturation sont obligatoires.';
    } elseif (!preg_match('/^[0-9A-Za-z\- ]{4,12}$/', $billing['postal_code'])) {
        $error = 'Code postal invalide.';
    } elseif ($stockError) {
        $error = 'Le panier contient des quantités supérieures au stock.';
    } elseif ($total > $balance) {
        $error = 'Solde insuffisant pour valider la commande.';
    } else {
        db()->beginTransaction();
        try {
            $userLock = db()->prepare('SELECT id, balance FROM users WHERE id = :id FOR UPDATE');
            $userLock->execute(['id' => $userId]);
            $lockedUser = $userLock->fetch();

            if (!$lockedUser) {
                throw new RuntimeException('Utilisateur introuvable.');
            }

            $lockCart = db()->prepare(
                'SELECT
                    c.article_id,
                    c.quantity,
                    a.title,
                    a.price,
                    COALESCE(s.quantity, 0) AS stock_quantity
                 FROM cart c
                 INNER JOIN articles a ON a.id = c.article_id
                 LEFT JOIN stock s ON s.article_id = a.id
                 WHERE c.user_id = :user_id
                 ORDER BY c.id ASC
                 FOR UPDATE'
            );
            $lockCart->execute(['user_id' => $userId]);
            $lockedItems = $lockCart->fetchAll();

            if (empty($lockedItems)) {
                throw new RuntimeException('Panier vide.');
            }

            $realTotal = 0.0;
            foreach ($lockedItems as $item) {
                if ((int) $item['quantity'] > (int) $item['stock_quantity']) {
                    throw new RuntimeException('Stock insuffisant pour ' . $item['title'] . '.');
                }
                $realTotal += (float) $item['price'] * (int) $item['quantity'];
            }

            if ($realTotal > (float) $lockedUser['balance']) {
                throw new RuntimeException('Solde insuffisant.');
            }

            $invoiceInsert = db()->prepare(
                'INSERT INTO invoice (user_id, transaction_date, amount, billing_address, billing_city, billing_postal_code)
                 VALUES (:user_id, NOW(), :amount, :billing_address, :billing_city, :billing_postal_code)'
            );
            $invoiceInsert->execute([
                'user_id' => $userId,
                'amount' => $realTotal,
                'billing_address' => $billing['address'],
                'billing_city' => $billing['city'],
                'billing_postal_code' => $billing['postal_code'],
            ]);

            $invoiceId = (int) db()->lastInsertId();

            $invoiceItemInsert = db()->prepare(
                'INSERT INTO invoice_items (invoice_id, article_id, article_name, unit_price, quantity)
                 VALUES (:invoice_id, :article_id, :article_name, :unit_price, :quantity)'
            );

            $stockUpdate = db()->prepare('UPDATE stock SET quantity = quantity - :ordered_qty WHERE article_id = :article_id');

            foreach ($lockedItems as $item) {
                $invoiceItemInsert->execute([
                    'invoice_id' => $invoiceId,
                    'article_id' => (int) $item['article_id'],
                    'article_name' => $item['title'],
                    'unit_price' => (float) $item['price'],
                    'quantity' => (int) $item['quantity'],
                ]);

                $stockUpdate->execute([
                    'ordered_qty' => (int) $item['quantity'],
                    'article_id' => (int) $item['article_id'],
                ]);
            }

            $balanceUpdate = db()->prepare('UPDATE users SET balance = balance - :amount WHERE id = :id');
            $balanceUpdate->execute([
                'amount' => $realTotal,
                'id' => $userId,
            ]);

            $cartDelete = db()->prepare('DELETE FROM cart WHERE user_id = :user_id');
            $cartDelete->execute(['user_id' => $userId]);

            db()->commit();
            current_user(true);
            set_flash('success', 'Commande validée. Facture #' . $invoiceId . ' générée.');
            redirect('account.php');
        } catch (Throwable $exception) {
            db()->rollBack();
            $error = $exception->getMessage();
        }
    }
}

render_header('Confirmation panier');
?>
<div class="table-card">
    <h1>Confirmation de commande</h1>

    <?php if ($error): ?>
        <div class="flash flash-error"><?= e($error) ?></div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Article</th>
                <th>Prix unitaire</th>
                <th>Quantité</th>
                <th>Stock</th>
                <th>Sous-total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cartItems as $item): ?>
                <tr>
                    <td><?= e($item['title']) ?></td>
                    <td><?= e(format_price((float) $item['price'])) ?></td>
                    <td><?= e((string) $item['quantity']) ?></td>
                    <td><?= e((string) $item['stock_quantity']) ?></td>
                    <td><?= e(format_price((float) $item['price'] * (int) $item['quantity'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="card">
        <p><strong>Total:</strong> <?= e(format_price($total)) ?></p>
        <p><strong>Votre solde:</strong> <?= e(format_price($balance)) ?></p>
    </div>
</div>

<div class="form-card">
    <h2>Informations de facturation</h2>
    <form method="post">
        <label for="billing_address">Adresse</label>
        <input id="billing_address" name="billing_address" required value="<?= e($billing['address']) ?>">

        <label for="billing_city">Ville</label>
        <input id="billing_city" name="billing_city" required value="<?= e($billing['city']) ?>">

        <label for="billing_postal_code">Code postal</label>
        <input id="billing_postal_code" name="billing_postal_code" required value="<?= e($billing['postal_code']) ?>">

        <button type="submit" name="confirm_order" value="1">Valider la commande</button>
    </form>
</div>
<?php render_footer(); ?>
