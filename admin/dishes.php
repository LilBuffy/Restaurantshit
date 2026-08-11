<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();

$categories = $db->query('SELECT * FROM categories ORDER BY sort_order')->fetchAll();
$errors = [];

function handleImageUpload(array $file): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Image upload failed.');
    }
    if ($file['size'] > 4 * 1024 * 1024) {
        throw new RuntimeException('Image must be under 4MB.');
    }
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime = mime_content_type($file['tmp_name']);
    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Only JPG, PNG, or WEBP images are allowed.');
    }
    $filename = bin2hex(random_bytes(12)) . '.' . $allowed[$mime];
    $destination = __DIR__ . '/../assets/images/dishes/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('Could not save the uploaded image.');
    }
    return 'assets/images/dishes/' . $filename;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid form submission.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'delete') {
            $db->prepare('DELETE FROM dishes WHERE dish_id = ?')->execute([(int) $_POST['dish_id']]);
            flash('success', 'Dish deleted.');
            redirect('/basta-masarap/admin/dishes.php');
        }

        if ($action === 'toggle') {
            $field = $_POST['field'] ?? '';
            if (in_array($field, ['is_available', 'is_featured', 'is_popular'], true)) {
                $db->prepare("UPDATE dishes SET {$field} = 1 - {$field} WHERE dish_id = ?")->execute([(int) $_POST['dish_id']]);
            }
            redirect('/basta-masarap/admin/dishes.php');
        }

        if (in_array($action, ['create', 'update'], true)) {
            $name = trim($_POST['name'] ?? '');
            $categoryId = (int) ($_POST['category_id'] ?? 0);
            $descEn = trim($_POST['desc_en'] ?? '');
            $descFil = trim($_POST['desc_fil'] ?? '');
            $ingredients = trim($_POST['ingredients'] ?? '');
            $price = (float) ($_POST['price'] ?? 0);

            if ($name === '' || $categoryId <= 0 || $descEn === '' || $descFil === '' || $price <= 0) {
                $errors[] = 'Please fill in all required fields with a valid price.';
            }

            $imagePath = null;
            if (!$errors) {
                try {
                    $imagePath = handleImageUpload($_FILES['image'] ?? []);
                } catch (RuntimeException $e) {
                    $errors[] = $e->getMessage();
                }
            }

            if (!$errors) {
                if ($action === 'create') {
                    $stmt = $db->prepare(
                        'INSERT INTO dishes (category_id, name, desc_en, desc_fil, ingredients, price, image_path, is_available)
                         VALUES (?, ?, ?, ?, ?, ?, ?, 1)'
                    );
                    $stmt->execute([$categoryId, $name, $descEn, $descFil, $ingredients, $price, $imagePath ?? 'assets/images/dishes/placeholder.jpg']);
                    flash('success', 'Dish added.');
                } else {
                    $dishId = (int) $_POST['dish_id'];
                    if ($imagePath) {
                        $db->prepare('UPDATE dishes SET category_id=?, name=?, desc_en=?, desc_fil=?, ingredients=?, price=?, image_path=? WHERE dish_id=?')
                           ->execute([$categoryId, $name, $descEn, $descFil, $ingredients, $price, $imagePath, $dishId]);
                    } else {
                        $db->prepare('UPDATE dishes SET category_id=?, name=?, desc_en=?, desc_fil=?, ingredients=?, price=? WHERE dish_id=?')
                           ->execute([$categoryId, $name, $descEn, $descFil, $ingredients, $price, $dishId]);
                    }
                    flash('success', 'Dish updated.');
                }
                redirect('/basta-masarap/admin/dishes.php');
            }
        }
    }
}

$editId = (int) ($_GET['edit'] ?? 0);
$editDish = null;
if ($editId) {
    $stmt = $db->prepare('SELECT * FROM dishes WHERE dish_id = ?');
    $stmt->execute([$editId]);
    $editDish = $stmt->fetch();
}

$dishes = $db->query(
    "SELECT d.*, c.name_en AS cat_name FROM dishes d JOIN categories c ON c.category_id = d.category_id ORDER BY d.dish_id DESC"
)->fetchAll();

$pageTitle = 'Dishes';
require __DIR__ . '/includes/admin_header.php';
?>
<h2 style="margin-bottom: 20px;">Dishes</h2>
<?php foreach ($errors as $e): ?><div class="alert alert-error"><?= esc($e) ?></div><?php endforeach; ?>

<div class="card" style="padding: 24px; margin-bottom: 28px;">
    <h4 style="margin-bottom: 14px;"><?= $editDish ? 'Edit Dish' : 'Add New Dish' ?></h4>
    <form method="POST" enctype="multipart/form-data" data-validate>
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
        <input type="hidden" name="action" value="<?= $editDish ? 'update' : 'create' ?>">
        <?php if ($editDish): ?><input type="hidden" name="dish_id" value="<?= $editDish['dish_id'] ?>"><?php endif; ?>
        <div class="form-row">
            <div class="form-group">
                <label>Name</label>
                <input class="form-control" type="text" name="name" value="<?= esc($editDish['name'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Category</label>
                <select class="form-control" name="category_id" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['category_id'] ?>" <?= (isset($editDish['category_id']) && $editDish['category_id'] == $cat['category_id']) ? 'selected' : '' ?>><?= esc($cat['name_en']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label>English Description</label>
            <textarea class="form-control" name="desc_en" required><?= esc($editDish['desc_en'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
            <label>Filipino Description</label>
            <textarea class="form-control" name="desc_fil" required><?= esc($editDish['desc_fil'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
            <label>Ingredients</label>
            <input class="form-control" type="text" name="ingredients" value="<?= esc($editDish['ingredients'] ?? '') ?>">
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Price (₱)</label>
                <input class="form-control" type="number" step="0.01" min="1" name="price" value="<?= esc((string) ($editDish['price'] ?? '')) ?>" required>
            </div>
            <div class="form-group">
                <label>Image <?= $editDish ? '(leave empty to keep current)' : '' ?></label>
                <input class="form-control" type="file" name="image" accept="image/png,image/jpeg,image/webp">
            </div>
        </div>
        <button class="btn btn-primary" type="submit"><?= $editDish ? 'Save Changes' : 'Add Dish' ?></button>
        <?php if ($editDish): ?><a href="/basta-masarap/admin/dishes.php" class="btn btn-ghost">Cancel</a><?php endif; ?>
    </form>
</div>

<div class="table-wrap">
    <table class="data-table">
        <thead><tr><th>Name</th><th>Category</th><th>Price</th><th>Available</th><th>Featured</th><th>Popular</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($dishes as $d): ?>
        <tr>
            <td><?= esc($d['name']) ?></td>
            <td><?= esc($d['cat_name']) ?></td>
            <td><?= peso((float) $d['price']) ?></td>
            <?php foreach (['is_available', 'is_featured', 'is_popular'] as $field): ?>
            <td>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <input type="hidden" name="action" value="toggle">
                    <input type="hidden" name="field" value="<?= $field ?>">
                    <input type="hidden" name="dish_id" value="<?= $d['dish_id'] ?>">
                    <button class="btn btn-sm <?= $d[$field] ? 'btn-primary' : 'btn-secondary' ?>" type="submit"><?= $d[$field] ? 'Yes' : 'No' ?></button>
                </form>
            </td>
            <?php endforeach; ?>
            <td style="display: flex; gap: 6px;">
                <a href="?edit=<?= $d['dish_id'] ?>" class="btn btn-secondary btn-sm">Edit</a>
                <form method="POST" onsubmit="return confirm('Delete this dish permanently?');">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="dish_id" value="<?= $d['dish_id'] ?>">
                    <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
