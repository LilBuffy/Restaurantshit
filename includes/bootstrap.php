<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';

if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'en';
}
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'fil'], true)) {
    $_SESSION['lang'] = $_GET['lang'];
}
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$db = Database::connect();
