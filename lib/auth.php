<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

// ============================================================
// auth.php — owner (admin) session
// ============================================================

function admin_user(): ?array
{
    return $_SESSION['admin'] ?? null;
}

function require_admin(): void
{
    if (admin_user() === null) {
        flash('error', 'Please sign in to continue.');
        redirect('admin/login.php');
    }
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_verify(): void
{
    $sent = (string)($_POST['csrf'] ?? '');
    if ($sent === '' || !hash_equals((string)($_SESSION['csrf'] ?? ''), $sent)) {
        http_response_code(419);
        exit('Your session expired. Go back, reload the page, and try again.');
    }
}

function login_throttled(): bool
{
    $attempts = $_SESSION['login_attempts'] ?? null;
    if (!is_array($attempts) || ($attempts['started'] ?? 0) < time() - 900) {
        $_SESSION['login_attempts'] = ['started' => time(), 'count' => 0];
        return false;
    }
    return ($attempts['count'] ?? 0) >= 5;
}

function attempt_login(string $email, string $password): bool
{
    if (login_throttled()) {
        return false;
    }

    $stmt = db()->prepare(
        'SELECT * FROM admin_users WHERE lower(email) = lower(:email) LIMIT 1'
    );
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if (!$user || !$user['is_active'] || !password_verify($password, $user['password_hash'])) {
        $_SESSION['login_attempts']['count'] = ($_SESSION['login_attempts']['count'] ?? 0) + 1;
        return false;
    }

    session_regenerate_id(true);
    unset($_SESSION['login_attempts']);
    $_SESSION['admin'] = [
        'id'           => (int)$user['id'],
        'email'        => $user['email'],
        'display_name' => $user['display_name'],
    ];

    db()->prepare('UPDATE admin_users SET last_login_at = NOW() WHERE id = :id')
        ->execute(['id' => $user['id']]);

    return true;
}

function logout_admin(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}
