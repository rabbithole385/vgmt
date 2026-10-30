<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/auth.php';
require_login();

$pageTitle = 'My Profile';
$activeNav = 'profile';
$me = current_admin();
$errors = [];

$stmt = db()->prepare('SELECT * FROM admins WHERE id = :id');
$stmt->execute(['id' => $me['id']]);
$account = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Session expired. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'update_details') {
            $name  = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            if ($name === '' || $email === '') {
                $errors[] = 'Name and email are required.';
            } else {
                db()->prepare('UPDATE admins SET name = :n, email = :e WHERE id = :id')
                    ->execute(['n' => $name, 'e' => $email, 'id' => $me['id']]);
                $_SESSION['admin']['name'] = $name;
                flash_set('success', 'Profile updated.');
                redirect('/admin/profile.php');
            }
        }

        if ($action === 'change_password') {
            $current = (string)($_POST['current_password'] ?? '');
            $new     = (string)($_POST['new_password'] ?? '');
            $confirm = (string)($_POST['confirm_password'] ?? '');

            if (!password_verify($current, $account['password_hash'])) {
                $errors[] = 'Current password is incorrect.';
            } elseif (strlen($new) < 8) {
                $errors[] = 'New password must be at least 8 characters.';
            } elseif ($new !== $confirm) {
                $errors[] = 'New password and confirmation do not match.';
            } else {
                db()->prepare('UPDATE admins SET password_hash = :p WHERE id = :id')
                    ->execute(['p' => password_hash($new, PASSWORD_BCRYPT), 'id' => $me['id']]);
                flash_set('success', 'Password changed successfully.');
                redirect('/admin/profile.php');
            }
        }
    }
}

require __DIR__ . '/includes/admin-header.php';
?>

<?php if ($errors): ?>
    <div class="alert alert-error"><ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="panel">
    <h2>My Details</h2>
    <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="update_details">
        <div class="form-grid">
            <div class="form-row">
                <label>Full Name</label>
                <input type="text" name="name" value="<?= e($account['name']) ?>">
            </div>
            <div class="form-row">
                <label>Email</label>
                <input type="email" name="email" value="<?= e($account['email']) ?>">
            </div>
        </div>
        <p class="form-help">Username: <?= e($account['username']) ?> (cannot be changed here)</p>
        <button type="submit" class="btn btn-primary">Save Details</button>
    </form>
</div>

<div class="panel">
    <h2>Change Password</h2>
    <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="change_password">
        <div class="form-row">
            <label>Current Password</label>
            <input type="password" name="current_password" required>
        </div>
        <div class="form-grid">
            <div class="form-row">
                <label>New Password (min 8 characters)</label>
                <input type="password" name="new_password" required>
            </div>
            <div class="form-row">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" required>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Change Password</button>
    </form>
</div>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
