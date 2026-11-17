<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/bootstrap.php';
require_once __DIR__ . '/../lib/auth.php';
require_admin();

$counts = dashboard_counts();

// Rooms missing a cover photo (published only)
$no_cover_rooms = db()->query(
    'SELECT u.name, u.id FROM units u
      WHERE u.is_published
        AND u.id NOT IN (SELECT unit_id FROM unit_photos WHERE is_cover AND media_type = \'image\')
      ORDER BY u.sort_order, u.id LIMIT 5'
)->fetchAll();

// Next 14 days of blocks
$upcoming_blocks = db()->query(
    'SELECT block_date, slot, note FROM availability_blocks
      WHERE block_date BETWEEN CURRENT_DATE AND CURRENT_DATE + 14
      ORDER BY block_date, slot'
)->fetchAll();

// Active announcements
$announcements = db()->query(
    'SELECT title, start_date, end_date FROM announcements WHERE is_published ORDER BY created_at DESC LIMIT 3'
)->fetchAll();

$slotLabels = ['all' => 'Full day', 'overnight' => 'Overnight', 'daycation' => 'Daycation'];

$page_title = 'Dashboard';
$active     = 'index.php';
$top_action = '<a class="btn" href="' . e(url('admin/room_edit.php')) . '">' . icon('plus') . ' Add a room</a>';
include __DIR__ . '/../includes/admin_header.php';
?>

<div class="stat-grid">
    <a class="stat" href="<?= e(url('admin/rooms.php')) ?>">
        <span><?= icon('bed') ?> Total rooms</span>
        <strong><?= $counts['units'] ?></strong>
        <small><?= $counts['published'] ?> published</small>
    </a>
    <a class="stat" href="<?= e(url('admin/photos.php')) ?>">
        <span><?= icon('camera') ?> Media</span>
        <strong><?= $counts['photos'] ?></strong>
        <small>across all rooms</small>
    </a>
    <a class="stat" href="<?= e(url('admin/amenities.php')) ?>">
        <span><?= icon('check-circle') ?> Inclusions</span>
        <strong><?= $counts['amenities'] ?></strong>
        <small>amenities listed</small>
    </a>
    <a class="stat" href="<?= e(url('admin/faqs.php')) ?>">
        <span><?= icon('mail') ?> FAQs</span>
        <strong><?= $counts['faqs'] ?></strong>
        <small>published</small>
    </a>
</div>

<?php if ($counts['no_photos'] > 0 || $counts['no_cover'] > 0 || $no_cover_rooms): ?>
<div class="card dash-attention">
    <div class="card-head"><div><h2><?= icon('camera') ?> Needs attention</h2></div></div>
    <ul class="dash-list">
        <?php if ($counts['no_photos'] > 0): ?>
            <li>
                <span><?= $counts['no_photos'] ?> published <?= $counts['no_photos'] === 1 ? 'room has' : 'rooms have' ?> no photos</span>
                <a class="btn btn-ghost btn-sm" href="<?= e(url('admin/rooms.php')) ?>"><?= icon('arrow-right') ?> Rooms</a>
            </li>
        <?php endif; ?>
        <?php foreach ($no_cover_rooms as $r): ?>
            <li>
                <span><?= e($r['name']) ?> has no cover photo</span>
                <a class="btn btn-ghost btn-sm" href="<?= e(url('admin/photos.php?unit=' . $r['id'])) ?>"><?= icon('camera') ?> Add cover</a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="dash-cols">
    <div class="card">
        <div class="card-head"><div><h2><?= icon('calendar') ?> Next 14 days</h2></div>
            <a class="btn btn-ghost btn-sm" href="<?= e(url('admin/calendar.php')) ?>"><?= icon('arrow-right') ?> Manage</a>
        </div>
        <?php if ($upcoming_blocks): ?>
            <ul class="dash-list">
                <?php foreach ($upcoming_blocks as $b): ?>
                    <li>
                        <span>
                            <strong><?= e(date('D, M j', strtotime($b['block_date']))) ?></strong>
                            <em class="dash-slot-badge dash-slot-badge--<?= e($b['slot']) ?>"><?= e($slotLabels[$b['slot']] ?? $b['slot']) ?></em>
                            <?php if ($b['note']): ?><small class="muted"><?= e($b['note']) ?></small><?php endif; ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="empty">No blocked dates in the next 14 days.</p>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-head"><div><h2><?= icon('mail') ?> Active announcements</h2></div>
            <a class="btn btn-ghost btn-sm" href="<?= e(url('admin/calendar.php')) ?>"><?= icon('arrow-right') ?> Manage</a>
        </div>
        <?php if ($announcements): ?>
            <ul class="dash-list">
                <?php foreach ($announcements as $a): ?>
                    <li>
                        <span>
                            <strong><?= e($a['title']) ?></strong>
                            <?php if ($a['start_date'] || $a['end_date']): ?>
                                <small class="muted"><?= e($a['start_date'] ?? '') ?><?= $a['end_date'] ? ' — ' . e($a['end_date']) : '' ?></small>
                            <?php endif; ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="empty">No active announcements.</p>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
