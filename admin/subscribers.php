<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/auth.php';
require_login();

if (($_GET['export'] ?? '') === 'csv') {
    $rows = db()->query('SELECT email, created_at FROM subscribers ORDER BY created_at DESC')->fetchAll();
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="subscribers.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Email', 'Subscribed At']);
    foreach ($rows as $r) {
        fputcsv($out, [$r['email'], $r['created_at']]);
    }
    fclose($out);
    exit;
}

$pageTitle = 'Subscribers';
$activeNav = 'subscribers';

$subscribers = db()->query('SELECT * FROM subscribers ORDER BY created_at DESC')->fetchAll();

require __DIR__ . '/includes/admin-header.php';
?>

<div class="panel">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <h2 style="margin:0;">Newsletter Subscribers (<?= count($subscribers) ?>)</h2>
        <a href="/admin/subscribers.php?export=csv" class="btn btn-outline">Export CSV</a>
    </div>
    <div class="table-wrapper">
        <table class="admin-table">
            <thead><tr><th>Email</th><th>Subscribed</th></tr></thead>
            <tbody>
                <?php foreach ($subscribers as $s): ?>
                <tr><td><?= e($s['email']) ?></td><td><?= e(format_date($s['created_at'], 'M j, Y g:ia')) ?></td></tr>
                <?php endforeach; ?>
                <?php if (empty($subscribers)): ?>
                    <tr><td colspan="2" class="empty-state">No subscribers yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
