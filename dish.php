<?php
require_once __DIR__ . '/includes/bootstrap.php';

$dishId = (int) ($_GET['id'] ?? 0);
$stmt = $db->prepare(
    "SELECT d.*, c.name_en AS cat_en, c.name_fil AS cat_fil,
        (SELECT COUNT(*) FROM reactions WHERE dish_id = d.dish_id AND type='like') AS likes,
        (SELECT COUNT(*) FROM reactions WHERE dish_id = d.dish_id AND type='dislike') AS dislikes,
        (SELECT ROUND(AVG(rating),1) FROM reviews WHERE dish_id = d.dish_id AND status='visible') AS avg_rating,
        (SELECT COUNT(*) FROM reviews WHERE dish_id = d.dish_id AND status='visible') AS review_count
     FROM dishes d JOIN categories c ON c.category_id = d.category_id
     WHERE d.dish_id = ?"
);
$stmt->execute([$dishId]);
$dish = $stmt->fetch();

if (!$dish) {
    http_response_code(404);
    $pageTitle = 'Not Found';
    require __DIR__ . '/includes/header.php';
    echo '<div class="empty-state"><div class="icon">🍽️</div><p>Dish not found.</p></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$userReaction = null;
$isFavorite = false;
$isWishlisted = false;
$myReview = null;

if (isLoggedIn()) {
    $uid = $_SESSION['user_id'];
    $r = $db->prepare('SELECT type FROM reactions WHERE user_id = ? AND dish_id = ?');
    $r->execute([$uid, $dishId]);
    $userReaction = $r->fetchColumn() ?: null;

    $f = $db->prepare('SELECT 1 FROM favorites WHERE user_id = ? AND dish_id = ?');
    $f->execute([$uid, $dishId]);
    $isFavorite = (bool) $f->fetchColumn();

    $w = $db->prepare('SELECT 1 FROM wishlists WHERE user_id = ? AND dish_id = ?');
    $w->execute([$uid, $dishId]);
    $isWishlisted = (bool) $w->fetchColumn();

    $mr = $db->prepare('SELECT * FROM reviews WHERE user_id = ? AND dish_id = ?');
    $mr->execute([$uid, $dishId]);
    $myReview = $mr->fetch() ?: null;
}

$reviews = $db->prepare(
    "SELECT r.*, u.username FROM reviews r JOIN users u ON u.user_id = r.user_id
     WHERE r.dish_id = ? AND r.status = 'visible' ORDER BY r.created_at DESC"
);
$reviews->execute([$dishId]);
$reviews = $reviews->fetchAll();

$pageTitle = $dish['name'];
require __DIR__ . '/includes/header.php';
?>
<section class="section" style="padding-top: 10px;">
    <div class="container" style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; align-items: start;">
        <div class="dish-media" style="aspect-ratio: 1/1; border-radius: var(--radius-lg); font-size: 5rem;">
            <?= dishImageHtml($dish) ?>
        </div>
        <div>
            <p class="section-sub"><?= esc(currentLang() === 'fil' ? $dish['cat_fil'] : $dish['cat_en']) ?></p>
            <h1 style="font-size: 2.2rem; margin: 6px 0 12px;"><?= esc($dish['name']) ?></h1>
            <div class="dish-meta" style="margin-bottom: 14px;">
                <span class="dish-rating">★ <?= $dish['avg_rating'] ?: '—' ?> (<?= (int) $dish['review_count'] ?> <?= t('reviews') ?>)</span>
                <span class="dish-price" style="font-size: 1.6rem;"><?= peso((float) $dish['price']) ?></span>
            </div>
            <p style="margin-bottom: 18px;"><?= esc(dishText($dish, 'desc')) ?></p>

            <h4 style="margin-bottom: 6px; font-size: 0.9rem;"><?= t('ingredients') ?></h4>
            <p class="section-sub" style="margin-bottom: 22px;"><?= esc($dish['ingredients']) ?></p>

            <div style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;">
                <button class="btn btn-secondary <?= $userReaction === 'like' ? 'active' : '' ?>" data-react="like" data-dish-id="<?= $dish['dish_id'] ?>" style="<?= $userReaction === 'like' ? 'border-color: var(--color-primary); color: var(--color-primary);' : '' ?>">
                    👍 <?= t('like') ?> (<span data-like-count="<?= $dish['dish_id'] ?>"><?= (int) $dish['likes'] ?></span>)
                </button>
                <button class="btn btn-secondary <?= $userReaction === 'dislike' ? 'active' : '' ?>" data-react="dislike" data-dish-id="<?= $dish['dish_id'] ?>" style="<?= $userReaction === 'dislike' ? 'border-color: var(--color-primary); color: var(--color-primary);' : '' ?>">
                    👎 <?= t('dislike') ?> (<span data-dislike-count="<?= $dish['dish_id'] ?>"><?= (int) $dish['dislikes'] ?></span>)
                </button>
                <button class="btn btn-secondary <?= $isFavorite ? 'active' : '' ?>" data-favorite data-dish-id="<?= $dish['dish_id'] ?>" style="<?= $isFavorite ? 'border-color: var(--color-primary); color: var(--color-primary);' : '' ?>">
                    <?= $isFavorite ? '❤️' : '🤍' ?> <?= $isFavorite ? t('remove_favorite') : t('add_favorite') ?>
                </button>
                <button class="btn btn-secondary <?= $isWishlisted ? 'active' : '' ?>" data-wishlist data-dish-id="<?= $dish['dish_id'] ?>" style="<?= $isWishlisted ? 'border-color: var(--color-primary); color: var(--color-primary);' : '' ?>">
                    🔖 <?= $isWishlisted ? t('remove_wishlist') : t('add_wishlist') ?>
                </button>
            </div>

            <?php if ($dish['is_available']): ?>
                <button class="btn btn-primary btn-block" data-add-cart="<?= $dish['dish_id'] ?>"><?= t('add_to_cart') ?> — <?= peso((float) $dish['price']) ?></button>
            <?php else: ?>
                <button class="btn btn-secondary btn-block" disabled><?= t('out_of_stock') ?></button>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="section" style="padding-top: 0;">
    <div class="container" style="max-width: 800px;">
        <h3 style="margin-bottom: 18px;"><?= t('reviews') ?> (<?= (int) $dish['review_count'] ?>)</h3>

        <?php if (isLoggedIn() && !$myReview): ?>
        <div class="card" style="padding: 20px; margin-bottom: 24px;">
            <h4 style="margin-bottom: 12px;"><?= t('write_review') ?></h4>
            <div class="form-group">
                <label><?= t('your_rating') ?></label>
                <div class="star-rating-input" data-for="ratingInput" style="font-size: 1.6rem; cursor: pointer;">
                    <span data-value="1">☆</span><span data-value="2">☆</span><span data-value="3">☆</span><span data-value="4">☆</span><span data-value="5">☆</span>
                </div>
                <input type="hidden" id="ratingInput" value="5">
            </div>
            <div class="form-group">
                <textarea class="form-control" id="reviewComment" placeholder="<?= currentLang() === 'fil' ? 'Ano ang masasabi mo tungkol dito?' : 'What did you think?' ?>"></textarea>
            </div>
            <button class="btn btn-primary" id="submitReviewBtn"><?= t('submit_review') ?></button>
        </div>
        <script>
        document.querySelectorAll('.star-rating-input span').forEach(s => s.classList.add('filled'));
        document.querySelectorAll('.star-rating-input').forEach(g => g.querySelectorAll('span').forEach(s => {
            s.style.color = 'var(--color-secondary)';
        }));
        document.getElementById('submitReviewBtn')?.addEventListener('click', async () => {
            const rating = parseInt(document.getElementById('ratingInput').value, 10);
            const comment = document.getElementById('reviewComment').value.trim();
            if (!comment) { BM.showToast('Please write a comment.', 'error'); return; }
            const data = await BM.api('/ajax/review.php', { action: 'create', dish_id: <?= $dish['dish_id'] ?>, rating, comment });
            if (data.success) { BM.showToast(data.message); setTimeout(() => location.reload(), 700); }
            else BM.showToast(data.message, 'error');
        });
        </script>
        <?php endif; ?>

        <?php if ($myReview): ?>
        <div class="card" style="padding: 20px; margin-bottom: 24px; border-color: var(--color-primary);">
            <p class="section-sub" style="margin-bottom: 8px;"><?= currentLang() === 'fil' ? 'Review Mo' : 'Your Review' ?></p>
            <div class="review-stars"><?= str_repeat('★', (int) $myReview['rating']) . str_repeat('☆', 5 - (int) $myReview['rating']) ?></div>
            <p style="margin: 8px 0;"><?= esc($myReview['comment']) ?></p>
            <button class="btn btn-ghost btn-sm" onclick="if(confirm('Delete your review?')) BM.api('/ajax/review.php', {action:'delete', review_id: <?= $myReview['review_id'] ?>}).then(d => { if(d.success) location.reload(); else BM.showToast(d.message,'error'); })"><?= t('remove') ?></button>
        </div>
        <?php endif; ?>

        <?php if (!$reviews): ?>
            <p class="section-sub"><?= currentLang() === 'fil' ? 'Wala pang review.' : 'No reviews yet — be the first!' ?></p>
        <?php endif; ?>
        <?php foreach ($reviews as $rev): ?>
            <div class="review-item">
                <div class="review-head">
                    <span class="review-user"><?= esc($rev['username']) ?></span>
                    <span class="review-date"><?= timeAgo($rev['created_at']) ?><?= $rev['is_edited'] ? ' · ' . t('edited') : '' ?></span>
                </div>
                <div class="review-stars"><?= str_repeat('★', (int) $rev['rating']) . str_repeat('☆', 5 - (int) $rev['rating']) ?></div>
                <p style="margin-top: 6px;"><?= esc($rev['comment']) ?></p>
                <?php if (isLoggedIn() && (int) $rev['user_id'] !== (int) $_SESSION['user_id']): ?>
                <button class="btn btn-ghost btn-sm" style="margin-top: 4px; padding: 4px 10px;" onclick="BM.api('/ajax/report_review.php', {review_id: <?= $rev['review_id'] ?>}).then(d => BM.showToast(d.message, d.success ? 'success' : 'error'))">🚩 <?= currentLang() === 'fil' ? 'I-report' : 'Report' ?></button>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
