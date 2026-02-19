<<<<<<< HEAD
<?php
// ============================================================
// admin/tickets.php — Maintenance Ticket Management
// ============================================================
// Lists all tickets with status / priority info.  Admins can
// change status and assign tickets to themselves.
// ============================================================

$page_title = 'Manage Tickets';
require_once '../includes/auth.php';
require_role('admin');
require_once '../db_connect.php';

// Optional status filter.
$filter = $_GET['status'] ?? '';

$sql = "SELECT t.*, u.first_name, u.last_name, un.unit_number
        FROM tickets t
        JOIN users u ON t.tenant_id = u.id
        JOIN units un ON t.unit_id = un.id";

if (in_array($filter, ['Pending', 'In Progress', 'Resolved'])) {
    $sql .= " WHERE t.status = '" . mysqli_real_escape_string($conn, $filter) . "'";
}
$sql .= " ORDER BY t.created_at DESC";
$result = mysqli_query($conn, $sql);

require_once '../includes/header.php';
?>

<div class="page-header">
    <h2>All Tickets</h2>
</div>

<!-- Filter bar -->
<div class="filter-bar">
    <a href="tickets.php" class="btn btn-sm <?php echo($filter === '') ? 'btn-primary' : 'btn-outline'; ?>">All</a>
    <a href="tickets.php?status=Pending" class="btn btn-sm <?php echo($filter === 'Pending') ? 'btn-primary' : 'btn-outline'; ?>">Pending</a>
    <a href="tickets.php?status=In+Progress" class="btn btn-sm <?php echo($filter === 'In Progress') ? 'btn-primary' : 'btn-outline'; ?>">In Progress</a>
    <a href="tickets.php?status=Resolved" class="btn btn-sm <?php echo($filter === 'Resolved') ? 'btn-primary' : 'btn-outline'; ?>">Resolved</a>
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
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($t = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td>#<?php echo $t['id']; ?></td>
                    <td><?php echo htmlspecialchars($t['first_name'] . ' ' . $t['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($t['unit_number']); ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($t['subject']); ?></strong><br>
                        <small style="color:var(--color-text-muted);">
                            <?php echo htmlspecialchars(substr($t['description'], 0, 60)); ?>...
                        </small>
                    </td>
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
                    <td>
                        <?php if ($t['status'] !== 'Resolved'): ?>
                        <form method="POST" action="ticket_update.php" style="display:flex;gap:6px;align-items:center;">
                            <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
                            <select name="new_status" class="form-control" style="min-width:130px;padding:6px 10px;font-size:0.8rem;">
                                <?php if ($t['status'] === 'Pending'): ?>
                                    <option value="In Progress">In Progress</option>
                                    <option value="Resolved">Resolved</option>
                                <?php
            elseif ($t['status'] === 'In Progress'): ?>
                                    <option value="Resolved">Resolved</option>
                                    <option value="Pending">Back to Pending</option>
                                <?php
            endif; ?>
                            </select>
                            <button type="submit" class="btn btn-primary btn-sm">Update</button>
                        </form>
                        <?php
        else: ?>
                            <span style="color:var(--color-text-muted);font-size:0.82rem;">
                                <?php echo $t['resolved_at'] ? date('M d', strtotime($t['resolved_at'])) : 'Done'; ?>
                            </span>
                        <?php
        endif; ?>
                    </td>
                </tr>
                <?php
    endwhile; ?>
            <?php
else: ?>
                <tr><td colspan="8" style="text-align:center;color:var(--color-text-muted);padding:32px;">No tickets found.</td></tr>
            <?php
endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>
=======
<?php
// ============================================================
// admin/tickets.php — Maintenance Ticket Management
// ============================================================
// Lists all tickets with status / priority info.  Admins can
// change status and assign tickets to themselves.
// ============================================================

$page_title = 'Manage Tickets';
require_once '../includes/auth.php';
require_role('admin');
require_once '../db_connect.php';

// Optional status filter.
$filter = $_GET['status'] ?? '';

$sql = "SELECT t.*, u.first_name, u.last_name, un.unit_number
        FROM tickets t
        JOIN users u ON t.tenant_id = u.id
        JOIN units un ON t.unit_id = un.id";

if (in_array($filter, ['Pending', 'In Progress', 'Resolved'])) {
    $sql .= " WHERE t.status = '" . mysqli_real_escape_string($conn, $filter) . "'";
}
$sql .= " ORDER BY t.created_at DESC";
$result = mysqli_query($conn, $sql);

require_once '../includes/header.php';
?>

<div class="page-header">
    <h2>All Tickets</h2>
</div>

<!-- Filter bar -->
<div class="filter-bar">
    <a href="tickets.php" class="btn btn-sm <?php echo($filter === '') ? 'btn-primary' : 'btn-outline'; ?>">All</a>
    <a href="tickets.php?status=Pending" class="btn btn-sm <?php echo($filter === 'Pending') ? 'btn-primary' : 'btn-outline'; ?>">Pending</a>
    <a href="tickets.php?status=In+Progress" class="btn btn-sm <?php echo($filter === 'In Progress') ? 'btn-primary' : 'btn-outline'; ?>">In Progress</a>
    <a href="tickets.php?status=Resolved" class="btn btn-sm <?php echo($filter === 'Resolved') ? 'btn-primary' : 'btn-outline'; ?>">Resolved</a>
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
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($t = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td>#<?php echo $t['id']; ?></td>
                    <td><?php echo htmlspecialchars($t['first_name'] . ' ' . $t['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($t['unit_number']); ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($t['subject']); ?></strong><br>
                        <small style="color:var(--color-text-muted);">
                            <?php echo htmlspecialchars(substr($t['description'], 0, 60)); ?>...
                        </small>
                    </td>
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
                    <td>
                        <?php if ($t['status'] !== 'Resolved'): ?>
                        <form method="POST" action="ticket_update.php" style="display:flex;gap:6px;align-items:center;">
                            <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
                            <select name="new_status" class="form-control" style="min-width:130px;padding:6px 10px;font-size:0.8rem;">
                                <?php if ($t['status'] === 'Pending'): ?>
                                    <option value="In Progress">In Progress</option>
                                    <option value="Resolved">Resolved</option>
                                <?php
            elseif ($t['status'] === 'In Progress'): ?>
                                    <option value="Resolved">Resolved</option>
                                    <option value="Pending">Back to Pending</option>
                                <?php
            endif; ?>
                            </select>
                            <button type="submit" class="btn btn-primary btn-sm">Update</button>
                        </form>
                        <?php
        else: ?>
                            <span style="color:var(--color-text-muted);font-size:0.82rem;">
                                <?php echo $t['resolved_at'] ? date('M d', strtotime($t['resolved_at'])) : 'Done'; ?>
                            </span>
                        <?php
        endif; ?>
                    </td>
                </tr>
                <?php
    endwhile; ?>
            <?php
else: ?>
                <tr><td colspan="8" style="text-align:center;color:var(--color-text-muted);padding:32px;">No tickets found.</td></tr>
            <?php
endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>
>>>>>>> 047237f57cd0fd2e115602c620d70bf3bd6e29a0
