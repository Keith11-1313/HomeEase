<?php
declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';

$page_title = 'Availability';
$page_desc = setting('calendar_intro', 'Check unavailable dates and current announcements.');
$blocks = db()->query('SELECT block_date, slot, note FROM availability_blocks ORDER BY block_date')->fetchAll();
$announcements = db()->query('SELECT * FROM announcements WHERE is_published ORDER BY created_at DESC')->fetchAll();
$calendarData = [
    'blocks' => $blocks,
    'ranges' => array_map(static fn (array $a): array => ['start' => $a['start_date'], 'end' => $a['end_date'], 'title' => $a['title']], $announcements),
];
include __DIR__ . '/includes/public_header.php';
?>

<main class="availability-page wrap">
    <p class="page-kicker"><?= icon('calendar') ?> Availability</p>
    <h1>Check availability</h1>
    <p class="lead"><?= e($page_desc) ?> This calendar is a guide, not a live reservation system. Message us to confirm.</p>

    <section class="availability-calendar" aria-labelledby="calendar-heading" data-calendar="<?= e(json_encode($calendarData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)) ?>">
        <h2 id="calendar-heading">Unavailable dates</h2>
        <div class="avail-months" data-calendar-months aria-live="polite"></div>
        <div class="avail-slot-legend">
            <span class="avail-legend-item"><span class="avail-swatch avail-swatch--overnight"></span> Overnight (7pm–7am) booked</span>
            <span class="avail-legend-item"><span class="avail-swatch avail-swatch--daycation"></span> Daycation (7am–7pm) booked</span>
            <span class="avail-legend-item"><span class="avail-swatch avail-swatch--full"></span> Fully booked</span>
            <span class="avail-legend-item"><span class="avail-swatch avail-swatch--announced"></span> Announced period</span>
        </div>
    </section>

    <?php if ($announcements): ?>
        <section class="availability-announcements" aria-labelledby="announcements-heading">
            <h2 id="announcements-heading"><?= icon('mail') ?> Announcements</h2>
            <?php foreach ($announcements as $announcement): ?>
                <article class="avail-announcement">
                    <h3><?= e($announcement['title']) ?></h3>
                    <?php if ($announcement['body'] !== ''): ?><p><?= nl2br(e($announcement['body'])) ?></p><?php endif; ?>
                    <?php if ($announcement['start_date'] || $announcement['end_date']): ?><p class="avail-announcement-dates"><?= e($announcement['start_date'] ?? 'Now') ?><?= $announcement['end_date'] ? ' — ' . e($announcement['end_date']) : '' ?></p><?php endif; ?>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>

    <p class="availability-contact">To confirm, message us <a class="btn btn--accent" href="<?= e(setting('messenger_url', 'https://m.me/')) ?>" target="_blank" rel="noopener"><?= icon('messenger') ?> Message us on Messenger</a></p>
</main>

<script src="<?= e(asset('js/availability.js')) ?>" defer></script>
<?php include __DIR__ . '/includes/public_footer.php'; ?>
