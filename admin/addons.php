<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/bootstrap.php';
require_once __DIR__ . '/../lib/auth.php';
require_admin();

if (is_post()) {
    csrf_verify();
    $action = post('action');

    if ($action === 'save_addons') {
        $update = db()->prepare(
            'UPDATE addons SET label = :label, amount = :amount, unit_label = :unit_label, is_active = :active
              WHERE id = :id'
        );
        foreach ((array)($_POST['addon'] ?? []) as $id => $row) {
            $update->execute([
                'label'      => trim((string)($row['label'] ?? '')),
                'amount'     => max(0, (float)($row['amount'] ?? 0)),
                'unit_label' => trim((string)($row['unit_label'] ?? '')),
                'active'     => !empty($row['is_active']),
                'id'         => (int)$id,
            ]);
        }
        audit('update', 'addons');
        flash('success', 'Extras saved.');
    }

    if ($action === 'save_pool') {
        $upsert = db()->prepare(
            'INSERT INTO pool_rates (unit_id, day_type, amount)
             VALUES (:unit, :day, :amount)
             ON CONFLICT (unit_id, day_type) DO UPDATE SET amount = EXCLUDED.amount'
        );
        foreach ((array)($_POST['pool'] ?? []) as $unitId => $days) {
            foreach ((array)$days as $dayType => $amount) {
                if (!in_array($dayType, ['weekday', 'weekend_holiday'], true)) continue;
                $upsert->execute(['unit' => (int)$unitId, 'day' => $dayType, 'amount' => max(0, (float)$amount)]);
            }
        }
        audit('update', 'pool_rates');
        flash('success', 'Pool fees saved.');
    }

    if ($action === 'save_deposits') {
        $update = db()->prepare('UPDATE deposit_rules SET amount = :amount, label = :label WHERE min_hours = :hours');
        foreach ((array)($_POST['deposit'] ?? []) as $hours => $row) {
            $update->execute([
                'amount' => max(0, (float)($row['amount'] ?? 0)),
                'label'  => trim((string)($row['label'] ?? '')),
                'hours'  => (int)$hours,
            ]);
        }
        audit('update', 'deposit_rules');
        flash('success', 'Deposit tiers saved.');
    }

    if ($action === 'add_deposit') {
        $hours = (int)post('min_hours');
        if ($hours > 0) {
            try {
                db()->prepare('INSERT INTO deposit_rules (min_hours, amount, label) VALUES (:h, :a, :l)')
                    ->execute(['h' => $hours, 'a' => max(0, (float)post('amount')), 'l' => post('label')]);
                audit('create', 'deposit_rules', (string)$hours);
                flash('success', 'Deposit tier added.');
            } catch (PDOException $e) {
                flash('error', 'A tier starting at that many hours already exists.');
            }
        }
    }

    if ($action === 'delete_deposit') {
        $hours = (int)post('min_hours');
        db()->prepare('DELETE FROM deposit_rules WHERE min_hours = :h')->execute(['h' => $hours]);
        audit('delete', 'deposit_rules', (string)$hours);
        flash('success', 'Deposit tier removed.');
    }

    redirect('admin/addons.php');
}

$addons   = addons_all();
$units    = units_all();
$deposits = deposit_rules_all();

$page_title = 'Add-ons';
$active     = 'addons.php';
include __DIR__ . '/../includes/admin_header.php';
?>

<!-- ── Extras ─────────────────────────────────────────────── -->
<div class="card">
    <div class="card-head"><div><h2><?= icon('car') ?> Extras</h2></div></div>

    <?php if ($addons): ?>
    <form method="post" action="<?= e(url('admin/addons.php')) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save_addons">

        <div class="addon-list">
            <?php foreach ($addons as $addon): ?>
            <div class="addon-row">
                <div class="addon-row-head">
                    <label class="toggle">
                        <input type="checkbox" name="addon[<?= (int)$addon['id'] ?>][is_active]" value="1"
                               <?= checked((bool)$addon['is_active']) ?>>
                        <span class="toggle-track"></span>
                    </label>
                    <span class="addon-name"><?= e($addon['label']) ?></span>
                </div>
                <div class="addon-row-fields">
                    <label class="field">
                        <span>Name</span>
                        <input type="text" name="addon[<?= (int)$addon['id'] ?>][label]"
                               value="<?= e($addon['label']) ?>">
                    </label>
                    <label class="field">
                        <span>Price (₱)</span>
                        <input type="number" step="0.01" min="0"
                               name="addon[<?= (int)$addon['id'] ?>][amount]"
                               value="<?= e((string)$addon['amount']) ?>">
                    </label>
                    <label class="field">
                        <span>Unit</span>
                        <input type="text" name="addon[<?= (int)$addon['id'] ?>][unit_label]"
                               value="<?= e($addon['unit_label']) ?>"
                               placeholder="e.g. per head, per night">
                    </label>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="row-actions" style="margin-top:1.2rem">
            <button class="btn" type="submit"><?= icon('check') ?> Save extras</button>
        </div>
    </form>
    <?php else: ?>
        <p class="empty">No extras configured.</p>
    <?php endif; ?>
</div>

<!-- ── Pool fees ──────────────────────────────────────────── -->
<div class="card">
    <div class="card-head"><div><h2><?= icon('waves') ?> Pool fees</h2></div></div>

    <form method="post" action="<?= e(url('admin/addons.php')) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save_pool">

        <div class="addon-list">
            <?php foreach ($units as $unit): ?>
            <?php $map = pool_rates((int)$unit['id']); ?>
            <div class="addon-row">
                <div class="addon-row-head">
                    <span class="addon-name"><?= e($unit['name']) ?></span>
                </div>
                <div class="addon-row-fields">
                    <label class="field">
                        <span>Weekday (₱)</span>
                        <input type="number" step="0.01" min="0"
                               name="pool[<?= (int)$unit['id'] ?>][weekday]"
                               value="<?= e((string)($map['weekday'] ?? '')) ?>"
                               placeholder="—">
                    </label>
                    <label class="field">
                        <span>Weekend / holiday (₱)</span>
                        <input type="number" step="0.01" min="0"
                               name="pool[<?= (int)$unit['id'] ?>][weekend_holiday]"
                               value="<?= e((string)($map['weekend_holiday'] ?? '')) ?>"
                               placeholder="—">
                    </label>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="row-actions" style="margin-top:1.2rem">
            <button class="btn" type="submit"><?= icon('check') ?> Save pool fees</button>
        </div>
    </form>
</div>

<!-- ── Refundable deposit ──────────────────────────────────── -->
<div class="card">
    <div class="card-head"><div><h2><?= icon('shield') ?> Refundable deposit</h2></div></div>

    <?php if ($deposits): ?>
    <form method="post" action="<?= e(url('admin/addons.php')) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save_deposits">

        <div class="addon-list">
            <?php foreach ($deposits as $deposit): ?>
            <div class="addon-row">
                <div class="addon-row-head">
                    <span class="addon-name">Stay of <?= (int)$deposit['min_hours'] ?>+ hours</span>
                    <button class="btn btn-danger btn-sm" type="submit"
                            form="del-dep-<?= (int)$deposit['min_hours'] ?>"><?= icon('trash') ?> Remove</button>
                </div>
                <div class="addon-row-fields">
                    <label class="field">
                        <span>Deposit amount (₱)</span>
                        <input type="number" step="0.01" min="0"
                               name="deposit[<?= (int)$deposit['min_hours'] ?>][amount]"
                               value="<?= e((string)$deposit['amount']) ?>">
                    </label>
                    <label class="field">
                        <span>Label shown to guests</span>
                        <input type="text"
                               name="deposit[<?= (int)$deposit['min_hours'] ?>][label]"
                               value="<?= e($deposit['label']) ?>">
                    </label>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="row-actions" style="margin-top:1.2rem">
            <button class="btn" type="submit"><?= icon('check') ?> Save deposit tiers</button>
        </div>
    </form>

    <?php foreach ($deposits as $deposit): ?>
    <form id="del-dep-<?= (int)$deposit['min_hours'] ?>" method="post" action="<?= e(url('admin/addons.php')) ?>"
          data-confirm="Remove this deposit tier?">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="delete_deposit">
        <input type="hidden" name="min_hours" value="<?= (int)$deposit['min_hours'] ?>">
    </form>
    <?php endforeach; ?>
    <?php endif; ?>

    <div class="addon-add-tier">
        <h3 class="section-title"><?= icon('plus') ?> Add a tier</h3>
        <form method="post" action="<?= e(url('admin/addons.php')) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="add_deposit">
            <div class="grid-3">
                <label class="field">
                    <span>Minimum hours</span>
                    <input type="number" name="min_hours" min="1" required placeholder="e.g. 24">
                </label>
                <label class="field">
                    <span>Deposit amount (₱)</span>
                    <input type="number" name="amount" step="0.01" min="0" required>
                </label>
                <label class="field">
                    <span>Label shown to guests</span>
                    <input type="text" name="label" placeholder="e.g. Stay of 24 hours or more">
                </label>
            </div>
            <button class="btn btn-ghost" type="submit"><?= icon('plus') ?> Add tier</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
