<?php
require_once __DIR__ . '/includes/bootstrap.php';

$categories = $db->query('SELECT * FROM categories ORDER BY sort_order')->fetchAll();

$search = trim($_GET['search'] ?? '');
$categorySlug = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? 'popular';

$where = ['d.is_available = 1'];
$params = [];

if ($search !== '') {
    $where[] = '(d.name LIKE ? OR d.desc_en LIKE ? OR d.desc_fil LIKE ?)';
    $like = "%{$search}%";
    array_push($params, $like, $like, $like);
}
if ($categorySlug !== '') {
    $where[] = 'c.slug = ?';
    $params[] = $categorySlug;
}

$orderBy = match ($sort) {
    'price_low' => 'd.price ASC',
    'price_high' => 'd.price DESC',
    'rating' => 'avg_rating DESC',
    'newest' => 'd.created_at DESC',
    default => 'd.is_popular DESC, d.is_featured DESC',
};

$sql = "SELECT d.*, c.slug AS category_slug,
            (SELECT COUNT(*) FROM reactions WHERE dish_id = d.dish_id AND type='like') AS likes,
            (SELECT COUNT(*) FROM reactions WHERE dish_id = d.dish_id AND type='dislike') AS dislikes,
            (SELECT ROUND(AVG(rating),1) FROM reviews WHERE dish_id = d.dish_id AND status='visible') AS avg_rating,
            (SELECT COUNT(*) FROM reviews WHERE dish_id = d.dish_id AND status='visible') AS review_count
        FROM dishes d JOIN categories c ON c.category_id = d.category_id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY {$orderBy}, d.name ASC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$dishes = $stmt->fetchAll();

$wishlistIds = [];
if (isLoggedIn()) {
    $w = $db->prepare('SELECT dish_id FROM wishlists WHERE user_id = ?');
    $w->execute([$_SESSION['user_id']]);
    $wishlistIds = array_column($w->fetchAll(), 'dish_id');
}

$pageTitle = t('nav_menu');
require __DIR__ . '/includes/header.php';
?>
<section class="section" style="padding-top: 20px;">
    <div class="container">
        <div class="section-head"><h2><?= t('nav_menu') ?></h2></div>

        <form id="menuFilterForm" method="GET" class="filters-bar">
            <input type="text" name="search" class="search-input" placeholder="<?= t('search_placeholder') ?>" value="<?= esc($search) ?>">
            <select name="category" class="select-input">
                <option value=""><?= t('filter_category') ?>: <?= currentLang() === 'fil' ? 'Lahat' : 'All' ?></option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= esc($cat['slug']) ?>" <?= $categorySlug === $cat['slug'] ? 'selected' : '' ?>>
                        <?= currentLang() === 'fil' ? esc($cat['name_fil']) : esc($cat['name_en']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="sort" class="select-input">
                <option value="popular" <?= $sort === 'popular' ? 'selected' : '' ?>><?= t('sort_popular') ?></option>
                <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>><?= t('sort_price_low') ?></option>
                <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>><?= t('sort_price_high') ?></option>
                <option value="rating" <?= $sort === 'rating' ? 'selected' : '' ?>><?= t('sort_rating') ?></option>
                <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>><?= t('sort_newest') ?></option>
            </select>
            <noscript><button class="btn btn-primary btn-sm" type="submit">Go</button></noscript>
        </form>

        <?php if (!$dishes): ?>
            <div class="empty-state">
                <div class="icon">🍽️</div>
                <p><?= currentLang() === 'fil' ? 'Walang nahanap na ulam.' : 'No dishes found matching your search.' ?></p>
            </div>
        <?php else: ?>
        <div class="dish-grid">
            <?php foreach ($dishes as $dish): ?>
                <?php $inWishlist = in_array($dish['dish_id'], $wishlistIds); ?>
                <div class="card dish-card">
                    <div class="dish-media">
                        <div class="dish-badges">
                            <?php if ($dish['is_featured']): ?><span class="badge badge-featured">★ Featured</span><?php endif; ?>
                            <?php if ($dish['is_popular']): ?><span class="badge badge-popular">🔥 Popular</span><?php endif; ?>
                        </div>
                        <button class="wishlist-toggle <?= $inWishlist ? 'active' : '' ?>" data-wishlist data-dish-id="<?= $dish['dish_id'] ?>"><?= $inWishlist ? '❤️' : '🤍' ?></button>
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
