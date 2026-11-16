<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/bootstrap.php';
require_once __DIR__ . '/../lib/auth.php';
require_admin();

if (is_post()) {
    csrf_verify();
    $id     = (int)post('id');
    $action = post('action');

    if ($id > 0 && $action === 'toggle') {
        db()->prepare('UPDATE units SET is_published = NOT is_published WHERE id = :id')->execute(['id' => $id]);
        audit('update', 'units', (string)$id, null, ['is_published' => 'toggled']);
        flash('success', 'Visibility updated.');
    } elseif ($id > 0 && $action === 'delete') {
        $unit = unit_by_id($id);
        db()->prepare('DELETE FROM units WHERE id = :id')->execute(['id' => $id]);
        audit('delete', 'units', (string)$id, $unit ? ['name' => $unit['name'], 'slug' => $unit['slug']] : null);
        flash('success', 'Room deleted.');
    }

    redirect('admin/rooms.php');
}

$units = units_all();

$page_title = 'Rooms';
$active     = 'rooms.php';
$top_action = '<a class="btn" href="' . e(url('admin/room_edit.php')) . '">' . icon('plus') . ' Add a room</a>';
include __DIR__ . '/../includes/admin_header.php';
?>

<div class="card">
    <div class="card-head">
        <div>
            <h2><?= icon('bed') ?> All rooms</h2>
        </div>
    </div>

    <?php if ($units): ?>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Room</th>
                        <th>Building</th>
                        <th>Size</th>
                        <th>From</th>
                        <th>Media</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($units as $unit): ?>
                        <?php $photoCount = count(unit_photos((int)$unit['id'])); ?>
                        <tr>
                            <td>
                                <div class="room-thumb-cell">
                                    <?php if ($unit['cover_url']): ?>
                                        <img class="room-thumb" src="<?= e(drive_image_url($unit['cover_url'])) ?>" alt="">
                                    <?php else: ?>
                                        <span class="room-thumb room-thumb--empty"><?= icon('camera') ?></span>
                                    <?php endif; ?>
                                    <div>
                                        <strong><?= e($unit['name']) ?></strong><br>
                                        <span class="muted small"><?= e($unit['tagline']) ?> · /<?= e($unit['slug']) ?></span>
                                    </div>
                                </div>
                            </td>
                            <td><?= e($unit['building_name'] ?: '—') ?></td>
                            <td><?= $unit['size_sqm'] ? e(rtrim(rtrim((string)$unit['size_sqm'], '0'), '.')) . ' sqm' : '—' ?></td>
                            <td><?= $unit['from_price'] !== null ? e(money($unit['from_price'])) : '—' ?></td>
                            <td><?= $photoCount ?></td>
                            <td>
                                <span class="pill <?= $unit['is_published'] ? '' : 'pill-off' ?>">
                                    <?= $unit['is_published'] ? 'Published' : 'Hidden' ?>
                                </span>
                            </td>
                            <td>
                                <div class="row-actions">
                                    <a class="btn btn-ghost btn-sm" href="<?= e(url('admin/room_edit.php?id=' . $unit['id'])) ?>"><?= icon('key') ?> Edit</a>
                                    <a class="btn btn-ghost btn-sm" href="<?= e(url('admin/photos.php?unit=' . $unit['id'])) ?>"><?= icon('camera') ?> Manage media</a>
                                    <form class="inline" method="post" action="<?= e(url('admin/rooms.php')) ?>">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= (int)$unit['id'] ?>">
                                        <input type="hidden" name="action" value="toggle">
                                        <button class="btn btn-ghost btn-sm" type="submit"><?= $unit['is_published'] ? icon('close') . ' Hide' : icon('check') . ' Publish' ?></button>
                                    </form>
                                    <form class="inline" method="post" action="<?= e(url('admin/rooms.php')) ?>"
                                          data-confirm="Delete this room and all of its photos, rates, and inclusions?">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= (int)$unit['id'] ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button class="btn btn-danger btn-sm" type="submit"><?= icon('close') ?> Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="empty">No rooms yet. <a href="<?= e(url('admin/room_edit.php')) ?>">Add the first one</a>.</p>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
