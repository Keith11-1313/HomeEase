<?php
require_once __DIR__ . '/lib/bootstrap.php';

$photos = gallery_photos();

$grouped = [];
foreach ($photos as $photo) {
    $grouped[$photo['unit_name']][] = $photo;
}

$page_title = 'Gallery';
$page_desc  = 'Photographs of the studios and the neighbourhood.';
$active     = 'gallery';
include __DIR__ . '/includes/public_header.php';
?>

<section class="page-head wrap">
    <p class="page-kicker"><?= icon('camera') ?> The gallery</p>
    <h1>Inside the studios</h1>
    <p class="lead">Real rooms, photographed as they are handed over. New sets are added as each studio is shot.</p>
</section>

<?php if ($grouped): ?>
    <section class="chapter gallery-list">
        <div class="wrap">
            <?php foreach ($grouped as $unitName => $items): ?>
                <?php $roomUrl = e(url('room.php?slug=' . urlencode($items[0]['unit_slug']))); ?>
                <div class="gallery-group">
                    <div class="gallery-section">
                        <h2><?= icon('camera') ?> <?= e($unitName) ?></h2>
                        <a class="text-link" href="<?= $roomUrl ?>">View room <?= icon('arrow-right') ?></a>
                    </div>

                    <?php
                    $shapes  = ['bento-item--tall', 'bento-item--sq', 'bento-item--sq', 'bento-item--tall'];
                    $colsFor = ['bento-item--featured' => 2, 'bento-item--tall' => 1, 'bento-item--sq' => 1];
                    $total   = count($items);

                    $itemShapes = [];
                    foreach ($items as $i => $photo) {
                        $itemShapes[$i] = $i === 0 ? 'bento-item--featured' : $shapes[($i - 1) % 4];
                    }

                    // Fill orphan gap on last row
                    if ($total > 1) {
                        $totalCols = 0;
                        foreach ($itemShapes as $sh) $totalCols += $colsFor[explode(' ', $sh)[0]] ?? 1;
                        $rem = $totalCols % 4;
                        if ($rem !== 0) {
                            $gap  = 4 - $rem;
                            $last = $itemShapes[$total - 1];
                            $base = explode(' ', $last)[0];
                            $span = ($colsFor[$base] ?? 1) + $gap;
                            if ($span <= 4) $itemShapes[$total - 1] = $last . ' bento-item--fill-' . $span;
                        }
                    }
                    ?>
                    <div class="bento-grid">
                        <?php foreach ($items as $i => $photo):
                            $shapeClass = $itemShapes[$i];
                        ?>
                            <?php if ($photo['media_type'] === 'video'): ?>
                                <figure class="bento-item <?= $shapeClass ?> bento-item--video">
                                    <iframe src="<?= e($photo['url']) ?>"
                                            title="<?= e($photo['caption'] ?: $unitName . ' video') ?>"
                                            loading="lazy"
                                            allow="autoplay; encrypted-media; picture-in-picture"
                                            allowfullscreen
                                            referrerpolicy="strict-origin-when-cross-origin"></iframe>
                                    <?php if ($photo['caption']): ?>
                                        <figcaption><a href="<?= $roomUrl ?>"><?= e($photo['caption']) ?></a></figcaption>
                                    <?php endif; ?>
                                </figure>
                            <?php else: ?>
                                <figure class="bento-item <?= $shapeClass ?>">
                                    <a href="<?= $roomUrl ?>" aria-label="<?= e($photo['caption'] ?: $unitName . ' photo ' . ($i + 1)) ?>">
                                        <img src="<?= e(drive_image_url($photo['url'])) ?>"
                                             alt="<?= e($photo['caption'] ?: $unitName . ' photo ' . ($i + 1)) ?>"
                                             loading="lazy">
                                    </a>
                                    <?php if ($photo['caption']): ?>
                                        <figcaption><a href="<?= $roomUrl ?>"><?= e($photo['caption']) ?></a></figcaption>
                                    <?php endif; ?>
                                </figure>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php else: ?>
    <section class="chapter gallery-list">
        <div class="wrap">
            <div class="empty-state">
                <span class="empty-state-label" aria-hidden="true">—</span>
                <h2>No photos yet</h2>
                <p>Room photographs are being added. Ask on Messenger for the current set.</p>
                <a class="btn" href="<?= e(setting('messenger_url', 'https://m.me/')) ?>" target="_blank" rel="noopener"><?= icon('messenger') ?> Ask for photos</a>
            </div>
        </div>
    </section>
<?php endif; ?>

<section class="chapter chapter--ink cta gallery-cta" data-field="dark">
    <div class="wrap">
        <h2 class="reveal">Want to see a room live?</h2>
        <div class="cta-foot reveal" style="--d:80ms">
            <p>Ask on Messenger and we will send a fresh walkthrough of the studio you are considering.</p>
            <a class="btn btn--accent" href="<?= e(setting('messenger_url', 'https://m.me/')) ?>" target="_blank" rel="noopener"><?= icon('messenger') ?> Message us</a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/public_footer.php'; ?>
