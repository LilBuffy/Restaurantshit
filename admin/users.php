<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        flash('error', 'Invalid form submission.');
    } else {
        $targetId = (int) ($_POST['user_id'] ?? 0);
        $action = $_POST['action'] ?? '';

        if ($targetId === (int) $_SESSION['user_id']) {
            flash('error', 'You cannot modify your own admin account here.');
        } elseif ($action === 'toggle_status') {
            $db->prepare("UPDATE users SET status = IF(status='active','inactive','active') WHERE user_id = ?")->execute([$targetId]);
            flash('success', 'User status updated.');
        } elseif ($action === 'delete') {
            $hasOrders = $db->prepare('SELECT COUNT(*) FROM orders WHERE user_id = ?');
            $hasOrders->execute([$targetId]);
            if ($hasOrders->fetchColumn() > 0) {
                flash('error', 'Cannot delete a user with existing orders. Deactivate instead.');
            } else {
                $db->prepare('DELETE FROM users WHERE user_id = ?')->execute([$targetId]);
                flash('success', 'User deleted.');
            }
        }
    }
    redirect('/basta-masarap/admin/users.php');
}

$search = trim($_GET['search'] ?? '');
$where = '1=1';
$params = [];
if ($search !== '') {
    $where = '(u.username LIKE ? OR u.email LIKE ? OR u.full_name LIKE ?)';
    $like = "%{$search}%";
    $params = [$like, $like, $like];
}

$stmt = $db->prepare(
    "SELECT u.*, r.role_name, (SELECT COUNT(*) FROM orders WHERE user_id = u.user_id) AS order_count
     FROM users u JOIN roles r ON r.role_id = u.role_id
     WHERE {$where} ORDER BY u.created_at DESC"
);
$stmt->execute($params);
$users = $stmt->fetchAll();

$pageTitle = 'Users';
require __DIR__ . '/includes/admin_header.php';
?>
<h2 style="margin-bottom: 20px;">Users</h2>
<form method="GET" class="filters-bar">
    <input type="text" name="search" class="search-input" placeholder="Search by name, username, or email" value="<?= esc($search) ?>">
    <button class="btn btn-primary btn-sm" type="submit">Search</button>
</form>
<div class="table-wrap">
    <table class="data-table">
        <thead><tr><th>ID</th><th>Username</th><th>Full Name</th><th>Email</th><th>Role</th><th>Orders</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($users as $u): ?>
            <tr>
                <td>#<?= $u['user_id'] ?></td>
                <td><?= esc($u['username']) ?></td>
                <td><?= esc($u['full_name']) ?></td>
                <td><?= esc($u['email']) ?></td>
                <td><?= esc($u['role_name']) ?></td>
                <td><?= (int) $u['order_count'] ?></td>
                <td><span class="status-pill status-<?= $u['status'] === 'active' ? 'delivered' : 'cancelled' ?>"><?= ucfirst($u['status']) ?></span></td>
                <td><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
                <td style="display: flex; gap: 6px;">
                    <?php if ($u['role_name'] !== 'admin'): ?>
                    <form method="POST" onsubmit="return confirm('Toggle status for this user?');">
                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                        <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                        <input type="hidden" name="action" value="toggle_status">
                        <button class="btn btn-secondary btn-sm" type="submit"><?= $u['status'] === 'active' ? 'Deactivate' : 'Activate' ?></button>
                    </form>
                    <form method="POST" onsubmit="return confirm('Delete this user permanently?');">
                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                        <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                        <input type="hidden" name="action" value="delete">
                        <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
