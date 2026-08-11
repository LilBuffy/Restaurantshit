<?php
require_once __DIR__ . '/includes/bootstrap.php';
requireLogin();

$stmt = $db->prepare(
    "SELECT o.*, del.delivery_code, del.status AS delivery_status
     FROM orders o LEFT JOIN deliveries del ON del.order_id = o.order_id
     WHERE o.user_id = ? ORDER BY o.created_at DESC"
);
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();

$itemsStmt = $db->prepare('SELECT * FROM order_items WHERE order_id = ?');

$confirmedCode = $_GET['confirmed'] ?? null;

$pageTitle = t('order_history');
require __DIR__ . '/includes/header.php';
?>
<section class="section" style="padding-top: 10px;">
    <div class="container">
        <?php if ($confirmedCode): ?>
        <div class="card" style="padding: 24px; margin-bottom: 28px; border-color: var(--color-success); background: #f2fbf5;">
            <h3 style="margin-bottom: 6px;">🎉 <?= t('order_confirmed_title') ?></h3>
            <p><?= t('order_id') ?>: <strong><?= esc($confirmedCode) ?></strong></p>
        </div>
        <?php endif; ?>

        <div class="section-head"><h2><?= t('order_history') ?></h2></div>

        <?php if (!$orders): ?>
            <div class="empty-state">
                <div class="icon">📦</div>
                <p><?= t('no_orders') ?></p>
                <a href="/basta-masarap/menu.php" class="btn btn-primary" style="margin-top: 16px;"><?= t('hero_cta') ?></a>
            </div>
        <?php else: ?>
        <?php foreach ($orders as $order): $itemsStmt->execute([$order['order_id']]); $orderItems = $itemsStmt->fetchAll(); ?>
            <div class="card" style="padding: 20px; margin-bottom: 16px;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 12px;">
                    <div>
                        <strong><?= t('order_id') ?>: <?= esc($order['order_code']) ?></strong>
                        <p class="section-sub"><?= t('delivery_id') ?>: <?= esc($order['delivery_code'] ?? '—') ?></p>
                    </div>
                    <span class="status-pill status-<?= esc($order['status']) ?>"><?= statusLabel($order['status']) ?></span>
                </div>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead><tr><th><?= t('nav_menu') ?></th><th>Qty</th><th><?= t('order_total') ?></th></tr></thead>
                        <tbody>
                        <?php foreach ($orderItems as $oi): ?>
                            <tr><td><?= esc($oi['dish_name']) ?></td><td><?= (int) $oi['quantity'] ?></td><td><?= peso((float) $oi['line_total']) ?></td></tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div style="display: flex; justify-content: space-between; margin-top: 12px; font-size: 0.88rem;">
                    <span class="section-sub"><?= t('order_date') ?>: <?= date('M j, Y g:i A', strtotime($order['created_at'])) ?></span>
                    <strong><?= t('total') ?>: <?= peso((float) $order['total']) ?></strong>
                </div>
            </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
