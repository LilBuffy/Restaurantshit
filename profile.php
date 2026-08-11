<?php
require_once __DIR__ . '/includes/bootstrap.php';
requireLogin();

$user = currentUser();
$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid form submission. Please try again.';
    } else {
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $contact = trim($_POST['contact_number'] ?? '');

        if ($fullName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid name and email.';
        } else {
            $dupe = $db->prepare('SELECT user_id FROM users WHERE email = ? AND user_id != ?');
            $dupe->execute([$email, $user['user_id']]);
            if ($dupe->fetchColumn()) {
                $errors[] = 'That email is already used by another account.';
            }
        }

        if (!$errors) {
            $upd = $db->prepare('UPDATE users SET full_name = ?, email = ?, contact_number = ? WHERE user_id = ?');
            $upd->execute([$fullName, $email, $contact ?: null, $user['user_id']]);
            $success = 'Profile updated.';
            $user = currentUser();
        }
    }
}

$reviewsStmt = $db->prepare(
    "SELECT r.*, d.name AS dish_name FROM reviews r JOIN dishes d ON d.dish_id = r.dish_id
     WHERE r.user_id = ? ORDER BY r.created_at DESC"
);
$reviewsStmt->execute([$user['user_id']]);
$myReviews = $reviewsStmt->fetchAll();

$pageTitle = t('profile_title');
require __DIR__ . '/includes/header.php';
?>
<section class="section" style="padding-top: 10px;">
    <div class="container layout-sidebar">
        <div class="sidebar-card">
            <a href="/basta-masarap/profile.php" class="active"><?= t('nav_profile') ?></a>
            <a href="/basta-masarap/orders.php"><?= t('nav_orders') ?></a>
            <a href="/basta-masarap/favorites.php"><?= t('nav_favorites') ?></a>
            <a href="/basta-masarap/wishlist.php"><?= t('nav_wishlist') ?></a>
        </div>
        <div>
            <div class="card" style="padding: 28px; margin-bottom: 24px;">
                <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 20px;">
                    <div style="width: 64px; height: 64px; border-radius: 50%; background: var(--color-primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; font-weight: 700;">
                        <?= esc(mb_substr($user['full_name'], 0, 1)) ?>
                    </div>
                    <div>
                        <h3><?= esc($user['full_name']) ?></h3>
                        <p class="section-sub">@<?= esc($user['username']) ?> · <?= t('member_since') ?> <?= date('M Y', strtotime($user['created_at'])) ?></p>
                    </div>
                </div>

                <?php foreach ($errors as $e): ?><div class="alert alert-error"><?= esc($e) ?></div><?php endforeach; ?>
                <?php if ($success): ?><div class="alert alert-success"><?= esc($success) ?></div><?php endif; ?>

                <form method="POST" data-validate>
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <div class="form-row">
                        <div class="form-group">
                            <label><?= t('full_name') ?></label>
                            <input class="form-control" type="text" name="full_name" value="<?= esc($user['full_name']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label><?= t('email') ?></label>
                            <input class="form-control" type="email" name="email" value="<?= esc($user['email']) ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label><?= t('contact_number') ?></label>
                        <input class="form-control" type="text" name="contact_number" value="<?= esc($user['contact_number'] ?? '') ?>">
                    </div>
                    <button class="btn btn-primary" type="submit"><?= t('save_changes') ?></button>
                </form>
            </div>

            <div class="card" style="padding: 24px;">
                <h4 style="margin-bottom: 14px;"><?= t('reviews') ?></h4>
                <?php if (!$myReviews): ?>
                    <p class="section-sub">—</p>
                <?php endif; ?>
                <?php foreach ($myReviews as $rev): ?>
                    <div class="review-item">
                        <div class="review-head">
                            <span class="review-user"><?= esc($rev['dish_name']) ?></span>
                            <span class="review-date"><?= timeAgo($rev['created_at']) ?></span>
                        </div>
                        <div class="review-stars"><?= str_repeat('★', (int) $rev['rating']) . str_repeat('☆', 5 - (int) $rev['rating']) ?></div>
                        <p style="margin-top: 4px;"><?= esc($rev['comment']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
