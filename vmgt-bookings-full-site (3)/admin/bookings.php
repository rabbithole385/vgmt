<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/auth.php';
require_login();

$pageTitle = 'Bookings';
$activeNav = 'bookings';

$statusFilter = $_GET['status'] ?? '';
$validStatuses = ['pending', 'contacted', 'confirmed', 'declined'];

if (in_array($statusFilter, $validStatuses, true)) {
    $stmt = db()->prepare('SELECT * FROM bookings WHERE status = :s ORDER BY created_at DESC');
    $stmt->execute(['s' => $statusFilter]);
} else {
    $stmt = db()->query('SELECT * FROM bookings ORDER BY created_at DESC');
}
$bookings = $stmt->fetchAll();

$counts = db()->query(
    "SELECT status, COUNT(*) c FROM bookings GROUP BY status"
)->fetchAll(PDO::FETCH_KEY_PAIR);

require __DIR__ . '/includes/admin-header.php';
?>

<div class="panel">
    <div class="filter-bar">
        <a href="/admin/bookings.php" class="<?= $statusFilter === '' ? 'active' : '' ?>">All (<?= array_sum($counts) ?>)</a>
        <?php foreach ($validStatuses as $s): ?>
            <a href="/admin/bookings.php?status=<?= $s ?>" class="<?= $statusFilter === $s ? 'active' : '' ?>">
                <?= ucfirst($s) ?> (<?= (int)($counts[$s] ?? 0) ?>)
            </a>
        <?php endforeach; ?>
    </div>

    <div class="table-wrapper">
        <table class="admin-table">
            <thead>
                <tr><th>Name</th><th>Celebrity</th><th>Event Date</th><th>Budget</th><th>Status</th><th>Received</th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $b): ?>
                <tr>
                    <td><?= e($b['full_name']) ?><br><small style="color:#6a7180;"><?= e($b['email']) ?></small></td>
                    <td><?= e($b['celebrity_name_snapshot'] ?: 'General inquiry') ?></td>
                    <td><?= $b['event_date'] ? e(format_date($b['event_date'])) : '—' ?></td>
                    <td><?= e($b['budget_range'] ?: '—') ?></td>
                    <td><span class="status status-<?= e($b['status']) ?>"><?= e($b['status']) ?></span></td>
                    <td><?= e(format_date($b['created_at'], 'M j, Y g:ia')) ?></td>
                    <td><a href="/admin/booking-view.php?id=<?= (int)$b['id'] ?>">View</a></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($bookings)): ?>
                    <tr><td colspan="7" class="empty-state">No bookings found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
