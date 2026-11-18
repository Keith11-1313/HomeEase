<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/bootstrap.php';
require_once __DIR__ . '/../lib/auth.php';
require_admin();

$validSlots = ['overnight', 'daycation', 'all'];

if (is_post()) {
    csrf_verify();
    $action = post('action');

    if ($action === 'block') {
        $date = post('date');
        $slot = post('slot', 'all');
        $note = post('note');
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        if (!$parsed || $parsed->format('Y-m-d') !== $date) {
            flash('error', 'Choose a valid date.');
        } elseif (!in_array($slot, $validSlots, true)) {
            flash('error', 'Invalid slot.');
        } else {
            db()->prepare('INSERT INTO availability_blocks (block_date, slot, note) VALUES (:date, :slot, :note) ON CONFLICT (block_date, slot) DO UPDATE SET note = EXCLUDED.note')
                ->execute(['date' => $date, 'slot' => $slot, 'note' => $note]);
            audit('update', 'availability_blocks', $date, null, ['slot' => $slot, 'note' => $note]);
            flash('success', 'Unavailable slot saved.');
        }
    } elseif ($action === 'unblock') {
        $id = (int)post('id');
        db()->prepare('DELETE FROM availability_blocks WHERE id = :id')->execute(['id' => $id]);
        audit('delete', 'availability_blocks', (string)$id);
        flash('success', 'Slot marked available.');
    } elseif ($action === 'announcement' || $action === 'delete_announcement') {
        $id = (int)post('id');
        if ($action === 'delete_announcement') {
            db()->prepare('DELETE FROM announcements WHERE id=:id')->execute(['id' => $id]);
            audit('delete', 'announcements', (string)$id);
            flash('success', 'Announcement deleted.');
        } else {
            $title = post('title');
            $body = post('body');
            $start = post('start_date') ?: null;
            $end = post('end_date') ?: null;
            $published = isset($_POST['is_published']);
            $validDate = static fn (?string $date): bool => $date === null || (bool)preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) && DateTimeImmutable::createFromFormat('!Y-m-d', $date)?->format('Y-m-d') === $date;

            if ($title === '' || !$validDate($start) || !$validDate($end) || ($start && $end && $end < $start)) {
                flash('error', 'Enter a title and valid date range.');
            } else {
                if ($id > 0) {
                    db()->prepare('UPDATE announcements SET title=:title, body=:body, start_date=:start, end_date=:end, is_published=:published WHERE id=:id')
                        ->execute(['title' => $title, 'body' => $body, 'start' => $start, 'end' => $end, 'published' => $published, 'id' => $id]);
                    audit('update', 'announcements', (string)$id, null, ['title' => $title, 'is_published' => $published]);
                } else {
                    $stmt = db()->prepare('INSERT INTO announcements (title, body, start_date, end_date, is_published) VALUES (:title,:body,:start,:end,:published) RETURNING id');
                    $stmt->execute(['title' => $title, 'body' => $body, 'start' => $start, 'end' => $end, 'published' => $published]);
                    audit('create', 'announcements', (string)$stmt->fetchColumn(), null, ['title' => $title]);
                }
                flash('success', 'Announcement saved.');
            }
        }
    }

    redirect('admin/calendar.php');
}

$blocks = db()->query('SELECT id, block_date, slot, note FROM availability_blocks ORDER BY block_date, slot')->fetchAll();
$announcements = db()->query('SELECT * FROM announcements ORDER BY created_at DESC')->fetchAll();
$page_title = 'Availability';
$active = 'calendar.php';

$slotLabels = ['all' => 'All day', 'overnight' => 'Overnight', 'daycation' => 'Daycation'];

include __DIR__ . '/../includes/admin_header.php';
?>

<div class="card">
    <div class="card-head"><div><h2><?= icon('calendar') ?> Unavailable slots</h2></div></div>
    <?php if ($blocks): ?>
        <div class="amenity-list-head">
            <span>Date</span>
            <span>Slot</span>
            <span>Note</span>
            <span></span>
        </div>
        <?php foreach ($blocks as $block): ?>
            <div class="amenity-row amenity-row--blocks">
                <span><?= e($block['block_date']) ?></span>
                <span><span class="slot-badge slot-badge--<?= e($block['slot']) ?>"><?= e($slotLabels[$block['slot']] ?? $block['slot']) ?></span></span>
                <span class="text-muted-sm"><?= e($block['note']) ?></span>
                <form method="post" data-confirm="Remove this unavailable slot?">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="unblock">
                    <input type="hidden" name="id" value="<?= (int)$block['id'] ?>">
                    <button class="btn btn-danger btn-sm" type="submit"><?= icon('trash') ?> Remove</button>
                </form>
            </div>
        <?php endforeach; ?>
    <?php else: ?><p class="empty">No slots marked unavailable.</p><?php endif; ?>

    <form method="post" class="block-add-form">
        <?= csrf_field() ?><input type="hidden" name="action" value="block">
        <div class="grid-3" style="align-items:end">
            <label class="field"><span>Date</span><input type="date" name="date" required></label>
            <label class="field"><span>Note</span><input type="text" name="note" maxlength="160"></label>
        </div>
        <fieldset class="slot-picker">
            <legend>Slot</legend>
            <label><input type="radio" name="slot" value="all" checked> All day</label>
            <label><input type="radio" name="slot" value="overnight"> Overnight <small>7pm–7am</small></label>
            <label><input type="radio" name="slot" value="daycation"> Daycation <small>7am–7pm</small></label>
        </fieldset>
        <button class="btn" type="submit"><?= icon('plus') ?> Mark unavailable</button>
    </form>
</div>

<div class="card">
    <div class="card-head"><div><h2><?= icon('mail') ?> Announcements</h2></div></div>
    <?php foreach ($announcements as $a): ?>
        <form method="post" class="faq-edit">
            <?= csrf_field() ?><input type="hidden" name="action" value="announcement"><input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
            <label class="field"><span>Title</span><input type="text" name="title" value="<?= e($a['title']) ?>" maxlength="160" required></label>
            <label class="field"><span>Announcement</span><textarea name="body" maxlength="2000"><?= e($a['body']) ?></textarea></label>
            <div class="grid-3">
                <label class="field"><span>From</span><input type="date" name="start_date" value="<?= e($a['start_date'] ?? '') ?>"></label>
                <label class="field"><span>Through</span><input type="date" name="end_date" value="<?= e($a['end_date'] ?? '') ?>"></label>
                <label class="field" style="display:flex;align-items:center;gap:.5rem;padding-top:1.2rem"><input type="checkbox" name="is_published" value="1" <?= checked((bool)$a['is_published']) ?>> Published</label>
            </div>
            <div class="row-actions">
                <button class="btn btn-sm" type="submit">Save</button>
                <button class="btn btn-danger btn-sm" type="button"
                    data-delete-announcement="<?= (int)$a['id'] ?>"
                    data-csrf="<?= csrf_token() ?>"><?= icon('trash') ?> Delete</button>
            </div>
        </form>
    <?php endforeach; ?>
    <form method="post" class="faq-edit">
        <?= csrf_field() ?><input type="hidden" name="action" value="announcement">
        <label class="field"><span>Title</span><input type="text" name="title" maxlength="160" required></label>
        <label class="field"><span>Announcement</span><textarea name="body" maxlength="2000"></textarea></label>
        <div class="grid-3">
            <label class="field"><span>From</span><input type="date" name="start_date"></label>
            <label class="field"><span>Through</span><input type="date" name="end_date"></label>
            <label class="field" style="display:flex;align-items:center;gap:.5rem;padding-top:1.2rem"><input type="checkbox" name="is_published" value="1"> Publish</label>
        </div>
        <button class="btn" type="submit"><?= icon('plus') ?> Add announcement</button>
    </form>
</div>

<form id="del-announce-form" method="post" hidden>
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="delete_announcement">
    <input type="hidden" name="id" id="del-announce-id">
</form>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
