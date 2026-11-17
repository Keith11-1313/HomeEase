<?php
require_once __DIR__ . '/../lib/bootstrap.php';
require_once __DIR__ . '/icons.php';

$brand = setting('brand_name', APP_NAME);
$page_title = $page_title ?? $brand;
$page_desc = $page_desc ?? setting('tagline');
$active = $active ?? '';
$messenger = setting('messenger_url', 'https://m.me/');

$nav = [
    'rooms' => ['Rooms', 'rooms.php'],
    'gallery' => ['Gallery', 'gallery.php'],
    'faqs' => ['FAQs', 'faq.php'],
    'availability' => ['Availability', 'availability.php'],
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($page_title === $brand ? $brand : $page_title . ' | ' . $brand) ?></title>
    <meta name="description" content="<?= e($page_desc) ?>">
    <meta name="theme-color" content="#eae7df">
    <link rel="icon" type="image/svg+xml" href="<?= e(asset('img/favicon.svg')) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Archivo:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,700;1,800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('css/site.css')) ?>">
    <script src="<?= e(asset('js/site.js')) ?>" defer></script>
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