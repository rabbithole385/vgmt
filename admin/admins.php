<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/auth.php';
require_login();
require_role('super_admin');

$pageTitle = 'Admin Users';
$activeNav = 'admins';
$errors = [];
$me = current_admin();

// ---- Handle delete ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (!csrf_verify()) {
        flash_set('error', 'Session expired, please try again.');
    } else {
        $id = (int)$_POST['id'];
        if ($id === (int)$me['id']) {
            flash_set('error', 'You cannot delete your own account while logged in.');
        } else {
            db()->prepare('DELETE FROM admins WHERE id = :id')->execute(['id' => $id]);
            flash_set('success', 'Admin account deleted.');
        }
    }
    redirect('/admin/admins.php');
}

// ---- Handle toggle status ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle_status') {
    if (!csrf_verify()) {
        flash_set('error', 'Session expired, please try again.');
    } else {
        $id = (int)$_POST['id'];
        if ($id === (int)$me['id']) {
            flash_set('error', 'You cannot disable your own account.');
        } else {
            $stmt = db()->prepare('SELECT status FROM admins WHERE id = :id');
            $stmt->execute(['id' => $id]);
            $row = $stmt->fetch();
            if ($row) {
                $newStatus = $row['status'] === 'active' ? 'disabled' : 'active';
                db()->prepare('UPDATE admins SET status = :s WHERE id = :id')->execute(['s' => $newStatus, 'id' => $id]);
                flash_set('success', 'Status updated.');
            }
        }
    }
    redirect('/admin/admins.php');
}

// ---- Handle add new admin ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    if (!csrf_verify()) {
        $errors[] = 'Session expired. Please try again.';
    } else {
        $name     = trim($_POST['name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = (string)($_POST['password'] ?? '');
        $role     = in_array($_POST['role'] ?? '', ['super_admin', 'editor'], true) ? $_POST['role'] : 'editor';

        if ($name === '' || $username === '' || $email === '' || $password === '') {
            $errors[] = 'All fields are required.';
        }
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        }
        if ($username !== '' && !preg_match('/^[a-zA-Z0-9_.-]+$/', $username)) {
            $errors[] = 'Username can only contain letters, numbers, dots, dashes and underscores.';
        }

        if (empty($errors)) {
            $check = db()->prepare('SELECT id FROM admins WHERE username = :u');
            $check->execute(['u' => $username]);
            if ($check->fetch()) {
                $errors[] = 'That username is already taken.';
            } else {
                $stmt = db()->prepare(
                    'INSERT INTO admins (name, username, email, password_hash, role) VALUES (:n, :u, :e, :p, :r)'
                );
                $stmt->execute([
                    'n' => $name, 'u' => $username, 'e' => $email,
                    'p' => password_hash($password, PASSWORD_BCRYPT), 'r' => $role,
                ]);
                flash_set('success', 'Admin account created.');
                redirect('/admin/admins.php');
            }
        }
    }
}

$admins = db()->query('SELECT * FROM admins ORDER BY created_at ASC')->fetchAll();

require __DIR__ . '/includes/admin-header.php';
?>

<div class="panel">
    <h2>Add New Admin User</h2>
    <?php if ($errors): ?>
        <div class="alert alert-error"><ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="create">
        <div class="form-grid">
            <div class="form-row">
                <label>Full Name *</label>
                <input type="text" name="name" required value="<?= e($_POST['name'] ?? '') ?>">
            </div>
            <div class="form-row">
                <label>Username *</label>
                <input type="text" name="username" required value="<?= e($_POST['username'] ?? '') ?>">
            </div>
        </div>
        <div class="form-grid">
            <div class="form-row">
                <label>Email *</label>
                <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-row">
                <label>Password * (min 8 characters)</label>
                <input type="password" name="password" required>
            </div>
        </div>
        <div class="form-row">
            <label>Role</label>
            <select name="role">
                <option value="editor">Editor (no admin-user management)</option>
                <option value="super_admin">Super Admin (full access)</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Create Admin</button>
    </form>
</div>

<div class="panel">
    <h2>All Admin Users (<?= count($admins) ?>)</h2>
    <div class="table-wrapper">
        <table class="admin-table">
            <thead><tr><th>Name</th><th>Username</th><th>Role</th><th>Status</th><th>Last Login</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($admins as $a): ?>
                <tr>
                    <td><?= e($a['name']) ?><?= (int)$a['id'] === (int)$me['id'] ? ' (you)' : '' ?></td>
                    <td><?= e($a['username']) ?></td>
                    <td><?= $a['role'] === 'super_admin' ? 'Super Admin' : 'Editor' ?></td>
                    <td><span class="status status-<?= e($a['status']) ?>"><?= e($a['status']) ?></span></td>
                    <td><?= $a['last_login_at'] ? e(format_date($a['last_login_at'], 'M j, Y g:ia')) : 'Never' ?></td>
                    <td class="table-actions">
                        <?php if ((int)$a['id'] !== (int)$me['id']): ?>
                            <form method="post" style="display:inline;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="toggle_status">
                                <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
                                <button type="submit" style="background:none;border:none;cursor:pointer;font-size:.85rem;color:#1d4ed8;">
                                    <?= $a['status'] === 'active' ? 'Disable' : 'Enable' ?>
                                </button>
                            </form>
                            <form method="post" data-confirm="Delete this admin account permanently?" style="display:inline;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
                                <button type="submit" class="btn-danger" style="background:none;border:none;cursor:pointer;font-size:.85rem;">Delete</button>
                            </form>
                        <?php else: ?>
                            <span style="color:#6a7180;">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
