<?php
declare(strict_types=1);

/**
 * Create (or reset the password of) an admin account.
 *
 *   ADMIN_PASSWORD="strong-password" php scripts/create_admin.php owner@example.com "Owner Name"
 */

require_once __DIR__ . '/../lib/bootstrap.php';

$email    = $argv[1] ?? '';
$name     = $argv[2] ?? 'Owner';
$password = (string)(getenv('ADMIN_PASSWORD') ?: '');

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Usage: php scripts/create_admin.php <email> [display name] [password]\n");
    exit(1);
}

if (strlen($password) < 8) {
    fwrite(STDERR, "Set ADMIN_PASSWORD to a value of at least 8 characters.\n");
    exit(1);
}

$email = strtolower($email);
$hash  = password_hash($password, PASSWORD_DEFAULT);

$stmt = db()->prepare(
    'INSERT INTO admin_users (email, password_hash, display_name)
     VALUES (:email, :hash, :name)
     ON CONFLICT (email) DO UPDATE
       SET password_hash = EXCLUDED.password_hash,
           display_name  = EXCLUDED.display_name,
           is_active     = TRUE
     RETURNING id'
);
$stmt->execute(['email' => $email, 'hash' => $hash, 'name' => $name]);

echo "Admin ready: {$email} (id {$stmt->fetchColumn()})\n";
