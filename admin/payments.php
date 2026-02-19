<?php
// ============================================================
// admin/payments.php — Payment Management
// ============================================================
// Lists all payments with status filter.  Admin can verify
// or reject pending payments.
// ============================================================

$page_title = 'Manage Payments';
require_once '../includes/auth.php';
require_role('admin');
require_once '../db_connect.php';

// Optional status filter.
$filter = $_GET['status'] ?? '';

$sql = "SELECT p.*, u.first_name, u.last_name, un.unit_number
        FROM payments p
        JOIN users u ON p.tenant_id = u.id
        JOIN units un ON p.unit_id = un.id";

if (in_array($filter, ['Pending', 'Verified', 'Rejected'])) {
    $sql .= " WHERE p.status = '" . mysqli_real_escape_string($conn, $filter) . "'";
}
$sql .= " ORDER BY p.created_at DESC";
$result = mysqli_query($conn, $sql);

require_once '../includes/header.php';
?>

<div class="page-header">
    <h2>All Payments</h2>
</div>

<!-- Filter bar -->
<div class="filter-bar">
    <a href="payments.php" class="btn btn-sm <?php echo($filter === '') ? 'btn-primary' : 'btn-outline'; ?>">All</a>
    <a href="payments.php?status=Pending" class="btn btn-sm <?php echo($filter === 'Pending') ? 'btn-primary' : 'btn-outline'; ?>">Pending</a>
    <a href="payments.php?status=Verified" class="btn btn-sm <?php echo($filter === 'Verified') ? 'btn-primary' : 'btn-outline'; ?>">Verified</a>
    <a href="payments.php?status=Rejected" class="btn btn-sm <?php echo($filter === 'Rejected') ? 'btn-primary' : 'btn-outline'; ?>">Rejected</a>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Tenant</th>
                <th>Unit</th>
                <th>Amount</th>
                <th>Method</th>
                <th>Reference</th>
                <th>Period</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($p = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo $p['id']; ?></td>
                    <td><?php echo htmlspecialchars($p['first_name'] . ' ' . $p['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($p['unit_number']); ?></td>
                    <td>PHP <?php echo number_format($p['amount'], 2); ?></td>
                    <td><?php echo htmlspecialchars($p['method']); ?></td>
                    <td><?php echo htmlspecialchars($p['reference_number'] ?? '—'); ?></td>
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
                    <td>
                        <?php if ($p['status'] === 'Pending'): ?>
                        <div class="btn-group">
                            <!-- Verify -->
                            <form method="POST" action="payment_action.php" style="display:inline;">
                                <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                <input type="hidden" name="action" value="Verified">
                                <button type="submit" class="btn btn-success btn-sm">
                                    <img src="https://cdn-icons-png.flaticon.com/512/845/845646.png"
                                         alt="Verify" style="width:14px;height:14px;">
                                    Verify
                                </button>
                            </form>
                            <!-- Reject -->
                            <form method="POST" action="payment_action.php" style="display:inline;">
                                <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                <input type="hidden" name="action" value="Rejected">
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <img src="https://cdn-icons-png.flaticon.com/512/399/399274.png"
                                         alt="Reject" style="width:14px;height:14px;">
                                    Reject
                                </button>
                            </form>
                        </div>
                        <?php
        else: ?>
                            <span style="color:var(--color-text-muted);font-size:0.82rem;">
                                <?php if ($p['verified_at']): ?>
                                    <?php echo date('M d, Y', strtotime($p['verified_at'])); ?>
                                <?php
            else: ?>
                                    —
                                <?php
            endif; ?>
                            </span>
                        <?php
        endif; ?>
                    </td>
                </tr>
                <?php
    endwhile; ?>
            <?php
else: ?>
                <tr><td colspan="10" style="text-align:center;color:var(--color-text-muted);padding:32px;">No payments found.</td></tr>
            <?php
endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>
