<?php

function currentLang(): string
{
    return $_SESSION['lang'] ?? 'en';
}

function t(string $key): string
{
    static $dict = null;
    if ($dict === null) {
        $dict = require __DIR__ . '/lang.php';
    }
    $lang = currentLang();
    return $dict[$lang][$key] ?? $dict['en'][$key] ?? $key;
}

function dishText(array $dish, string $field): string
{
    $lang = currentLang();
    return $lang === 'fil' ? ($dish["{$field}_fil"] ?? $dish[$field]) : ($dish["{$field}_en"] ?? $dish[$field]);
}

function peso(float $amount): string
{
    return '₱' . number_format($amount, 2);
}

function esc(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(?string $token): bool
{
    return !empty($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function generateOrderCode(PDO $db): string
{
    $prefix = 'ORD-' . date('Ymd') . '-';
    $stmt = $db->prepare("SELECT order_code FROM orders WHERE order_code LIKE ? ORDER BY order_id DESC LIMIT 1");
    $stmt->execute([$prefix . '%']);
    $last = $stmt->fetchColumn();
    $next = $last ? ((int) substr($last, -4)) + 1 : 1;
    return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
}

function generateDeliveryCode(PDO $db): string
{
    $prefix = 'DEL-' . date('Ymd') . '-';
    $stmt = $db->prepare("SELECT delivery_code FROM deliveries WHERE delivery_code LIKE ? ORDER BY delivery_id DESC LIMIT 1");
    $stmt->execute([$prefix . '%']);
    $last = $stmt->fetchColumn();
    $next = $last ? ((int) substr($last, -4)) + 1 : 1;
    return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
}

function jsonResponse(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function redirect(string $path): void
{
    header("Location: {$path}");
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function getFlashes(): array
{
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}

function dishImageHtml(array $dish): string
{
    $path = $dish['image_path'] ?? '';
    $fullPath = __DIR__ . '/../' . $path;
    if ($path && file_exists($fullPath) && is_file($fullPath)) {
        return '<img src="/basta-masarap/' . esc($path) . '" alt="' . esc($dish['name']) . '">';
    }
    return '🍽️';
}

function statusLabel(string $status): string
{
    return t('status_' . $status);
}

function timeAgo(string $datetime): string
{
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 2592000) return floor($diff / 86400) . 'd ago';
    return date('M j, Y', strtotime($datetime));
}
