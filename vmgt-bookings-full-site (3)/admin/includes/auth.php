<?php
/**
 * Admin auth guard. Include this at the top of every protected admin page
 * (after config.php has already been required).
 */

function current_admin(): ?array
{
    return $_SESSION['admin'] ?? null;
}

function require_login(): void
{
    if (!current_admin()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '/admin/';
        redirect('/admin/login.php');
    }
}

function require_role(string $role): void
{
    $admin = current_admin();
    if (!$admin || $admin['role'] !== $role) {
        http_response_code(403);
        die('You do not have permission to access this page.');
    }
}

function admin_login_attempt(string $username, string $password): bool
{
    $stmt = db()->prepare("SELECT * FROM admins WHERE username = :u AND status = 'active' LIMIT 1");
    $stmt->execute(['u' => $username]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($password, $admin['password_hash'])) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['admin'] = [
        'id'       => $admin['id'],
        'name'     => $admin['name'],
        'username' => $admin['username'],
        'role'     => $admin['role'],
    ];

    $update = db()->prepare('UPDATE admins SET last_login_at = :now WHERE id = :id');
    $update->execute(['now' => date('Y-m-d H:i:s'), 'id' => $admin['id']]);

    return true;
}

function admin_logout(): void
{
    unset($_SESSION['admin']);
    session_regenerate_id(true);
}
