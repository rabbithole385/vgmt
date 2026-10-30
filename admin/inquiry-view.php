<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/auth.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM inquiries WHERE id = :id');
$stmt->execute(['id' => $id]);
$inquiry = $stmt->fetch();

if (!$inquiry) {
    flash_set('error', 'Inquiry not found.');
    redirect('/admin/inquiries.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        flash_set('error', 'Session expired, please try again.');
    } else {
        $status = $_POST['status'] ?? 'new';
        if (!in_array($status, ['new', 'read', 'replied'], true)) {
            $status = 'new';
        }
        db()->prepare('UPDATE inquiries SET status = :s WHERE id = :id')->execute(['s' => $status, 'id' => $id]);
        flash_set('success', 'Inquiry updated.');
    }
    redirect('/admin/inquiry-view.php?id=' . $id);
} elseif ($inquiry['status'] === 'new') {
    // auto mark as read once opened
    db()->prepare("UPDATE inquiries SET status = 'read' WHERE id = :id")->execute(['id' => $id]);
    $inquiry['status'] = 'read';
}

$pageTitle = 'Inquiry #' . $inquiry['id'];
$activeNav = 'inquiries';

require __DIR__ . '/includes/admin-header.php';
?>

<div class="panel">
    <p><strong>Name:</strong> <?= e($inquiry['name']) ?></p>
    <p><strong>Email:</strong> <?= e($inquiry['email']) ?></p>
    <p><strong>Subject:</strong> <?= e($inquiry['subject'] ?: '—') ?></p>
    <p><strong>Message:</strong><br><?= nl2br(e($inquiry['message'])) ?></p>
    <p style="color:#6a7180;font-size:.85rem;">Submitted <?= e(format_date($inquiry['created_at'], 'M j, Y g:ia A')) ?></p>
    <a class="btn btn-outline" href="mailto:<?= e($inquiry['email']) ?>?subject=Re: <?= rawurlencode($inquiry['subject'] ?: 'Your inquiry') ?>">Reply by Email</a>
</div>

<div class="panel">
    <h2>Status</h2>
    <form method="post">
        <?= csrf_field() ?>
        <div class="form-row">
            <select name="status">
                <?php foreach (['new', 'read', 'replied'] as $s): ?>
                    <option value="<?= $s ?>" <?= $inquiry['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Save</button>
        <a href="/admin/inquiries.php" class="btn btn-outline">Back to List</a>
    </form>
</div>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
