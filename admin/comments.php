<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        flash('error', 'Invalid form submission.');
    } else {
        $reviewId = (int) ($_POST['review_id'] ?? 0);
        $action = $_POST['action'] ?? '';
        if ($action === 'hide') {
            $db->prepare("UPDATE reviews SET status = 'hidden' WHERE review_id = ?")->execute([$reviewId]);
            flash('success', 'Review hidden.');
        } elseif ($action === 'unhide') {
            $db->prepare("UPDATE reviews SET status = 'visible' WHERE review_id = ?")->execute([$reviewId]);
            flash('success', 'Review restored.');
        } elseif ($action === 'delete') {
            $db->prepare('DELETE FROM reviews WHERE review_id = ?')->execute([$reviewId]);
            flash('success', 'Review deleted.');
        }
    }
    redirect('/basta-masarap/admin/comments.php');
}

$filter = $_GET['filter'] ?? '';
$having = $filter === 'reported' ? 'HAVING report_count > 0' : '';

$reviews = $db->query(
    "SELECT r.*, u.username, d.name AS dish_name,
        (SELECT COUNT(*) FROM review_reports WHERE review_id = r.review_id) AS report_count
     FROM reviews r
     JOIN users u ON u.user_id = r.user_id JOIN dishes d ON d.dish_id = r.dish_id
     {$having}
     ORDER BY report_count DESC, r.created_at DESC"
)->fetchAll();

$pageTitle = 'Comments';
require __DIR__ . '/includes/admin_header.php';
?>
<h2 style="margin-bottom: 20px;">Comments &amp; Reviews</h2>
<div class="filters-bar">
    <div class="chip-filters">
        <a href="?filter=" class="chip <?= $filter === '' ? 'active' : '' ?>">All</a>
        <a href="?filter=reported" class="chip <?= $filter === 'reported' ? 'active' : '' ?>">🚩 Reported Only</a>
    </div>
</div>
<div class="table-wrap">
    <table class="data-table">
        <thead><tr><th>Dish</th><th>User</th><th>Rating</th><th>Comment</th><th>Reports</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($reviews as $r): ?>
        <tr>
            <td><?= esc($r['dish_name']) ?></td>
            <td><?= esc($r['username']) ?></td>
            <td><?= str_repeat('★', (int) $r['rating']) ?></td>
            <td style="white-space: normal; max-width: 320px;"><?= esc($r['comment']) ?></td>
            <td><?= (int) $r['report_count'] > 0 ? '🚩 ' . (int) $r['report_count'] : '—' ?></td>
            <td><span class="status-pill status-<?= $r['status'] === 'visible' ? 'delivered' : 'cancelled' ?>"><?= ucfirst($r['status']) ?></span></td>
            <td><?= date('M j, Y', strtotime($r['created_at'])) ?></td>
            <td style="display: flex; gap: 6px;">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <input type="hidden" name="review_id" value="<?= $r['review_id'] ?>">
                    <input type="hidden" name="action" value="<?= $r['status'] === 'visible' ? 'hide' : 'unhide' ?>">
                    <button class="btn btn-secondary btn-sm" type="submit"><?= $r['status'] === 'visible' ? 'Hide' : 'Unhide' ?></button>
                </form>
                <form method="POST" onsubmit="return confirm('Delete this review permanently?');">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <input type="hidden" name="review_id" value="<?= $r['review_id'] ?>">
                    <input type="hidden" name="action" value="delete">
                    <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php if (!$reviews): ?><div class="empty-state"><p>No reviews yet.</p></div><?php endif; ?>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
