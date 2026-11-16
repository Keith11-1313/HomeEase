<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/bootstrap.php';
require_once __DIR__ . '/../lib/auth.php';
require_admin();

$id   = (int)get('id');
$unit = $id > 0 ? unit_by_id($id) : null;

if ($id > 0 && !$unit) {
    flash('error', 'That room does not exist.');
    redirect('admin/rooms.php');
}

$buildings = buildings_all();
$amenities = amenities_all();

$selectedAmenities = [];
if ($unit) {
    foreach (unit_amenities((int)$unit['id']) as $items) {
        foreach ($items as $item) {
            $selectedAmenities[] = (int)$item['id'];
        }
    }
}

$errors = [];
$values = [
    'name'         => $unit['name'] ?? '',
    'tagline'      => $unit['tagline'] ?? '',
    'building_id'  => (string)($unit['building_id'] ?? ''),
    'description'  => $unit['description'] ?? '',
    'size_sqm'     => $unit['size_sqm'] ?? '',
    'floor_no'     => $unit['floor_no'] ?? '',
    'bed_type'     => $unit['bed_type'] ?? '',
    'capacity_max' => (string)($unit['capacity_max'] ?? 4),
    'view_note'    => $unit['view_note'] ?? '',
    'sort_order'   => (string)($unit['sort_order'] ?? 0),
];
$isPublished = $unit ? (bool)$unit['is_published'] : true;

if (is_post()) {
    csrf_verify();

    foreach (array_keys($values) as $key) {
        $values[$key] = post($key);
    }
    $isPublished = isset($_POST['is_published']);
    $submittedAmenities = $_POST['amenities'] ?? [];
    $selectedAmenities = array_values(array_unique(array_filter(
        array_map('intval', is_array($submittedAmenities) ? $submittedAmenities : []),
        static fn(int $amenityId): bool => in_array($amenityId, array_column($amenities, 'id'), true)
    )));

    if ($values['name'] === '') $errors[] = 'The room name is required.';
    elseif (strlen($values['name']) > 120) $errors[] = 'Room name must be 120 characters or fewer.';

    if ($values['tagline'] !== '' && strlen($values['tagline']) > 180) $errors[] = 'Tagline must be 180 characters or fewer.';
    if ($values['size_sqm'] !== '' && (!is_numeric($values['size_sqm']) || (float)$values['size_sqm'] <= 0 || (float)$values['size_sqm'] > 999.99)) $errors[] = 'Size must be between 0 and 999.99 sqm.';
    if ($values['floor_no'] !== '' && filter_var($values['floor_no'], FILTER_VALIDATE_INT) === false) $errors[] = 'Floor must be a whole number.';
    if (filter_var($values['capacity_max'], FILTER_VALIDATE_INT) === false || (int)$values['capacity_max'] < 1 || (int)$values['capacity_max'] > 50) $errors[] = 'Maximum guests must be between 1 and 50.';
    if (filter_var($values['sort_order'], FILTER_VALIDATE_INT) === false) $errors[] = 'Sort order must be a whole number.';
    if (strlen($values['description']) > 10000 || strlen($values['view_note']) > 250 || strlen($values['bed_type']) > 80) $errors[] = 'Description, view note, or bed type is too long.';

    $buildingId = $values['building_id'] === '' ? null : filter_var($values['building_id'], FILTER_VALIDATE_INT);
    if ($values['building_id'] !== '' && ($buildingId === false || !in_array((int)$buildingId, array_map('intval', array_column($buildings, 'id')), true))) $errors[] = 'Choose a valid building.';

    $values['slug'] = slugify($values['name']);

    // If slug collides, append the existing ID (or a suffix for new rooms)
    $dupe = db()->prepare('SELECT id FROM units WHERE slug = :slug AND id <> :id');
    $dupe->execute(['slug' => $values['slug'], 'id' => $id]);
    if ($dupe->fetchColumn()) {
        $values['slug'] = $values['slug'] . '-' . ($id > 0 ? $id : uniqid());
    }

    if (!$errors) {
        $params = [
            'slug'         => $values['slug'],
            'name'         => $values['name'],
            'tagline'      => $values['tagline'] !== '' ? $values['tagline'] : null,
            'building'     => $buildingId !== null ? (int)$buildingId : null,
            'description'  => $values['description'] !== '' ? $values['description'] : null,
            'size'         => $values['size_sqm'] !== '' ? $values['size_sqm'] : null,
            'floor'        => $values['floor_no'] !== '' ? (int)$values['floor_no'] : null,
            'bed'          => $values['bed_type'] !== '' ? $values['bed_type'] : null,
            'capacity'     => (int)$values['capacity_max'],
            'view'         => $values['view_note'] !== '' ? $values['view_note'] : null,
            'published'    => $isPublished,
            'sort'         => (int)$values['sort_order'],
        ];

        if ($id > 0) {
            $params['id'] = $id;
            db()->prepare(
                'UPDATE units SET slug = :slug, name = :name, tagline = :tagline,
                        building_id = :building, description = :description,
                        size_sqm = :size, floor_no = :floor, bed_type = :bed,
                        capacity_max = :capacity, view_note = :view,
                        is_published = :published, sort_order = :sort
                  WHERE id = :id'
            )->execute($params);
            audit('update', 'units', (string)$id, ['name' => $unit['name']], ['name' => $values['name']]);
        } else {
            $stmt = db()->prepare(
                'INSERT INTO units (slug, name, tagline, building_id, description,
                                    size_sqm, floor_no, bed_type, capacity_max,
                                    view_note, is_published, sort_order)
                 VALUES (:slug, :name, :tagline, :building, :description,
                         :size, :floor, :bed, :capacity, :view, :published, :sort)
                 RETURNING id'
            );
            $stmt->execute($params);
            $id = (int)$stmt->fetchColumn();
            audit('create', 'units', (string)$id, null, ['name' => $values['name']]);
        }

        // Replace the inclusion set.
        db()->prepare('DELETE FROM unit_amenities WHERE unit_id = :id')->execute(['id' => $id]);
        $insert = db()->prepare(
            'INSERT INTO unit_amenities (unit_id, amenity_id) VALUES (:unit, :amenity)
             ON CONFLICT DO NOTHING'
        );
        foreach ($selectedAmenities as $amenityId) {
            $insert->execute(['unit' => $id, 'amenity' => $amenityId]);
        }

        flash('success', 'Room saved.');
        redirect('admin/rooms.php');
    }
}

$page_title = $id > 0 ? 'Edit ' . $values['name'] : 'New room';
$active     = 'rooms.php';
$top_action = $id > 0
    ? '<a class="btn btn-ghost" href="' . e(url('admin/photos.php?unit=' . $id)) . '">' . icon('camera') . ' Manage photos</a>'
    : '';
include __DIR__ . '/../includes/admin_header.php';
?>

<?php if ($errors): ?>
    <div class="notice notice-error"><?= e(implode(' ', $errors)) ?></div>
<?php endif; ?>

<form method="post" action="<?= e(url('admin/room_edit.php' . ($id > 0 ? '?id=' . $id : ''))) ?>">
    <?= csrf_field() ?>

    <div class="card">
        <div class="card-head">
            <div><h2><?= icon('building') ?> Details</h2></div>
        </div>

        <div class="grid-2">
            <label class="field">
                <span>Name *</span>
                <input type="text" name="name" value="<?= e($values['name']) ?>" required>
            </label>

            <label class="field">
                <span>Tagline</span>
                <input type="text" name="tagline" value="<?= e($values['tagline']) ?>" placeholder="Simple Modern">
            </label>

            <label class="field">
                <span>Building</span>
                <select name="building_id">
                    <option value="">— none —</option>
                    <?php foreach ($buildings as $building): ?>
                        <option value="<?= (int)$building['id'] ?>" <?= selected($values['building_id'], $building['id']) ?>>
                            <?= e($building['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>

        <label class="field">
            <span>Description</span>
            <textarea name="description"><?= e($values['description']) ?></textarea>
        </label>

        <div class="grid-3">
            <label class="field">
                <span>Size (sqm)</span>
                <input type="number" name="size_sqm" step="0.01" min="0" value="<?= e((string)$values['size_sqm']) ?>">
            </label>
            <label class="field">
                <span>Floor</span>
                <input type="number" name="floor_no" step="1" value="<?= e((string)$values['floor_no']) ?>">
            </label>
            <label class="field">
                <span>Bed type</span>
                <input type="text" name="bed_type" value="<?= e($values['bed_type']) ?>" placeholder="Queen">
            </label>
            <label class="field">
                <span>Max pax</span>
                <input type="number" name="capacity_max" min="1" max="50" step="1" required value="<?= e((string)$values['capacity_max']) ?>">
            </label>
            <label class="field">
                <span>View note</span>
                <input type="text" name="view_note" value="<?= e($values['view_note']) ?>" placeholder="City view w/ balcony">
            </label>
            <label class="field">
                <span>Sort order</span>
                <input type="number" name="sort_order" value="<?= e((string)$values['sort_order']) ?>">
            </label>
        </div>

        <label class="field">
            <span>Visibility</span>
            <label class="check-list" style="display:flex;align-items:center;gap:.5rem;border:0;padding:0;background:transparent;">
                <input type="checkbox" name="is_published" value="1" <?= checked($isPublished) ?>>
                Published on the public site
            </label>
        </label>
    </div>

    <div class="card">
        <div class="card-head">
            <div><h2><?= icon('check-circle') ?> Inclusions</h2></div>
        </div>

        <?php if ($amenities): ?>
            <div class="check-list">
                <?php foreach ($amenities as $amenity): ?>
                    <label>
                        <input type="checkbox" name="amenities[]" value="<?= (int)$amenity['id'] ?>"
                               <?= in_array((int)$amenity['id'], $selectedAmenities, true) ? 'checked' : '' ?>>
                        <?= e($amenity['name']) ?>
                    </label>
                <?php endforeach; ?>
            </div>
            <p class="field-hint">Manage names and categories in <a href="<?= e(url('admin/amenities.php')) ?>">Inclusions</a>.</p>
        <?php else: ?>
            <p class="empty">No amenities yet. <a href="<?= e(url('admin/amenities.php')) ?>">Add some</a>.</p>
        <?php endif; ?>
    </div>

    <div class="row-actions">
        <button class="btn" type="submit"><?= icon('check') ?> Save room</button>
        <a class="btn btn-ghost" href="<?= e(url('admin/rooms.php')) ?>"><?= icon('close') ?> Cancel</a>
        <?php if ($id > 0): ?>
            <a class="btn btn-ghost" href="<?= e(url('room.php?slug=' . urlencode($unit['slug']))) ?>" target="_blank" rel="noopener"><?= icon('arrow-out') ?> Preview</a>
        <?php endif; ?>
    </div>
</form>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
