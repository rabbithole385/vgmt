<?php
require_once __DIR__ . '/../config.php';

$pdo = db();
$msg = '';
$err = '';

// Default credentials
$defaultUser = 'admin';
$defaultPass = 'AdminPassword2026!';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPass = trim((string)($_POST['password'] ?? ''));
    if (strlen($newPass) < 8) {
        $err = 'Password must be at least 8 characters long.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM admins WHERE username = :u LIMIT 1');
        $stmt->execute(['u' => $defaultUser]);
        $existing = $stmt->fetch();

        if ($existing) {
            $up = $pdo->prepare('UPDATE admins SET password_hash = :p, status = "active", role = "super_admin" WHERE id = :id');
            $up->execute([
                'p' => password_hash($newPass, PASSWORD_BCRYPT),
                'id' => $existing['id']
            ]);
        } else {
            $ins = $pdo->prepare('INSERT INTO admins (name, username, email, password_hash, role, status) VALUES ("Administrator", :u, "admin@vmgtbookings.com", :p, "super_admin", "active")');
            $ins->execute([
                'u' => $defaultUser,
                'p' => password_hash($newPass, PASSWORD_BCRYPT)
            ]);
        }
        $msg = 'Password successfully set to: ' . e($newPass);
        $defaultPass = $newPass;
    }
} else {
    // On GET, ensure the admin account exists with default password
    $stmt = $pdo->prepare('SELECT id FROM admins WHERE username = :u LIMIT 1');
    $stmt->execute(['u' => $defaultUser]);
    $existing = $stmt->fetch();

    if (!$existing) {
        $ins = $pdo->prepare('INSERT INTO admins (name, username, email, password_hash, role, status) VALUES ("Administrator", :u, "admin@vmgtbookings.com", :p, "super_admin", "active")');
        $ins->execute([
            'u' => $defaultUser,
            'p' => password_hash($defaultPass, PASSWORD_BCRYPT)
        ]);
        $msg = 'Default admin account created!';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Access & Setup - VMGT</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="/admin/assets/css/admin.css">
<style>
    .credential-card {
        background: rgba(245, 192, 66, 0.1);
        border: 1px solid rgba(245, 192, 66, 0.35);
        border-radius: 8px;
        padding: 16px;
        margin: 16px 0;
        text-align: left;
    }
    .credential-card code {
        font-size: 1.1rem;
        color: #f5c042;
        font-weight: 700;
    }
</style>
</head>
<body class="login-body">
    <div class="login-box" style="max-width: 460px;">
        <img src="/assets/images/logo.png" alt="Logo" class="login-logo">
        <h1>Admin Access</h1>

        <?php if ($msg): ?>
            <div class="alert alert-success"><?= $msg ?></div>
        <?php endif; ?>
        <?php if ($err): ?>
            <div class="alert alert-error"><?= e($err) ?></div>
        <?php endif; ?>

        <div class="credential-card">
            <p style="margin: 0 0 8px;"><strong>Username:</strong> <code>admin</code></p>
            <p style="margin: 0;"><strong>Active Password:</strong> <code><?= e($defaultPass) ?></code></p>
        </div>

        <a href="/admin/login.php" class="btn btn-primary btn-block" style="margin-bottom: 24px; padding: 12px; font-size: 1rem; text-decoration: none; display: block; text-align: center;">
            🚀 Click Here to Log In
        </a>

        <hr style="border-color: rgba(255,255,255,0.1); margin: 20px 0;">

        <form method="post" style="text-align: left;">
            <label style="display: block; font-weight: 600; margin-bottom: 6px; font-size: 0.9rem;">
                Want to set a different password?
            </label>
            <div style="display: flex; gap: 8px;">
                <input type="text" name="password" placeholder="Enter new password (min 8 chars)" required style="flex: 1; padding: 10px 12px; border-radius: 6px; border: 1px solid #333; background: #1a1e28; color: #fff;">
                <button type="submit" class="btn btn-outline" style="white-space: nowrap;">Save & Update</button>
            </div>
        </form>

        <p class="login-back" style="margin-top: 20px;"><a href="/">&larr; Back to website</a></p>
    </div>
</body>
</html>
