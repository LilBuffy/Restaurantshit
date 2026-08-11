<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (isLoggedIn()) {
    redirect('/basta-masarap/index.php');
}

$errors = [];
$old = ['full_name' => '', 'username' => '', 'email' => '', 'contact_number' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid form submission. Please try again.';
    } else {
        $old['full_name'] = trim($_POST['full_name'] ?? '');
        $old['username'] = trim($_POST['username'] ?? '');
        $old['email'] = trim($_POST['email'] ?? '');
        $old['contact_number'] = trim($_POST['contact_number'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if ($old['full_name'] === '' || $old['username'] === '' || $old['email'] === '') {
            $errors[] = 'Please fill in all required fields.';
        }
        if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        if (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $old['username'])) {
            $errors[] = 'Username must be 3-30 characters (letters, numbers, underscore only).';
        }
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long.';
        }
        if ($password !== $confirm) {
            $errors[] = 'Passwords do not match.';
        }

        if (!$errors) {
            $check = $db->prepare('SELECT user_id FROM users WHERE username = ? OR email = ?');
            $check->execute([$old['username'], $old['email']]);
            if ($check->fetchColumn()) {
                $errors[] = 'That username or email is already registered.';
            }
        }

        if (!$errors) {
            $stmt = $db->prepare(
                'INSERT INTO users (role_id, username, email, password_hash, full_name, contact_number)
                 VALUES (2, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $old['username'],
                $old['email'],
                password_hash($password, PASSWORD_DEFAULT),
                $old['full_name'],
                $old['contact_number'] ?: null,
            ]);
            flash('success', 'Account created! You may now log in.');
            redirect('/basta-masarap/login.php');
        }
    }
}

$pageTitle = t('register_title');
require __DIR__ . '/includes/header.php';
?>
<div class="form-card">
    <h2 class="text-center" style="margin-bottom: 24px;"><?= t('register_title') ?></h2>
    <?php foreach ($errors as $err): ?><div class="alert alert-error"><?= esc($err) ?></div><?php endforeach; ?>
    <form method="POST" data-validate>
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
        <div class="form-group">
            <label><?= t('full_name') ?></label>
            <input class="form-control" type="text" name="full_name" value="<?= esc($old['full_name']) ?>" required>
        </div>
        <div class="form-group">
            <label><?= t('username') ?></label>
            <input class="form-control" type="text" name="username" value="<?= esc($old['username']) ?>" required>
        </div>
        <div class="form-group">
            <label><?= t('email') ?></label>
            <input class="form-control" type="email" name="email" value="<?= esc($old['email']) ?>" required>
        </div>
        <div class="form-group">
            <label><?= t('contact_number') ?></label>
            <input class="form-control" type="text" name="contact_number" value="<?= esc($old['contact_number']) ?>">
        </div>
        <div class="form-row">
            <div class="form-group">
                <label><?= t('password') ?></label>
                <input class="form-control" type="password" name="password" required minlength="8">
            </div>
            <div class="form-group">
                <label><?= t('confirm_password') ?></label>
                <input class="form-control" type="password" name="confirm_password" required minlength="8">
            </div>
        </div>
        <button class="btn btn-primary btn-block" type="submit"><?= t('nav_register') ?></button>
    </form>
    <p class="text-center" style="margin-top: 18px; font-size: 0.9rem;">
        <?= t('already_have_account') ?> <a href="/basta-masarap/login.php" style="color: var(--color-primary); font-weight: 600;"><?= t('nav_login') ?></a>
    </p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
