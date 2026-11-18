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
        $alreadyQueued = false;
        foreach ($_SESSION['flash'] ?? [] as $f) {
            if (($f['message'] ?? '') === 'Please sign in to continue.') {
                $alreadyQueued = true;
                break;
            }
        }
        if (!$alreadyQueued) {
            flash('error', 'Please sign in to continue.');
        }
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

function client_ip(): string
{
    $forwarded = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
    if ($forwarded !== '') {
        $parts = explode(',', $forwarded);
        return trim($parts[0]);
    }
    return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
}

function login_throttle_key(string $email): string
{
    return hash('sha256', client_ip() . '|' . strtolower(trim($email)));
}

function login_is_throttled(string $email = ''): bool
{
    if (login_throttled()) {
        return true;
    }
    if ($email === '') {
        return false;
    }
    $file = sys_get_temp_dir() . '/auth_throttle_' . login_throttle_key($email) . '.json';
    if (!is_file($file)) {
        return false;
    }
    $data = json_decode((string)file_get_contents($file), true);
    if (!is_array($data) || ($data['started'] ?? 0) < time() - 900) {
        @unlink($file);
        return false;
    }
    return ($data['count'] ?? 0) >= 5;
}

function login_record_failure(string $email): void
{
    $_SESSION['login_attempts']['count'] = ($_SESSION['login_attempts']['count'] ?? 0) + 1;
    $file = sys_get_temp_dir() . '/auth_throttle_' . login_throttle_key($email) . '.json';
    $data = ['started' => time(), 'count' => 1];
    if (is_file($file)) {
        $existing = json_decode((string)file_get_contents($file), true);
        if (is_array($existing) && ($existing['started'] ?? 0) >= time() - 900) {
            $data['started'] = $existing['started'];
            $data['count'] = ($existing['count'] ?? 0) + 1;
        }
    }
    @file_put_contents($file, json_encode($data), LOCK_EX);
}

function login_clear_throttle(string $email): void
{
    unset($_SESSION['login_attempts']);
    $file = sys_get_temp_dir() . '/auth_throttle_' . login_throttle_key($email) . '.json';
    @unlink($file);
}

function attempt_login(string $email, string $password): bool
{
    if (login_is_throttled($email)) {
        return false;
    }

    $stmt = db()->prepare(
        'SELECT * FROM admin_users WHERE lower(email) = lower(:email) LIMIT 1'
    );
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if (!$user || !$user['is_active'] || !password_verify($password, $user['password_hash'])) {
        login_record_failure($email);
        return false;
    }

    session_regenerate_id(true);
    login_clear_throttle($email);
    unset($_SESSION['flash']);
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
