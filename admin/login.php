<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/bootstrap.php';
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../includes/icons.php';

if (!headers_sent()) {
    header('X-Robots-Tag: noindex, nofollow', true);
}

if (admin_user() !== null) {
    redirect('admin/index.php');
}

$error = '';

if (is_post()) {
    csrf_verify();

    $email    = post('email');
    $password = (string)($_POST['password'] ?? '');

    if (login_is_throttled($email)) {
        http_response_code(429);
        $error = 'Too many attempts. Try again in 15 minutes.';
    } elseif ($email === '' || $password === '') {
        $error = 'Enter your email and password.';
    } elseif (attempt_login($email, $password)) {
        redirect('admin/index.php');
    } else {
        $error = 'Invalid credentials.';
    }
}

$brand = setting('brand_name', APP_NAME);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Sign in — <?= e($brand) ?></title>
    <meta name="theme-color" content="#eae7df">
    <link rel="icon" type="image/svg+xml" href="<?= e(asset('img/favicon.svg')) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,700;1,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
    <script src="<?= e(asset('js/site.js')) ?>" defer></script>
</head>
<body class="admin">
<div class="login-wrap">
    <div class="login-card">
        <a class="login-brand" href="<?= e(url('index.php')) ?>">
            <?= logo_mark('login-mark', 56) ?>
            <span class="login-wordmark"><?= e($brand) ?></span>
            <span class="login-tag">Short-stay studios</span>
        </a>

        <h1><?= icon('key') ?> Owner sign in</h1>

        <?php foreach (take_flashes() as $flash): ?>
            <div class="notice notice-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endforeach; ?>

        <?php if ($error): ?>
            <div class="notice notice-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form id="login-form" method="post" action="<?= e(url('admin/login.php')) ?>">
            <?= csrf_field() ?>

            <label class="field">
                <span>Email</span>
                <input type="email" name="email" value="<?= e(post('email')) ?>" autocomplete="username" required autofocus>
            </label>

            <label class="field">
                <span>Password</span>
                <input type="password" name="password" autocomplete="current-password" required>
            </label>

            <button class="btn" type="submit" style="width:100%"><?= icon('arrow-right') ?> Sign in</button>
        </form>

        <p class="login-foot"><a href="<?= e(url('index.php')) ?>"><?= icon('arrow-right', 'icon--back') ?> Back to the site</a></p>
    </div>
</div>
</body>
</html>
