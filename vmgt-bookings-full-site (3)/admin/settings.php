<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/auth.php';
require_login();

$pageTitle = 'Site Settings';
$activeNav = 'settings';

$fields = [
    'site_name'      => 'Site Name',
    'tagline'        => 'Homepage Tagline',
    'hero_subtitle'  => 'Homepage Hero Subtitle',
    'about_title'    => 'About Section Title',
    'about_text'     => 'About Text',
    'cta_title'      => 'Call-To-Action Title',
    'cta_text'       => 'Call-To-Action Text',
    'contact_email'  => 'Contact Email',
    'contact_phone'  => 'Contact Phone',
    'contact_address'=> 'Contact Address',
    'facebook_url'   => 'Facebook URL',
    'twitter_url'    => 'Twitter / X URL',
    'instagram_url'  => 'Instagram URL',
    'youtube_url'    => 'YouTube URL',
];
$textareas = ['about_text', 'cta_text'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        flash_set('error', 'Session expired, please try again.');
    } else {
        foreach ($fields as $key => $label) {
            set_setting($key, trim($_POST[$key] ?? ''));
        }
        flash_set('success', 'Settings saved.');
    }
    redirect('/admin/settings.php');
}

require __DIR__ . '/includes/admin-header.php';
?>

<div class="panel">
    <form method="post">
        <?= csrf_field() ?>
        <?php foreach ($fields as $key => $label): ?>
            <div class="form-row">
                <label><?= e($label) ?></label>
                <?php if (in_array($key, $textareas, true)): ?>
                    <textarea name="<?= $key ?>" rows="4"><?= e(get_setting($key)) ?></textarea>
                <?php else: ?>
                    <input type="text" name="<?= $key ?>" value="<?= e(get_setting($key)) ?>">
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <button type="submit" class="btn btn-primary">Save Settings</button>
    </form>
</div>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
