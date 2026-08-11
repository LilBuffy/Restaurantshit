<?php
require_once __DIR__ . '/includes/bootstrap.php';
requireLogin();

if (empty($_SESSION['cart'])) {
    redirect('/basta-masarap/cart.php');
}

$deliveryFeeSetting = $db->query("SELECT setting_value FROM restaurant_settings WHERE setting_key = 'delivery_fee'")->fetchColumn();
$deliveryFee = (float) ($deliveryFeeSetting ?: 49);

$ids = array_keys($_SESSION['cart']);
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$stmt = $db->prepare("SELECT dish_id, name, price, is_available FROM dishes WHERE dish_id IN ({$placeholders})");
$stmt->execute($ids);
$dishById = [];
foreach ($stmt->fetchAll() as $row) {
    $dishById[$row['dish_id']] = $row;
}

$items = [];
$subtotal = 0.0;
foreach ($_SESSION['cart'] as $dishId => $qty) {
    if (!isset($dishById[$dishId]) || !$dishById[$dishId]['is_available']) continue;
    $dish = $dishById[$dishId];
    $lineTotal = (float) $dish['price'] * $qty;
    $subtotal += $lineTotal;
    $items[] = ['dish' => $dish, 'qty' => $qty, 'line_total' => $lineTotal];
}
$total = $subtotal + $deliveryFee;

$user = currentUser();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid form submission. Please try again.';
    } else {
        $address = trim($_POST['delivery_address'] ?? '');
        $contact = trim($_POST['contact_number'] ?? '');

        if ($address === '' || $contact === '') {
            $errors[] = 'Please provide a delivery address and contact number.';
        }
        if (!$items) {
            $errors[] = 'Your cart has no available items.';
        }

        if (!$errors) {
            try {
                $db->beginTransaction();

                $orderCode = generateOrderCode($db);
                $orderStmt = $db->prepare(
                    'INSERT INTO orders (order_code, user_id, delivery_address, contact_number, subtotal, delivery_fee, total, status)
                     VALUES (?, ?, ?, ?, ?, ?, ?, "pending")'
                );
                $orderStmt->execute([$orderCode, $user['user_id'], $address, $contact, $subtotal, $deliveryFee, $total]);
                $orderId = (int) $db->lastInsertId();

                $itemStmt = $db->prepare(
                    'INSERT INTO order_items (order_id, dish_id, dish_name, unit_price, quantity, line_total) VALUES (?, ?, ?, ?, ?, ?)'
                );
                foreach ($items as $item) {
                    $itemStmt->execute([
                        $orderId, $item['dish']['dish_id'], $item['dish']['name'],
                        $item['dish']['price'], $item['qty'], $item['line_total'],
                    ]);
                }

                $deliveryCode = generateDeliveryCode($db);
                $db->prepare('INSERT INTO deliveries (delivery_code, order_id, status) VALUES (?, ?, "pending")')
                   ->execute([$deliveryCode, $orderId]);

                $db->commit();
                $_SESSION['cart'] = [];
                redirect('/basta-masarap/orders.php?confirmed=' . $orderCode);
            } catch (Exception $e) {
                $db->rollBack();
                error_log('Checkout failed: ' . $e->getMessage());
                $errors[] = 'We could not process your order. Please try again.';
            }
        }
    }
}

$pageTitle = t('checkout_title');
require __DIR__ . '/includes/header.php';
?>
<section class="section" style="padding-top: 10px;">
    <div class="container">
        <div class="section-head"><h2><?= t('checkout_title') ?></h2></div>
        <?php foreach ($errors as $err): ?><div class="alert alert-error"><?= esc($err) ?></div><?php endforeach; ?>

        <div class="cart-layout">
            <form method="POST" class="card" style="padding: 24px;" data-validate>
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                <div class="form-group">
                    <label><?= t('delivery_address') ?></label>
                    <textarea class="form-control" name="delivery_address" required><?= esc($_POST['delivery_address'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label><?= t('contact_number') ?></label>
                    <input class="form-control" type="text" name="contact_number" value="<?= esc($_POST['contact_number'] ?? $user['contact_number'] ?? '') ?>" required>
                </div>
                <button class="btn btn-primary btn-block" type="submit"><?= t('place_order') ?> — <?= peso($total) ?></button>
            </form>
            <div class="summary-card">
                <h4 style="margin-bottom: 14px;"><?= t('cart_title') ?></h4>
                <?php foreach ($items as $item): ?>
                    <div class="summary-row"><span><?= $item['qty'] ?>× <?= esc($item['dish']['name']) ?></span><span><?= peso($item['line_total']) ?></span></div>
                <?php endforeach; ?>
                <div class="summary-row"><span><?= t('subtotal') ?></span><span><?= peso($subtotal) ?></span></div>
                <div class="summary-row"><span><?= t('delivery_fee') ?></span><span><?= peso($deliveryFee) ?></span></div>
                <div class="summary-row total"><span><?= t('total') ?></span><span><?= peso($total) ?></span></div>
            </div>
        </div>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
