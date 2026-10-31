<?php
require_once __DIR__ . '/config.php';

$pageTitle = 'Contact - ' . get_setting('site_name');
$currentPage = 'contact';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Your session expired. Please try again.';
    } elseif (too_many_requests('contact_form', 5, 600)) {
        $errors[] = 'Too many submissions. Please try again later.';
    } else {
        $name    = trim($_POST['name'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        // Honeypot field to catch simple bots
        $honeypot = trim($_POST['website'] ?? '');

        if ($honeypot !== '') {
            $errors[] = 'Submission rejected.';
        }
        if ($name === '' || $email === '' || $message === '') {
            $errors[] = 'Please fill in your name, email and message.';
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }

        if (empty($errors)) {
            $stmt = db()->prepare(
                'INSERT INTO inquiries (name, email, subject, message) VALUES (:n, :e, :s, :m)'
            );
            $stmt->execute([
                'n' => $name, 'e' => $email, 's' => $subject, 'm' => $message,
            ]);

            send_notification_email(
                ADMIN_EMAIL,
                'New contact inquiry - ' . get_setting('site_name'),
                '<p><strong>Name:</strong> ' . e($name) . '</p>'
                . '<p><strong>Email:</strong> ' . e($email) . '</p>'
                . '<p><strong>Subject:</strong> ' . e($subject) . '</p>'
                . '<p><strong>Message:</strong><br>' . nl2br(e($message)) . '</p>'
            );

            flash_set('success', 'Thanks for reaching out! We will get back to you shortly.');
            redirect('/contact.php');
        }
    }
}

require __DIR__ . '/includes/templates/header.php';
?>

<section class="page-title-band">
    <div class="container">
        <span class="badge badge-gold" style="margin-bottom: 10px;">PRIVATE INQUIRIES</span>
        <h1 class="text-gold">Contact Our Concierge</h1>
        <p>Reach our private VIP team for bookings, press inquiries, or partnership proposals.</p>
    </div>
</section>

<section class="content-section">
    <div class="container narrow">
        <?php if ($errors): ?>
            <div class="alert alert-error">
                <ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <div class="contact-grid">
            <form method="post" class="contact-form">
                <?= csrf_field() ?>
                <div style="position:absolute;left:-5000px" aria-hidden="true">
                    <label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                </div>
                <div class="form-row">
                    <label>Full Name
                        <input type="text" name="name" required value="<?= e($_POST['name'] ?? '') ?>">
                    </label>
                </div>
                <div class="form-row">
                    <label>Email
                        <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">
                    </label>
                </div>
                <div class="form-row">
                    <label>Subject
                        <input type="text" name="subject" value="<?= e($_POST['subject'] ?? '') ?>">
                    </label>
                </div>
                <div class="form-row">
                    <label>Message
                        <textarea name="message" rows="6" required><?= e($_POST['message'] ?? '') ?></textarea>
                    </label>
                </div>
                <button type="submit" class="btn btn-primary">Send Private Message</button>
            </form>

            <div class="contact-info">
                <h3>VIP Concierge Desk</h3>
                <p style="color: var(--color-text);">📍 <?= e(get_setting('contact_address')) ?></p>
                <p style="color: var(--color-text);">📧 <?= e(get_setting('contact_email')) ?></p>
                <p style="color: var(--color-text);">📞 <?= e(get_setting('contact_phone')) ?></p>
                <hr style="border-color: var(--color-border); margin: 18px 0;">
                <p style="font-size: 0.88rem;">For urgent celebrity availability or same-day bookings, please call directly. Our concierge team operates 24/7.</p>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/templates/footer.php'; ?>
