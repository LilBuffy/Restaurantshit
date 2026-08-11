<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();

$stats = [
    'total_users' => (int) $db->query("SELECT COUNT(*) FROM users WHERE role_id = (SELECT role_id FROM roles WHERE role_name='customer')")->fetchColumn(),
    'total_orders' => (int) $db->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
    'total_revenue' => (float) $db->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status != 'cancelled'")->fetchColumn(),
    'pending_orders' => (int) $db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn(),
    'completed_orders' => (int) $db->query("SELECT COUNT(*) FROM orders WHERE status = 'delivered'")->fetchColumn(),
    'cancelled_orders' => (int) $db->query("SELECT COUNT(*) FROM orders WHERE status = 'cancelled'")->fetchColumn(),
    'total_dishes' => (int) $db->query('SELECT COUNT(*) FROM dishes')->fetchColumn(),
    'total_comments' => (int) $db->query('SELECT COUNT(*) FROM reviews')->fetchColumn(),
];

$recentOrders = $db->query(
    "SELECT o.order_code, o.total, o.status, o.created_at, u.username
     FROM orders o JOIN users u ON u.user_id = o.user_id
     ORDER BY o.created_at DESC LIMIT 8"
)->fetchAll();

$pageTitle = 'Dashboard';
require __DIR__ . '/includes/admin_header.php';
?>
<h2 style="margin-bottom: 20px;">Dashboard</h2>
<div class="stat-grid">
    <div class="stat-card"><div class="value"><?= $stats['total_users'] ?></div><div class="label">Total Users</div></div>
    <div class="stat-card"><div class="value"><?= $stats['total_orders'] ?></div><div class="label">Total Orders</div></div>
    <div class="stat-card"><div class="value"><?= peso($stats['total_revenue']) ?></div><div class="label">Total Revenue</div></div>
    <div class="stat-card"><div class="value"><?= $stats['pending_orders'] ?></div><div class="label">Pending Orders</div></div>
    <div class="stat-card"><div class="value"><?= $stats['completed_orders'] ?></div><div class="label">Completed Orders</div></div>
    <div class="stat-card"><div class="value"><?= $stats['cancelled_orders'] ?></div><div class="label">Cancelled Orders</div></div>
    <div class="stat-card"><div class="value"><?= $stats['total_dishes'] ?></div><div class="label">Total Dishes</div></div>
    <div class="stat-card"><div class="value"><?= $stats['total_comments'] ?></div><div class="label">Total Reviews</div></div>
</div>

<h3 style="margin-bottom: 14px;">Recent Orders</h3>
<div class="table-wrap">
    <table class="data-table">
        <thead><tr><th>Order ID</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th></tr></thead>
        <tbody>
        <?php foreach ($recentOrders as $o): ?>
            <tr>
                <td><?= esc($o['order_code']) ?></td>
                <td><?= esc($o['username']) ?></td>
                <td><?= peso((float) $o['total']) ?></td>
                <td><span class="status-pill status-<?= esc($o['status']) ?>"><?= statusLabel($o['status']) ?></span></td>
                <td><?= date('M j, Y', strtotime($o['created_at'])) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
