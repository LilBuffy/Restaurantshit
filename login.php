<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (isLoggedIn()) {
    redirect('/basta-masarap/index.php');
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        $error = 'Invalid form submission. Please try again.';
    } else {
        $identifier = trim($_POST['identifier'] ?? '');
        $password = $_POST['password'] ?? '';
        $result = attemptLogin($db, $identifier, $password);

        if ($result['success']) {
            $redirectTo = $_SESSION['redirect_after_login'] ?? '/basta-masarap/index.php';
            unset($_SESSION['redirect_after_login']);
            redirect($redirectTo);
        } else {
            $error = $result['message'];
        }
    }
}

$pageTitle = t('login_title');
require __DIR__ . '/includes/header.php';
?>
<div class="form-card">
    <h2 class="text-center" style="margin-bottom: 24px;"><?= t('login_title') ?></h2>
    <?php if ($error): ?><div class="alert alert-error"><?= esc($error) ?></div><?php endif; ?>
    <form method="POST" data-validate>
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
        <div class="form-group">
            <label><?= t('username_or_email') ?></label>
            <input class="form-control" type="text" name="identifier" required autofocus>
        </div>
        <div class="form-group">
            <label><?= t('password') ?></label>
            <input class="form-control" type="password" name="password" required>
        </div>
        <button class="btn btn-primary btn-block" type="submit"><?= t('nav_login') ?></button>
    </form>
    <p class="text-center" style="margin-top: 18px; font-size: 0.9rem;">
        <?= t('no_account') ?> <a href="/basta-masarap/register.php" style="color: var(--color-primary); font-weight: 600;"><?= t('nav_register') ?></a>
    </p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
