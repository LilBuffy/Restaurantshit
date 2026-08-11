<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$input = json_decode(file_get_contents('php://input'), true) ?? [];

if (!verifyCsrf($input['csrf_token'] ?? null)) {
    jsonResponse(['success' => false, 'message' => 'Invalid or expired session. Please refresh the page.'], 403);
}

$action = $input['action'] ?? '';
$dishId = (int) ($input['dish_id'] ?? 0);

if ($dishId <= 0) {
    jsonResponse(['success' => false, 'message' => 'Invalid dish.'], 400);
}

$stmt = $db->prepare('SELECT dish_id, is_available FROM dishes WHERE dish_id = ?');
$stmt->execute([$dishId]);
$dish = $stmt->fetch();
if (!$dish) {
    jsonResponse(['success' => false, 'message' => 'Dish not found.'], 404);
}

switch ($action) {
    case 'add':
        if (!$dish['is_available']) {
            jsonResponse(['success' => false, 'message' => 'This dish is currently unavailable.']);
        }
        $qty = max(1, (int) ($input['qty'] ?? 1));
        $_SESSION['cart'][$dishId] = ($_SESSION['cart'][$dishId] ?? 0) + $qty;
        jsonResponse(['success' => true, 'message' => t('add_to_cart') . ' ✓', 'cart_count' => array_sum($_SESSION['cart'])]);

    case 'update':
        $delta = (int) ($input['delta'] ?? 0);
        if (!isset($_SESSION['cart'][$dishId])) {
            jsonResponse(['success' => false, 'message' => 'Item not in cart.'], 404);
        }
        $_SESSION['cart'][$dishId] += $delta;
        if ($_SESSION['cart'][$dishId] <= 0) {
            unset($_SESSION['cart'][$dishId]);
        }
        jsonResponse(['success' => true, 'cart_count' => array_sum($_SESSION['cart'])]);

    case 'remove':
        unset($_SESSION['cart'][$dishId]);
        jsonResponse(['success' => true, 'cart_count' => array_sum($_SESSION['cart'])]);

    default:
        jsonResponse(['success' => false, 'message' => 'Unknown action.'], 400);
}
