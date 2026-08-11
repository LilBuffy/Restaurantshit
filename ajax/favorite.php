<?php
require_once __DIR__ . '/../includes/bootstrap.php';

if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Please log in to save favorites.'], 401);
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];

if (!verifyCsrf($input['csrf_token'] ?? null)) {
    jsonResponse(['success' => false, 'message' => 'Invalid or expired session. Please refresh the page.'], 403);
}

$dishId = (int) ($input['dish_id'] ?? 0);
$userId = $_SESSION['user_id'];

if ($dishId <= 0) {
    jsonResponse(['success' => false, 'message' => 'Invalid dish.'], 400);
}

$stmt = $db->prepare('SELECT favorite_id FROM favorites WHERE user_id = ? AND dish_id = ?');
$stmt->execute([$userId, $dishId]);

if ($stmt->fetchColumn()) {
    $db->prepare('DELETE FROM favorites WHERE user_id = ? AND dish_id = ?')->execute([$userId, $dishId]);
    jsonResponse(['success' => true, 'active' => false, 'message' => t('remove_favorite')]);
}

$db->prepare('INSERT INTO favorites (user_id, dish_id) VALUES (?, ?)')->execute([$userId, $dishId]);
jsonResponse(['success' => true, 'active' => true, 'message' => t('add_favorite')]);
