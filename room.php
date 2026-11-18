<?php
require_once __DIR__ . '/lib/bootstrap.php';

$slug = get('slug');
$unit = $slug !== '' ? unit_by_slug($slug) : null;

if (!$unit || !$unit['is_published']) {
    http_response_code(404);
    $page_title = 'Room not found';
    include __DIR__ . '/includes/public_header.php';
    ?>
    <section class="wrap">
        <div class="empty-state">
            <span class="empty-state-label" aria-hidden="true">404</span>
            <h2>That room isn't listed</h2>
            <p>It may have been renamed or taken off the site.</p>
            <a class="btn" href="<?= e(url('rooms.php')) ?>">See all rooms</a>
        </div>
    </section>
    <?php
    include __DIR__ . '/includes/public_footer.php';
    exit;
}

$photos      = unit_photos((int)$unit['id']);
$images      = array_values(array_filter($photos, static fn(array $media): bool => ($media['media_type'] ?? 'image') === 'image'));
$videos      = array_values(array_filter($photos, static fn(array $media): bool => ($media['media_type'] ?? 'image') === 'video'));
$inclusions  = unit_amenities((int)$unit['id']);
$rates       = unit_rates((int)$unit['id']);
$poolMap     = pool_rates((int)$unit['id']);
$deposits    = deposit_rules_all();
$includedPax = max(1, (int)setting('included_pax', '2'));

$rateTable = [];
foreach ($rates as $rate) {
    $rateTable[$rate['slot_type']][(int)$rate['hours']][$rate['day_type']] = (float)$rate['amount'];
}

$parkingAddons = array_values(array_filter(
    addons_all(true),
    static fn(array $addon): bool => $addon['category'] === 'parking'
));

$slotOrder = ['overnight', 'daycation', 'flexible'];
$slots     = array_keys($rateTable);
usort($slots, static fn($a, $b) => array_search($a, $slotOrder, true) <=> array_search($b, $slotOrder, true));

$facts = unit_fact_items($unit);

$page_title = $unit['name'] . ($unit['tagline'] ? ' | ' . $unit['tagline'] : '');
$page_desc  = $unit['description'] ?: setting('tagline');
$active     = 'rooms';

$room_schema = [
    '@context' => 'https://schema.org',
    '@type'    => 'HotelRoom',
    'name'     => $unit['name'],
    'description' => $unit['description'] ?: $unit['tagline'],
    'occupancy' => [
        '@type'    => 'QuantitativeValue',
        'maxValue' => (int)($unit['capacity_max'] ?? 4),
    ],
    'bed' => $unit['bed_type'] ? $unit['bed_type'] . ' bed' : 'Bed',
];
if ($unit['size_sqm']) {
    $room_schema['floorSize'] = [
        '@type' => 'QuantitativeValue',
        'value' => (float)$unit['size_sqm'],
        'unitCode' => 'MTK',
    ];
}
if (!empty($images[0]['url'])) {
    $room_schema['image'] = drive_image_url($images[0]['url']);
}
$extra_head = '<script type="application/ld+json">' . json_encode(array_filter($room_schema), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';

include __DIR__ . '/includes/public_header.php';
?>

<nav class="crumbs wrap" aria-label="Breadcrumb">
    <a href="<?= e(url('rooms.php')) ?>"><?= icon('arrow-right', 'icon--back') ?> Rooms</a>
    <span aria-hidden="true">/</span>
    <span><?= e($unit['name']) ?></span>
</nav>

<section class="room-head wrap reveal">
    <div>
        <h1><?= e($unit['name']) ?></h1>
        <p class="room-tagline"><?= icon('bed') ?> <?= e($unit['tagline']) ?></p>
        <p class="lead room-summary">A furnished studio in Fairview, Quezon City, kept for short overnight and daycation stays.</p>
        <p class="room-facts">
            <?php foreach ($facts as $fact): ?>
                <span><?= icon($fact['icon']) ?><?= e($fact['text']) ?></span>
            <?php endforeach; ?>
        </p>
        <a class="btn" href="<?= e(setting('messenger_url', 'https://m.me/')) ?>" target="_blank" rel="noopener"><?= icon('messenger') ?> Reserve on Messenger</a>
    </div>

    <div class="room-head-price">
        <span>Stays from</span>
        <strong><?= e($unit['from_price'] !== null ? money($unit['from_price']) : 'Ask us') ?></strong>
        <span>per <?= e((string)($unit['max_hours'] ?: 12)) ?>h · weekday overnight, 2 pax</span>
    </div>
</section>

<section class="room-media wrap reveal">
    <?php if ($images): ?>
        <div class="room-photo-strip">
            <?php foreach ($images as $i => $photo): ?>
                <figure class="room-photo-item">
                    <img src="<?= e(drive_image_url($photo['url'])) ?>"
                         alt="<?= e($photo['caption'] ?: $unit['name'] . ' photo ' . ($i + 1)) ?>"
                         loading="<?= $i === 0 ? 'eager' : 'lazy' ?>">
                    <?php if ($photo['caption']): ?>
                        <figcaption><?= e($photo['caption']) ?></figcaption>
                    <?php endif; ?>
                </figure>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php foreach ($videos as $video): ?>
        <figure class="room-video">
            <iframe src="<?= e($video['url']) ?>" title="<?= e($video['caption'] ?: $unit['name'] . ' video') ?>" loading="lazy" allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>
            <?php if ($video['caption']): ?><figcaption><?= e($video['caption']) ?></figcaption><?php endif; ?>
        </figure>
    <?php endforeach; ?>
</section>

<div class="room-body wrap">
    <div class="room-main">
        <?php if ($unit['description']): ?>
            <section class="reveal">
                <h2><?= icon('lamp') ?> About the room</h2>
                <p class="prose"><?= nl2br(e($unit['description'])) ?></p>
            </section>
        <?php endif; ?>

        <?php if ($inclusions): ?>
            <section class="reveal">
                <h2><?= icon('check-circle') ?> What's included</h2>
                <div class="inclusion-groups">
                    <?php foreach ($inclusions as $category => $items): ?>
                        <div class="inclusion-group">
                            <h3><?= e($category) ?></h3>
                            <ul class="inclusion-list">
                                <?php foreach ($items as $item): ?>
                                    <li>
                                        <?= icon(amenity_icon($item['name'])) ?>
                                        <span>
                                            <?= e($item['name']) ?>
                                            <?php if (!empty($item['note'])): ?><em><?= e($item['note']) ?></em><?php endif; ?>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($rateTable): ?>
            <section class="reveal">
                <h2><?= icon('calendar') ?> Rates</h2>
                <div class="table-scroll">
                    <table class="rate-table">
                        <thead>
                            <tr>
                                <th>Stay</th>
                                <th>Hours</th>
                                <th>Weekday</th>
                                <th>Weekend / holiday</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($slots as $slot): ?>
                                <?php foreach ($rateTable[$slot] as $hours => $amounts): ?>
                                    <tr>
                                        <td><?= e(ucfirst($slot === 'flexible' ? 'flexible stay' : $slot)) ?></td>
                                        <td><?= e((string)$hours) ?></td>
                                        <td><?= e(isset($amounts['weekday']) ? money($amounts['weekday']) : '—') ?></td>
                                        <td><?= e(isset($amounts['weekend_holiday']) ? money($amounts['weekend_holiday']) : '—') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p class="table-note">Rates cover <?= e((string)$includedPax) ?> pax. Additional hours, pax, parking and pool access are added in the calculator.</p>
            </section>
        <?php endif; ?>

        <?php if ($deposits): ?>
            <section class="reveal">
                <h2><?= icon('shield') ?> Security deposit</h2>
                <p class="prose">A separate refundable security deposit is collected before check-in and returned after check-out, provided there are no damages, missing items or rule violations.</p>
                <ul class="deposit-list">
                    <?php foreach ($deposits as $deposit): ?>
                        <li>
                            <span><?= e($deposit['label'] ?: 'From ' . $deposit['min_hours'] . ' hours') ?></span>
                            <strong><?= e(money($deposit['amount'])) ?></strong>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>
        <?php endif; ?>

        <section class="reveal">
            <h2><?= icon('shield') ?> House rules summary</h2>
            <p class="prose">Quiet hours are strictly observed (<?= e(policy_house_rules()['quiet_hours']) ?>). No smoking, no parties, no unregistered visitors, and no pets. Valid IDs are required by building security for entry passes.</p>
            <p style="margin-top:0.75rem;">
                <a class="text-link" href="<?= e(url('house-rules.php')) ?>">Read all house rules <?= icon('arrow-right') ?></a>
            </p>
        </section>
    </div>

    <aside class="room-aside reveal">
        <div class="calc">
            <h2><?= icon('calendar') ?> Price my stay</h2>
            <p class="calc-note">An estimate only — availability is confirmed in chat.</p>

            <?php if (!$rateTable): ?>
                <p class="muted" style="margin:0">Rates are being updated. <a class="text-link" href="<?= e(setting('messenger_url','https://m.me/')) ?>" target="_blank" rel="noopener">Message us for a quote.</a></p>
            <?php else: ?>
                <form id="quote-form" action="<?= e(url('calculator.php')) ?>" method="post">
                    <input type="hidden" name="unit_id" value="<?= (int)$unit['id'] ?>">

                    <label class="field">
                        <span>Date</span>
                        <input type="date" name="date" value="<?= e(date('Y-m-d')) ?>" min="<?= e(date('Y-m-d')) ?>" required>
                    </label>

                    <div class="field-row">
                        <label class="field">
                            <span>Slot</span>
                            <select name="slot">
                                <?php foreach ($slots as $slot): ?>
                                    <option value="<?= e($slot) ?>"><?= e($slot === 'flexible' ? 'Flexible' : ucfirst($slot)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>

                        <label class="field">
                            <span>Hours</span>
                            <input type="number" name="hours" value="<?= (int)($unit['max_hours'] ?: 12) ?>" min="12" max="72" step="1">
                        </label>
                    </div>

                    <div class="field-row">
                        <label class="field">
                            <span>Pax</span>
                            <input type="number" name="pax" value="<?= (int)$includedPax ?>" min="1" max="10" step="1">
                        </label>

                        <label class="field">
                            <span>Parking</span>
                            <select name="parking">
                                <option value="none">None</option>
                                <?php foreach ($parkingAddons as $addon): ?>
                                    <option value="<?= e($addon['code']) ?>"><?= e($addon['label'] . ' · ' . money($addon['amount'])) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                    </div>

                    <?php if ($poolMap): ?>
                        <label class="check">
                            <input type="checkbox" name="pool" value="1">
                            <span>Add pool access</span>
                        </label>
                    <?php endif; ?>

                    <button class="btn btn--block" type="submit">Calculate estimate</button>
                </form>

                <div id="quote-result" class="quote-result" aria-live="polite" hidden></div>
            <?php endif; ?>
        </div>

        <div class="aside-note reveal">
            <h3>Reservations happen in chat</h3>
            <p><?= e(setting('contact_note')) ?> A <?= e(BOOKING_FEE_AMOUNT) ?> booking fee secures the slot; the remaining stay balance and refundable security deposit are settled on check-in.</p>
            <a class="text-link" href="<?= e(setting('messenger_url', 'https://m.me/')) ?>" target="_blank" rel="noopener">Open Messenger</a>
        </div>

        <?php if (setting('promo_note')): ?>
            <p class="promo-note"><?= e(setting('promo_note')) ?></p>
        <?php endif; ?>
    </aside>
</div>

<?php include __DIR__ . '/includes/public_footer.php'; ?>
