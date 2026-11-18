<?php
require_once __DIR__ . '/lib/bootstrap.php';

$units     = units_published();
$faqs      = array_slice(faqs_published(), 0, 8);
$fees      = standard_fees();
$reviews   = reviews_published();
$steps     = parse_pairs(setting('booking_steps'));
$result    = parse_pairs(setting('booking_result'))[0] ?? ['You hold the slot', '₱500 booking fee'];
$hero      = lines(setting('hero_title'));
$cta       = lines(setting('cta_title'));
$from      = price_from();
$messenger = setting('messenger_url', 'https://m.me/');
$nbPhotos  = random_gallery_images(4);
/* hero photos for crossfade — pick up to 4 from gallery */
$heroPhotos = random_gallery_images(4);

$page_title = setting('brand_name', APP_NAME);
$page_desc  = setting('hero_support');

$lodging_schema = [
    '@context'    => 'https://schema.org',
    '@type'       => 'LodgingBusiness',
    'name'        => setting('brand_name', APP_NAME),
    'description' => setting('hero_support'),
    'url'         => BASE_URL ?: 'https://homeease-om0y.onrender.com',
    'address'     => [
        '@type'           => 'PostalAddress',
        'streetAddress'   => setting('address', 'Fairview, Quezon City'),
        'addressLocality' => 'Quezon City',
        'addressRegion'   => 'Metro Manila',
        'postalCode'      => '1118',
        'addressCountry'  => 'PH',
    ],
    'geo' => [
        '@type'     => 'GeoCoordinates',
        'latitude'  => 14.73566,
        'longitude' => 121.06017,
    ],
    'priceRange'   => '₱1550 - ₱2000',
    'checkinTime'  => 'Overnight: 18:00, Daycation: 07:00',
    'checkoutTime' => 'Overnight: 07:00, Daycation: 19:00',
    'sameAs' => [
        'https://www.instagram.com/flynnscozystaycation/',
        'https://www.tiktok.com/@cozystaycation',
        setting('messenger_url', 'https://m.me/flynnscozystaycation'),
    ],
];
$extra_head = '<script type="application/ld+json">' . json_encode($lodging_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';

include __DIR__ . '/includes/public_header.php';
?>

<!-- ================================================================
     HERO — split stage: striped card left, sticky photo right
     ================================================================ -->
<section class="hero" aria-label="Welcome" data-field="dark">

    <!-- Left: scrolling card -->
    <div class="hero-card">
        <!-- Striped band: alternating ink/paper bars, 28px each, 56px tall -->
        <div class="hero-stripe" aria-hidden="true">
            <?php for ($i = 0; $i < 48; $i++): ?>
                <span class="hero-stripe-bar"></span>
            <?php endfor; ?>
        </div>

        <div class="hero-body">
            <p class="hero-kicker"><?= icon('pin') ?> Fairview, Quezon City <span aria-hidden="true">·</span> 12-hour stays</p>

            <h1 class="hero-logo">
                <?= e($hero[0] ?? "Flynn's Cozy") ?>
                <span class="hero-emblem"><?= logo_mark() ?></span>
                <?= e($hero[1] ?? 'Staycation.') ?>
            </h1>

            <p class="hero-tagline">Short-stay studios · Fairview QC</p>

            <p class="hero-support"><?= e(setting('hero_support')) ?></p>

            <div class="hero-actions">
                <a class="btn" href="<?= e($messenger) ?>" target="_blank" rel="noopener"><?= icon('messenger') ?> Message us</a>
                <a class="btn btn--ghost" href="<?= e(url('rooms.php')) ?>"><?= icon('arrow-right') ?> See the rooms</a>
            </div>

            <nav class="hero-links" aria-label="Quick links">
                <a href="<?= e(url('rooms.php')) ?>">Rooms &amp; rates</a>
                <a href="<?= e(url('availability.php')) ?>">Availability</a>
                <a href="<?= e(url('gallery.php')) ?>">Gallery</a>
            </nav>
        </div>

        <!-- Info rail pinned to card bottom -->
        <div class="hero-rail" aria-label="Stay essentials">
            <div class="hero-rail-inner">
                <span><?= icon('moon') ?> Overnight · 7pm–7am</span>
                <span><?= icon('sun') ?> Daycation · 7am–7pm</span>
                <span><?= icon('users') ?> 2 pax included</span>
                <span><?= icon('car') ?> Parking on request</span>
            </div>
        </div>
    </div>

    <!-- Right: sticky photo with crossfade -->
    <div class="hero-photo" aria-hidden="true">
        <div class="hero-photo-frame"
             data-crossfade
             <?php if ($heroPhotos): ?>
             data-photos="<?= e(implode(',', array_map(fn($p) => drive_image_url($p['url']), $heroPhotos))) ?>"
             <?php endif; ?>>
            <?php if ($heroPhotos): ?>
                <?php foreach ($heroPhotos as $i => $p): ?>
                    <img src="<?= e(drive_image_url($p['url'])) ?>"
                         alt=""
                         class="<?= $i === 0 ? 'is-active' : '' ?>"
                         loading="<?= $i === 0 ? 'eager' : 'lazy' ?>">
                <?php endforeach; ?>
            <?php else: ?>
                <!-- fallback: solid field -->
                <div style="width:100%;height:100%;background:var(--surface);"></div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ================================================================
     SHOWCASE — "Square plate, spare text" — stays by type
     ================================================================ -->
<section class="chapter showcase" id="stays">
    <div class="wrap">
        <div class="showcase-inner">
            <!-- Left: crossfade photo frame -->
            <figure class="showcase-photo reveal">
                <?php
                $showcasePhotos = random_gallery_images(3);
                if ($showcasePhotos):
                    foreach ($showcasePhotos as $si => $sp):
                ?>
                    <img class="xfade-img<?= $si === 0 ? ' is-active' : '' ?>"
                         src="<?= e(drive_image_url($sp['url'])) ?>"
                         alt="<?= e($sp['caption'] ?: ($sp['unit_name'] ?? '')) ?>"
                         loading="<?= $si === 0 ? 'eager' : 'lazy' ?>">
                <?php
                    endforeach;
                else:
                ?>
                    <div style="width:100%;height:100%;min-height:380px;background:var(--surface);"></div>
                <?php endif; ?>
            </figure>

            <!-- Right: tabs + content -->
            <div class="showcase-body reveal" style="--d:80ms">
                <ul class="showcase-tabs" role="tablist">
                    <li class="is-active" role="tab">Overnight</li>
                    <li role="tab">Daycation</li>
                </ul>

                <div>
                    <h2 class="showcase-title">Stay your way, day or night.</h2>
                    <p class="showcase-desc" style="margin-top:1.2rem">
                        Every stay is a twelve-hour slot. Take the night from 7pm to 7am, or the day from 7am to 7pm. Both include two pax, air conditioning and the full studio.
                    </p>
                </div>

                <div class="showcase-actions">
                    <a href="<?= e($messenger) ?>" target="_blank" rel="noopener"><?= icon('messenger') ?> Book now</a>
                    <a href="<?= e(url('rooms.php')) ?>"><?= icon('arrow-right') ?> All rooms</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================================================================
     ROOMS index list
     ================================================================ -->
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
                        <?php if ($unit['cover_url']): ?>
                            <span class="room-thumb"><img src="<?= e(drive_image_url($unit['cover_url'])) ?>" alt="" loading="lazy"></span>
                        <?php else: ?>
                            <span class="room-thumb room-thumb--empty"></span>
                        <?php endif; ?>
                        <h3><?= e($unit['name']) ?><span class="sr-only">, <?= e($unit['tagline']) ?></span></h3>
                        <span class="room-facts">
                            <?php foreach (unit_fact_items($unit) as $fact): ?>
                                <span><?= icon($fact['icon']) ?><?= e($fact['text']) ?></span>
                            <?php endforeach; ?>
                        </span>
                        <span class="room-from">
                            from <?= e($unit['from_price'] !== null ? money($unit['from_price']) : 'ask') ?>
                            <span>per 12h (weekday overnight, 2 pax)</span>
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

<!-- ================================================================
     FEATURES — "Capabilities as a menu" — printed card on dark field
     ================================================================ -->
<section class="chapter features" id="how" data-field="dark">
    <div class="wrap">
        <div class="menu-card reveal">
            <h2 class="menu-card-title">How it works</h2>

            <div class="menu-group">
                <p class="menu-group-name">Booking</p>
                <?php foreach ($steps as $stepIndex => [$label, $value]): ?>
                    <div class="menu-row">
                        <span class="menu-row-name"><?= icon(['messenger', 'calendar', 'key'][$stepIndex] ?? 'check') ?><?= e($label) ?></span>
                        <span class="menu-row-dots" aria-hidden="true"></span>
                        <span class="menu-row-value"><?= e($value) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="menu-group">
                <p class="menu-group-name">On arrival</p>
                <div class="menu-row">
                    <span class="menu-row-name"><?= icon('moon') ?>Overnight check-in</span>
                    <span class="menu-row-dots" aria-hidden="true"></span>
                    <span class="menu-row-value"><?= e(policy_checkin_overnight()) ?></span>
                </div>
                <div class="menu-row">
                    <span class="menu-row-name"><?= icon('sun') ?>Daycation check-in</span>
                    <span class="menu-row-dots" aria-hidden="true"></span>
                    <span class="menu-row-value"><?= e(policy_checkin_daycation()) ?></span>
                </div>
                <div class="menu-row">
                    <span class="menu-row-name"><?= icon('key') ?>Access</span>
                    <span class="menu-row-dots" aria-hidden="true"></span>
                    <span class="menu-row-value">Keyless passcode</span>
                </div>
            </div>

            <div class="menu-group">
                <p class="menu-group-name">Included</p>
                <div class="menu-row">
                    <span class="menu-row-name"><?= icon('users') ?>Guests</span>
                    <span class="menu-row-dots" aria-hidden="true"></span>
                    <span class="menu-row-value">2 pax</span>
                </div>
                <div class="menu-row">
                    <span class="menu-row-name"><?= icon('wifi') ?>Wi-Fi</span>
                    <span class="menu-row-dots" aria-hidden="true"></span>
                    <span class="menu-row-value">included</span>
                </div>
                <div class="menu-row">
                    <span class="menu-row-name"><?= icon('check') ?>No forms</span>
                    <span class="menu-row-dots" aria-hidden="true"></span>
                    <span class="menu-row-value">Messenger only</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================================================================
     PRICING — "Prices like a menu"
     Full-width price statement + two-column list
     ================================================================ -->
<section class="chapter pricing" id="pricing">
    <div class="wrap">
        <!-- Full-width break per direction: large price statement -->
        <p class="price-sentence reveal">
            Twelve hours<br>
            from <span class="price-mark"><?= e(money($from)) ?></span>.
        </p>
        <p class="muted reveal" style="--d:60ms;margin-top:.8rem;font-size:var(--step-small);">Weekday overnight, 2 pax included</p>

        <div class="pricing-layout" style="margin-top:clamp(2.5rem,5vw,4rem)">
            <div class="pricing-heading reveal">
                <h2>Rate breakdown</h2>
                <p>Rates vary by room, day type and slot. The calculator on each room page gives you an exact estimate.</p>
                <a class="btn" href="<?= e(url('rooms.php')) ?>" style="margin-top:2rem"><?= icon('arrow-right') ?> Price a stay</a>
            </div>

            <div class="pricing-list reveal" style="--d:80ms">
                <div class="price-group">
                    <p class="price-group-name">Add-ons</p>
                    <div class="price-item">
                        <div>
                            <span class="price-item-name">Weekend / Holiday</span>
                            <span class="price-item-desc">Applies on Sat, Sun and PH holidays</span>
                        </div>
                        <span class="price-item-amt"><?= e($fees['weekend_diff']) ?></span>
                    </div>
                    <div class="price-item">
                        <div>
                            <span class="price-item-name">Extra guest</span>
                            <span class="price-item-desc">Per additional person beyond 2</span>
                        </div>
                        <span class="price-item-amt"><?= e($fees['extra_pax']) ?></span>
                    </div>
                    <div class="price-item">
                        <div>
                            <span class="price-item-name">Extra hour</span>
                            <span class="price-item-desc">Beyond the 12-hour slot</span>
                        </div>
                        <span class="price-item-amt"><?= e($fees['extra_hour']) ?></span>
                    </div>
                    <div class="price-item">
                        <div>
                            <span class="price-item-name">Parking</span>
                            <span class="price-item-desc">On-site, subject to availability</span>
                        </div>
                        <span class="price-item-amt"><?= e($fees['parking']) ?></span>
                    </div>
                    <div class="price-item">
                        <div>
                            <span class="price-item-name">Pool access</span>
                            <span class="price-item-desc">Building amenity fee</span>
                        </div>
                        <span class="price-item-amt"><?= e($fees['pool']) ?></span>
                    </div>
                </div>

                <div class="pricing-foot">
                    <p style="max-width:46ch;color:var(--muted);font-size:var(--step-small);"><?= e(setting('pricing_note')) ?></p>
                    <a class="text-link" href="<?= e(url('rooms.php')) ?>">See every rate</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================================================================
     HOUSE RULES preview
     ================================================================ -->
<section class="chapter chapter--surface rules-preview" id="rules-preview">
    <div class="wrap">
        <div class="chapter-head reveal">
            <p class="page-kicker"><?= icon('shield') ?> Peaceful stays</p>
            <h2>House rules &amp; building care</h2>
            <p class="lead">Our studios are inside residential condominiums. We keep our spaces calm and well-maintained for every guest.</p>
        </div>

        <div class="rules-grid">
            <div class="rule-card reveal">
                <h3><?= icon('clock') ?> Quiet hours</h3>
                <p><?= e(policy_house_rules()['quiet_hours']) ?>. No loud parties or disturbances.</p>
            </div>
            <div class="rule-card reveal" style="--d:80ms">
                <h3><?= icon('close') ?> Smoke &amp; vape free</h3>
                <p><?= e(policy_house_rules()['smoking']) ?></p>
            </div>
            <div class="rule-card reveal" style="--d:160ms">
                <h3><?= icon('users') ?> Registered guests</h3>
                <p><?= e(policy_house_rules()['visitors']) ?></p>
            </div>
        </div>

        <p style="margin-top:1.5rem" class="reveal">
            <a class="text-link" href="<?= e(url('house-rules.php')) ?>">Review complete house rules <?= icon('arrow-right') ?></a>
        </p>
    </div>
</section>

<!-- ================================================================
     REVIEWS — horizontal scroll strip
     ================================================================ -->
<?php if ($reviews): ?>
<section class="chapter reviews" id="reviews">
    <div class="wrap">
        <div class="chapter-head reveal">
            <p class="page-kicker"><?= icon('check-circle') ?> Verified guests</p>
            <h2>What guests say</h2>
        </div>
    </div>
    <div class="wrap" style="padding-right:0">
        <div class="reviews-strip">
            <?php foreach ($reviews as $rev): ?>
                <blockquote class="review-card reveal">
                    <div class="review-card-head">
                        <strong><?= e($rev['guest_name']) ?></strong>
                        <span class="review-card-stars"><?= str_repeat('★', (int)$rev['rating']) ?></span>
                    </div>
                    <p><?= nl2br(e($rev['body'])) ?></p>
                    <?php if ($rev['review_date'] || $rev['source']): ?>
                        <footer class="review-card-meta">
                            <?= e($rev['source'] ?: 'Direct guest') ?><?= $rev['review_date'] ? ' · ' . e(date('M Y', strtotime($rev['review_date']))) : '' ?>
                        </footer>
                    <?php endif; ?>
                </blockquote>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ================================================================
     NEIGHBOURHOOD
     ================================================================ -->
<section class="chapter neighbourhood" id="location">
    <div class="wrap">
        <div class="nb-grid">
            <div class="nb-copy reveal">
                <h2>Your Fairview staycation</h2>
                <p class="lead">Two residences a few minutes from SM Fairview and Ayala Terraces, with tricycles and jeepneys at the gate.</p>

                <dl class="nb-facts">
                    <div><dt><?= icon('pin') ?> Address</dt><dd><?= e(setting('address')) ?></dd></div>
                    <div><dt><?= icon('building') ?> Landmarks</dt><dd><?= e(setting('landmarks')) ?></dd></div>
                    <div>
                        <dt><?= icon('clock') ?> Check-in</dt>
                        <dd>Overnight: <?= e(policy_checkin_overnight()) ?><br>Daycation: <?= e(policy_checkin_daycation()) ?></dd>
                    </div>
                </dl>
            </div>

            <?php if (isset($nbPhotos[0])): ?>
                <figure class="nb-figure reveal" style="--d:80ms">
                    <img src="<?= e(drive_image_url($nbPhotos[0]['url'])) ?>"
                         alt="<?= e($nbPhotos[0]['caption'] ?: $nbPhotos[0]['unit_name']) ?>"
                         loading="lazy">
                </figure>
            <?php else: ?>
                <div class="nb-figure nb-figure--empty reveal" style="--d:80ms"><?= icon('camera') ?></div>
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

<!-- ================================================================
     FAQ — "Answers as reading text" (run-in questions, two-column prose)
     ================================================================ -->
<?php if ($faqs): ?>
<section class="chapter chapter--surface faq-reading" id="faq">
    <div class="wrap">
        <div class="faq-reading-layout">
            <h2 class="reveal">The things people ask.</h2>

            <div class="reveal" style="--d:80ms">
                <div class="faq-prose">
                    <?php foreach ($faqs as $faq): ?>
                        <p><strong><?= e($faq['question']) ?></strong> <?= nl2br(e($faq['answer'])) ?></p>
                    <?php endforeach; ?>
                </div>
                <p class="faq-all-link">
                    <a class="text-link" href="<?= e(url('faq.php')) ?>">All questions</a>
                </p>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ================================================================
     CTA — "Last photograph, text beside" — full-bleed 90svh
     ================================================================ -->
<div class="cta-photo" id="reserve" data-field="dark">
    <?php
    $ctaUnit = $units[0] ?? null;
    $ctaCoverUrl = $ctaUnit['cover_url'] ?? null;
    if ($ctaCoverUrl):
    ?>
        <img src="<?= e(drive_image_url($ctaCoverUrl)) ?>"
             alt=""
             loading="eager">
    <?php endif; ?>

    <div class="cta-photo-text">
        <h2><?= e($cta[0] ?? 'Take the night,') ?><br><?= e($cta[1] ?? 'or take the day.') ?></h2>
        <p><?= e(setting('cta_note')) ?></p>
        <a class="btn btn--accent" href="<?= e($messenger) ?>" target="_blank" rel="noopener"><?= icon('messenger') ?> Message us</a>
    </div>
</div>

<?php $hide_footer_cta = true; include __DIR__ . '/includes/public_footer.php'; ?>
