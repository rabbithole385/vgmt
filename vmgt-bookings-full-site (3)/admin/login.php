<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/auth.php';

if (current_admin()) {
    redirect('/admin/index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = 'Your session expired. Please try again.';
    } elseif (too_many_requests('admin_login', 8, 600)) {
        $error = 'Too many login attempts. Please wait a few minutes and try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = (string)($_POST['password'] ?? '');

        if (admin_login_attempt($username, $password)) {
            $redirect = $_SESSION['redirect_after_login'] ?? '/admin/index.php';
            unset($_SESSION['redirect_after_login']);
            redirect($redirect);
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Login - <?= e(get_setting('site_name', 'VMGT BOOKINGS')) ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="/admin/assets/css/admin.css">
</head>
<body class="login-body">
    <div class="login-box">
        <img src="/assets/images/logo.png" alt="Logo" class="login-logo">
        <h1>Admin Login</h1>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="post">
            <?= csrf_field() ?>
            <div class="form-row">
                <label>Username
                    <input type="text" name="username" required autofocus value="<?= e($_POST['username'] ?? '') ?>">
                </label>
            </div>
            <div class="form-row">
                <label>Password
                    <input type="password" name="password" required>
                </label>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Log In</button>
        </form>
        <p class="login-back"><a href="/">&larr; Back to website</a></p>
    </div>
</body>
</html>
