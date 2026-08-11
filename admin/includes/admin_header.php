<?php
$adminPage = basename($_SERVER['SCRIPT_NAME']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="<?= csrfToken() ?>">
<title><?= esc($pageTitle ?? 'Admin') ?> — Basta Masarap Admin</title>
<link rel="stylesheet" href="/basta-masarap/assets/css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<header class="navbar">
    <div class="container">
        <a href="/basta-masarap/admin/index.php" class="brand"><span class="brand-badge">BM</span> Admin</a>
        <div class="nav-actions">
            <a href="/basta-masarap/index.php" class="btn btn-ghost btn-sm">← Back to Site</a>
            <a href="/basta-masarap/logout.php" class="btn btn-secondary btn-sm"><?= t('nav_logout') ?></a>
        </div>
    </div>
</header>
<div class="toast-stack" id="toastStack"></div>
<main>
<div class="container" style="padding-top: 24px;">
<?php foreach (getFlashes() as $f): ?>
    <div class="alert alert-<?= $f['type'] === 'error' ? 'error' : 'success' ?>"><?= esc($f['message']) ?></div>
<?php endforeach; ?>
<div class="layout-sidebar">
    <div class="sidebar-card">
        <a href="/basta-masarap/admin/index.php" class="<?= $adminPage === 'index.php' ? 'active' : '' ?>">📊 Dashboard</a>
        <a href="/basta-masarap/admin/dishes.php" class="<?= $adminPage === 'dishes.php' ? 'active' : '' ?>">🍽️ Dishes</a>
        <a href="/basta-masarap/admin/orders.php" class="<?= $adminPage === 'orders.php' ? 'active' : '' ?>">📦 Orders</a>
        <a href="/basta-masarap/admin/users.php" class="<?= $adminPage === 'users.php' ? 'active' : '' ?>">👥 Users</a>
        <a href="/basta-masarap/admin/comments.php" class="<?= $adminPage === 'comments.php' ? 'active' : '' ?>">💬 Comments</a>
        <a href="/basta-masarap/admin/settings.php" class="<?= $adminPage === 'settings.php' ? 'active' : '' ?>">⚙️ Settings</a>
    </div>
    <div>
