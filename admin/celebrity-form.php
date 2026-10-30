<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/auth.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$celeb = [
    'id' => 0, 'name' => '', 'category' => '', 'short_bio' => '', 'bio' => '',
    'image' => '', 'base_fee_note' => '', 'is_featured' => 1, 'status' => 'published', 'sort_order' => 0,
];
$errors = [];

if ($id) {
    $stmt = db()->prepare('SELECT * FROM celebrities WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $found = $stmt->fetch();
    if (!$found) {
        flash_set('error', 'Celebrity not found.');
        redirect('/admin/celebrities.php');
    }
    $celeb = $found;
}

$pageTitle = $id ? 'Edit Celebrity' : 'Add Celebrity';
$activeNav = 'celebrities';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Session expired. Please try again.';
    } else {
        $name         = trim($_POST['name'] ?? '');
        $category     = trim($_POST['category'] ?? '');
        $shortBio     = trim($_POST['short_bio'] ?? '');
        $bio          = trim($_POST['bio'] ?? '');
        $baseFeeNote  = trim($_POST['base_fee_note'] ?? '');
        $isFeatured   = isset($_POST['is_featured']) ? 1 : 0;
        $status       = in_array($_POST['status'] ?? '', ['published', 'draft'], true) ? $_POST['status'] : 'published';
        $sortOrder    = (int)($_POST['sort_order'] ?? 0);

        if ($name === '') {
            $errors[] = 'Name is required.';
        }

        $imageResult = handle_image_upload($_FILES['image'] ?? [], UPLOAD_DIR_CELEB, $celeb['image'] ?: null);
        if (!$imageResult['ok']) {
            $errors[] = $imageResult['error'];
        }
        if (!$id && empty($imageResult['filename'])) {
            $errors[] = 'Please upload a photo for this celebrity.';
        }

        if (empty($errors)) {
            $slugBase = slugify($name);
            $slug = unique_slug($slugBase, 'celebrities', $id ?: null);

            if ($id) {
                $stmt = db()->prepare(
                    'UPDATE celebrities SET name=:name, slug=:slug, category=:category, short_bio=:short_bio,
                     bio=:bio, image=:image, base_fee_note=:fee, is_featured=:featured, status=:status,
                     sort_order=:sort_order WHERE id=:id'
                );
                $stmt->execute([
                    'name' => $name, 'slug' => $slug, 'category' => $category, 'short_bio' => $shortBio,
                    'bio' => $bio, 'image' => $imageResult['filename'], 'fee' => $baseFeeNote,
                    'featured' => $isFeatured, 'status' => $status, 'sort_order' => $sortOrder, 'id' => $id,
                ]);
                flash_set('success', 'Celebrity updated.');
            } else {
                $stmt = db()->prepare(
                    'INSERT INTO celebrities (name, slug, category, short_bio, bio, image, base_fee_note,
                     is_featured, status, sort_order)
                     VALUES (:name, :slug, :category, :short_bio, :bio, :image, :fee, :featured, :status, :sort_order)'
                );
                $stmt->execute([
                    'name' => $name, 'slug' => $slug, 'category' => $category, 'short_bio' => $shortBio,
                    'bio' => $bio, 'image' => $imageResult['filename'], 'fee' => $baseFeeNote,
                    'featured' => $isFeatured, 'status' => $status, 'sort_order' => $sortOrder,
                ]);
                flash_set('success', 'Celebrity added.');
            }
            redirect('/admin/celebrities.php');
        }

        // repopulate for re-render on error
        $celeb = array_merge($celeb, compact('name', 'category', 'shortBio', 'bio', 'baseFeeNote', 'isFeatured', 'status', 'sortOrder'));
        $celeb['short_bio'] = $shortBio;
        $celeb['base_fee_note'] = $baseFeeNote;
        $celeb['is_featured'] = $isFeatured;
        $celeb['sort_order'] = $sortOrder;
    }
}

require __DIR__ . '/includes/admin-header.php';
?>

<div class="panel">
    <?php if ($errors): ?>
        <div class="alert alert-error"><ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="form-grid">
            <div class="form-row">
                <label>Full Name *</label>
                <input type="text" name="name" required value="<?= e($celeb['name']) ?>">
            </div>
            <div class="form-row">
                <label>Category / Profession</label>
                <input type="text" name="category" placeholder="e.g. Actor, Singer" value="<?= e($celeb['category']) ?>">
            </div>
        </div>

        <div class="form-row">
            <label>Short Bio (shown on cards, keep it brief)</label>
            <input type="text" name="short_bio" maxlength="300" value="<?= e($celeb['short_bio']) ?>">
        </div>

        <div class="form-row">
            <label>Full Bio (shown on profile page)</label>
            <textarea name="bio" rows="6"><?= e($celeb['bio']) ?></textarea>
        </div>

        <div class="form-row">
            <label>Booking Note (e.g. fee range, travel requirements)</label>
            <input type="text" name="base_fee_note" value="<?= e($celeb['base_fee_note']) ?>">
        </div>

        <div class="form-row">
            <label>Photo <?= $id ? '' : '*' ?></label>
            <?php if ($celeb['image']): ?>
                <img src="/uploads/celebrities/<?= e($celeb['image']) ?>" class="current-image">
            <?php endif; ?>
            <input type="file" name="image" accept="image/*">
            <p class="form-help">JPG, PNG or WEBP. Max 3MB. Leave empty to keep the current photo.</p>
        </div>

        <div class="form-grid">
            <div class="form-row">
                <label>Status</label>
                <select name="status">
                    <option value="published" <?= $celeb['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                    <option value="draft" <?= $celeb['status'] === 'draft' ? 'selected' : '' ?>>Draft (hidden from site)</option>
                </select>
            </div>
            <div class="form-row">
                <label>Display Order (lower shows first)</label>
                <input type="number" name="sort_order" value="<?= (int)$celeb['sort_order'] ?>">
            </div>
        </div>

        <div class="form-row">
            <label><input type="checkbox" name="is_featured" value="1" style="width:auto;display:inline-block;"
                <?= $celeb['is_featured'] ? 'checked' : '' ?>> Show in "Featured Celebrities" on the homepage</label>
        </div>

        <button type="submit" class="btn btn-primary"><?= $id ? 'Save Changes' : 'Add Celebrity' ?></button>
        <a href="/admin/celebrities.php" class="btn btn-outline">Cancel</a>
    </form>
</div>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
