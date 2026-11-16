<?php
// ============================================================
// config.php — environment + database settings
// ============================================================
// Values come from real environment variables (Railway, Render,
// VPS) or a local .env file. Nothing secret belongs in this file.
// ============================================================

declare(strict_types=1);

// --- load .env (simple KEY=VALUE parser) --------------------
$envPath = __DIR__ . '/.env';
if (is_file($envPath)) {
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value);
        if (strlen($value) > 1
            && ($value[0] === '"' || $value[0] === "'")
            && $value[0] === substr($value, -1)
        ) {
            $value = substr($value, 1, -1);
        }
        if (getenv($key) === false) {
            putenv($key . '=' . $value);
        }
    }
}

// --- app ----------------------------------------------------
define('APP_NAME', getenv('APP_NAME') ?: "Flynn's Cozy Staycation");
define('BASE_URL', rtrim((string)(getenv('BASE_URL') ?: ''), '/'));
define('APP_DEBUG', filter_var(getenv('APP_DEBUG') ?: '0', FILTER_VALIDATE_BOOLEAN));
define('CURRENCY', getenv('CURRENCY') ?: '₱');

date_default_timezone_set(getenv('APP_TZ') ?: 'Asia/Manila');

// --- database -----------------------------------------------
// DATABASE_URL wins. Supabase gives you a URI like:
//   postgresql://postgres.<ref>:<pass>@aws-0-<region>.pooler.supabase.com:5432/postgres?sslmode=require
$dbUrl = (string)(getenv('DATABASE_URL') ?: '');

if ($dbUrl !== '') {
    $parts = parse_url($dbUrl);
    if ($parts === false || empty($parts['host'])) {
        throw new RuntimeException('DATABASE_URL is not a valid postgres:// URI.');
    }
    $port = isset($parts['port']) ? (int)$parts['port'] : 5432;
    $name = ltrim((string)($parts['path'] ?? '/postgres'), '/') ?: 'postgres';

    $dsn = sprintf('pgsql:host=%s;port=%d;dbname=%s', $parts['host'], $port, $name);

    parse_str((string)($parts['query'] ?? ''), $query);
    $ssl = $query['sslmode'] ?? (str_contains($parts['host'], 'supabase.') ? 'require' : null);
    if ($ssl !== null) {
        $dsn .= ';sslmode=' . $ssl;
    }

    define('DB_DSN', $dsn);
    define('DB_USER', urldecode((string)($parts['user'] ?? '')));
    define('DB_PASS', urldecode((string)($parts['pass'] ?? '')));
} else {
    $dsn = getenv('DB_DSN');
    $user = getenv('DB_USER');
    $password = getenv('DB_PASS');
    if (!$dsn || !$user || $password === false) {
        throw new RuntimeException('Set DATABASE_URL or DB_DSN, DB_USER and DB_PASS.');
    }
    define('DB_DSN', $dsn);
    define('DB_USER', $user);
    define('DB_PASS', $password);
}
