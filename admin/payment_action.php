<?php
// ============================================================
// admin/payment_action.php — Verify or Reject a Payment
// ============================================================
// POST handler: updates payment status, sets verified_by and
// verified_at, logs audit, and sends a notification to the
// tenant.
// ============================================================

require_once '../includes/auth.php';
require_role('admin');
require_once '../db_connect.php';
require_once '../includes/audit_helper.php';
require_once '../includes/notification_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: payments.php');
    exit;
}

$payment_id = (int)($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';

// Validate the action value.
if (!in_array($action, ['Verified', 'Rejected'])) {
    $_SESSION['flash_error'] = 'Invalid action.';
    header('Location: payments.php');
    exit;
}

if ($payment_id > 0) {

    // Fetch old payment data for the audit log.
    $stmt = mysqli_prepare($conn, "SELECT p.*, u.first_name, u.last_name, un.unit_number
                                    FROM payments p
                                    JOIN users u ON p.tenant_id = u.id
                                    JOIN units un ON p.unit_id = un.id
                                    WHERE p.id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $payment_id);
    mysqli_stmt_execute($stmt);
    $old = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$old || $old['status'] !== 'Pending') {
        $_SESSION['flash_error'] = 'Payment not found or already processed.';
        header('Location: payments.php');
        exit;
    }

    // Update the payment status.
    $admin_id = $_SESSION['user_id'];
    $now = date('Y-m-d H:i:s');

    $upd = mysqli_prepare($conn,
        "UPDATE payments SET status = ?, verified_by = ?, verified_at = ? WHERE id = ?"
    );
    mysqli_stmt_bind_param($upd, 'sisi', $action, $admin_id, $now, $payment_id);
    mysqli_stmt_execute($upd);
    mysqli_stmt_close($upd);

    // Log the audit trail.
    log_audit($conn, 'Update', 'payments', $payment_id,
    ['status' => 'Pending'],
    ['status' => $action, 'verified_by' => $admin_id, 'verified_at' => $now]
    );

    // Notify the tenant about the action.
    $amount_str = 'PHP ' . number_format($old['amount'], 2);
    $period = $old['period_covered'] ?? 'N/A';

    if ($action === 'Verified') {
        create_notification($conn, $old['tenant_id'],
            'Payment Verified',
            "Your payment of {$amount_str} for {$period} has been verified. Thank you!",
            'payment',
            '/Apartment Management System/tenant/payments.php'
        );
    }
    else {
        create_notification($conn, $old['tenant_id'],
            'Payment Rejected',
            "Your payment of {$amount_str} for {$period} was rejected. Please contact the admin for details.",
            'payment',
            '/Apartment Management System/tenant/payments.php'
        );
    }

    $_SESSION['flash_success'] = "Payment #{$payment_id} {$action} successfully.";
}
else {
    $_SESSION['flash_error'] = 'Invalid payment ID.';
}

header('Location: payments.php');
exit;
