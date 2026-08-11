<?php
require_once __DIR__ . '/includes/bootstrap.php';
requireLogin();

$stmt = $db->prepare(
    "SELECT d.*,
        (SELECT ROUND(AVG(rating),1) FROM reviews WHERE dish_id = d.dish_id AND status='visible') AS avg_rating,
        (SELECT COUNT(*) FROM reviews WHERE dish_id = d.dish_id AND status='visible') AS review_count
     FROM wishlists w JOIN dishes d ON d.dish_id = w.dish_id
     WHERE w.user_id = ? ORDER BY w.created_at DESC"
);
$stmt->execute([$_SESSION['user_id']]);
$dishes = $stmt->fetchAll();

$pageTitle = t('wishlist_title');
require __DIR__ . '/includes/header.php';
?>
<section class="section" style="padding-top: 10px;">
    <div class="container">
        <div class="section-head"><h2><?= t('wishlist_title') ?></h2></div>
        <?php if (!$dishes): ?>
            <div class="empty-state"><div class="icon">🔖</div><p><?= t('no_wishlist') ?></p></div>
        <?php else: ?>
        <div class="dish-grid">
            <?php foreach ($dishes as $dish): ?>
                <div class="card dish-card">
                    <div class="dish-media">
                        <button class="wishlist-toggle active" data-wishlist data-dish-id="<?= $dish['dish_id'] ?>">🔖</button>
                        <?= dishImageHtml($dish) ?>
                    </div>
                    <div class="dish-body">
                        <h3><?= esc($dish['name']) ?></h3>
                        <p class="dish-desc"><?= esc(dishText($dish, 'desc')) ?></p>
                        <div class="dish-meta">
                            <span class="dish-rating">★ <?= $dish['avg_rating'] ?: '—' ?> (<?= (int) $dish['review_count'] ?>)</span>
                            <span class="dish-price"><?= peso((float) $dish['price']) ?></span>
                        </div>
                        <div class="dish-footer">
                            <a href="/basta-masarap/dish.php?id=<?= $dish['dish_id'] ?>" class="btn btn-secondary btn-sm"><?= t('view_details') ?></a>
                            <button class="btn btn-primary btn-sm" data-add-cart="<?= $dish['dish_id'] ?>"><?= t('add_to_cart') ?></button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
