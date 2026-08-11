<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        flash('error', 'Invalid form submission.');
    } else {
        $stmt = $db->prepare('UPDATE restaurant_settings SET setting_value = ? WHERE setting_key = ?');
        foreach (['delivery_fee', 'restaurant_name', 'tagline_fil', 'tagline_en'] as $key) {
            if (isset($_POST[$key])) {
                $stmt->execute([trim($_POST[$key]), $key]);
            }
        }
        flash('success', 'Settings updated.');
    }
    redirect('/basta-masarap/admin/settings.php');
}

$rows = $db->query('SELECT * FROM restaurant_settings')->fetchAll();
$settings = array_column($rows, 'setting_value', 'setting_key');

$pageTitle = 'Settings';
require __DIR__ . '/includes/admin_header.php';
?>
<h2 style="margin-bottom: 20px;">Restaurant Settings</h2>
<div class="card" style="padding: 24px; max-width: 560px;">
    <form method="POST" data-validate>
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
        <div class="form-group">
            <label>Restaurant Name</label>
            <input class="form-control" type="text" name="restaurant_name" value="<?= esc($settings['restaurant_name'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>Delivery Fee (₱)</label>
            <input class="form-control" type="number" step="0.01" min="0" name="delivery_fee" value="<?= esc($settings['delivery_fee'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>Tagline (English)</label>
            <input class="form-control" type="text" name="tagline_en" value="<?= esc($settings['tagline_en'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Tagline (Filipino)</label>
            <input class="form-control" type="text" name="tagline_fil" value="<?= esc($settings['tagline_fil'] ?? '') ?>">
        </div>
        <button class="btn btn-primary" type="submit">Save Settings</button>
    </form>
</div>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
