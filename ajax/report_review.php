<?php
require_once __DIR__ . '/../includes/bootstrap.php';

if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Please log in to report a review.'], 401);
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];

if (!verifyCsrf($input['csrf_token'] ?? null)) {
    jsonResponse(['success' => false, 'message' => 'Invalid or expired session. Please refresh the page.'], 403);
}

$reviewId = (int) ($input['review_id'] ?? 0);
$userId = $_SESSION['user_id'];

if ($reviewId <= 0) {
    jsonResponse(['success' => false, 'message' => 'Invalid review.'], 400);
}

$owner = $db->prepare('SELECT user_id FROM reviews WHERE review_id = ?');
$owner->execute([$reviewId]);
$ownerId = $owner->fetchColumn();

if ($ownerId === false) {
    jsonResponse(['success' => false, 'message' => 'Review not found.'], 404);
}
if ((int) $ownerId === $userId) {
    jsonResponse(['success' => false, 'message' => 'You cannot report your own review.'], 400);
}

$stmt = $db->prepare('INSERT IGNORE INTO review_reports (review_id, user_id) VALUES (?, ?)');
$stmt->execute([$reviewId, $userId]);

jsonResponse(['success' => true, 'message' => $stmt->rowCount() > 0 ? 'Thanks — our team will take a look.' : 'You already reported this review.']);
