<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/bootstrap.php';
require_once __DIR__ . '/../lib/auth.php';
require_admin();

$units = units_all();
if (!$units) {
    flash('error', 'Add a room before adding photos.');
    redirect('admin/rooms.php');
}

$unitId = (int)get('unit');
if ($unitId <= 0) {
    $unitId = (int)$units[0]['id'];
}
$unit = unit_by_id($unitId);
if (!$unit) {
    flash('error', 'That room does not exist.');
    redirect('admin/rooms.php');
}

if (is_post()) {
    csrf_verify();
    $action = post('action');

    if ($action === 'reorder') {
        $ids = array_filter(array_map('intval', explode(',', post('ids'))));
        foreach ($ids as $pos => $photoId) {
            db()->prepare('UPDATE unit_photos SET sort_order = :sort WHERE id = :id AND unit_id = :unit')
                ->execute(['sort' => $pos, 'id' => $photoId, 'unit' => $unitId]);
        }
        header('Content-Type: application/json');
        echo json_encode(['ok' => true]);
        exit;
    } elseif ($action === 'add') {
        $type = post('media_type', 'image');
        $url = post($type === 'video' ? 'video_url' : 'url');
        $parsed = filter_var($url, FILTER_VALIDATE_URL) ? parse_url($url) : false;
        if (!$parsed || strtolower((string)($parsed['scheme'] ?? '')) !== 'https' || !in_array($type, ['image', 'video'], true)) {
            flash('error', 'Use a valid public HTTPS URL and select a media type.');
        } elseif ($type === 'video' && !($fileId = drive_video_id($url))) {
            flash('error', 'Use a valid Google Drive video link.');
        } else {
            if ($type === 'video') {
                $url = 'https://drive.google.com/file/d/' . $fileId . '/preview';
            }
            try {
                db()->beginTransaction();
                db()->prepare(
                    'INSERT INTO unit_photos (unit_id, url, caption, media_type, poster_url, sort_order, is_cover)
                     VALUES (:unit, :url, :caption, :type, NULL, :sort, FALSE)'
                )->execute([
                    'unit'    => $unitId,
                    'url'     => $url,
                    'caption' => post('caption') !== '' ? post('caption') : null,
                    'type'    => $type,
                    'sort'    => (int)post('sort_order'),
                ]);
                db()->commit();
            } catch (Throwable $error) {
                if (db()->inTransaction()) db()->rollBack();
                throw $error;
            }
            audit('create', 'unit_photos', (string)$unitId, null, ['url' => $url, 'media_type' => $type]);
            flash('success', ucfirst($type) . ' added.');
        }
    } elseif ($action === 'delete') {
        $photoId = (int)post('id');
        db()->prepare('DELETE FROM unit_photos WHERE id = :id AND unit_id = :unit')
            ->execute(['id' => $photoId, 'unit' => $unitId]);
        audit('delete', 'unit_photos', (string)$photoId);
        flash('success', 'Photo removed.');
    } elseif ($action === 'cover') {
        $photoId = (int)post('id');
        db()->prepare('UPDATE unit_photos SET is_cover = (id = :id) WHERE unit_id = :unit')
            ->execute(['id' => $photoId, 'unit' => $unitId]);
        flash('success', 'Cover updated.');
    }

    redirect('admin/photos.php?unit=' . $unitId);
}

$photos = unit_photos($unitId);

$page_title = 'Media | ' . $unit['name'];
$active     = 'rooms.php';
include __DIR__ . '/../includes/admin_header.php';
?>

<div class="card">
    <div class="card-head">
        <div>
            <h2><?= icon('camera') ?> Room media</h2>
        </div>
        <form method="get" action="<?= e(url('admin/photos.php')) ?>" class="media-room-switch">
            <select name="unit" aria-label="Select room" data-autosubmit>
                <?php foreach ($units as $option): ?>
                    <option value="<?= (int)$option['id'] ?>" <?= selected($unitId, $option['id']) ?>><?= e($option['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <?php if ($photos): ?>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr><th></th><th>Preview</th><th>Source</th><th>Caption</th><th>Cover</th><th></th></tr>
                </thead>
                <tbody id="photo-list" data-reorder-url="<?= e(url('admin/photos.php?unit=' . $unitId)) ?>" data-csrf="<?= e(csrf_token()) ?>">
                    <?php foreach ($photos as $photo): ?>
                        <tr data-id="<?= (int)$photo['id'] ?>">
                            <td class="drag-handle" title="Drag to reorder">⠿</td>
                            <td style="width:90px">
                                <?php if ($photo['media_type'] === 'video'): ?><span class="media-video-preview"><?= icon('tv') ?> Video</span><?php else: ?><img src="<?= e(drive_image_url($photo['url'])) ?>" alt="" style="width:80px;height:56px;object-fit:cover;background:var(--surface)"><?php endif; ?>
                            </td>
                            <td class="small" style="max-width:320px;word-break:break-all"><span class="pill"><?= e(ucfirst($photo['media_type'])) ?></span><br><?= e($photo['url']) ?></td>
                            <td><?= e($photo['caption'] ?: '—') ?></td>
                            <td><?= $photo['is_cover'] ? '<span class="pill">Cover</span>' : '—' ?></td>
                            <td>
                                <div class="row-actions">
                                    <?php if ($photo['media_type'] === 'image' && !$photo['is_cover']): ?>
                                        <form class="inline" method="post" action="<?= e(url('admin/photos.php?unit=' . $unitId)) ?>">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="cover">
                                            <input type="hidden" name="id" value="<?= (int)$photo['id'] ?>">
                                            <button class="btn btn-ghost btn-sm" type="submit"><?= icon('check') ?> Set cover</button>
                                        </form>
                                    <?php endif; ?>
                                    <form class="inline" method="post" action="<?= e(url('admin/photos.php?unit=' . $unitId)) ?>"
                                          data-confirm="Remove this photo?">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= (int)$photo['id'] ?>">
                                        <button class="btn btn-danger btn-sm" type="submit"><?= icon('close') ?> Remove</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="empty">No room media yet.</p>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-head">
        <div><h2><?= icon('plus') ?> Add room media</h2></div>
        <fieldset class="media-type-picker">
            <label><input type="radio" name="media_type" value="image" form="add-media-form" checked><span>Photo</span></label>
            <label><input type="radio" name="media_type" value="video" form="add-media-form"><span>Video</span></label>
        </fieldset>
    </div>

    <form id="add-media-form" method="post" action="<?= e(url('admin/photos.php?unit=' . $unitId)) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add">

        <label class="field" data-media-field="image">
            <span id="media-url-label">Photo URL *</span>
            <input type="url" name="url" placeholder="https://image-url.jpg" required data-image-placeholder="https://image-url.jpg" data-video-placeholder="https://drive.google.com/file/d/FILE_ID/view">
        </label>

        <label class="field" data-media-field="video" hidden>
            <span id="video-url-label">Video link *</span>
            <input type="url" name="video_url" placeholder="https://drive.google.com/file/d/FILE_ID/view" disabled>
        </label>

        <label class="field">
            <span>Caption</span>
            <input type="text" name="caption" placeholder="Living area">
        </label>

        <button class="btn" id="media-submit" type="submit" data-photo-icon="<?= e(asset('icons/camera.svg')) ?>" data-video-icon="<?= e(asset('icons/tv.svg')) ?>"><?= icon('camera') ?> <span id="media-submit-label">Add photo</span></button>
    </form>
</div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
