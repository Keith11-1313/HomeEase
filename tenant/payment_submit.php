<<<<<<< HEAD
<?php
// ============================================================
// tenant/payment_submit.php — Submit a New Payment
// ============================================================
// Tenants fill in payment details (amount, method, reference
// number, period covered) and submit.  Admin users are
// notified automatically.
// ============================================================
$page_title = 'Submit Payment';
require_once '../includes/auth.php';
require_role('tenant');
require_once '../db_connect.php';
require_once '../includes/notification_helper.php';
$user_id = $_SESSION['user_id'];
// Get the tenant's assigned unit (pre-fill amount).
$unit_row = mysqli_query($conn,
    "SELECT * FROM units WHERE tenant_id = {$user_id} LIMIT 1"
);
$unit = mysqli_fetch_assoc($unit_row);
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount     = (float)($_POST['amount'] ?? 0);
    $method     = $_POST['method'] ?? '';
    $reference  = trim($_POST['reference_number'] ?? '');
    $period     = trim($_POST['period_covered'] ?? '');
    $notes      = trim($_POST['notes'] ?? '');
    $pay_date   = $_POST['payment_date'] ?? date('Y-m-d');
    if (!$unit) {
        $error = 'You are not assigned to a unit. Contact the admin.';
    } elseif ($amount <= 0) {
        $error = 'Please enter a valid payment amount.';
    } elseif ($method === '') {
        $error = 'Please select a payment method.';
    } elseif ($reference === '') {
        $error = 'Reference number is required.';
    } elseif ($period === '') {
        $error = 'Period covered is required.';
    } else {
        $stmt = mysqli_prepare($conn,
            "INSERT INTO payments
                (tenant_id, unit_id, amount, payment_date, method,
                 reference_number, status, notes, period_covered)
             VALUES (?, ?, ?, ?, ?, ?, 'Pending', ?, ?)"
        );
        $unit_id = $unit['id'];
        mysqli_stmt_bind_param($stmt, 'iidsssss',
            $user_id, $unit_id, $amount, $pay_date,
            $method, $reference, $notes, $period
        );
        if (mysqli_stmt_execute($stmt)) {
            // Notify all admins.
            $admins = mysqli_query($conn,
                "SELECT id FROM users WHERE role = 'admin'"
            );
            while ($adm = mysqli_fetch_assoc($admins)) {
                create_notification($conn, $adm['id'],
                    'New Payment Submitted',
                    $_SESSION['first_name'] . ' ' . $_SESSION['last_name']
                    . ' submitted PHP ' . number_format($amount, 2)
                    . " for {$period}.",
                    'payment',
                    '/Apartment Management System/admin/payments.php'
                );
            }
            $_SESSION['flash_success'] = 'Payment submitted! It will be reviewed by the admin.';
            header('Location: payments.php');
            exit;
        } else {
            $error = 'Failed to submit payment.';
        }
        mysqli_stmt_close($stmt);
    }
}
require_once '../includes/header.php';
?>
<div class="page-header">
    <h2>Submit Payment</h2>
    <a href="payments.php" class="btn btn-outline btn-sm">&larr; Back</a>
</div>
<?php if ($error): ?>
    <div class="flash-message flash-error">
        <img src="https://cdn-icons-png.flaticon.com/512/399/399274.png" alt="Error">
        <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>
<?php if (!$unit): ?>
    <div class="card" style="text-align:center;padding:48px;">
        <img src="https://cdn-icons-png.flaticon.com/512/564/564619.png"
             alt="Warning" width="48" height="48"
             style="filter:invert(1);opacity:0.5;margin-bottom:16px;">
        <h3 style="color:var(--color-text-muted);">No Unit Assigned</h3>
        <p style="color:var(--color-text-muted);">
            You need to be assigned to a unit before you can submit payments.</p>
    </div>
<?php else: ?>
<div class="card" style="max-width:600px;">
    <p style="margin-bottom:20px;color:var(--color-text-muted);font-size:0.88rem;">
        Paying for <strong>Unit <?php echo htmlspecialchars($unit['unit_number']); ?></strong>
        &mdash; Monthly Rent: <strong>PHP <?php echo number_format($unit['rent_price'], 2); ?></strong>
    </p>
    <form method="POST" action="">
        <div class="form-row">
            <div class="form-group">
                <label for="amount">Amount (PHP) *</label>
                <input type="number" id="amount" name="amount"
                       class="form-control" step="0.01" min="0"
                       value="<?php echo htmlspecialchars($_POST['amount'] ?? $unit['rent_price']); ?>"
                       required>
            </div>
            <div class="form-group">
                <label for="payment_date">Payment Date *</label>
                <input type="date" id="payment_date" name="payment_date"
                       class="form-control"
                       value="<?php echo htmlspecialchars($_POST['payment_date'] ?? date('Y-m-d')); ?>"
                       required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="method">Payment Method *</label>
                <select id="method" name="method" class="form-control" required>
                    <option value="">— Select —</option>
                    <?php
                    $methods = ['Cash', 'Bank Transfer', 'GCash', 'Maya', 'Other'];
                    $sel_method = $_POST['method'] ?? '';
                    foreach ($methods as $m):
                    ?>
                    <option value="<?php echo $m; ?>"
                            <?php echo ($sel_method === $m) ? 'selected' : ''; ?>>
                        <?php echo $m; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="reference_number">Reference Number *</label>
                <input type="text" id="reference_number" name="reference_number"
                       class="form-control"
                       placeholder="e.g. GCASH-20260105-001"
                       value="<?php echo htmlspecialchars($_POST['reference_number'] ?? ''); ?>"
                       required>
            </div>
        </div>
        <div class="form-group">
            <label for="period_covered">Period Covered *</label>
            <input type="text" id="period_covered" name="period_covered"
                   class="form-control" placeholder="e.g. January 2026"
                   value="<?php echo htmlspecialchars($_POST['period_covered'] ?? ''); ?>"
                   required>
        </div>
        <div class="form-group">
            <label for="notes">Notes (optional)</label>
            <textarea id="notes" name="notes" class="form-control"
                      placeholder="Any additional notes..."
            ><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">
            <img src="https://cdn-icons-png.flaticon.com/512/845/845646.png" alt="Submit">
            Submit Payment
        </button>
    </form>
</div>
<?php endif; ?>
=======
<?php
// ============================================================
// tenant/payment_submit.php — Submit a New Payment
// ============================================================
// Tenants fill in payment details (amount, method, reference
// number, period covered) and submit.  Admin users are
// notified automatically.
// ============================================================
$page_title = 'Submit Payment';
require_once '../includes/auth.php';
require_role('tenant');
require_once '../db_connect.php';
require_once '../includes/notification_helper.php';
$user_id = $_SESSION['user_id'];
// Get the tenant's assigned unit (pre-fill amount).
$unit_row = mysqli_query($conn,
    "SELECT * FROM units WHERE tenant_id = {$user_id} LIMIT 1"
);
$unit = mysqli_fetch_assoc($unit_row);
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount     = (float)($_POST['amount'] ?? 0);
    $method     = $_POST['method'] ?? '';
    $reference  = trim($_POST['reference_number'] ?? '');
    $period     = trim($_POST['period_covered'] ?? '');
    $notes      = trim($_POST['notes'] ?? '');
    $pay_date   = $_POST['payment_date'] ?? date('Y-m-d');
    if (!$unit) {
        $error = 'You are not assigned to a unit. Contact the admin.';
    } elseif ($amount <= 0) {
        $error = 'Please enter a valid payment amount.';
    } elseif ($method === '') {
        $error = 'Please select a payment method.';
    } elseif ($reference === '') {
        $error = 'Reference number is required.';
    } elseif ($period === '') {
        $error = 'Period covered is required.';
    } else {
        $stmt = mysqli_prepare($conn,
            "INSERT INTO payments
                (tenant_id, unit_id, amount, payment_date, method,
                 reference_number, status, notes, period_covered)
             VALUES (?, ?, ?, ?, ?, ?, 'Pending', ?, ?)"
        );
        $unit_id = $unit['id'];
        mysqli_stmt_bind_param($stmt, 'iidsssss',
            $user_id, $unit_id, $amount, $pay_date,
            $method, $reference, $notes, $period
        );
        if (mysqli_stmt_execute($stmt)) {
            // Notify all admins.
            $admins = mysqli_query($conn,
                "SELECT id FROM users WHERE role = 'admin'"
            );
            while ($adm = mysqli_fetch_assoc($admins)) {
                create_notification($conn, $adm['id'],
                    'New Payment Submitted',
                    $_SESSION['first_name'] . ' ' . $_SESSION['last_name']
                    . ' submitted PHP ' . number_format($amount, 2)
                    . " for {$period}.",
                    'payment',
                    '/Apartment Management System/admin/payments.php'
                );
            }
            $_SESSION['flash_success'] = 'Payment submitted! It will be reviewed by the admin.';
            header('Location: payments.php');
            exit;
        } else {
            $error = 'Failed to submit payment.';
        }
        mysqli_stmt_close($stmt);
    }
}
require_once '../includes/header.php';
?>
<div class="page-header">
    <h2>Submit Payment</h2>
    <a href="payments.php" class="btn btn-outline btn-sm">&larr; Back</a>
</div>
<?php if ($error): ?>
    <div class="flash-message flash-error">
        <img src="https://cdn-icons-png.flaticon.com/512/399/399274.png" alt="Error">
        <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>
<?php if (!$unit): ?>
    <div class="card" style="text-align:center;padding:48px;">
        <img src="https://cdn-icons-png.flaticon.com/512/564/564619.png"
             alt="Warning" width="48" height="48"
             style="filter:invert(1);opacity:0.5;margin-bottom:16px;">
        <h3 style="color:var(--color-text-muted);">No Unit Assigned</h3>
        <p style="color:var(--color-text-muted);">
            You need to be assigned to a unit before you can submit payments.</p>
    </div>
<?php else: ?>
<div class="card" style="max-width:600px;">
    <p style="margin-bottom:20px;color:var(--color-text-muted);font-size:0.88rem;">
        Paying for <strong>Unit <?php echo htmlspecialchars($unit['unit_number']); ?></strong>
        &mdash; Monthly Rent: <strong>PHP <?php echo number_format($unit['rent_price'], 2); ?></strong>
    </p>
    <form method="POST" action="">
        <div class="form-row">
            <div class="form-group">
                <label for="amount">Amount (PHP) *</label>
                <input type="number" id="amount" name="amount"
                       class="form-control" step="0.01" min="0"
                       value="<?php echo htmlspecialchars($_POST['amount'] ?? $unit['rent_price']); ?>"
                       required>
            </div>
            <div class="form-group">
                <label for="payment_date">Payment Date *</label>
                <input type="date" id="payment_date" name="payment_date"
                       class="form-control"
                       value="<?php echo htmlspecialchars($_POST['payment_date'] ?? date('Y-m-d')); ?>"
                       required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="method">Payment Method *</label>
                <select id="method" name="method" class="form-control" required>
                    <option value="">— Select —</option>
                    <?php
                    $methods = ['Cash', 'Bank Transfer', 'GCash', 'Maya', 'Other'];
                    $sel_method = $_POST['method'] ?? '';
                    foreach ($methods as $m):
                    ?>
                    <option value="<?php echo $m; ?>"
                            <?php echo ($sel_method === $m) ? 'selected' : ''; ?>>
                        <?php echo $m; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="reference_number">Reference Number *</label>
                <input type="text" id="reference_number" name="reference_number"
                       class="form-control"
                       placeholder="e.g. GCASH-20260105-001"
                       value="<?php echo htmlspecialchars($_POST['reference_number'] ?? ''); ?>"
                       required>
            </div>
        </div>
        <div class="form-group">
            <label for="period_covered">Period Covered *</label>
            <input type="text" id="period_covered" name="period_covered"
                   class="form-control" placeholder="e.g. January 2026"
                   value="<?php echo htmlspecialchars($_POST['period_covered'] ?? ''); ?>"
                   required>
        </div>
        <div class="form-group">
            <label for="notes">Notes (optional)</label>
            <textarea id="notes" name="notes" class="form-control"
                      placeholder="Any additional notes..."
            ><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">
            <img src="https://cdn-icons-png.flaticon.com/512/845/845646.png" alt="Submit">
            Submit Payment
        </button>
    </form>
</div>
<?php endif; ?>
>>>>>>> 047237f57cd0fd2e115602c620d70bf3bd6e29a0
<?php require_once '../includes/footer.php'; ?>