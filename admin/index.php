<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/bootstrap.php';
require_once __DIR__ . '/../lib/auth.php';
require_admin();

$counts = dashboard_counts();
$audit  = recent_audit(12);

$page_title = 'Dashboard';
$active     = 'index.php';
$top_action = '<a class="btn" href="' . e(url('admin/room_edit.php')) . '">' . icon('plus') . ' Add a room</a>';
include __DIR__ . '/../includes/admin_header.php';
?>

<div class="stat-grid">
    <div class="stat"><span><?= icon('bed') ?> Rooms</span><strong><?= $counts['units'] ?></strong></div>
    <div class="stat"><span><?= icon('check-circle') ?> Published</span><strong><?= $counts['published'] ?></strong></div>
    <div class="stat"><span><?= icon('camera') ?> Media</span><strong><?= $counts['photos'] ?></strong></div>
    <div class="stat"><span><?= icon('check-circle') ?> Inclusions</span><strong><?= $counts['amenities'] ?></strong></div>
    <div class="stat"><span><?= icon('mail') ?> FAQs</span><strong><?= $counts['faqs'] ?></strong></div>
</div>

<div class="card">
    <div class="card-head">
        <div>
            <h2><?= icon('clock') ?> Recent changes</h2>
        </div>
    </div>

    <?php if ($audit): ?>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr><th>When</th><th>Who</th><th>Action</th><th>Table</th><th>Record</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($audit as $row): ?>
                        <tr>
                            <td class="nowrap"><?= e(date('M j, g:i a', strtotime($row['created_at']))) ?></td>
                            <td><?= e($row['display_name'] ?: 'system') ?></td>
                            <td><?= e(ucfirst($row['action'])) ?></td>
                            <td><?= e($row['table_name']) ?></td>
                            <td><?= e($row['record_id'] ?: '—') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="empty">No changes recorded yet.</p>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-head">
        <div>
            <h2><?= icon('arrow-right') ?> Quick links</h2>
        </div>
    </div>
    <div class="row-actions">
        <a class="btn btn-ghost btn-sm" href="<?= e(url('admin/rooms.php')) ?>"><?= icon('bed') ?> Manage rooms</a>
        <a class="btn btn-ghost btn-sm" href="<?= e(url('admin/rates.php')) ?>"><?= icon('calendar') ?> Update rates</a>
        <a class="btn btn-ghost btn-sm" href="<?= e(url('admin/addons.php')) ?>"><?= icon('car') ?> Add-ons &amp; deposit</a>
        <a class="btn btn-ghost btn-sm" href="<?= e(url('admin/settings.php')) ?>"><?= icon('shield') ?> Site settings</a>
        <a class="btn btn-ghost btn-sm" href="<?= e(url('admin/calendar.php')) ?>"><?= icon('calendar') ?> Availability</a>
        <a class="btn btn-ghost btn-sm" href="<?= e(url('index.php')) ?>" target="_blank" rel="noopener"><?= icon('arrow-out') ?> View site</a>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
