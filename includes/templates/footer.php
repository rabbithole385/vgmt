<?php $siteName = get_setting('site_name', 'VMGT BOOKINGS'); ?>
</main>

<section class="newsletter-section">
    <div class="container newsletter-inner">
        <div>
            <span class="badge badge-gold" style="margin-bottom: 8px;">VIP INSIDER</span>
            <h3>Exclusive Talent & Private Vacation Drops</h3>
            <p>Subscribe to receive first access to high-profile celebrity availability and ultra-luxury getaway packages.</p>
        </div>
        <form id="newsletterForm" class="newsletter-form">
            <input type="email" name="email" placeholder="Enter your email address" required>
            <button type="submit" class="btn btn-primary">Join VIP List</button>
        </form>
    </div>
    <p class="newsletter-message" id="newsletterMessage"></p>
</section>

<footer class="site-footer">
    <div class="container footer-inner">
        <div class="footer-col">
            <img src="/assets/images/logo_white.png" alt="<?= e($siteName) ?>" height="38" style="margin-bottom: 16px;">
            <p><?= e(get_setting('contact_address', 'Abuja, Nigeria & Worldwide Concierge')) ?></p>
            <p><?= e(get_setting('contact_email', 'vip@vmgtbookings.com')) ?><br><?= e(get_setting('contact_phone', '+234 800 000 0000')) ?></p>
        </div>
        <div class="footer-col">
            <h4>Concierge Services</h4>
            <a href="/celebrities.php">Celebrity Roster</a>
            <a href="/vacations.php">VIP Vacations & Getaways</a>
            <a href="/book.php">Book An Appearance</a>
            <a href="/blog.php">Latest News & Press</a>
            <a href="/about.php">About Agency</a>
            <a href="/contact.php">Private Inquiries</a>
        </div>
        <div class="footer-col">
            <h4>Global Presence</h4>
            <p style="font-size: 0.9rem;">Providing discreet, premier talent acquisition, keynote speakers, and private island retreats across Europe, the Americas, Asia, and Africa.</p>
            <div class="social-links" style="margin-top: 14px;">
                <a href="<?= e(get_setting('instagram_url', '#')) ?>" target="_blank" rel="noopener">Instagram</a>
                <a href="<?= e(get_setting('twitter_url', '#')) ?>" target="_blank" rel="noopener">X / Twitter</a>
                <a href="<?= e(get_setting('facebook_url', '#')) ?>" target="_blank" rel="noopener">Facebook</a>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        &copy; <?= date('Y') ?> <?= e($siteName) ?>. Luxury Talent Concierge & VIP Vacations. All rights reserved.
    </div>
</footer>

<!-- Mobile App Bottom Navigation Bar -->
<nav class="app-bottom-nav" aria-label="Mobile Navigation">
    <a href="/" class="app-nav-item <?= ($currentPage ?? '') === 'home' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
        <span>Home</span>
    </a>
    <a href="/celebrities.php" class="app-nav-item <?= ($currentPage ?? '') === 'celebrities' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
        <span>Celebrities</span>
    </a>
    <a href="/book.php" class="app-nav-item app-nav-highlight <?= ($currentPage ?? '') === 'book' ? 'active' : '' ?>">
        <div class="app-nav-glow">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
        </div>
        <span>Book VIP</span>
    </a>
    <a href="/vacations.php" class="app-nav-item <?= ($currentPage ?? '') === 'vacations' ? 'active' : '' ?>">
        <span class="nav-badge-pill">NEW</span>
        <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M14 6l-3.75 5 2.85 3.8-1.6 1.2C9.8 13.7 7 10 7 10l-6 8h22L14 6z"/></svg>
        <span>Vacations</span>
    </a>
    <a href="/blog.php" class="app-nav-item <?= ($currentPage ?? '') === 'blog' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
        <span>News</span>
    </a>
</nav>

<script src="/assets/js/app.js"></script>
</body>
</html>
