<?php
require_once __DIR__ . '/lib/bootstrap.php';

$rules = policy_house_rules();

$page_title = 'House Rules';
$page_desc  = 'House rules and building policies for Flynn\'s Cozy Staycation studios in Fairview.';
$active     = 'house-rules';
include __DIR__ . '/includes/public_header.php';
?>

<section class="page-head wrap">
    <p class="page-kicker"><?= icon('shield') ?> Guidelines for a comfortable stay</p>
    <h1>House rules</h1>
    <p class="lead">Our studios are located inside quiet residential condominium communities in Fairview. We ask all guests to respect the neighbors and care for the space.</p>
</section>

<section class="chapter">
    <div class="wrap">
        <div class="rules-grid" style="display:grid;gap:1.5rem;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));">
            <article class="card" style="padding:1.5rem;background:var(--paper);border:1px solid var(--line);border-radius:var(--radius);">
                <h2 style="font-size:1.15rem;margin-bottom:0.5rem;display:flex;align-items:center;gap:0.5rem;">
                    <?= icon('clock') ?> Quiet hours
                </h2>
                <p><?= e($rules['quiet_hours']) ?></p>
                <p class="text-muted-sm"><?= e($rules['max_noise']) ?></p>
            </article>

            <article class="card" style="padding:1.5rem;background:var(--paper);border:1px solid var(--line);border-radius:var(--radius);">
                <h2 style="font-size:1.15rem;margin-bottom:0.5rem;display:flex;align-items:center;gap:0.5rem;">
                    <?= icon('close') ?> Smoking &amp; vaping
                </h2>
                <p><?= e($rules['smoking']) ?></p>
            </article>

            <article class="card" style="padding:1.5rem;background:var(--paper);border:1px solid var(--line);border-radius:var(--radius);">
                <h2 style="font-size:1.15rem;margin-bottom:0.5rem;display:flex;align-items:center;gap:0.5rem;">
                    <?= icon('users') ?> Visitors &amp; extra guests
                </h2>
                <p><?= e($rules['visitors']) ?></p>
            </article>

            <article class="card" style="padding:1.5rem;background:var(--paper);border:1px solid var(--line);border-radius:var(--radius);">
                <h2 style="font-size:1.15rem;margin-bottom:0.5rem;display:flex;align-items:center;gap:0.5rem;">
                    <?= icon('bed') ?> Pets
                </h2>
                <p><?= e($rules['pets']) ?></p>
            </article>

            <article class="card" style="padding:1.5rem;background:var(--paper);border:1px solid var(--line);border-radius:var(--radius);">
                <h2 style="font-size:1.15rem;margin-bottom:0.5rem;display:flex;align-items:center;gap:0.5rem;">
                    <?= icon('sparkle') ?> Cleanliness &amp; CLAYGO
                </h2>
                <p><?= e($rules['cleanliness']) ?></p>
            </article>

            <article class="card" style="padding:1.5rem;background:var(--paper);border:1px solid var(--line);border-radius:var(--radius);">
                <h2 style="font-size:1.15rem;margin-bottom:0.5rem;display:flex;align-items:center;gap:0.5rem;">
                    <?= icon('kitchen') ?> Cooking &amp; Odor
                </h2>
                <p><?= e($rules['cooking']) ?></p>
            </article>

            <article class="card" style="padding:1.5rem;background:var(--paper);border:1px solid var(--line);border-radius:var(--radius);">
                <h2 style="font-size:1.15rem;margin-bottom:0.5rem;display:flex;align-items:center;gap:0.5rem;">
                    <?= icon('trash') ?> Trash disposal
                </h2>
                <p><?= e($rules['trash']) ?></p>
            </article>

            <article class="card" style="padding:1.5rem;background:var(--paper);border:1px solid var(--line);border-radius:var(--radius);">
                <h2 style="font-size:1.15rem;margin-bottom:0.5rem;display:flex;align-items:center;gap:0.5rem;">
                    <?= icon('close') ?> In-room massage
                </h2>
                <p><?= e($rules['massage']) ?></p>
            </article>

            <article class="card" style="padding:1.5rem;background:var(--paper);border:1px solid var(--line);border-radius:var(--radius);">
                <h2 style="font-size:1.15rem;margin-bottom:0.5rem;display:flex;align-items:center;gap:0.5rem;">
                    <?= icon('car') ?> Parking guidelines
                </h2>
                <p><?= e($rules['parking']) ?></p>
            </article>

            <article class="card" style="padding:1.5rem;background:var(--paper);border:1px solid var(--line);border-radius:var(--radius);">
                <h2 style="font-size:1.15rem;margin-bottom:0.5rem;display:flex;align-items:center;gap:0.5rem;">
                    <?= icon('shield') ?> Care &amp; damages
                </h2>
                <p><?= e($rules['damages']) ?></p>
            </article>
        </div>

        <div style="margin-top:2.5rem;display:grid;gap:1.5rem;grid-template-columns:repeat(auto-fit, minmax(320px, 1fr));">
            <article class="card" style="padding:1.5rem;background:var(--paper);border:1px solid var(--line);border-radius:var(--radius);">
                <h2 style="font-size:1.15rem;margin-bottom:0.5rem;display:flex;align-items:center;gap:0.5rem;">
                    <?= icon('building') ?> Trees Residences policies
                </h2>
                <p><?= e($rules['trees_rules']) ?></p>
            </article>

            <article class="card" style="padding:1.5rem;background:var(--paper);border:1px solid var(--line);border-radius:var(--radius);">
                <h2 style="font-size:1.15rem;margin-bottom:0.5rem;display:flex;align-items:center;gap:0.5rem;">
                    <?= icon('building') ?> Milan Residenze policies
                </h2>
                <p><?= e($rules['milan_rules']) ?></p>
            </article>
        </div>

        <div style="margin-top:2.5rem;padding:1.5rem;background:var(--paper);border:1px solid var(--line);border-radius:var(--radius);">
            <h2 style="font-size:1.15rem;margin-bottom:0.5rem;display:flex;align-items:center;gap:0.5rem;">
                <?= icon('clock') ?> Check-out guide &amp; security deposit release
            </h2>
            <p><strong>Timing:</strong> <?= e($rules['checkout']) ?></p>
            <p><strong>Before leaving:</strong> <?= e($rules['checkout_clean']) ?></p>
            <p class="text-muted-sm" style="margin-top:0.75rem;">Please wait for our team to inspect the unit before the security deposit is released. We manage 5 units; inspection and deposit release (via GCash, Maya, or BPI) usually take less than 2–3 hours.</p>
        </div>
    </div>
</section>

<section class="chapter chapter--ink cta" data-field="dark">
    <div class="wrap">
        <h2>Have questions about our house rules?</h2>
        <div class="cta-foot" style="margin-top:1rem;">
            <p>Message us directly on Facebook Messenger if you need clarification on any rule before booking.</p>
            <a class="btn btn--accent" href="<?= e(setting('messenger_url', 'https://m.me/')) ?>" target="_blank" rel="noopener">
                <?= icon('messenger') ?> Message us
            </a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/public_footer.php'; ?>
