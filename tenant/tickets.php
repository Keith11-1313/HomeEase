<<<<<<< HEAD
<?php
// ============================================================
// tenant/tickets.php — My Maintenance Tickets
// ============================================================
// Lists all tickets filed by this tenant with status badges
// and a button to create a new ticket.
// ============================================================

$page_title = 'Maintenance Tickets';
require_once '../includes/auth.php';
require_role('tenant');
require_once '../db_connect.php';

$user_id = $_SESSION['user_id'];

$result = mysqli_query($conn,
    "SELECT t.*, un.unit_number
     FROM tickets t
     LEFT JOIN units un ON t.unit_id = un.id
     WHERE t.tenant_id = {$user_id}
     ORDER BY t.created_at DESC"
);

require_once '../includes/header.php';
?>

<div class="page-header">
    <h2>My Tickets</h2>
    <a href="/Apartment Management System/tenant/ticket_new.php"
       class="btn btn-primary">
        <img src="https://cdn-icons-png.flaticon.com/512/748/748113.png" alt="New">
        New Ticket
    </a>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Subject</th>
                <th>Unit</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Created</th>
                <th>Resolved</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($t = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td>#<?php echo $t['id']; ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($t['subject']); ?></strong><br>
                        <small style="color:var(--color-text-muted);">
                            <?php echo htmlspecialchars(substr($t['description'], 0, 80)); ?>...
                        </small>
                    </td>
                    <td><?php echo htmlspecialchars($t['unit_number'] ?? '—'); ?></td>
                    <td>
                        <?php
        $p_badge = 'badge-info';
        if ($t['priority'] === 'Medium')
            $p_badge = 'badge-warning';
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
                    <td><?php echo $t['resolved_at'] ? date('M d, Y', strtotime($t['resolved_at'])) : '—'; ?></td>
                </tr>
                <?php
    endwhile; ?>
            <?php
else: ?>
                <tr><td colspan="7" style="text-align:center;color:var(--color-text-muted);padding:32px;">
                    No tickets yet. Create one if you have a maintenance issue.
                </td></tr>
            <?php
endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>
=======
<?php
// ============================================================
// tenant/tickets.php — My Maintenance Tickets
// ============================================================
// Lists all tickets filed by this tenant with status badges
// and a button to create a new ticket.
// ============================================================

$page_title = 'Maintenance Tickets';
require_once '../includes/auth.php';
require_role('tenant');
require_once '../db_connect.php';

$user_id = $_SESSION['user_id'];

$result = mysqli_query($conn,
    "SELECT t.*, un.unit_number
     FROM tickets t
     LEFT JOIN units un ON t.unit_id = un.id
     WHERE t.tenant_id = {$user_id}
     ORDER BY t.created_at DESC"
);

require_once '../includes/header.php';
?>

<div class="page-header">
    <h2>My Tickets</h2>
    <a href="/Apartment Management System/tenant/ticket_new.php"
       class="btn btn-primary">
        <img src="https://cdn-icons-png.flaticon.com/512/748/748113.png" alt="New">
        New Ticket
    </a>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Subject</th>
                <th>Unit</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Created</th>
                <th>Resolved</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($t = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td>#<?php echo $t['id']; ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($t['subject']); ?></strong><br>
                        <small style="color:var(--color-text-muted);">
                            <?php echo htmlspecialchars(substr($t['description'], 0, 80)); ?>...
                        </small>
                    </td>
                    <td><?php echo htmlspecialchars($t['unit_number'] ?? '—'); ?></td>
                    <td>
                        <?php
        $p_badge = 'badge-info';
        if ($t['priority'] === 'Medium')
            $p_badge = 'badge-warning';
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
                    <td><?php echo $t['resolved_at'] ? date('M d, Y', strtotime($t['resolved_at'])) : '—'; ?></td>
                </tr>
                <?php
    endwhile; ?>
            <?php
else: ?>
                <tr><td colspan="7" style="text-align:center;color:var(--color-text-muted);padding:32px;">
                    No tickets yet. Create one if you have a maintenance issue.
                </td></tr>
            <?php
endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>
>>>>>>> 047237f57cd0fd2e115602c620d70bf3bd6e29a0
