<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/bootstrap.php';
require_once __DIR__ . '/../lib/auth.php';
require_admin();

$units = units_all();
if (!$units) {
    flash('error', 'Add a room before setting rates.');
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

    if ($action === 'save') {
        $amounts = (array)($_POST['amount'] ?? []);
        $update = db()->prepare('UPDATE rates SET amount = :amount WHERE id = :id AND unit_id = :unit');
        foreach ($amounts as $rateId => $amount) {
            if (!ctype_digit((string)$rateId) || !is_numeric($amount) || !is_finite((float)$amount) || (float)$amount < 0) {
                flash('error', 'Enter non-negative numbers for every rate amount.');
                redirect('admin/rates.php?unit=' . $unitId);
            }
            $update->execute([
                'amount' => (float)$amount,
                'id'     => (int)$rateId,
                'unit'   => $unitId,
            ]);
        }
        audit('update', 'rates', (string)$unitId);
        flash('success', 'Rates updated.');
        redirect('admin/rates.php?unit=' . $unitId);
    }

    if ($action === 'add') {
        $dayType  = post('day_type');
        $slotType = post('slot_type');
        $hours    = (int)post('hours');
        $amount   = (float)post('amount');

        if (!in_array($dayType, ['weekday', 'weekend_holiday'], true)
            || !in_array($slotType, ['overnight', 'daycation', 'flexible'], true)
            || $hours < 1 || $hours > 168
            || !is_numeric(post('amount')) || !is_finite($amount) || $amount < 0) {
            flash('error', 'Check the day type, slot, and hours.');
        } else {
            try {
                db()->prepare(
                    'INSERT INTO rates (unit_id, day_type, slot_type, hours, amount)
                     VALUES (:unit, :day, :slot, :hours, :amount)'
                )->execute([
                    'unit'   => $unitId,
                    'day'    => $dayType,
                    'slot'   => $slotType,
                    'hours'  => $hours,
                    'amount' => $amount,
                ]);
                audit('create', 'rates', (string)$unitId, null, ['day_type' => $dayType, 'hours' => $hours]);
                flash('success', 'Rate added.');
            } catch (PDOException $error) {
                flash('error', 'That room / day / slot / hours combination already exists.');
            }
        }
        redirect('admin/rates.php?unit=' . $unitId);
    }

    if ($action === 'delete') {
        $rateId = (int)post('id');
        db()->prepare('DELETE FROM rates WHERE id = :id AND unit_id = :unit')
            ->execute(['id' => $rateId, 'unit' => $unitId]);
        audit('delete', 'rates', (string)$rateId);
        flash('success', 'Rate removed.');
        redirect('admin/rates.php?unit=' . $unitId);
    }
}

$rates = unit_rates($unitId);

$page_title = 'Rates | ' . $unit['name'];
$active     = 'rates.php';
include __DIR__ . '/../includes/admin_header.php';
?>

<div class="card">
    <div class="card-head">
        <div>
            <h2><?= icon('calendar') ?> Price card</h2>
        </div>
        <form method="get" action="<?= e(url('admin/rates.php')) ?>">
            <label class="field" style="margin:0">
                <span>Room</span>
                <select name="unit" data-autosubmit>
                    <?php foreach ($units as $option): ?>
                        <option value="<?= (int)$option['id'] ?>" <?= selected($unitId, $option['id']) ?>>
                            <?= e($option['name']) ?> — <?= e($option['tagline']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
        </form>
    </div>

    <?php if ($rates): ?>
        <form method="post" action="<?= e(url('admin/rates.php?unit=' . $unitId)) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="save">

            <div class="table-wrap">
                <table class="data">
                    <thead>
                        <tr><th>Day type</th><th>Slot</th><th>Hours</th><th>Amount</th><th></th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rates as $rate): ?>
                            <tr>
                                <td><?= e($rate['day_type'] === 'weekend_holiday' ? 'Weekend / holiday' : 'Weekday') ?></td>
                                <td><?= e(ucfirst($rate['slot_type'])) ?></td>
                                <td><?= (int)$rate['hours'] ?> hrs</td>
                                <td style="width:160px">
                                    <input type="number" step="0.01" min="0"
                                           name="amount[<?= (int)$rate['id'] ?>]"
                                           value="<?= e((string)$rate['amount']) ?>"
                                           style="width:100%;padding:.4rem .5rem;border:1px solid var(--line)">
                                </td>
                                <td></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="row-actions" style="margin-top:1rem">
                <button class="btn" type="submit"><?= icon('check') ?> Save amounts</button>
            </div>
        </form>
    <?php else: ?>
        <p class="empty">No rates yet for this room.</p>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-head">
        <div><h2><?= icon('plus') ?> Add a rate</h2></div>
    </div>

    <form method="post" action="<?= e(url('admin/rates.php?unit=' . $unitId)) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add">

        <div class="grid-3">
            <label class="field">
                <span>Day type</span>
                <select name="day_type">
                    <option value="weekday">Weekday</option>
                    <option value="weekend_holiday">Weekend / holiday</option>
                </select>
            </label>
            <label class="field">
                <span>Slot</span>
                <select name="slot_type">
                    <option value="overnight">Overnight</option>
                    <option value="daycation">Daycation</option>
                    <option value="flexible">Flexible</option>
                </select>
            </label>
            <label class="field">
                <span>Hours</span>
                <input type="number" name="hours" value="12" min="1" max="168">
            </label>
        </div>

        <label class="field" style="max-width:220px">
            <span>Amount</span>
            <input type="number" name="amount" step="0.01" min="0" required>
        </label>

        <button class="btn" type="submit"><?= icon('plus') ?> Add rate</button>
    </form>
</div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
