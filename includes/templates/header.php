<?php
/**
 * Shared header for all public pages.
 * Expects $pageTitle to be set by the including page.
 */
$siteName = get_setting('site_name', 'VMGT BOOKINGS');
$pageTitle = $pageTitle ?? $siteName;
$currentPage = $currentPage ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= e($pageTitle) ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<meta name="description" content="<?= e(get_setting('tagline')) ?>">
<link rel="icon" href="/assets/images/logo.png">
<link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>

<header class="site-header">
    <div class="container header-inner">
        <a href="/" class="logo">
            <img src="/assets/images/logo.png" alt="<?= e($siteName) ?>" height="46">
        </a>
        <nav class="main-nav" id="mainNav">
            <a href="/" class="<?= $currentPage === 'home' ? 'active' : '' ?>">Home</a>
            <a href="/celebrities.php" class="<?= $currentPage === 'celebrities' ? 'active' : '' ?>">Find Celebrity</a>
            <a href="/blog.php" class="<?= $currentPage === 'blog' ? 'active' : '' ?>">News</a>
            <a href="/about.php" class="<?= $currentPage === 'about' ? 'active' : '' ?>">About</a>
            <a href="/contact.php" class="<?= $currentPage === 'contact' ? 'active' : '' ?>">Contact</a>
            <a href="/book.php" class="btn btn-primary nav-cta">Book Now</a>
        </nav>
        <button class="mobile-toggle" id="mobileToggle" aria-label="Menu">
            <span></span><span></span><span></span>
        </button>
    </div>
</header>

<main>
<?php foreach (flash_get_all() as $msg): ?>
    <div class="container">
        <div class="alert alert-<?= e($msg['type']) ?>"><?= e($msg['message']) ?></div>
    </div>
<?php endforeach; ?>
