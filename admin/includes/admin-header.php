<?php
/**
 * Shared admin layout header. Every protected admin page must:
 *   require_once __DIR__ . '/../config.php';
 *   require_once __DIR__ . '/includes/auth.php';
 *   require_login();
 * before including this file. Expects $pageTitle and $activeNav to be set.
 */
$admin = current_admin();
$pageTitle = $pageTitle ?? 'Admin';
$activeNav = $activeNav ?? '';

// Small counters for sidebar badges
$pendingBookings = (int)db()->query("SELECT COUNT(*) c FROM bookings WHERE status = 'pending'")->fetch()['c'];
$newInquiries    = (int)db()->query("SELECT COUNT(*) c FROM inquiries WHERE status = 'new'")->fetch()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= e($pageTitle) ?> - Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="/admin/assets/css/admin.css">
</head>
<body>
<div class="admin-layout">
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-brand">
            <img src="/assets/images/logo.png" alt="Logo">
        </div>
        <nav class="admin-nav">
            <a href="/admin/index.php" class="<?= $activeNav === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
            <a href="/admin/celebrities.php" class="<?= $activeNav === 'celebrities' ? 'active' : '' ?>">Celebrities</a>
            <a href="/admin/bookings.php" class="<?= $activeNav === 'bookings' ? 'active' : '' ?>">
                Bookings <?php if ($pendingBookings): ?><span class="badge"><?= $pendingBookings ?></span><?php endif; ?>
            </a>
            <a href="/admin/inquiries.php" class="<?= $activeNav === 'inquiries' ? 'active' : '' ?>">
                Inquiries <?php if ($newInquiries): ?><span class="badge"><?= $newInquiries ?></span><?php endif; ?>
            </a>
            <a href="/admin/blog.php" class="<?= $activeNav === 'blog' ? 'active' : '' ?>">Blog / News</a>
            <a href="/admin/subscribers.php" class="<?= $activeNav === 'subscribers' ? 'active' : '' ?>">Subscribers</a>
            <a href="/admin/settings.php" class="<?= $activeNav === 'settings' ? 'active' : '' ?>">Site Settings</a>
            <?php if ($admin['role'] === 'super_admin'): ?>
                <a href="/admin/admins.php" class="<?= $activeNav === 'admins' ? 'active' : '' ?>">Admin Users</a>
            <?php endif; ?>
            <a href="/admin/profile.php" class="<?= $activeNav === 'profile' ? 'active' : '' ?>">My Profile</a>
        </nav>
        <div class="admin-sidebar-footer">
            <a href="/" target="_blank">View Website &#8599;</a>
            <a href="/admin/logout.php">Log Out</a>
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <button id="sidebarToggle" class="sidebar-toggle">&#9776;</button>
            <h1><?= e($pageTitle) ?></h1>
            <div class="admin-user">Hi, <?= e($admin['name']) ?></div>
        </header>

        <div class="admin-content">
            <?php foreach (flash_get_all() as $msg): ?>
                <div class="alert alert-<?= e($msg['type']) ?>"><?= e($msg['message']) ?></div>
            <?php endforeach; ?>
