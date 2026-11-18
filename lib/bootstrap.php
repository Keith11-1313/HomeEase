<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/repo.php';
require_once __DIR__ . '/policies.php';

// --- errors ---------------------------------------------------
error_reporting(E_ALL);
ini_set('display_errors', APP_DEBUG ? '1' : '0');
ini_set('log_errors', '1');

// --- baseline security headers --------------------------------
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), camera=(), microphone=()');
    header(
        "Content-Security-Policy: default-src 'self'; "
        . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
        . "font-src https://fonts.gstatic.com; "
        . "img-src 'self' data: https:; "
        . "script-src 'self'; "
        . "frame-src https://drive.google.com https://www.google.com; "
        . "connect-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'self'"
    );
}

// --- session -------------------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => $https,
    ]);
    session_start();
}

// --- output --------------------------------------------------
function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

function redirect(string $path, int $code = 302): never
{
    header('Location: ' . (str_starts_with($path, 'http') ? $path : url($path)), true, $code);
    exit;
}

/** ₱1,550 — decimals only when the amount actually has them. */
function money(int|float|string|null $amount, bool $withSymbol = true): string
{
    $n = (float)$amount;
    $decimals = (floor($n) === $n) ? 0 : 2;
    return ($withSymbol ? CURRENCY : '') . number_format($n, $decimals);
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function post(string $key, string $default = ''): string
{
    return trim((string)($_POST[$key] ?? $default));
}

function get(string $key, string $default = ''): string
{
    return trim((string)($_GET[$key] ?? $default));
}

// --- flash messages ------------------------------------------
function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function take_flashes(): array
{
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}

// --- small form helpers --------------------------------------
function checked(bool $condition): string
{
    return $condition ? 'checked' : '';
}

function selected(mixed $a, mixed $b): string
{
    return (string)$a === (string)$b ? 'selected' : '';
}

function format_date(?string $date, string $format = 'M j, Y'): string
{
    if (!$date) {
        return '';
    }
    $ts = strtotime($date);
    return $ts ? date($format, $ts) : '';
}

function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
    return trim($text, '-') ?: 'room';
}

function drive_video_id(string $url): ?string
{
    $parts = parse_url(trim($url));
    if (!$parts || strtolower((string)($parts['scheme'] ?? '')) !== 'https'
        || !in_array(strtolower((string)($parts['host'] ?? '')), ['drive.google.com', 'www.drive.google.com'], true)) {
        return null;
    }
    $path = (string)($parts['path'] ?? '');
    if (!preg_match('~^/(?:file/d/|open$|uc$)~', $path)) return null;
    if (preg_match('~^/file/d/([\w-]+)(?:/|$)~', $path, $m)) return $m[1];
    parse_str((string)($parts['query'] ?? ''), $query);
    return isset($query['id']) && preg_match('/^[\w-]+$/', (string)$query['id']) ? (string)$query['id'] : null;
}

/** Convert a Google Drive share/view URL to a direct thumbnail URL for images. */
function drive_image_url(string $url): string
{
    $id = drive_video_id($url);
    return $id ? 'https://drive.google.com/thumbnail?id=' . $id . '&sz=w1200' : $url;
}

/**
 * Split a CMS string of rows into pairs.
 * "Message us|Reply the same day;Send ₱500|Refundable" → [[...], [...]]
 */
function parse_pairs(string $value, string $rowSeparator = ';', string $columnSeparator = '|'): array
{
    $rows = [];
    foreach (explode($rowSeparator, $value) as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }
        $parts  = array_map('trim', explode($columnSeparator, $line, 2));
        $rows[] = [$parts[0], $parts[1] ?? ''];
    }
    return $rows;
}

/** Two lines of hero type, owner-editable. */
function lines(string $value, int $limit = 2): array
{
    $lines = preg_split('/\r\n|\r|\n/', $value) ?: [];
    $lines = array_values(array_filter(array_map('trim', $lines), 'strlen'));
    return array_slice($lines, 0, $limit);
}

// --- last-resort error page -----------------------------------
set_exception_handler(static function (Throwable $error): void {
    error_log(sprintf(
        '[%s] %s in %s:%d',
        $error::class,
        $error->getMessage(),
        $error->getFile(),
        $error->getLine()
    ));

    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=utf-8');
    }

    if (APP_DEBUG) {
        echo '<pre style="padding:2rem;font:14px/1.5 monospace;white-space:pre-wrap">'
            . e((string)$error)
            . '</pre>';
        return;
    }

    echo '<!doctype html><html lang="en"><head><meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<title>Something went wrong</title></head>'
        . '<body style="font:16px/1.6 system-ui,sans-serif;margin:0;padding:12vh 8vw;background:#eae7df;color:#191917">'
        . '<h1 style="font-size:2rem">Something went wrong</h1>'
        . '<p>Please try again in a moment, or message us on Facebook Messenger.</p>'
        . '</body></html>';
});

// Shared inline SVG icon set and brand lockups (public site + CMS).
require_once __DIR__ . '/../includes/icons.php';
