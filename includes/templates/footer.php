<?php $siteName = get_setting('site_name', 'VMGT BOOKINGS'); ?>
</main>

<section class="newsletter-section">
    <div class="container newsletter-inner">
        <div>
            <h3>Don't miss a thing!</h3>
            <p>Sign up to receive news about the latest talent available for booking.</p>
        </div>
        <form id="newsletterForm" class="newsletter-form">
            <input type="email" name="email" placeholder="Your email address" required>
            <button type="submit" class="btn btn-primary">Subscribe</button>
        </form>
    </div>
    <p class="newsletter-message" id="newsletterMessage"></p>
</section>

<footer class="site-footer">
    <div class="container footer-inner">
        <div class="footer-col">
            <img src="/assets/images/logo_white.png" alt="<?= e($siteName) ?>" height="40">
            <p><?= e(get_setting('contact_address')) ?></p>
            <p><?= e(get_setting('contact_email')) ?><br><?= e(get_setting('contact_phone')) ?></p>
        </div>
        <div class="footer-col">
            <h4>Quick Links</h4>
            <a href="/celebrities.php">Find Celebrity</a>
            <a href="/about.php">About Us</a>
            <a href="/blog.php">News</a>
            <a href="/contact.php">Contact</a>
        </div>
        <div class="footer-col">
            <h4>Follow Us</h4>
            <div class="social-links">
                <a href="<?= e(get_setting('facebook_url', '#')) ?>" target="_blank" rel="noopener">Facebook</a>
                <a href="<?= e(get_setting('twitter_url', '#')) ?>" target="_blank" rel="noopener">Twitter</a>
                <a href="<?= e(get_setting('instagram_url', '#')) ?>" target="_blank" rel="noopener">Instagram</a>
                <a href="<?= e(get_setting('youtube_url', '#')) ?>" target="_blank" rel="noopener">YouTube</a>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        &copy; <?= date('Y') ?> <?= e($siteName) ?>. All rights reserved.
    </div>
</footer>

<script src="/assets/js/app.js"></script>
</body>
</html>
