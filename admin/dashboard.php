<<<<<<< HEAD
<?php
// ============================================================
// admin/dashboard.php — Admin Dashboard
// ============================================================
// Shows overview stat cards (total units, occupied, vacant,
// pending payments, open tickets) and recent activity tables.
// ============================================================

// Set the page title before including the header.
$page_title = 'Dashboard';

// Load auth helpers and ensure only admins can view this page.
require_once '../includes/auth.php';
require_role('admin');

// Connect to the database.
require_once '../db_connect.php';

// --------------------------------------------------------
// QUERY: Stat counts
// --------------------------------------------------------
// Total units
$total_units = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS c FROM units")
)['c'];

// Occupied units
$occupied = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS c FROM units WHERE status = 'Occupied'")
)['c'];

// Vacant units
$vacant = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS c FROM units WHERE status = 'Vacant'")
)['c'];

// Pending payments
$pending_payments = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS c FROM payments WHERE status = 'Pending'")
)['c'];

// Open tickets (Pending + In Progress)
$open_tickets = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS c FROM tickets WHERE status != 'Resolved'")
)['c'];

// --------------------------------------------------------
// QUERY: Recent 5 payments
// --------------------------------------------------------
$recent_payments = mysqli_query($conn,
    "SELECT p.*, u.first_name, u.last_name, un.unit_number
     FROM payments p
     JOIN users u ON p.tenant_id = u.id
     JOIN units un ON p.unit_id = un.id
     ORDER BY p.created_at DESC
     LIMIT 5"
);

// --------------------------------------------------------
// QUERY: Recent 5 tickets
// --------------------------------------------------------
$recent_tickets = mysqli_query($conn,
    "SELECT t.*, u.first_name, u.last_name, un.unit_number
     FROM tickets t
     JOIN users u ON t.tenant_id = u.id
     JOIN units un ON t.unit_id = un.id
     ORDER BY t.created_at DESC
     LIMIT 5"
);

// Include the shared header (outputs HTML <head> + topbar).
require_once '../includes/header.php';
?>

<!-- ====== STAT CARDS ====== -->
<div class="stats-grid">
    <!-- Total Units -->
    <div class="stat-card">
        <div class="stat-icon blue">
            <img src="https://cdn-icons-png.flaticon.com/512/25/25694.png" alt="Units">
        </div>
        <div class="stat-info">
            <h3><?php echo $total_units; ?></h3>
            <p>Total Units</p>
        </div>
    </div>

    <!-- Occupied -->
    <div class="stat-card">
        <div class="stat-icon green">
            <img src="https://cdn-icons-png.flaticon.com/512/1077/1077063.png" alt="Occupied">
        </div>
        <div class="stat-info">
            <h3><?php echo $occupied; ?></h3>
            <p>Occupied Units</p>
        </div>
    </div>

    <!-- Vacant -->
    <div class="stat-card">
        <div class="stat-icon orange">
            <img src="https://cdn-icons-png.flaticon.com/512/25/25694.png" alt="Vacant">
        </div>
        <div class="stat-info">
            <h3><?php echo $vacant; ?></h3>
            <p>Vacant Units</p>
        </div>
    </div>

    <!-- Pending Payments -->
    <div class="stat-card">
        <div class="stat-icon red">
            <img src="https://cdn-icons-png.flaticon.com/512/138/138389.png" alt="Payments">
        </div>
        <div class="stat-info">
            <h3><?php echo $pending_payments; ?></h3>
            <p>Pending Payments</p>
        </div>
    </div>

    <!-- Open Tickets -->
    <div class="stat-card">
        <div class="stat-icon purple">
            <img src="https://cdn-icons-png.flaticon.com/512/503/503849.png" alt="Tickets">
        </div>
        <div class="stat-info">
            <h3><?php echo $open_tickets; ?></h3>
            <p>Open Tickets</p>
        </div>
    </div>
</div>


<!-- ====== RECENT PAYMENTS TABLE ====== -->
<div class="page-header">
    <h2>Recent Payments</h2>
    <a href="/Apartment Management System/admin/payments.php"
       class="btn btn-outline btn-sm">View All</a>
</div>

<div class="table-container" style="margin-bottom:40px;">
    <table>
        <thead>
            <tr>
                <th>Tenant</th>
                <th>Unit</th>
                <th>Amount</th>
                <th>Method</th>
                <th>Period</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($recent_payments) > 0): ?>
                <?php while ($p = mysqli_fetch_assoc($recent_payments)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($p['first_name'] . ' ' . $p['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($p['unit_number']); ?></td>
                    <td>PHP <?php echo number_format($p['amount'], 2); ?></td>
                    <td><?php echo htmlspecialchars($p['method']); ?></td>
                    <td><?php echo htmlspecialchars($p['period_covered'] ?? '—'); ?></td>
                    <td>
                        <?php
        // Choose badge colour based on status.
        $badge = 'badge-warning';
        if ($p['status'] === 'Verified')
            $badge = 'badge-success';
        if ($p['status'] === 'Rejected')
            $badge = 'badge-danger';
?>
                        <span class="badge <?php echo $badge; ?>">
                            <?php echo $p['status']; ?>
                        </span>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($p['payment_date'])); ?></td>
                </tr>
                <?php
    endwhile; ?>
            <?php
else: ?>
                <tr><td colspan="7" style="text-align:center;color:var(--color-text-muted);">
                    No payments yet.</td></tr>
            <?php
endif; ?>
        </tbody>
    </table>
</div>


<!-- ====== RECENT TICKETS TABLE ====== -->
<div class="page-header">
    <h2>Recent Tickets</h2>
    <a href="/Apartment Management System/admin/tickets.php"
       class="btn btn-outline btn-sm">View All</a>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Tenant</th>
                <th>Unit</th>
                <th>Subject</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Created</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($recent_tickets) > 0): ?>
                <?php while ($t = mysqli_fetch_assoc($recent_tickets)): ?>
                <tr>
                    <td>#<?php echo $t['id']; ?></td>
                    <td><?php echo htmlspecialchars($t['first_name'] . ' ' . $t['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($t['unit_number']); ?></td>
                    <td><?php echo htmlspecialchars($t['subject']); ?></td>
                    <td>
                        <?php
        $p_badge = 'badge-info';
        if ($t['priority'] === 'High')
            $p_badge = 'badge-warning';
        if ($t['priority'] === 'Urgent')
            $p_badge = 'badge-danger';
?>
                        <span class="badge <?php echo $p_badge; ?>">
                            <?php echo $t['priority']; ?>
                        </span>
                    </td>
                    <td>
                        <?php
        $s_badge = 'badge-warning';
        if ($t['status'] === 'In Progress')
            $s_badge = 'badge-info';
        if ($t['status'] === 'Resolved')
            $s_badge = 'badge-success';
?>
                        <span class="badge <?php echo $s_badge; ?>">
                            <?php echo $t['status']; ?>
                        </span>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($t['created_at'])); ?></td>
                </tr>
                <?php
    endwhile; ?>
            <?php
else: ?>
                <tr><td colspan="7" style="text-align:center;color:var(--color-text-muted);">
                    No tickets yet.</td></tr>
            <?php
endif; ?>
        </tbody>
    </table>
</div>

<?php
// Include the shared footer (closes HTML + loads JS).
require_once '../includes/footer.php';
?>
=======
<?php
// ============================================================
// admin/dashboard.php — Admin Dashboard
// ============================================================
// Shows overview stat cards (total units, occupied, vacant,
// pending payments, open tickets) and recent activity tables.
// ============================================================

// Set the page title before including the header.
$page_title = 'Dashboard';

// Load auth helpers and ensure only admins can view this page.
require_once '../includes/auth.php';
require_role('admin');

// Connect to the database.
require_once '../db_connect.php';

// --------------------------------------------------------
// QUERY: Stat counts
// --------------------------------------------------------
// Total units
$total_units = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS c FROM units")
)['c'];

// Occupied units
$occupied = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS c FROM units WHERE status = 'Occupied'")
)['c'];

// Vacant units
$vacant = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS c FROM units WHERE status = 'Vacant'")
)['c'];

// Pending payments
$pending_payments = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS c FROM payments WHERE status = 'Pending'")
)['c'];

// Open tickets (Pending + In Progress)
$open_tickets = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS c FROM tickets WHERE status != 'Resolved'")
)['c'];

// --------------------------------------------------------
// QUERY: Recent 5 payments
// --------------------------------------------------------
$recent_payments = mysqli_query($conn,
    "SELECT p.*, u.first_name, u.last_name, un.unit_number
     FROM payments p
     JOIN users u ON p.tenant_id = u.id
     JOIN units un ON p.unit_id = un.id
     ORDER BY p.created_at DESC
     LIMIT 5"
);

// --------------------------------------------------------
// QUERY: Recent 5 tickets
// --------------------------------------------------------
$recent_tickets = mysqli_query($conn,
    "SELECT t.*, u.first_name, u.last_name, un.unit_number
     FROM tickets t
     JOIN users u ON t.tenant_id = u.id
     JOIN units un ON t.unit_id = un.id
     ORDER BY t.created_at DESC
     LIMIT 5"
);

// Include the shared header (outputs HTML <head> + topbar).
require_once '../includes/header.php';
?>

<!-- ====== STAT CARDS ====== -->
<div class="stats-grid">
    <!-- Total Units -->
    <div class="stat-card">
        <div class="stat-icon blue">
            <img src="https://cdn-icons-png.flaticon.com/512/25/25694.png" alt="Units">
        </div>
        <div class="stat-info">
            <h3><?php echo $total_units; ?></h3>
            <p>Total Units</p>
        </div>
    </div>

    <!-- Occupied -->
    <div class="stat-card">
        <div class="stat-icon green">
            <img src="https://cdn-icons-png.flaticon.com/512/1077/1077063.png" alt="Occupied">
        </div>
        <div class="stat-info">
            <h3><?php echo $occupied; ?></h3>
            <p>Occupied Units</p>
        </div>
    </div>

    <!-- Vacant -->
    <div class="stat-card">
        <div class="stat-icon orange">
            <img src="https://cdn-icons-png.flaticon.com/512/25/25694.png" alt="Vacant">
        </div>
        <div class="stat-info">
            <h3><?php echo $vacant; ?></h3>
            <p>Vacant Units</p>
        </div>
    </div>

    <!-- Pending Payments -->
    <div class="stat-card">
        <div class="stat-icon red">
            <img src="https://cdn-icons-png.flaticon.com/512/138/138389.png" alt="Payments">
        </div>
        <div class="stat-info">
            <h3><?php echo $pending_payments; ?></h3>
            <p>Pending Payments</p>
        </div>
    </div>

    <!-- Open Tickets -->
    <div class="stat-card">
        <div class="stat-icon purple">
            <img src="https://cdn-icons-png.flaticon.com/512/503/503849.png" alt="Tickets">
        </div>
        <div class="stat-info">
            <h3><?php echo $open_tickets; ?></h3>
            <p>Open Tickets</p>
        </div>
    </div>
</div>


<!-- ====== RECENT PAYMENTS TABLE ====== -->
<div class="page-header">
    <h2>Recent Payments</h2>
    <a href="/Apartment Management System/admin/payments.php"
       class="btn btn-outline btn-sm">View All</a>
</div>

<div class="table-container" style="margin-bottom:40px;">
    <table>
        <thead>
            <tr>
                <th>Tenant</th>
                <th>Unit</th>
                <th>Amount</th>
                <th>Method</th>
                <th>Period</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($recent_payments) > 0): ?>
                <?php while ($p = mysqli_fetch_assoc($recent_payments)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($p['first_name'] . ' ' . $p['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($p['unit_number']); ?></td>
                    <td>PHP <?php echo number_format($p['amount'], 2); ?></td>
                    <td><?php echo htmlspecialchars($p['method']); ?></td>
                    <td><?php echo htmlspecialchars($p['period_covered'] ?? '—'); ?></td>
                    <td>
                        <?php
        // Choose badge colour based on status.
        $badge = 'badge-warning';
        if ($p['status'] === 'Verified')
            $badge = 'badge-success';
        if ($p['status'] === 'Rejected')
            $badge = 'badge-danger';
?>
                        <span class="badge <?php echo $badge; ?>">
                            <?php echo $p['status']; ?>
                        </span>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($p['payment_date'])); ?></td>
                </tr>
                <?php
    endwhile; ?>
            <?php
else: ?>
                <tr><td colspan="7" style="text-align:center;color:var(--color-text-muted);">
                    No payments yet.</td></tr>
            <?php
endif; ?>
        </tbody>
    </table>
</div>


<!-- ====== RECENT TICKETS TABLE ====== -->
<div class="page-header">
    <h2>Recent Tickets</h2>
    <a href="/Apartment Management System/admin/tickets.php"
       class="btn btn-outline btn-sm">View All</a>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Tenant</th>
                <th>Unit</th>
                <th>Subject</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Created</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($recent_tickets) > 0): ?>
                <?php while ($t = mysqli_fetch_assoc($recent_tickets)): ?>
                <tr>
                    <td>#<?php echo $t['id']; ?></td>
                    <td><?php echo htmlspecialchars($t['first_name'] . ' ' . $t['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($t['unit_number']); ?></td>
                    <td><?php echo htmlspecialchars($t['subject']); ?></td>
                    <td>
                        <?php
        $p_badge = 'badge-info';
        if ($t['priority'] === 'High')
            $p_badge = 'badge-warning';
        if ($t['priority'] === 'Urgent')
            $p_badge = 'badge-danger';
?>
                        <span class="badge <?php echo $p_badge; ?>">
                            <?php echo $t['priority']; ?>
                        </span>
                    </td>
                    <td>
                        <?php
        $s_badge = 'badge-warning';
        if ($t['status'] === 'In Progress')
            $s_badge = 'badge-info';
        if ($t['status'] === 'Resolved')
            $s_badge = 'badge-success';
?>
                        <span class="badge <?php echo $s_badge; ?>">
                            <?php echo $t['status']; ?>
                        </span>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($t['created_at'])); ?></td>
                </tr>
                <?php
    endwhile; ?>
            <?php
else: ?>
                <tr><td colspan="7" style="text-align:center;color:var(--color-text-muted);">
                    No tickets yet.</td></tr>
            <?php
endif; ?>
        </tbody>
    </table>
</div>

<?php
// Include the shared footer (closes HTML + loads JS).
require_once '../includes/footer.php';
?>
>>>>>>> 047237f57cd0fd2e115602c620d70bf3bd6e29a0
