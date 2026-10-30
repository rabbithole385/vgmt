<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/auth.php';
require_login();

$pageTitle = 'Inquiries';
$activeNav = 'inquiries';

$statusFilter = $_GET['status'] ?? '';
$validStatuses = ['new', 'read', 'replied'];

if (in_array($statusFilter, $validStatuses, true)) {
    $stmt = db()->prepare('SELECT * FROM inquiries WHERE status = :s ORDER BY created_at DESC');
    $stmt->execute(['s' => $statusFilter]);
} else {
    $stmt = db()->query('SELECT * FROM inquiries ORDER BY created_at DESC');
}
$inquiries = $stmt->fetchAll();
$counts = db()->query('SELECT status, COUNT(*) c FROM inquiries GROUP BY status')->fetchAll(PDO::FETCH_KEY_PAIR);

require __DIR__ . '/includes/admin-header.php';
?>

<div class="panel">
    <div class="filter-bar">
        <a href="/admin/inquiries.php" class="<?= $statusFilter === '' ? 'active' : '' ?>">All (<?= array_sum($counts) ?>)</a>
        <?php foreach ($validStatuses as $s): ?>
            <a href="/admin/inquiries.php?status=<?= $s ?>" class="<?= $statusFilter === $s ? 'active' : '' ?>">
                <?= ucfirst($s) ?> (<?= (int)($counts[$s] ?? 0) ?>)
            </a>
        <?php endforeach; ?>
    </div>

    <div class="table-wrapper">
        <table class="admin-table">
            <thead><tr><th>Name</th><th>Subject</th><th>Status</th><th>Received</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($inquiries as $i): ?>
                <tr>
                    <td><?= e($i['name']) ?><br><small style="color:#6a7180;"><?= e($i['email']) ?></small></td>
                    <td><?= e($i['subject'] ?: '—') ?></td>
                    <td><span class="status status-<?= e($i['status']) ?>"><?= e($i['status']) ?></span></td>
                    <td><?= e(format_date($i['created_at'], 'M j, Y g:ia')) ?></td>
                    <td><a href="/admin/inquiry-view.php?id=<?= (int)$i['id'] ?>">View</a></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($inquiries)): ?>
                    <tr><td colspan="5" class="empty-state">No inquiries found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
