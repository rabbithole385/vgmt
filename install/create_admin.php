<?php
/**
 * ONE-TIME SETUP SCRIPT
 * Visit this file in your browser once after importing sql/schema.sql to
 * create your first admin login. It will refuse to run again once an admin
 * account already exists, and you should delete this whole /install folder
 * from the server after you're done as an extra safety measure.
 */

require_once __DIR__ . '/../config.php';

$existingCount = (int)db()->query('SELECT COUNT(*) c FROM admins')->fetch()['c'];
$errors = [];
$done = false;

if ($existingCount > 0) {
    // Already set up - refuse to run again.
    http_response_code(403);
    ?>
    <!DOCTYPE html><html><head><meta charset="UTF-8"><title>Setup Already Complete</title>
    <link rel="stylesheet" href="/admin/assets/css/admin.css"></head>
    <body class="login-body">
        <div class="login-box">
            <h1>Setup Already Complete</h1>
            <p>An admin account already exists, so this setup page is locked.</p>
            <p>Please delete the <code>/install</code> folder from your server, and log in at
               <a href="/admin/login.php">/admin/login.php</a>.</p>
        </div>
    </body></html>
    <?php
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = (string)($_POST['password'] ?? '');
    $confirm  = (string)($_POST['confirm_password'] ?? '');

    if ($name === '' || $username === '' || $email === '' || $password === '') {
        $errors[] = 'All fields are required.';
    }
    if (!preg_match('/^[a-zA-Z0-9_.-]+$/', $username)) {
        $errors[] = 'Username can only contain letters, numbers, dots, dashes and underscores.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Password and confirmation do not match.';
    }

    if (empty($errors)) {
        // Double-check no admin was created in the meantime (race protection)
        $recheck = (int)db()->query('SELECT COUNT(*) c FROM admins')->fetch()['c'];
        if ($recheck > 0) {
            $errors[] = 'An admin account already exists. Please refresh and log in instead.';
        } else {
            $stmt = db()->prepare(
                'INSERT INTO admins (name, username, email, password_hash, role) VALUES (:n, :u, :e, :p, :r)'
            );
            $stmt->execute([
                'n' => $name, 'u' => $username, 'e' => $email,
                'p' => password_hash($password, PASSWORD_BCRYPT), 'r' => 'super_admin',
            ]);
            $done = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>First-Time Setup - <?= e(get_setting('site_name', 'VMGT BOOKINGS')) ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="/admin/assets/css/admin.css">
</head>
<body class="login-body">
<div class="login-box" style="max-width:440px;">
    <?php if ($done): ?>
        <h1>All Set!</h1>
        <div class="alert alert-success">Your admin account has been created.</div>
        <p><strong>Important:</strong> now delete the <code>/install</code> folder from your server
           so this page can never be used again.</p>
        <a class="btn btn-primary btn-block" href="/admin/login.php">Go to Admin Login</a>
    <?php else: ?>
        <h1>First-Time Setup</h1>
        <p style="font-size:.85rem;">Create your admin account. This page will lock itself immediately afterwards.</p>
        <?php if ($errors): ?>
            <div class="alert alert-error"><ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
        <?php endif; ?>
        <form method="post">
            <div class="form-row">
                <label>Full Name</label>
                <input type="text" name="name" required value="<?= e($_POST['name'] ?? '') ?>">
            </div>
            <div class="form-row">
                <label>Username</label>
                <input type="text" name="username" required value="<?= e($_POST['username'] ?? '') ?>">
            </div>
            <div class="form-row">
                <label>Email</label>
                <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-row">
                <label>Password (min 8 characters)</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-row">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Create Admin Account</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
