<?php

function isLoggedIn(): bool
{
    return !empty($_SESSION['user_id']);
}

function isAdmin(): bool
{
    return isLoggedIn() && ($_SESSION['role'] ?? '') === 'admin';
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '/basta-masarap/';
        redirect('/basta-masarap/login.php');
    }
}

function requireAdmin(): void
{
    if (!isAdmin()) {
        http_response_code(403);
        die('Access denied.');
    }
}

function currentUser(): ?array
{
    static $user = null;
    if (!isLoggedIn()) {
        return null;
    }
    if ($user === null) {
        $db = Database::connect();
        $stmt = $db->prepare('SELECT user_id, username, email, full_name, contact_number, profile_picture, created_at FROM users WHERE user_id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch() ?: null;
    }
    return $user;
}

function attemptLogin(PDO $db, string $identifier, string $password): array
{
    $stmt = $db->prepare(
        "SELECT u.user_id, u.username, u.password_hash, u.status, r.role_name
         FROM users u JOIN roles r ON r.role_id = u.role_id
         WHERE u.username = ? OR u.email = ? LIMIT 1"
    );
    $stmt->execute([$identifier, $identifier]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return ['success' => false, 'message' => 'Invalid username/email or password.'];
    }
    if ($user['status'] !== 'active') {
        return ['success' => false, 'message' => 'This account has been deactivated. Please contact support.'];
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role_name'];

    return ['success' => true];
}
