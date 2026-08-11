<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();

$statuses = ['pending', 'confirmed', 'preparing', 'out_for_delivery', 'delivered', 'cancelled'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        flash('error', 'Invalid form submission.');
    } else {
        $orderId = (int) ($_POST['order_id'] ?? 0);
        $newStatus = $_POST['status'] ?? '';
        if (in_array($newStatus, $statuses, true)) {
            $db->beginTransaction();
            $db->prepare('UPDATE orders SET status = ? WHERE order_id = ?')->execute([$newStatus, $orderId]);
            $db->prepare('UPDATE deliveries SET status = ? WHERE order_id = ?')->execute([$newStatus, $orderId]);
            $db->commit();
            flash('success', 'Order status updated.');
        } else {
            flash('error', 'Invalid status.');
        }
    }
    redirect('/basta-masarap/admin/orders.php');
}

$filterStatus = $_GET['status'] ?? '';
$where = '1=1';
$params = [];
if (in_array($filterStatus, $statuses, true)) {
    $where = 'o.status = ?';
    $params[] = $filterStatus;
}

$stmt = $db->prepare(
    "SELECT o.*, u.username, u.full_name, del.delivery_code
     FROM orders o JOIN users u ON u.user_id = o.user_id
     LEFT JOIN deliveries del ON del.order_id = o.order_id
     WHERE {$where} ORDER BY o.created_at DESC"
);
$stmt->execute($params);
$orders = $stmt->fetchAll();

$itemsStmt = $db->prepare('SELECT dish_name, quantity, line_total FROM order_items WHERE order_id = ?');

$pageTitle = 'Orders';
require __DIR__ . '/includes/admin_header.php';
?>
<h2 style="margin-bottom: 20px;">Orders</h2>
<div class="filters-bar">
    <div class="chip-filters">
        <a href="?status=" class="chip <?= $filterStatus === '' ? 'active' : '' ?>">All</a>
        <?php foreach ($statuses as $s): ?>
            <a href="?status=<?= $s ?>" class="chip <?= $filterStatus === $s ? 'active' : '' ?>"><?= statusLabel($s) ?></a>
        <?php endforeach; ?>
    </div>
</div>

<?php foreach ($orders as $order): $itemsStmt->execute([$order['order_id']]); $items = $itemsStmt->fetchAll(); ?>
<div class="card" style="padding: 18px 20px; margin-bottom: 14px;">
    <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 12px; align-items: center;">
        <div>
            <strong><?= esc($order['order_code']) ?></strong> · <?= esc($order['delivery_code'] ?? '—') ?>
            <p class="section-sub"><?= esc($order['full_name']) ?> (@<?= esc($order['username']) ?>) · <?= esc($order['contact_number']) ?></p>
            <p class="section-sub"><?= esc($order['delivery_address']) ?></p>
        </div>
        <form method="POST" style="display: flex; gap: 8px; align-items: center;">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
            <select name="status" class="select-input">
                <?php foreach ($statuses as $s): ?>
                    <option value="<?= $s ?>" <?= $order['status'] === $s ? 'selected' : '' ?>><?= statusLabel($s) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-primary btn-sm" type="submit">Update</button>
        </form>
    </div>
    <div class="table-wrap" style="margin-top: 12px;">
        <table class="data-table">
            <thead><tr><th>Item</th><th>Qty</th><th>Line Total</th></tr></thead>
            <tbody>
            <?php foreach ($items as $it): ?>
                <tr><td><?= esc($it['dish_name']) ?></td><td><?= (int) $it['quantity'] ?></td><td><?= peso((float) $it['line_total']) ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p style="text-align: right; margin-top: 8px;"><strong>Total: <?= peso((float) $order['total']) ?></strong> · <?= date('M j, Y g:i A', strtotime($order['created_at'])) ?></p>
</div>
<?php endforeach; ?>
<?php if (!$orders): ?><div class="empty-state"><p>No orders found.</p></div><?php endif; ?>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
