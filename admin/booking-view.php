<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/auth.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM bookings WHERE id = :id');
$stmt->execute(['id' => $id]);
$booking = $stmt->fetch();

if (!$booking) {
    flash_set('error', 'Booking not found.');
    redirect('/admin/bookings.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        flash_set('error', 'Session expired, please try again.');
    } else {
        $status = $_POST['status'] ?? 'pending';
        $notes  = trim($_POST['admin_notes'] ?? '');
        if (!in_array($status, ['pending', 'contacted', 'confirmed', 'declined'], true)) {
            $status = 'pending';
        }
        $upd = db()->prepare('UPDATE bookings SET status = :s, admin_notes = :n WHERE id = :id');
        $upd->execute(['s' => $status, 'n' => $notes, 'id' => $id]);
        flash_set('success', 'Booking updated.');
    }
    redirect('/admin/booking-view.php?id=' . $id);
}

$pageTitle = 'Booking #' . $booking['id'];
$activeNav = 'bookings';

require __DIR__ . '/includes/admin-header.php';
?>

<div class="panel">
    <h2>Booking Details</h2>
    <div class="form-grid">
        <div>
            <p><strong>Full Name:</strong> <?= e($booking['full_name']) ?></p>
            <p><strong>Email:</strong> <?= e($booking['email']) ?></p>
            <p><strong>Phone:</strong> <?= e($booking['phone'] ?: '—') ?></p>
            <p><strong>Organization:</strong> <?= e($booking['organization'] ?: '—') ?></p>
        </div>
        <div>
            <p><strong>Celebrity Requested:</strong> <?= e($booking['celebrity_name_snapshot'] ?: 'General inquiry') ?></p>
            <p><strong>Event Type:</strong> <?= e($booking['event_type'] ?: '—') ?></p>
            <p><strong>Event Date:</strong> <?= $booking['event_date'] ? e(format_date($booking['event_date'])) : '—' ?></p>
            <p><strong>Event Location:</strong> <?= e($booking['event_location'] ?: '—') ?></p>
            <p><strong>Budget Range:</strong> <?= e($booking['budget_range'] ?: '—') ?></p>
        </div>
    </div>
    <p><strong>Message:</strong><br><?= nl2br(e($booking['message'] ?: '—')) ?></p>
    <p style="color:#6a7180;font-size:.85rem;">Submitted <?= e(format_date($booking['created_at'], 'M j, Y g:ia A')) ?></p>
</div>

<div class="panel">
    <h2>Update Status & Notes</h2>
    <form method="post">
        <?= csrf_field() ?>
        <div class="form-row">
            <label>Status</label>
            <select name="status">
                <?php foreach (['pending', 'contacted', 'confirmed', 'declined'] as $s): ?>
                    <option value="<?= $s ?>" <?= $booking['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-row">
            <label>Internal Notes (not visible to the client)</label>
            <textarea name="admin_notes" rows="5"><?= e($booking['admin_notes']) ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Save</button>
        <a href="/admin/bookings.php" class="btn btn-outline">Back to List</a>
    </form>
</div>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
