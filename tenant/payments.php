<?php
// ============================================================
// tenant/payments.php — Payment History + Submit New
// ============================================================
// Shows all of this tenant's payments and a button to submit
// a new payment.
// ============================================================

$page_title = 'My Payments';
require_once '../includes/auth.php';
require_role('tenant');
require_once '../db_connect.php';

$user_id = $_SESSION['user_id'];

// Fetch all payments for this tenant.
$result = mysqli_query($conn,
    "SELECT p.*, un.unit_number
     FROM payments p
     LEFT JOIN units un ON p.unit_id = un.id
     WHERE p.tenant_id = {$user_id}
     ORDER BY p.created_at DESC"
);

require_once '../includes/header.php';
?>

<div class="page-header">
    <h2>My Payments</h2>
    <a href="/Apartment Management System/tenant/payment_submit.php"
       class="btn btn-primary">
        <img src="https://cdn-icons-png.flaticon.com/512/748/748113.png" alt="Add">
        Submit Payment
    </a>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Unit</th>
                <th>Amount</th>
                <th>Method</th>
                <th>Reference</th>
                <th>Period</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($p = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo $p['id']; ?></td>
                    <td><?php echo htmlspecialchars($p['unit_number'] ?? '—'); ?></td>
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
                </tr>
                <?php
    endwhile; ?>
            <?php
else: ?>
                <tr><td colspan="8" style="text-align:center;color:var(--color-text-muted);padding:32px;">
                    No payments yet. Submit your first payment above.
                </td></tr>
            <?php
endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>
