<?php
// ============================================================
// tenant/dashboard.php — Tenant Home Dashboard
// ============================================================
// Shows the tenant's unit info, next due payment, recent
// tickets, and unread notifications count.
// ============================================================

$page_title = 'Dashboard';
require_once '../includes/auth.php';
require_role('tenant');
require_once '../db_connect.php';
require_once '../includes/notification_helper.php';

$user_id = $_SESSION['user_id'];

// --------------------------------------------------------
// Get the tenant's assigned unit (if any).
// --------------------------------------------------------
$unit_result = mysqli_query($conn,
    "SELECT * FROM units WHERE tenant_id = {$user_id} LIMIT 1"
);
$unit = mysqli_fetch_assoc($unit_result);

// --------------------------------------------------------
// Count pending payments.
// --------------------------------------------------------
$pending_pay = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS c FROM payments WHERE tenant_id = {$user_id} AND status = 'Pending'")
)['c'];

// Count verified payments.
$verified_pay = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS c FROM payments WHERE tenant_id = {$user_id} AND status = 'Verified'")
)['c'];

// --------------------------------------------------------
// Count open tickets.
// --------------------------------------------------------
$open_tickets = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS c FROM tickets WHERE tenant_id = {$user_id} AND status != 'Resolved'")
)['c'];

// --------------------------------------------------------
// Unread notifications.
// --------------------------------------------------------
$unread = count_unread($conn, $user_id);

// --------------------------------------------------------
// Recent 5 payments.
// --------------------------------------------------------
$recent_payments = mysqli_query($conn,
    "SELECT p.*, un.unit_number
     FROM payments p
     LEFT JOIN units un ON p.unit_id = un.id
     WHERE p.tenant_id = {$user_id}
     ORDER BY p.created_at DESC LIMIT 5"
);

// --------------------------------------------------------
// Recent 5 tickets.
// --------------------------------------------------------
$recent_tickets = mysqli_query($conn,
    "SELECT t.*, un.unit_number
     FROM tickets t
     LEFT JOIN units un ON t.unit_id = un.id
     WHERE t.tenant_id = {$user_id}
     ORDER BY t.created_at DESC LIMIT 5"
);

require_once '../includes/header.php';
?>

<!-- Stat cards -->
<div class="stats-grid">
    <!-- My Unit -->
    <div class="stat-card">
        <div class="stat-icon blue">
            <img src="https://cdn-icons-png.flaticon.com/512/25/25694.png" alt="Unit">
        </div>
        <div class="stat-info">
            <h3><?php echo $unit ? htmlspecialchars($unit['unit_number']) : '—'; ?></h3>
            <p>My Unit</p>
        </div>
    </div>

    <!-- Pending Payments -->
    <div class="stat-card">
        <div class="stat-icon orange">
            <img src="https://cdn-icons-png.flaticon.com/512/138/138389.png" alt="Pending">
        </div>
        <div class="stat-info">
            <h3><?php echo $pending_pay; ?></h3>
            <p>Pending Payments</p>
        </div>
    </div>

    <!-- Verified Payments -->
    <div class="stat-card">
        <div class="stat-icon green">
            <img src="https://cdn-icons-png.flaticon.com/512/845/845646.png" alt="Verified">
        </div>
        <div class="stat-info">
            <h3><?php echo $verified_pay; ?></h3>
            <p>Verified Payments</p>
        </div>
    </div>

    <!-- Open Tickets -->
    <div class="stat-card">
        <div class="stat-icon red">
            <img src="https://cdn-icons-png.flaticon.com/512/503/503849.png" alt="Tickets">
        </div>
        <div class="stat-info">
            <h3><?php echo $open_tickets; ?></h3>
            <p>Open Tickets</p>
        </div>
    </div>

    <!-- Unread Notifications -->
    <div class="stat-card">
        <div class="stat-icon purple">
            <img src="https://cdn-icons-png.flaticon.com/512/60/60753.png" alt="Notifications">
        </div>
        <div class="stat-info">
            <h3><?php echo $unread; ?></h3>
            <p>Unread Notifications</p>
        </div>
    </div>
</div>

<!-- Unit info card -->
<?php if ($unit): ?>
<div class="card" style="margin-bottom:32px;">
    <h3 style="margin-bottom:16px;">My Unit Details</h3>
    <div class="form-row">
        <div>
            <p><strong>Unit Number:</strong> <?php echo htmlspecialchars($unit['unit_number']); ?></p>
            <p><strong>Type:</strong> <?php echo htmlspecialchars($unit['type']); ?></p>
        </div>
        <div>
            <p><strong>Floor:</strong> <?php echo $unit['floor_number'] ? (int)$unit['floor_number'] : '—'; ?></p>
            <p><strong>Monthly Rent:</strong> PHP <?php echo number_format($unit['rent_price'], 2); ?></p>
        </div>
    </div>
</div>
<?php
endif; ?>

<!-- Recent Payments -->
<div class="page-header">
    <h2>Recent Payments</h2>
    <a href="/Apartment Management System/tenant/payments.php" class="btn btn-outline btn-sm">View All</a>
</div>
<div class="table-container" style="margin-bottom:40px;">
    <table>
        <thead>
            <tr><th>Amount</th><th>Method</th><th>Period</th><th>Status</th><th>Date</th></tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($recent_payments) > 0): ?>
                <?php while ($p = mysqli_fetch_assoc($recent_payments)): ?>
                <tr>
                    <td>PHP <?php echo number_format($p['amount'], 2); ?></td>
                    <td><?php echo htmlspecialchars($p['method']); ?></td>
                    <td><?php echo htmlspecialchars($p['period_covered'] ?? '—'); ?></td>
                    <td>
                        <?php
        $badge = 'badge-warning';
        if ($p['status'] === 'Verified')
            $badge = 'badge-success';
        if ($p['status'] === 'Rejected')
            $badge = 'badge-danger';
?>
                        <span class="badge <?php echo $badge; ?>"><?php echo $p['status']; ?></span>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($p['payment_date'])); ?></td>
                </tr>
                <?php
    endwhile; ?>
            <?php
else: ?>
                <tr><td colspan="5" style="text-align:center;color:var(--color-text-muted);">No payments yet.</td></tr>
            <?php
endif; ?>
        </tbody>
    </table>
</div>

<!-- Recent Tickets -->
<div class="page-header">
    <h2>Recent Tickets</h2>
    <a href="/Apartment Management System/tenant/tickets.php" class="btn btn-outline btn-sm">View All</a>
</div>
<div class="table-container">
    <table>
        <thead>
            <tr><th>#</th><th>Subject</th><th>Priority</th><th>Status</th><th>Created</th></tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($recent_tickets) > 0): ?>
                <?php while ($t = mysqli_fetch_assoc($recent_tickets)): ?>
                <tr>
                    <td>#<?php echo $t['id']; ?></td>
                    <td><?php echo htmlspecialchars($t['subject']); ?></td>
                    <td>
                        <?php
        $p_badge = 'badge-info';
        if ($t['priority'] === 'High')
            $p_badge = 'badge-warning';
        if ($t['priority'] === 'Urgent')
            $p_badge = 'badge-danger';
?>
                        <span class="badge <?php echo $p_badge; ?>"><?php echo $t['priority']; ?></span>
                    </td>
                    <td>
                        <?php
        $s_badge = 'badge-warning';
        if ($t['status'] === 'In Progress')
            $s_badge = 'badge-info';
        if ($t['status'] === 'Resolved')
            $s_badge = 'badge-success';
?>
                        <span class="badge <?php echo $s_badge; ?>"><?php echo $t['status']; ?></span>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($t['created_at'])); ?></td>
                </tr>
                <?php
    endwhile; ?>
            <?php
else: ?>
                <tr><td colspan="5" style="text-align:center;color:var(--color-text-muted);">No tickets yet.</td></tr>
            <?php
endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>
