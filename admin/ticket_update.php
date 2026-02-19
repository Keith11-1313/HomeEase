<<<<<<< HEAD
<?php
// ============================================================
// admin/ticket_update.php — Change Ticket Status (POST)
// ============================================================
// Updates ticket status, inserts a ticket_update history row,
// logs to audit, and notifies the tenant.
// ============================================================

require_once '../includes/auth.php';
require_role('admin');
require_once '../db_connect.php';
require_once '../includes/audit_helper.php';
require_once '../includes/notification_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: tickets.php');
    exit;
}

$ticket_id = (int)($_POST['id'] ?? 0);
$new_status = $_POST['new_status'] ?? '';
$comment = trim($_POST['comment'] ?? '');

$valid_statuses = ['Pending', 'In Progress', 'Resolved'];
if (!in_array($new_status, $valid_statuses)) {
    $_SESSION['flash_error'] = 'Invalid status.';
    header('Location: tickets.php');
    exit;
}

if ($ticket_id > 0) {

    // Get current ticket.
    $stmt = mysqli_prepare($conn, "SELECT * FROM tickets WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $ticket_id);
    mysqli_stmt_execute($stmt);
    $ticket = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$ticket) {
        $_SESSION['flash_error'] = 'Ticket not found.';
        header('Location: tickets.php');
        exit;
    }

    $old_status = $ticket['status'];
    $admin_id = $_SESSION['user_id'];
    $resolved = ($new_status === 'Resolved') ? date('Y-m-d H:i:s') : null;

    // Update ticket.
    $upd = mysqli_prepare($conn,
        "UPDATE tickets SET status = ?, assigned_to = ?, resolved_at = ? WHERE id = ?"
    );
    mysqli_stmt_bind_param($upd, 'sisi', $new_status, $admin_id, $resolved, $ticket_id);
    mysqli_stmt_execute($upd);
    mysqli_stmt_close($upd);

    // Insert ticket_update history row.
    if ($comment === '') {
        $comment = "Status changed from {$old_status} to {$new_status}.";
    }

    $hist = mysqli_prepare($conn,
        "INSERT INTO ticket_updates (ticket_id, updated_by, old_status, new_status, comment)
         VALUES (?, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($hist, 'iisss', $ticket_id, $admin_id, $old_status, $new_status, $comment);
    mysqli_stmt_execute($hist);
    mysqli_stmt_close($hist);

    // Audit log.
    log_audit($conn, 'Update', 'tickets', $ticket_id,
    ['status' => $old_status],
    ['status' => $new_status, 'assigned_to' => $admin_id]
    );

    // Notify the tenant.
    create_notification($conn, $ticket['tenant_id'],
        'Ticket #' . $ticket_id . ' Updated',
        "Your ticket \"{$ticket['subject']}\" has been updated to: {$new_status}.",
        'ticket',
        '/Apartment Management System/tenant/tickets.php'
    );

    $_SESSION['flash_success'] = "Ticket #{$ticket_id} updated to {$new_status}.";
}
else {
    $_SESSION['flash_error'] = 'Invalid ticket ID.';
}

header('Location: tickets.php');
exit;
=======
<?php
// ============================================================
// admin/ticket_update.php — Change Ticket Status (POST)
// ============================================================
// Updates ticket status, inserts a ticket_update history row,
// logs to audit, and notifies the tenant.
// ============================================================

require_once '../includes/auth.php';
require_role('admin');
require_once '../db_connect.php';
require_once '../includes/audit_helper.php';
require_once '../includes/notification_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: tickets.php');
    exit;
}

$ticket_id = (int)($_POST['id'] ?? 0);
$new_status = $_POST['new_status'] ?? '';
$comment = trim($_POST['comment'] ?? '');

$valid_statuses = ['Pending', 'In Progress', 'Resolved'];
if (!in_array($new_status, $valid_statuses)) {
    $_SESSION['flash_error'] = 'Invalid status.';
    header('Location: tickets.php');
    exit;
}

if ($ticket_id > 0) {

    // Get current ticket.
    $stmt = mysqli_prepare($conn, "SELECT * FROM tickets WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $ticket_id);
    mysqli_stmt_execute($stmt);
    $ticket = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$ticket) {
        $_SESSION['flash_error'] = 'Ticket not found.';
        header('Location: tickets.php');
        exit;
    }

    $old_status = $ticket['status'];
    $admin_id = $_SESSION['user_id'];
    $resolved = ($new_status === 'Resolved') ? date('Y-m-d H:i:s') : null;

    // Update ticket.
    $upd = mysqli_prepare($conn,
        "UPDATE tickets SET status = ?, assigned_to = ?, resolved_at = ? WHERE id = ?"
    );
    mysqli_stmt_bind_param($upd, 'sisi', $new_status, $admin_id, $resolved, $ticket_id);
    mysqli_stmt_execute($upd);
    mysqli_stmt_close($upd);

    // Insert ticket_update history row.
    if ($comment === '') {
        $comment = "Status changed from {$old_status} to {$new_status}.";
    }

    $hist = mysqli_prepare($conn,
        "INSERT INTO ticket_updates (ticket_id, updated_by, old_status, new_status, comment)
         VALUES (?, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($hist, 'iisss', $ticket_id, $admin_id, $old_status, $new_status, $comment);
    mysqli_stmt_execute($hist);
    mysqli_stmt_close($hist);

    // Audit log.
    log_audit($conn, 'Update', 'tickets', $ticket_id,
    ['status' => $old_status],
    ['status' => $new_status, 'assigned_to' => $admin_id]
    );

    // Notify the tenant.
    create_notification($conn, $ticket['tenant_id'],
        'Ticket #' . $ticket_id . ' Updated',
        "Your ticket \"{$ticket['subject']}\" has been updated to: {$new_status}.",
        'ticket',
        '/Apartment Management System/tenant/tickets.php'
    );

    $_SESSION['flash_success'] = "Ticket #{$ticket_id} updated to {$new_status}.";
}
else {
    $_SESSION['flash_error'] = 'Invalid ticket ID.';
}

header('Location: tickets.php');
exit;
>>>>>>> 047237f57cd0fd2e115602c620d70bf3bd6e29a0
