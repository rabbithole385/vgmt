<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/auth.php';
require_login();

$pageTitle = 'Dashboard';
$activeNav = 'dashboard';

$totalCelebs   = (int)db()->query("SELECT COUNT(*) c FROM celebrities")->fetch()['c'];
$totalBookings = (int)db()->query("SELECT COUNT(*) c FROM bookings")->fetch()['c'];
$pendingCount  = (int)db()->query("SELECT COUNT(*) c FROM bookings WHERE status = 'pending'")->fetch()['c'];
$newInquiries  = (int)db()->query("SELECT COUNT(*) c FROM inquiries WHERE status = 'new'")->fetch()['c'];
$subscribers   = (int)db()->query("SELECT COUNT(*) c FROM subscribers")->fetch()['c'];
$publishedPosts = (int)db()->query("SELECT COUNT(*) c FROM blog_posts WHERE status = 'published'")->fetch()['c'];

$recentBookings = db()->query(
    "SELECT * FROM bookings ORDER BY created_at DESC LIMIT 6"
)->fetchAll();
$recentInquiries = db()->query(
    "SELECT * FROM inquiries ORDER BY created_at DESC LIMIT 6"
)->fetchAll();

require __DIR__ . '/includes/admin-header.php';
?>

<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-value"><?= $totalCelebs ?></div>
        <div class="stat-label">Celebrities</div>
        <a href="/admin/celebrities.php">Manage &rarr;</a>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $pendingCount ?> <span style="font-size:1rem;color:#6a7180;">/ <?= $totalBookings ?></span></div>
        <div class="stat-label">Pending Bookings</div>
        <a href="/admin/bookings.php">Manage &rarr;</a>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $newInquiries ?></div>
        <div class="stat-label">New Inquiries</div>
        <a href="/admin/inquiries.php">Manage &rarr;</a>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $publishedPosts ?></div>
        <div class="stat-label">Published Posts</div>
        <a href="/admin/blog.php">Manage &rarr;</a>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $subscribers ?></div>
        <div class="stat-label">Newsletter Subscribers</div>
        <a href="/admin/subscribers.php">View &rarr;</a>
    </div>
</div>

<div class="panel">
    <h2>Recent Booking Requests</h2>
    <div class="table-wrapper">
        <table class="admin-table">
            <thead>
                <tr><th>Name</th><th>Celebrity</th><th>Event Date</th><th>Status</th><th>Received</th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach ($recentBookings as $b): ?>
                <tr>
                    <td><?= e($b['full_name']) ?></td>
                    <td><?= e($b['celebrity_name_snapshot'] ?: '—') ?></td>
                    <td><?= e(format_date($b['event_date'])) ?: '—' ?></td>
                    <td><span class="status status-<?= e($b['status']) ?>"><?= e($b['status']) ?></span></td>
                    <td><?= e(format_date($b['created_at'], 'M j, Y g:ia')) ?></td>
                    <td><a href="/admin/booking-view.php?id=<?= (int)$b['id'] ?>">View</a></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($recentBookings)): ?>
                    <tr><td colspan="6" class="empty-state">No booking requests yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="panel">
    <h2>Recent Contact Inquiries</h2>
    <div class="table-wrapper">
        <table class="admin-table">
            <thead>
                <tr><th>Name</th><th>Subject</th><th>Status</th><th>Received</th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach ($recentInquiries as $i): ?>
                <tr>
                    <td><?= e($i['name']) ?></td>
                    <td><?= e($i['subject'] ?: '—') ?></td>
                    <td><span class="status status-<?= e($i['status']) ?>"><?= e($i['status']) ?></span></td>
                    <td><?= e(format_date($i['created_at'], 'M j, Y g:ia')) ?></td>
                    <td><a href="/admin/inquiry-view.php?id=<?= (int)$i['id'] ?>">View</a></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($recentInquiries)): ?>
                    <tr><td colspan="5" class="empty-state">No inquiries yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
