<?php
require_once __DIR__ . '/../lib/bootstrap.php';
require_once __DIR__ . '/icons.php';

$brand = setting('brand_name', APP_NAME);
$page_title = $page_title ?? $brand;
$page_desc = $page_desc ?? setting('tagline');
$active = $active ?? '';
$messenger = setting('messenger_url', 'https://m.me/');

$nav = [
    'rooms'       => ['Rooms', 'rooms.php'],
    'gallery'     => ['Gallery', 'gallery.php'],
    'faqs'        => ['FAQs', 'faq.php'],
    'availability'=> ['Availability', 'availability.php'],
    'house-rules' => ['House rules', 'house-rules.php'],
];

$page_full_title = $page_title === $brand ? $brand : $page_title . ' | ' . $brand;
$current_canonical = $canonical_url ?? (BASE_URL !== '' ? BASE_URL . '/' . ltrim($_SERVER['REQUEST_URI'] ?? '', '/') : '');
$og_image_url = $og_image ?? (BASE_URL !== '' ? BASE_URL . '/assets/img/og-cover.jpg' : '');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($page_full_title) ?></title>
    <meta name="description" content="<?= e($page_desc) ?>">
    <meta name="theme-color" content="#eae7df">
    <?php if ($current_canonical): ?>
        <link rel="canonical" href="<?= e($current_canonical) ?>">
    <?php endif; ?>

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="<?= e($og_type ?? 'website') ?>">
    <meta property="og:title" content="<?= e($page_full_title) ?>">
    <meta property="og:description" content="<?= e($page_desc) ?>">
    <meta property="og:image" content="<?= e($og_image_url) ?>">
    <?php if ($current_canonical): ?>
        <meta property="og:url" content="<?= e($current_canonical) ?>">
    <?php endif; ?>

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($page_full_title) ?>">
    <meta name="twitter:description" content="<?= e($page_desc) ?>">
    <meta name="twitter:image" content="<?= e($og_image_url) ?>">

    <link rel="icon" type="image/svg+xml" href="<?= e(asset('img/favicon.svg')) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Archivo:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,700;1,800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('css/site.css')) ?>">
    <script src="<?= e(asset('js/site.js')) ?>" defer></script>
    <?php if (!empty($extra_head)): ?>
        <?= $extra_head ?>
    <?php endif; ?>
</head>

<body>

    <header class="site-header" id="site-header">
        <a class="brand-link" href="<?= e(url('index.php')) ?>" aria-label="<?= e($brand) ?> — home">
            <?= logo_lockup() ?>
        </a>

        <div class="header-right">
            <nav class="site-nav" id="site-nav" aria-label="Main">
                <?php foreach ($nav as $key => [$label, $href]): ?>
                    <a href="<?= e(url($href)) ?>" <?= $active === $key ? 'aria-current="page"' : '' ?>><?= e($label) ?></a>
                <?php endforeach; ?>
            </nav>

            <div class="site-actions">
                <a class="btn" href="<?= e($messenger) ?>" target="_blank" rel="noopener"><?= icon('messenger') ?>
                    Message us</a>
                <button class="nav-toggle" id="nav-toggle" aria-expanded="false" aria-controls="site-nav"
                    aria-label="Open menu">
                    <?= icon('menu') ?>
                </button>
            </div>
        </div>
    </header>

    <main>