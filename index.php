<?php
require_once __DIR__ . '/lib/bootstrap.php';

$units    = units_published();
$faqs     = array_slice(faqs_published(), 0, 5);
$steps    = parse_pairs(setting('booking_steps'));
$result   = parse_pairs(setting('booking_result'))[0] ?? ['You hold the slot', '₱500 only'];
$hero     = lines(setting('hero_title'));
$cta      = lines(setting('cta_title'));
$from     = price_from();
$messenger = setting('messenger_url', 'https://m.me/');
$nbPhotos  = random_gallery_images(4);

$page_title = setting('brand_name', APP_NAME);
$page_desc  = setting('hero_support');
include __DIR__ . '/includes/public_header.php';
?>

<section class="hero">
    <div class="wrap hero-inner">
        <p class="hero-kicker"><?= icon('pin') ?> Fairview, Quezon City <span aria-hidden="true">·</span> 12-hour stays</p>

        <h1 class="hero-logo">
            <span class="hero-word"><?= e($hero[0] ?? "Flynn's Cozy") ?></span>
            <span class="hero-emblem"><?= logo_mark() ?></span>
            <span class="hero-word hero-word--last"><?= e($hero[1] ?? 'Staycation.') ?></span>
        </h1>

        <p class="hero-tagline">Airbnb / Transient</p>

        <div class="hero-foot">
            <p class="hero-support"><?= e(setting('hero_support')) ?></p>
            <div class="hero-actions">
                <a class="btn" href="<?= e($messenger) ?>" target="_blank" rel="noopener"><?= icon('messenger') ?> Message us</a>
                <a class="btn btn--ghost" href="<?= e(url('rooms.php')) ?>"><?= icon('arrow-right') ?> See the rooms</a>
            </div>
        </div>
    </div>

    <div class="hero-rail" aria-label="Stay essentials">
        <div class="wrap hero-rail-inner">
            <span><?= icon('moon') ?> Overnight · 7pm–7am</span>
            <span><?= icon('sun') ?> Daycation · 7am–7pm</span>
            <span><?= icon('users') ?> 2 pax included</span>
            <span><?= icon('car') ?> Parking on request</span>
        </div>
    </div>
</section>

<section class="chapter showcase" id="stays">
    <div class="wrap">
        <div class="chapter-head reveal">
                <h2>Stay your way, day or night</h2>
            <p>Every stay is a twelve-hour slot. Take the night from 7pm to 7am, or the day from 7am to 7pm. Rates change with the day and the room; both slots include two pax, air conditioning and the run of the studio.</p>
        </div>

        <div class="specimens">
            <figure class="specimen specimen--night reveal">
                <?= icon('moon') ?>
                <b>Overnight</b>
                <figcaption>7pm to 7am · settle in after dark</figcaption>
            </figure>

            <figure class="specimen specimen--day reveal" style="--d:90ms">
                <?= icon('sun') ?>
                <b>Daycation</b>
                <figcaption>7am to 7pm · the studio for a day</figcaption>
            </figure>
        </div>
    </div>
</section>

<section class="chapter chapter--surface" id="rooms">
    <div class="wrap">
        <div class="chapter-head reveal">
                <h2><?= count($units) ?> cozy studios in Fairview</h2>
            <p class="lead"><?= e(setting('rooms_intro')) ?></p>
        </div>

        <?php if ($units): ?>
            <div class="room-index">
                <?php foreach ($units as $unit): ?>
                    <a class="reveal" href="<?= e(url('room.php?slug=' . urlencode($unit['slug']))) ?>">
                        <h3><?= e($unit['name']) ?><span class="sr-only">, <?= e($unit['tagline']) ?></span></h3>
                        <span class="room-facts">
                            <?php foreach (unit_fact_items($unit) as $fact): ?>
                                <span><?= icon($fact['icon']) ?><?= e($fact['text']) ?></span>
                            <?php endforeach; ?>
                        </span>
                        <span class="room-from">
                            from <?= e($unit['from_price'] !== null ? money($unit['from_price']) : 'ask') ?>
                            <span>per 12 hours</span>
                        </span>
                        <span class="room-arrow" aria-hidden="true"><?= icon('arrow-up') ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <span class="empty-state-label" aria-hidden="true">—</span>
                <h2>Studios coming soon</h2>
                <p>Message us to find out what's available this week.</p>
                <a class="btn" href="<?= e($messenger) ?>" target="_blank" rel="noopener"><?= icon('messenger') ?> Message us</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="chapter chapter--field" id="how" data-field="dark">
    <div class="wrap steps-grid">
        <div class="chapter-head reveal">
            <h2>Three messages, then you're in.</h2>
            <p class="lead"><?= e(setting('contact_note')) ?> No forms, no accounts, no card details on this site.</p>
        </div>

        <div class="slip reveal" style="--d:90ms" data-field="dark">
            <p class="slip-title">Your reservation</p>

            <?php foreach ($steps as $stepIndex => [$label, $value]): ?>
                <div class="slip-row">
                    <span class="slip-label"><?= icon(['messenger', 'calendar', 'key'][$stepIndex] ?? 'check') ?><?= e($label) ?></span>
                    <span class="slip-dots" aria-hidden="true"></span>
                    <span class="slip-value"><?= e($value) ?></span>
                </div>
            <?php endforeach; ?>

            <div class="slip-total">
                <span><?= e($result[0]) ?></span>
                <strong><?= e($result[1]) ?></strong>
            </div>

            <p class="slip-note">Check-in window <?= e(setting('checkin_window')) ?>. The passcode is sent once the balance is settled.</p>
        </div>
    </div>
</section>

<section class="chapter pricing" id="pricing">
    <div class="wrap">
        <p class="price-sentence reveal">
            Twelve hours<br>
            from <span class="price-mark"><?= e(money($from)) ?></span>.
        </p>

        <div class="pricing-foot reveal" style="--d:90ms">
            <p><?= e(setting('pricing_note')) ?></p>
            <div class="pricing-actions">
                <a class="btn" href="<?= e(url('rooms.php')) ?>"><?= icon('arrow-right') ?> Price a stay</a>
                <a class="text-link" href="<?= e(url('rooms.php')) ?>">See every rate</a>
            </div>
        </div>
    </div>
</section>

<section class="chapter neighbourhood" id="location">
    <div class="wrap">
        <div class="nb-grid">
            <div class="nb-copy reveal">
                <h2>Your Fairview staycation</h2>
                <p class="lead">Two residences a few minutes from SM Fairview and Ayala Terraces, with tricycles and jeepneys at the gate.</p>

                <dl class="nb-facts">
                    <div><dt><?= icon('pin') ?> Address</dt><dd><?= e(setting('address')) ?></dd></div>
                    <div><dt><?= icon('building') ?> Landmarks</dt><dd><?= e(setting('landmarks')) ?></dd></div>
                    <div><dt><?= icon('clock') ?> Check-in</dt><dd><?= e(setting('checkin_window')) ?></dd></div>
                </dl>
            </div>

            <?php if (isset($nbPhotos[0])): ?>
                <figure class="nb-figure reveal" style="--d:80ms">
                    <img src="<?= e(drive_image_url($nbPhotos[0]['url'])) ?>"
                         alt="<?= e($nbPhotos[0]['caption'] ?: $nbPhotos[0]['unit_name']) ?>"
                         loading="lazy">
                </figure>
            <?php else: ?>
                <div class="nb-figure nb-figure--empty reveal" style="--d:80ms">
                    <?= icon('camera') ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="nb-maps reveal">
            <figure class="nb-map">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1622.3379755548124!2d121.06017385415817!3d14.735659685767839!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397b1000bae4fa1%3A0x7de46669f7a288b1!2sTrees%20Residences%20Fairview!5e0!3m2!1sen!2sph!4v1794460481034!5m2!1sen!2sph"
                        loading="lazy" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>
                <figcaption>Trees Residences, Fairview</figcaption>
            </figure>
            <figure class="nb-map">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1622.3379755548124!2d121.06017385415817!3d14.735659685767839!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397b06844bcbdbb%3A0x100f2217aea4e215!2sMilan%20Residenze%20Fairview!5e0!3m2!1sen!2sph!4v1794460529018!5m2!1sen!2sph"
                        loading="lazy" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>
                <figcaption>Milan Residenze, Fairview</figcaption>
            </figure>
        </div>

        <?php if (isset($nbPhotos[1])): ?>
            <figure class="nb-wide reveal">
                <img src="<?= e(drive_image_url($nbPhotos[1]['url'])) ?>"
                     alt="<?= e($nbPhotos[1]['caption'] ?: $nbPhotos[1]['unit_name']) ?>"
                     loading="lazy">
            </figure>
        <?php endif; ?>

        <?php if (isset($nbPhotos[2]) || isset($nbPhotos[3])): ?>
            <div class="nb-pair">
                <?php if (isset($nbPhotos[2])): ?>
                    <figure class="reveal">
                        <img src="<?= e(drive_image_url($nbPhotos[2]['url'])) ?>"
                             alt="<?= e($nbPhotos[2]['caption'] ?: $nbPhotos[2]['unit_name']) ?>"
                             loading="lazy">
                    </figure>
                <?php endif; ?>
                <?php if (isset($nbPhotos[3])): ?>
                    <figure class="reveal" style="--d:90ms">
                        <img src="<?= e(drive_image_url($nbPhotos[3]['url'])) ?>"
                             alt="<?= e($nbPhotos[3]['caption'] ?: $nbPhotos[3]['unit_name']) ?>"
                             loading="lazy">
                    </figure>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php if ($faqs): ?>
<section class="chapter chapter--surface" id="faq">
    <div class="wrap faq-layout">
        <h2 class="reveal">The things people ask.</h2>

        <div class="faq-list reveal" style="--d:80ms">
            <?php foreach ($faqs as $faq): ?>
                <details class="faq">
                    <summary><?= e($faq['question']) ?></summary>
                    <p><?= nl2br(e($faq['answer'])) ?></p>
                </details>
            <?php endforeach; ?>

            <p style="margin-top:2.2rem">
                <a class="text-link" href="<?= e(url('faq.php')) ?>">All questions</a>
            </p>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="chapter chapter--ink cta" id="reserve" data-field="dark">
    <div class="wrap">
        <h2 class="reveal"><?= e($cta[0] ?? 'Take the night,') ?><br><?= e($cta[1] ?? 'or take the day.') ?></h2>

        <div class="cta-foot reveal" style="--d:90ms">
            <p><?= e(setting('cta_note')) ?></p>
            <a class="btn btn--accent" href="<?= e($messenger) ?>" target="_blank" rel="noopener"><?= icon('messenger') ?> Message us</a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/public_footer.php'; ?>
