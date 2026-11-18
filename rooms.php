<?php
require_once __DIR__ . '/lib/bootstrap.php';

$units     = units_published();
$fees      = standard_fees();
$messenger = setting('messenger_url', 'https://m.me/');

$page_title = 'Rooms';
$page_desc  = setting('rooms_intro');
$active     = 'rooms';
include __DIR__ . '/includes/public_header.php';
?>

<section class="page-head wrap">
    <p class="page-kicker"><?= icon('building') ?> The rooms</p>
    <h1><?= count($units) ?> cozy studios in Fairview</h1>
    <p class="lead"><?= e(setting('rooms_intro')) ?></p>
</section>

<!-- ── Transparent Standard Fee Table ────────────────────────── -->
<section class="wrap" style="margin-bottom:2rem;">
    <div style="background:var(--paper);border:1px solid var(--line);border-radius:var(--radius);padding:1.25rem 1.5rem;">
        <h2 style="font-size:1rem;margin-bottom:0.75rem;display:flex;align-items:center;gap:0.5rem;">
            <?= icon('shield') ?> Transparent fee schedule
        </h2>
        <div style="display:grid;gap:1rem;grid-template-columns:repeat(auto-fit, minmax(160px, 1fr));font-size:0.9rem;">
            <div>
                <span class="text-muted-sm" style="display:block;">Weekend / Holiday</span>
                <strong><?= e($fees['weekend_diff']) ?></strong>
            </div>
            <div>
                <span class="text-muted-sm" style="display:block;">Extra guest</span>
                <strong><?= e($fees['extra_pax']) ?></strong>
            </div>
            <div>
                <span class="text-muted-sm" style="display:block;">Extra hour</span>
                <strong><?= e($fees['extra_hour']) ?></strong>
            </div>
            <div>
                <span class="text-muted-sm" style="display:block;">Parking</span>
                <strong><?= e($fees['parking']) ?></strong>
            </div>
            <div>
                <span class="text-muted-sm" style="display:block;">Pool access</span>
                <strong><?= e($fees['pool']) ?></strong>
            </div>
        </div>
        <p class="text-muted-sm" style="margin-top:0.75rem;margin-bottom:0;">Base rates include 2 guests. Stated "from" rates reflect 12-hour weekday overnight stays.</p>
    </div>
</section>

<section class="room-list">
    <div class="wrap">
        <?php if ($units): ?>
            <?php foreach ($units as $unit): ?>
                <?php $href = url('room.php?slug=' . urlencode($unit['slug'])); ?>
                <article class="room-entry">
                    <figure class="room-entry-media">
                        <?php if (!empty($unit['cover_url'])): ?>
                            <a href="<?= e($href) ?>">
                                <img src="<?= e(drive_image_url($unit['cover_url'])) ?>"
                                     alt="<?= e($unit['name'] . ' — ' . $unit['tagline']) ?>"
                                     loading="lazy">
                            </a>
                        <?php else: ?>
                        <?php endif; ?>
                    </figure>

                    <div class="room-entry-body">
                        <h2><a href="<?= e($href) ?>"><?= e($unit['name']) ?></a></h2>
                        <p class="room-facts">
                            <?php foreach (unit_fact_items($unit) as $fact): ?>
                                <span><?= icon($fact['icon']) ?><?= e($fact['text']) ?></span>
                            <?php endforeach; ?>
                        </p>

                        <p class="room-entry-price">
                            <strong><?= e($unit['from_price'] !== null ? money($unit['from_price']) : 'Ask us') ?></strong>
                            <span>from, per 12 hours (weekday overnight, 2 pax)</span>
                        </p>

                        <a class="text-link" href="<?= e($href) ?>">View room and rates <?= icon('arrow-right') ?></a>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <span class="empty-state-label" aria-hidden="true">—</span>
                <h2>No rooms listed yet</h2>
                <p>Studios are being prepared. Message us to find out what's available this week.</p>
                <a class="btn" href="<?= e($messenger) ?>" target="_blank" rel="noopener"><?= icon('messenger') ?> Ask on Messenger</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="chapter chapter--field" data-field="dark">
    <div class="wrap cta-foot">
        <h2 style="max-width:18ch">Not sure which one fits?</h2>
        <a class="btn btn--paper" href="<?= e($messenger) ?>" target="_blank" rel="noopener"><?= icon('messenger') ?> Ask on Messenger</a>
    </div>
</section>

<?php include __DIR__ . '/includes/public_footer.php'; ?>
