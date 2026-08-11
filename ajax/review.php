<?php
require_once __DIR__ . '/../includes/bootstrap.php';

if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Please log in to leave a review.'], 401);
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];

if (!verifyCsrf($input['csrf_token'] ?? null)) {
    jsonResponse(['success' => false, 'message' => 'Invalid or expired session. Please refresh the page.'], 403);
}

$action = $input['action'] ?? 'create';
$userId = $_SESSION['user_id'];

if ($action === 'create') {
    $dishId = (int) ($input['dish_id'] ?? 0);
    $rating = (int) ($input['rating'] ?? 0);
    $comment = trim((string) ($input['comment'] ?? ''));

    if ($dishId <= 0 || $rating < 1 || $rating > 5 || $comment === '') {
        jsonResponse(['success' => false, 'message' => 'Please give a rating and a comment.'], 400);
    }

    $existing = $db->prepare('SELECT review_id FROM reviews WHERE user_id = ? AND dish_id = ?');
    $existing->execute([$userId, $dishId]);
    if ($existing->fetchColumn()) {
        jsonResponse(['success' => false, 'message' => 'You already reviewed this dish. Edit your existing review instead.'], 409);
    }

    $stmt = $db->prepare('INSERT INTO reviews (user_id, dish_id, rating, comment) VALUES (?, ?, ?, ?)');
    $stmt->execute([$userId, $dishId, $rating, $comment]);
    jsonResponse(['success' => true, 'message' => 'Review posted. Salamat!', 'reload' => true]);
}

if ($action === 'edit') {
    $reviewId = (int) ($input['review_id'] ?? 0);
    $rating = (int) ($input['rating'] ?? 0);
    $comment = trim((string) ($input['comment'] ?? ''));

    if ($reviewId <= 0 || $rating < 1 || $rating > 5 || $comment === '') {
        jsonResponse(['success' => false, 'message' => 'Invalid review data.'], 400);
    }

    $stmt = $db->prepare('UPDATE reviews SET rating = ?, comment = ?, is_edited = 1 WHERE review_id = ? AND user_id = ?');
    $stmt->execute([$rating, $comment, $reviewId, $userId]);
    if ($stmt->rowCount() === 0) {
        jsonResponse(['success' => false, 'message' => 'Review not found or not yours.'], 404);
    }
    jsonResponse(['success' => true, 'message' => 'Review updated.', 'reload' => true]);
}

if ($action === 'delete') {
    $reviewId = (int) ($input['review_id'] ?? 0);
    $stmt = $db->prepare('DELETE FROM reviews WHERE review_id = ? AND user_id = ?');
    $stmt->execute([$reviewId, $userId]);
    if ($stmt->rowCount() === 0) {
        jsonResponse(['success' => false, 'message' => 'Review not found or not yours.'], 404);
    }
    jsonResponse(['success' => true, 'message' => 'Review deleted.', 'reload' => true]);
}

jsonResponse(['success' => false, 'message' => 'Unknown action.'], 400);
