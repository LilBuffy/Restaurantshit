<?php
require_once __DIR__ . '/includes/bootstrap.php';

$featured = $db->query(
    "SELECT d.*, c.slug AS category_slug,
        (SELECT COUNT(*) FROM reactions WHERE dish_id = d.dish_id AND type='like') AS likes,
        (SELECT ROUND(AVG(rating),1) FROM reviews WHERE dish_id = d.dish_id AND status='visible') AS avg_rating,
        (SELECT COUNT(*) FROM reviews WHERE dish_id = d.dish_id AND status='visible') AS review_count
     FROM dishes d JOIN categories c ON c.category_id = d.category_id
     WHERE d.is_featured = 1 AND d.is_available = 1
     ORDER BY d.dish_id DESC LIMIT 6"
)->fetchAll();

$popular = $db->query(
    "SELECT d.*, c.slug AS category_slug,
        (SELECT COUNT(*) FROM reactions WHERE dish_id = d.dish_id AND type='like') AS likes,
        (SELECT ROUND(AVG(rating),1) FROM reviews WHERE dish_id = d.dish_id AND status='visible') AS avg_rating,
        (SELECT COUNT(*) FROM reviews WHERE dish_id = d.dish_id AND status='visible') AS review_count
     FROM dishes d JOIN categories c ON c.category_id = d.category_id
     WHERE d.is_popular = 1 AND d.is_available = 1
     ORDER BY d.dish_id DESC LIMIT 6"
)->fetchAll();

$categories = $db->query('SELECT * FROM categories ORDER BY sort_order')->fetchAll();

$reviews = $db->query(
    "SELECT r.*, u.username, d.name AS dish_name FROM reviews r
     JOIN users u ON u.user_id = r.user_id
     JOIN dishes d ON d.dish_id = r.dish_id
     WHERE r.status = 'visible' ORDER BY r.created_at DESC LIMIT 3"
)->fetchAll();

$categoryEmoji = ['ulam' => '🍲', 'rice-meals' => '🍚', 'silog' => '🍳', 'noodles' => '🍜', 'appetizers' => '🥟', 'desserts' => '🍮', 'drinks' => '🥤', 'specials' => '⭐'];

function dishCardHtml(array $dish): string
{
    $name = esc($dish['name']);
    $desc = esc(mb_substr(dishText($dish, 'desc'), 0, 90));
    $available = (bool) $dish['is_available'];
    ob_start();
    ?>
    <div class="card dish-card">
        <div class="dish-media">
            <div class="dish-badges">
                <?php if ($dish['is_featured']): ?><span class="badge badge-featured">★ Featured</span><?php endif; ?>
                <?php if ($dish['is_popular']): ?><span class="badge badge-popular">🔥 Popular</span><?php endif; ?>
                <?php if (!$available): ?><span class="badge badge-unavailable"><?= t('out_of_stock') ?></span><?php endif; ?>
            </div>
            <button class="wishlist-toggle" data-wishlist data-dish-id="<?= $dish['dish_id'] ?>" title="<?= t('add_wishlist') ?>">🤍</button>
            <?= dishImageHtml($dish) ?>
        </div>
        <div class="dish-body">
            <h3><?= $name ?></h3>
            <p class="dish-desc"><?= $desc ?></p>
            <div class="dish-meta">
                <span class="dish-rating">★ <?= $dish['avg_rating'] ?: '—' ?> (<?= (int) $dish['review_count'] ?>)</span>
                <span class="dish-price"><?= peso((float) $dish['price']) ?></span>
            </div>
            <div class="dish-footer">
                <a href="/basta-masarap/dish.php?id=<?= $dish['dish_id'] ?>" class="btn btn-secondary btn-sm"><?= t('view_details') ?></a>
                <button class="btn btn-primary btn-sm" data-add-cart="<?= $dish['dish_id'] ?>" <?= $available ? '' : 'disabled' ?>><?= t('add_to_cart') ?></button>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

$pageTitle = null;
require __DIR__ . '/includes/header.php';
?>
<section class="hero">
    <div class="container">
        <div>
            <span class="hero-eyebrow">🇵🇭 <?= currentLang() === 'fil' ? 'Tunay na Lutong Pinoy' : 'Authentic Filipino Cooking' ?></span>
            <h1><?= t('hero_title') ?></h1>
            <p class="tagline"><?= t('hero_tagline') ?></p>
            <div class="hero-actions">
                <a href="/basta-masarap/menu.php" class="btn btn-primary"><?= t('hero_cta') ?></a>
                <a href="/basta-masarap/menu.php" class="btn btn-secondary"><?= t('hero_view_menu') ?></a>
            </div>
        </div>
        <div class="hero-visual">
            <img src="/basta-masarap/assets/images/hero.jpg" alt="u r not supposed to see this" style="width: 100%; border-radius: var(--radius-lg); box-shadow: var(--shadow-lg);">
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head"><h2><?= t('section_categories') ?></h2></div>
        <div class="category-grid">
            <?php foreach ($categories as $cat): ?>
                <a href="/basta-masarap/menu.php?category=<?= esc($cat['slug']) ?>" class="category-chip">
                    <span class="emoji"><?= $categoryEmoji[$cat['slug']] ?? '🍴' ?></span>
                    <?= currentLang() === 'fil' ? esc($cat['name_fil']) : esc($cat['name_en']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php if ($featured): ?>
<section class="section" style="padding-top: 0;">
    <div class="container">
        <div class="section-head">
            <h2><?= t('section_featured') ?></h2>
            <a href="/basta-masarap/menu.php" class="btn btn-ghost"><?= t('hero_view_menu') ?> →</a>
        </div>
        <div class="dish-grid">
            <?php foreach ($featured as $dish): ?><?= dishCardHtml($dish) ?><?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="section" style="background: var(--color-surface); padding-top: 72px;">
    <div class="container">
        <div class="section-head"><h2><?= t('about_title') ?></h2></div>
        <p style="max-width: 720px; font-size: 1.05rem;"><?= t('about_body') ?></p>
    </div>
</section>

<?php if ($popular): ?>
<section class="section">
    <div class="container">
        <div class="section-head">
            <h2><?= t('section_popular') ?></h2>
            <a href="/basta-masarap/menu.php" class="btn btn-ghost"><?= t('hero_view_menu') ?> →</a>
        </div>
        <div class="dish-grid">
            <?php foreach ($popular as $dish): ?><?= dishCardHtml($dish) ?><?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($reviews): ?>
<section class="section" style="background: var(--color-surface);">
    <div class="container">
        <div class="section-head"><h2><?= t('section_reviews') ?></h2></div>
        <div class="dish-grid">
            <?php foreach ($reviews as $rev): ?>
                <div class="card" style="padding: 22px;">
                    <div class="review-stars"><?= str_repeat('★', (int) $rev['rating']) . str_repeat('☆', 5 - (int) $rev['rating']) ?></div>
                    <p style="margin: 10px 0; font-size: 0.92rem;">"<?= esc($rev['comment']) ?>"</p>
                    <p style="font-weight: 700; font-size: 0.85rem;">— <?= esc($rev['username']) ?>, on <?= esc($rev['dish_name']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="section text-center">
    <div class="container">
        <h2 style="margin-bottom: 12px;"><?= t('section_offers') ?></h2>
        <p class="section-sub" style="margin-bottom: 24px;"><?= currentLang() === 'fil' ? 'Libreng garlic rice sa bawat order na may Kare Kare o Bulalo!' : 'Free garlic rice with every Kare Kare or Bulalo order!' ?></p>
        <a href="/basta-masarap/menu.php" class="btn btn-primary"><?= t('hero_cta') ?></a>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
