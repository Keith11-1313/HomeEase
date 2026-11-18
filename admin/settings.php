<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/bootstrap.php';
require_once __DIR__ . '/../lib/auth.php';
require_admin();

/**
 * Every setting the owner is allowed to edit, grouped for the form.
 * 'type' drives the input: text, textarea, url, number.
 */
$groups = [
    'Brand & hero' => [
        'brand_name'  => ['Brand name', 'text'],
        'tagline'     => ['Tagline', 'textarea'],
        'hero_title'  => ['Hero headline (one or two lines)', 'textarea'],
        'hero_support'=> ['Hero supporting sentence', 'textarea'],
        'rooms_intro' => ['Rooms intro', 'textarea'],
    ],
    'Contact & location' => [
        'messenger_url'      => ['Messenger link', 'url'],
        'contact_note'       => ['Contact note', 'textarea'],
        'address'            => ['Address', 'text'],
        'landmarks'          => ['Landmarks (comma separated)', 'text'],
        'checkin_overnight'  => ['Overnight check-in window', 'text'],
        'checkin_daycation'  => ['Daycation check-in window', 'text'],
        'checkout_overnight' => ['Overnight check-out time', 'text'],
        'checkout_daycation' => ['Daycation check-out time', 'text'],
    ],
    'How it works' => [
        'booking_steps'  => ['Reservation steps', 'textarea'],
        'booking_result' => ['Final slip row', 'text'],
        'pricing_note'   => ['Pricing note', 'textarea'],
        'cta_title'      => ['Closing headline (one or two lines)', 'textarea'],
        'cta_note'       => ['Closing sentence', 'textarea'],
    ],
    'Calculator' => [
        'included_pax' => ['Pax included in every rate', 'number'],
        'promo_note'   => ['Promo note', 'textarea'],
    ],
];

$allowed = [];
foreach ($groups as $fields) {
    foreach (array_keys($fields) as $key) {
        $allowed[] = $key;
    }
}

if (is_post()) {
    csrf_verify();

    $messengerUrl = filter_var(post('messenger_url'), FILTER_VALIDATE_URL);
    if ($messengerUrl === false || !in_array(strtolower((string)parse_url($messengerUrl, PHP_URL_SCHEME)), ['https'], true)) {
        flash('error', 'Messenger link must be a valid HTTPS URL.');
        redirect('admin/settings.php');
    }

    $upsert = db()->prepare(
        'INSERT INTO settings ("key", value) VALUES (:key, :value)
         ON CONFLICT ("key") DO UPDATE SET value = EXCLUDED.value'
    );

    foreach ($allowed as $key) {
        if (!array_key_exists($key, $_POST)) {
            continue;
        }
        $upsert->execute([
            'key'   => $key,
            'value' => trim((string)$_POST[$key]),
        ]);
    }

    audit('update', 'settings', null, null, ['keys' => $allowed]);
    flash('success', 'Settings saved.');
    redirect('admin/settings.php');
}

$values = settings_all();

$groupIcons = [
    'Brand & hero'       => 'lamp',
    'Contact & location' => 'pin',
    'How it works'       => 'check-circle',
    'Calculator'         => 'calendar',
];

$page_title = 'Settings';
$active     = 'settings.php';
include __DIR__ . '/../includes/admin_header.php';
?>

<form method="post" action="<?= e(url('admin/settings.php')) ?>">
    <?= csrf_field() ?>

    <?php foreach ($groups as $groupName => $fields): ?>
        <div class="card">
            <div class="card-head">
                <div><h2><?= icon($groupIcons[$groupName] ?? 'shield') ?> <?= e($groupName) ?></h2></div>
            </div>

            <?php foreach ($fields as $key => [$label, $type]): ?>
                <label class="field">
                    <span><?= e($label) ?></span>

                    <?php if ($type === 'textarea'): ?>
                        <textarea name="<?= e($key) ?>"><?= e($values[$key] ?? '') ?></textarea>
                    <?php elseif ($type === 'number'): ?>
                        <input type="number" name="<?= e($key) ?>" min="1" step="1"
                               value="<?= e($values[$key] ?? '') ?>" style="max-width:160px" required>
                    <?php elseif ($type === 'url'): ?>
                        <input type="url" name="<?= e($key) ?>" value="<?= e($values[$key] ?? '') ?>"
                               placeholder="https://..." required>
                        <span class="field-hint">Use your Facebook Page's full Messenger URL.</span>
                    <?php else: ?>
                        <input type="text" name="<?= e($key) ?>" value="<?= e($values[$key] ?? '') ?>">
                    <?php endif; ?>

                    <?php if ($key === 'messenger_url'): ?>
                        <span class="field-hint">Open your Facebook Page → Message → copy the m.me link.</span>
                    <?php elseif ($key === 'booking_steps'): ?>
                        <span class="field-hint">One step per row: <code>Label|Detail</code>, rows separated by <code>;</code>. Shown on the receipt slip.</span>
                    <?php elseif ($key === 'hero_title' || $key === 'cta_title'): ?>
                        <span class="field-hint">One line per row. The second line crosses the colour seam in the hero.</span>
                    <?php elseif ($key === 'booking_result'): ?>
                        <span class="field-hint">The inverted last row: <code>Label|Value</code>.</span>
                    <?php endif; ?>
                </label>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>

    <div class="row-actions">
        <button class="btn" type="submit"><?= icon('check') ?> Save settings</button>
        <a class="btn btn-ghost" href="<?= e(url('index.php')) ?>" target="_blank" rel="noopener"><?= icon('arrow-out') ?> Preview site</a>
    </div>
</form>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
