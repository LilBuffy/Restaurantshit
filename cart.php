<?php
require_once __DIR__ . '/includes/bootstrap.php';

$deliveryFeeSetting = $db->query("SELECT setting_value FROM restaurant_settings WHERE setting_key = 'delivery_fee'")->fetchColumn();
$deliveryFee = (float) ($deliveryFeeSetting ?: 49);

$items = [];
$subtotal = 0.0;

if (!empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $db->prepare("SELECT dish_id, name, price, is_available, image_path FROM dishes WHERE dish_id IN ({$placeholders})");
    $stmt->execute($ids);
    $dishRows = $stmt->fetchAll();
    $dishById = [];
    foreach ($dishRows as $row) {
        $dishById[$row['dish_id']] = $row;
    }
    foreach ($_SESSION['cart'] as $dishId => $qty) {
        if (!isset($dishById[$dishId])) {
            unset($_SESSION['cart'][$dishId]);
            continue;
        }
        $dish = $dishById[$dishId];
        $lineTotal = (float) $dish['price'] * $qty;
        $subtotal += $lineTotal;
        $items[] = ['dish' => $dish, 'qty' => $qty, 'line_total' => $lineTotal];
    }
}

$total = $items ? $subtotal + $deliveryFee : 0;

$pageTitle = t('cart_title');
require __DIR__ . '/includes/header.php';
?>
<section class="section" style="padding-top: 10px;">
    <div class="container">
        <div class="section-head"><h2><?= t('cart_title') ?></h2></div>

        <?php if (!$items): ?>
            <div class="empty-state">
                <div class="icon">🛒</div>
                <p><?= t('cart_empty') ?></p>
                <a href="/basta-masarap/menu.php" class="btn btn-primary" style="margin-top: 16px;"><?= t('continue_shopping') ?></a>
            </div>
        <?php else: ?>
        <div class="cart-layout">
            <div class="card" style="padding: 8px 20px;" id="cartItems">
                <?php foreach ($items as $item): $dish = $item['dish']; ?>
                <div class="cart-item" data-dish-id="<?= $dish['dish_id'] ?>">
                    <div class="cart-item-img"><?= dishImageHtml($dish) ?></div>
                    <div style="flex: 1;">
                        <h4><?= esc($dish['name']) ?></h4>
                        <p class="section-sub"><?= peso((float) $dish['price']) ?> <?= currentLang() === 'fil' ? 'bawat piraso' : 'each' ?></p>
                    </div>
                    <div class="qty-control">
                        <button data-qty-decrease>−</button>
                        <span><?= $item['qty'] ?></span>
                        <button data-qty-increase>+</button>
                    </div>
                    <div style="width: 90px; text-align: right; font-weight: 700;"><?= peso($item['line_total']) ?></div>
                    <button class="btn btn-ghost btn-sm" data-remove-item>✕</button>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="summary-card">
                <div class="summary-row"><span><?= t('subtotal') ?></span><span><?= peso($subtotal) ?></span></div>
                <div class="summary-row"><span><?= t('delivery_fee') ?></span><span><?= peso($deliveryFee) ?></span></div>
                <div class="summary-row total"><span><?= t('total') ?></span><span><?= peso($total) ?></span></div>
                <a href="/basta-masarap/checkout.php" class="btn btn-primary btn-block" style="margin-top: 16px;"><?= t('checkout') ?></a>
                <a href="/basta-masarap/menu.php" class="btn btn-ghost btn-block" style="margin-top: 8px;"><?= t('continue_shopping') ?></a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
