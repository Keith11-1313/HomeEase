<?php
// ============================================================
// admin/units.php — Unit Listing & Management
// ============================================================
// Shows all units in a table with filter by status.
// Admin can add, edit, or delete units from here.
// ============================================================

$page_title = 'Manage Units';
require_once '../includes/auth.php';
require_role('admin');
require_once '../db_connect.php';

// --------------------------------------------------------
// Get optional status filter from the URL query string.
// e.g. units.php?status=Vacant
// --------------------------------------------------------
$filter_status = $_GET['status'] ?? '';

// Build the SQL query with an optional WHERE clause.
$sql = "SELECT u.*, t.first_name, t.last_name
        FROM units u
        LEFT JOIN users t ON u.tenant_id = t.id";

if ($filter_status === 'Vacant' || $filter_status === 'Occupied') {
    $sql .= " WHERE u.status = '" . mysqli_real_escape_string($conn, $filter_status) . "'";
}
$sql .= " ORDER BY u.unit_number ASC";
$result = mysqli_query($conn, $sql);

require_once '../includes/header.php';
?>

<!-- Page header with Add button -->
<div class="page-header">
    <h2>All Units</h2>
    <a href="/Apartment Management System/admin/unit_form.php"
       class="btn btn-primary">
        <img src="https://cdn-icons-png.flaticon.com/512/748/748113.png" alt="Add">
        Add Unit
    </a>
</div>

<!-- Filter bar -->
<div class="filter-bar">
    <a href="units.php"
       class="btn btn-sm <?php echo($filter_status === '') ? 'btn-primary' : 'btn-outline'; ?>">
       All
    </a>
    <a href="units.php?status=Vacant"
       class="btn btn-sm <?php echo($filter_status === 'Vacant') ? 'btn-primary' : 'btn-outline'; ?>">
       Vacant
    </a>
    <a href="units.php?status=Occupied"
       class="btn btn-sm <?php echo($filter_status === 'Occupied') ? 'btn-primary' : 'btn-outline'; ?>">
       Occupied
    </a>
</div>

<!-- Units table -->
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Unit #</th>
                <th>Type</th>
                <th>Floor</th>
                <th>Rent (PHP)</th>
                <th>Status</th>
                <th>Tenant</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($u = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($u['unit_number']); ?></strong></td>
                    <td><?php echo htmlspecialchars($u['type']); ?></td>
                    <td><?php echo $u['floor_number'] ? (int)$u['floor_number'] : '—'; ?></td>
                    <td><?php echo number_format($u['rent_price'], 2); ?></td>
                    <td>
                        <span class="badge <?php echo($u['status'] === 'Occupied') ? 'badge-success' : 'badge-warning'; ?>">
                            <?php echo $u['status']; ?>
                        </span>
                    </td>
                    <td>
                        <?php
        if ($u['first_name']) {
            echo htmlspecialchars($u['first_name'] . ' ' . $u['last_name']);
        }
        else {
            echo '<span style="color:var(--color-text-muted);">—</span>';
        }
?>
                    </td>
                    <td>
                        <div class="btn-group">
                            <!-- Edit button -->
                            <a href="unit_form.php?id=<?php echo $u['id']; ?>"
                               class="btn btn-outline btn-sm">
                                <img src="https://cdn-icons-png.flaticon.com/512/860/860814.png"
                                     alt="Edit" style="filter:invert(1);width:14px;height:14px;">
                                Edit
                            </a>
                            <!-- Delete button (triggers modal) -->
                            <form method="POST"
                                  action="unit_delete.php"
                                  style="display:inline;">
                                <input type="hidden" name="id"
                                       value="<?php echo $u['id']; ?>">
                                <button type="submit"
                                        class="btn btn-danger btn-sm delete-btn">
                                    <img src="https://cdn-icons-png.flaticon.com/512/484/484662.png"
                                         alt="Delete" style="width:14px;height:14px;">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php
    endwhile; ?>
            <?php
else: ?>
                <tr>
                    <td colspan="7" style="text-align:center;color:var(--color-text-muted);padding:32px;">
                        No units found.
                    </td>
                </tr>
            <?php
endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>
