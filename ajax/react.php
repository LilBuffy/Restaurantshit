<?php
require_once __DIR__ . '/../includes/bootstrap.php';

if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Please log in to react to dishes.'], 401);
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];

if (!verifyCsrf($input['csrf_token'] ?? null)) {
    jsonResponse(['success' => false, 'message' => 'Invalid or expired session. Please refresh the page.'], 403);
}

$dishId = (int) ($input['dish_id'] ?? 0);
$type = $input['type'] ?? '';

if ($dishId <= 0 || !in_array($type, ['like', 'dislike'], true)) {
    jsonResponse(['success' => false, 'message' => 'Invalid request.'], 400);
}

$userId = $_SESSION['user_id'];

$stmt = $db->prepare('SELECT type FROM reactions WHERE user_id = ? AND dish_id = ?');
$stmt->execute([$userId, $dishId]);
$existing = $stmt->fetchColumn();

if ($existing === $type) {
    $del = $db->prepare('DELETE FROM reactions WHERE user_id = ? AND dish_id = ?');
    $del->execute([$userId, $dishId]);
    $reactionNow = null;
} elseif ($existing) {
    $upd = $db->prepare('UPDATE reactions SET type = ? WHERE user_id = ? AND dish_id = ?');
    $upd->execute([$type, $userId, $dishId]);
    $reactionNow = $type;
} else {
    $ins = $db->prepare('INSERT INTO reactions (user_id, dish_id, type) VALUES (?, ?, ?)');
    $ins->execute([$userId, $dishId, $type]);
    $reactionNow = $type;
}

$counts = $db->prepare("SELECT
    SUM(type = 'like') AS likes, SUM(type = 'dislike') AS dislikes
    FROM reactions WHERE dish_id = ?");
$counts->execute([$dishId]);
$row = $counts->fetch();

jsonResponse([
    'success' => true,
    'reaction' => $reactionNow,
    'likes' => (int) ($row['likes'] ?? 0),
    'dislikes' => (int) ($row['dislikes'] ?? 0),
]);
