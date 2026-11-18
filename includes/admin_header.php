<?php
require_once __DIR__ . '/icons.php';

if (!headers_sent()) {
    header('X-Robots-Tag: noindex, nofollow', true);
}

$admin = admin_user();
$brand = setting('brand_name', APP_NAME);
$active = $active ?? '';
$nav = [
    'index.php'     => ['Dashboard', 'layout-dashboard'],
    'rooms.php'     => ['Rooms', 'bed'],
    'amenities.php' => ['Inclusions', 'check-circle'],
    'rates.php'     => ['Rates', 'calendar'],
    'addons.php'    => ['Add-ons', 'car'],
    'reviews.php'   => ['Reviews', 'users'],
    'faqs.php'      => ['FAQs', 'mail'],
    'calendar.php'  => ['Availability', 'calendar'],
    'settings.php'  => ['Settings', 'shield'],
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($page_title ?? 'Admin') ?> | <?= e($brand) ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= e(asset('img/favicon.svg')) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Archivo:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,700;1,800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
    <script src="<?= e(asset('js/site.js')) ?>" defer></script>
</head>

<body class="admin">

    <!-- mobile top bar (hidden on desktop) -->
    <header class="admin-topbar">
        <a class="admin-brand" href="<?= e(url('admin/index.php')) ?>">
            <?= logo_mark('admin-mark', 28) ?>
            <span class="admin-brand-name"><?= e($brand) ?></span>
        </a>
        <button class="admin-burger" id="admin-burger" aria-label="Open menu" aria-expanded="false" aria-controls="admin-side">
            <span></span><span></span><span></span>
        </button>
    </header>

    <!-- overlay -->
    <div class="admin-overlay" id="admin-overlay" hidden></div>

    <div class="admin-shell">
        <aside class="admin-side" id="admin-side">
            <!-- desktop logo -->
            <a class="admin-brand admin-brand--desk" href="<?= e(url('admin/index.php')) ?>">
                <?= logo_mark('admin-mark', 34) ?>
                <span class="admin-brand-text">
                    <span class="admin-brand-name"><?= e($brand) ?></span>
                    <span class="admin-brand-tag">Short-stay studios</span>
                </span>
            </a>
            <!-- mobile drawer header -->
            <div class="admin-side-top">
                <a class="admin-brand" href="<?= e(url('admin/index.php')) ?>">
                    <?= logo_mark('admin-mark', 34) ?>
                    <span class="admin-brand-text">
                        <span class="admin-brand-name"><?= e($brand) ?></span>
                        <span class="admin-brand-tag">Short-stay studios</span>
                    </span>
                </a>
                <button class="admin-side-close" id="admin-side-close" aria-label="Close menu">✕</button>
            </div>
            <nav class="admin-nav">
                <?php foreach ($nav as $file => [$label, $navIcon]): ?>
                    <a href="<?= e(url('admin/' . $file)) ?>" class="<?= $active === $file ? 'is-active' : '' ?>">
                        <?= icon($navIcon) ?>
                        <span><?= e($label) ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="admin-side-foot">
                <a href="<?= e(url('index.php')) ?>" target="_blank" rel="noopener">View site ↗</a>
                <button type="button" id="admin-logout-trigger">Sign out</button>
            </div>
        </aside>

        <div class="admin-main">
            <header class="admin-top">
                <div>
                    <h1><?= e($page_title ?? 'Dashboard') ?></h1>
                </div>
                <?php if (!empty($top_action)): ?>
                    <div class="admin-top-action"><?= $top_action ?></div>
                <?php endif; ?>
            </header>

            <div class="admin-content">
                <?php foreach (take_flashes() as $flash): ?>
                    <div class="notice notice-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
                <?php endforeach; ?>

                <?php if (!empty($top_action)): ?>
                    <div class="admin-fab"><?= $top_action ?></div>
                <?php endif; ?>