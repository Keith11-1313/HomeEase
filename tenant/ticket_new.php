<<<<<<< HEAD
<?php
// ============================================================
// tenant/ticket_new.php — Submit a New Maintenance Ticket
// ============================================================
// Tenants describe their issue, pick a priority, and submit.
// The admin is notified.
// ============================================================

$page_title = 'New Ticket';
require_once '../includes/auth.php';
require_role('tenant');
require_once '../db_connect.php';
require_once '../includes/notification_helper.php';

$user_id = $_SESSION['user_id'];

// Get the tenant's unit.
$unit_row = mysqli_query($conn, "SELECT * FROM units WHERE tenant_id = {$user_id} LIMIT 1");
$unit = mysqli_fetch_assoc($unit_row);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $subject = trim($_POST['subject'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $priority = $_POST['priority'] ?? 'Medium';

    if (!$unit) {
        $error = 'You are not assigned to a unit. Contact the admin.';
    }
    elseif ($subject === '' || $description === '') {
        $error = 'Subject and description are required.';
    }
    else {
        $stmt = mysqli_prepare($conn,
            "INSERT INTO tickets
                (tenant_id, unit_id, subject, description, priority, status)
             VALUES (?, ?, ?, ?, ?, 'Pending')"
        );
        $unit_id = $unit['id'];
        mysqli_stmt_bind_param($stmt, 'iisss',
            $user_id, $unit_id, $subject, $description, $priority
        );

        if (mysqli_stmt_execute($stmt)) {
            // Notify all admins.
            $admins = mysqli_query($conn, "SELECT id FROM users WHERE role = 'admin'");
            while ($adm = mysqli_fetch_assoc($admins)) {
                create_notification($conn, $adm['id'],
                    'New Maintenance Ticket',
                    $_SESSION['first_name'] . ' ' . $_SESSION['last_name']
                    . " submitted: \"{$subject}\" (Priority: {$priority})",
                    'ticket',
                    '/Apartment Management System/admin/tickets.php'
                );
            }

            $_SESSION['flash_success'] = 'Ticket submitted! The admin will be notified.';
            header('Location: tickets.php');
            exit;
        }
        else {
            $error = 'Failed to submit ticket.';
        }
        mysqli_stmt_close($stmt);
    }
}

require_once '../includes/header.php';
?>

<div class="page-header">
    <h2>Submit New Ticket</h2>
    <a href="tickets.php" class="btn btn-outline btn-sm">&larr; Back</a>
</div>

<?php if ($error): ?>
    <div class="flash-message flash-error">
        <img src="https://cdn-icons-png.flaticon.com/512/399/399274.png" alt="Error">
        <?php echo htmlspecialchars($error); ?>
    </div>
<?php
endif; ?>

<?php if (!$unit): ?>
    <div class="card" style="text-align:center;padding:48px;">
        <img src="https://cdn-icons-png.flaticon.com/512/564/564619.png"
             alt="Warning" width="48" height="48" style="filter:invert(1);opacity:0.5;margin-bottom:16px;">
        <h3 style="color:var(--color-text-muted);">No Unit Assigned</h3>
        <p style="color:var(--color-text-muted);">You need to be assigned to a unit before you can file tickets.</p>
    </div>
<?php
else: ?>
<div class="card" style="max-width:600px;">
    <p style="margin-bottom:20px;color:var(--color-text-muted);font-size:0.88rem;">
        Filing for <strong>Unit <?php echo htmlspecialchars($unit['unit_number']); ?></strong>
    </p>

    <form method="POST" action="">
        <div class="form-group">
            <label for="subject">Subject *</label>
            <input type="text" id="subject" name="subject"
                   class="form-control"
                   placeholder="e.g. Leaky faucet in the kitchen"
                   value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>"
                   required>
        </div>

        <div class="form-group">
            <label for="description">Description *</label>
            <textarea id="description" name="description"
                      class="form-control"
                      placeholder="Describe the issue in detail..."
                      required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
        </div>

        <div class="form-group">
            <label for="priority">Priority</label>
            <select id="priority" name="priority" class="form-control">
                <?php
    $priorities = ['Low', 'Medium', 'High', 'Urgent'];
    $sel = $_POST['priority'] ?? 'Medium';
    foreach ($priorities as $pr):
?>
                <option value="<?php echo $pr; ?>"
                        <?php echo($sel === $pr) ? 'selected' : ''; ?>>
                    <?php echo $pr; ?>
                </option>
                <?php
    endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">
            <img src="https://cdn-icons-png.flaticon.com/512/845/845646.png" alt="Submit">
            Submit Ticket
        </button>
    </form>
</div>
<?php
endif; ?>

<?php require_once '../includes/footer.php'; ?>
=======
<?php
// ============================================================
// tenant/ticket_new.php — Submit a New Maintenance Ticket
// ============================================================
// Tenants describe their issue, pick a priority, and submit.
// The admin is notified.
// ============================================================

$page_title = 'New Ticket';
require_once '../includes/auth.php';
require_role('tenant');
require_once '../db_connect.php';
require_once '../includes/notification_helper.php';

$user_id = $_SESSION['user_id'];

// Get the tenant's unit.
$unit_row = mysqli_query($conn, "SELECT * FROM units WHERE tenant_id = {$user_id} LIMIT 1");
$unit = mysqli_fetch_assoc($unit_row);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $subject = trim($_POST['subject'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $priority = $_POST['priority'] ?? 'Medium';

    if (!$unit) {
        $error = 'You are not assigned to a unit. Contact the admin.';
    }
    elseif ($subject === '' || $description === '') {
        $error = 'Subject and description are required.';
    }
    else {
        $stmt = mysqli_prepare($conn,
            "INSERT INTO tickets
                (tenant_id, unit_id, subject, description, priority, status)
             VALUES (?, ?, ?, ?, ?, 'Pending')"
        );
        $unit_id = $unit['id'];
        mysqli_stmt_bind_param($stmt, 'iisss',
            $user_id, $unit_id, $subject, $description, $priority
        );

        if (mysqli_stmt_execute($stmt)) {
            // Notify all admins.
            $admins = mysqli_query($conn, "SELECT id FROM users WHERE role = 'admin'");
            while ($adm = mysqli_fetch_assoc($admins)) {
                create_notification($conn, $adm['id'],
                    'New Maintenance Ticket',
                    $_SESSION['first_name'] . ' ' . $_SESSION['last_name']
                    . " submitted: \"{$subject}\" (Priority: {$priority})",
                    'ticket',
                    '/Apartment Management System/admin/tickets.php'
                );
            }

            $_SESSION['flash_success'] = 'Ticket submitted! The admin will be notified.';
            header('Location: tickets.php');
            exit;
        }
        else {
            $error = 'Failed to submit ticket.';
        }
        mysqli_stmt_close($stmt);
    }
}

require_once '../includes/header.php';
?>

<div class="page-header">
    <h2>Submit New Ticket</h2>
    <a href="tickets.php" class="btn btn-outline btn-sm">&larr; Back</a>
</div>

<?php if ($error): ?>
    <div class="flash-message flash-error">
        <img src="https://cdn-icons-png.flaticon.com/512/399/399274.png" alt="Error">
        <?php echo htmlspecialchars($error); ?>
    </div>
<?php
endif; ?>

<?php if (!$unit): ?>
    <div class="card" style="text-align:center;padding:48px;">
        <img src="https://cdn-icons-png.flaticon.com/512/564/564619.png"
             alt="Warning" width="48" height="48" style="filter:invert(1);opacity:0.5;margin-bottom:16px;">
        <h3 style="color:var(--color-text-muted);">No Unit Assigned</h3>
        <p style="color:var(--color-text-muted);">You need to be assigned to a unit before you can file tickets.</p>
    </div>
<?php
else: ?>
<div class="card" style="max-width:600px;">
    <p style="margin-bottom:20px;color:var(--color-text-muted);font-size:0.88rem;">
        Filing for <strong>Unit <?php echo htmlspecialchars($unit['unit_number']); ?></strong>
    </p>

    <form method="POST" action="">
        <div class="form-group">
            <label for="subject">Subject *</label>
            <input type="text" id="subject" name="subject"
                   class="form-control"
                   placeholder="e.g. Leaky faucet in the kitchen"
                   value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>"
                   required>
        </div>

        <div class="form-group">
            <label for="description">Description *</label>
            <textarea id="description" name="description"
                      class="form-control"
                      placeholder="Describe the issue in detail..."
                      required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
        </div>

        <div class="form-group">
            <label for="priority">Priority</label>
            <select id="priority" name="priority" class="form-control">
                <?php
    $priorities = ['Low', 'Medium', 'High', 'Urgent'];
    $sel = $_POST['priority'] ?? 'Medium';
    foreach ($priorities as $pr):
?>
                <option value="<?php echo $pr; ?>"
                        <?php echo($sel === $pr) ? 'selected' : ''; ?>>
                    <?php echo $pr; ?>
                </option>
                <?php
    endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">
            <img src="https://cdn-icons-png.flaticon.com/512/845/845646.png" alt="Submit">
            Submit Ticket
        </button>
    </form>
</div>
<?php
endif; ?>

<?php require_once '../includes/footer.php'; ?>
>>>>>>> 047237f57cd0fd2e115602c620d70bf3bd6e29a0
