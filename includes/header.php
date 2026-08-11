<?php
$cartCount = array_sum($_SESSION['cart'] ?? []);
$currentPage = basename($_SERVER['SCRIPT_NAME']);
?>
<!DOCTYPE html>
<html lang="<?= currentLang() === 'fil' ? 'tl' : 'en' ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="<?= csrfToken() ?>">
<title><?= isset($pageTitle) ? esc($pageTitle) . ' — Basta Masarap' : 'Basta Masarap Restaurant' ?></title>
<link rel="stylesheet" href="/basta-masarap/assets/css/style.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<header class="navbar">
    <div class="container">
        <a href="/basta-masarap/index.php" class="brand">
            <span class="brand-badge">BM</span> Basta Masarap
        </a>
        <nav class="nav-links" id="navLinks">
            <a href="/basta-masarap/index.php" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>"><?= t('nav_home') ?></a>
            <a href="/basta-masarap/menu.php" class="<?= $currentPage === 'menu.php' ? 'active' : '' ?>"><?= t('nav_menu') ?></a>
            <?php if (isLoggedIn()): ?>
                <a href="/basta-masarap/orders.php" class="<?= $currentPage === 'orders.php' ? 'active' : '' ?>"><?= t('nav_orders') ?></a>
                <a href="/basta-masarap/favorites.php" class="<?= $currentPage === 'favorites.php' ? 'active' : '' ?>"><?= t('nav_favorites') ?></a>
                <a href="/basta-masarap/wishlist.php" class="<?= $currentPage === 'wishlist.php' ? 'active' : '' ?>"><?= t('nav_wishlist') ?></a>
                <?php if (isAdmin()): ?><a href="/basta-masarap/admin/index.php"><?= t('nav_admin') ?></a><?php endif; ?>
            <?php endif; ?>
        </nav>
        <div class="nav-actions">
            <div class="lang-switch">
                <a href="?lang=en" class="<?= currentLang() === 'en' ? 'active' : '' ?>">EN</a>
                <a href="?lang=fil" class="<?= currentLang() === 'fil' ? 'active' : '' ?>">FIL</a>
            </div>
            <a href="/basta-masarap/cart.php" class="icon-btn" title="<?= t('nav_cart') ?>">
                🛒<?php if ($cartCount > 0): ?><span class="cart-badge"><?= $cartCount ?></span><?php endif; ?>
            </a>
            <?php if (isLoggedIn()): ?>
                <div class="dropdown">
                    <button class="icon-btn" id="userMenuToggle">👤</button>
                    <div class="dropdown-menu" id="userMenu">
                        <a href="/basta-masarap/profile.php"><?= t('nav_profile') ?></a>
                        <a href="/basta-masarap/orders.php"><?= t('nav_orders') ?></a>
                        <div class="dropdown-divider"></div>
                        <a href="/basta-masarap/logout.php"><?= t('nav_logout') ?></a>
                    </div>
                </div>
            <?php else: ?>
                <a href="/basta-masarap/login.php" class="btn btn-secondary btn-sm"><?= t('nav_login') ?></a>
                <a href="/basta-masarap/register.php" class="btn btn-primary btn-sm"><?= t('nav_register') ?></a>
            <?php endif; ?>
            <button class="mobile-toggle" id="mobileToggle">☰</button>
        </div>
    </div>
</header>
<div class="toast-stack" id="toastStack"></div>
<main>
<div class="container" style="padding-top: 20px;">
<?php foreach (getFlashes() as $f): ?>
    <div class="alert alert-<?= $f['type'] === 'error' ? 'error' : 'success' ?>"><?= esc($f['message']) ?></div>
<?php endforeach; ?>
</div>
