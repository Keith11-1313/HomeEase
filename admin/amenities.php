<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/bootstrap.php';
require_once __DIR__ . '/../lib/auth.php';
require_admin();

if (is_post()) {
    csrf_verify();
    $action = post('action');
    $id     = (int)post('id');

    if ($action === 'save' && $id > 0) {
        $name = post('name');
        if ($name === '') {
            flash('error', 'An amenity needs a name.');
        } else {
            try {
                db()->prepare(
                    'UPDATE amenities
                        SET name = :name, category = :category, sort_order = :sort
                      WHERE id = :id'
                )->execute([
                    'name'     => $name,
                    'category' => post('category') !== '' ? post('category') : 'General',
                    'sort'     => (int)post('sort_order'),
                    'id'       => $id,
                ]);
                audit('update', 'amenities', (string)$id, null, ['name' => $name]);
                flash('success', 'Inclusion updated.');
            } catch (PDOException $e) {
                flash('error', 'That name is already used by another inclusion.');
            }
        }
    }

    if ($action === 'add') {
        $name = post('name');
        if ($name === '') {
            flash('error', 'An amenity needs a name.');
        } else {
            try {
                $stmt = db()->prepare(
                    'INSERT INTO amenities (name, category, sort_order)
                     VALUES (:name, :category, :sort)
                     RETURNING id'
                );
                $stmt->execute([
                    'name'     => $name,
                    'category' => post('category') !== '' ? post('category') : 'General',
                    'sort'     => (int)post('sort_order'),
                ]);
                audit('create', 'amenities', (string)$stmt->fetchColumn(), null, ['name' => $name]);
                flash('success', 'Inclusion added. Tick it on a room to use it.');
            } catch (PDOException $e) {
                flash('error', 'That name is already used by another inclusion.');
            }
        }
    }

    if ($action === 'delete' && $id > 0) {
        db()->prepare('DELETE FROM amenities WHERE id = :id')->execute(['id' => $id]);
        audit('delete', 'amenities', (string)$id);
        flash('success', 'Inclusion deleted and removed from every room.');
    }

    redirect('admin/amenities.php');
}

$amenities = amenities_all();

$usage = [];
foreach (db()->query('SELECT amenity_id, COUNT(*) AS n FROM unit_amenities GROUP BY amenity_id') as $row) {
    $usage[(int)$row['amenity_id']] = (int)$row['n'];
}

$categories = array_values(array_unique(array_map(
    static fn(array $a): string => $a['category'],
    $amenities
)));
sort($categories);

$page_title = 'Inclusions';
$active     = 'amenities.php';
include __DIR__ . '/../includes/admin_header.php';
?>

<div class="card">
    <div class="card-head">
        <div><h2><?= icon('check-circle') ?> Inclusions</h2></div>
        <button class="btn btn-sm" type="button" onclick="document.getElementById('add-amenity-form').toggleAttribute('hidden')">
            <?= icon('plus') ?> Add inclusion
        </button>
    </div>

    <div id="add-amenity-form" hidden>
        <form method="post" action="<?= e(url('admin/amenities.php')) ?>" class="amenity-add-form">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="add">

            <label class="field">
                <span>Name</span>
                <input type="text" name="name" required placeholder="e.g. Coffee maker" autofocus>
            </label>
            <label class="field">
                <span>Category</span>
                <input type="text" name="category" value="General" list="amenity-categories">
            </label>
            <input type="hidden" name="sort_order" value="0">

            <button class="btn" type="submit"><?= icon('plus') ?> Add</button>
        </form>
        <hr class="amenity-divider">
    </div>

    <?php if ($amenities): ?>
        <div class="amenity-list-head">
            <span>Name</span>
            <span>Category</span>
            <span>In use</span>
            <span></span>
        </div>

        <?php foreach ($amenities as $amenity): ?>
            <form method="post" action="<?= e(url('admin/amenities.php')) ?>" class="amenity-row">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int)$amenity['id'] ?>">
                <input type="hidden" name="sort_order" value="<?= (int)$amenity['sort_order'] ?>">

                <input type="text" name="name" value="<?= e($amenity['name']) ?>" required class="amenity-field">
                <input type="text" name="category" value="<?= e($amenity['category']) ?>"
                       list="amenity-categories" class="amenity-field">
                <span class="amenity-usage">
                    <?= (int)($usage[(int)$amenity['id']] ?? 0) ?>
                    <?= (int)($usage[(int)$amenity['id']] ?? 0) === 1 ? 'room' : 'rooms' ?>
                </span>
                <div class="row-actions">
                    <button class="btn btn-sm" type="submit" name="action" value="save">Save</button>
                    <button class="btn btn-danger btn-sm" type="submit" name="action" value="delete"
                            data-confirm="Delete '<?= e($amenity['name']) ?>' from every room?"
                            formnovalidate><?= icon('trash') ?></button>
                </div>
            </form>
        <?php endforeach; ?>

        <datalist id="amenity-categories">
            <?php foreach ($categories as $category): ?>
                <option value="<?= e($category) ?>"></option>
            <?php endforeach; ?>
        </datalist>
    <?php else: ?>
        <p class="empty">No inclusions yet. Add one above.</p>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
