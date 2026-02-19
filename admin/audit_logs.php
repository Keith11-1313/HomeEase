<<<<<<< HEAD
<?php
// ============================================================
// admin/audit_logs.php — View Audit Trail + PDF Export
// ============================================================
// Displays a paginated, filterable table of every UPDATE
// and DELETE action.  Includes an "Export PDF" button that
// generates a print-friendly page the admin can save as PDF.
// ============================================================

$page_title = 'Audit Logs';
require_once '../includes/auth.php';
require_role('admin');
require_once '../db_connect.php';

// --------------------------------------------------------
// FILTERS
// --------------------------------------------------------
$filter_action = $_GET['action'] ?? '';
$filter_table = $_GET['table'] ?? '';

// Build query with optional filters.
$sql = "SELECT a.*, u.first_name, u.last_name
        FROM audit_logs a
        LEFT JOIN users u ON a.user_id = u.id
        WHERE 1=1";

$params = [];
$types = '';

if ($filter_action === 'Update' || $filter_action === 'Delete') {
    $sql .= " AND a.action = ?";
    $params[] = $filter_action;
    $types .= 's';
}

if ($filter_table !== '') {
    $sql .= " AND a.table_name = ?";
    $params[] = $filter_table;
    $types .= 's';
}

$sql .= " ORDER BY a.created_at DESC";

$stmt = mysqli_prepare($conn, $sql);
if ($types !== '') {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// --------------------------------------------------------
// PDF mode: output a clean table for printing.
// --------------------------------------------------------
if (isset($_GET['pdf'])) {
?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Audit Logs — HomeEase</title>
        <style>
            body { font-family: 'Inter', Arial, sans-serif; font-size: 12px; color: #222; padding: 20px; }
            h1 { font-size: 18px; margin-bottom: 4px; }
            p { color: #666; margin-bottom: 16px; font-size: 11px; }
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #ddd; padding: 8px 10px; text-align: left; font-size: 11px; }
            th { background: #f5f5f5; font-weight: 600; }
            tr:nth-child(even) { background: #fafafa; }
            @media print { body { padding: 0; } }
        </style>
    </head>
    <body>
        <h1>HomeEase — Audit Logs Report</h1>
        <p>Generated on <?php echo date('F d, Y \a\t h:i A'); ?></p>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Table</th>
                    <th>Record</th>
                    <th>Old Values</th>
                    <th>New Values</th>
                    <th>IP</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($log = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo $log['id']; ?></td>
                    <td><?php echo htmlspecialchars(($log['first_name'] ?? 'System') . ' ' . ($log['last_name'] ?? '')); ?></td>
                    <td><?php echo $log['action']; ?></td>
                    <td><?php echo htmlspecialchars($log['table_name']); ?></td>
                    <td>#<?php echo $log['record_id']; ?></td>
                    <td><code><?php echo htmlspecialchars($log['old_values'] ?? '—'); ?></code></td>
                    <td><code><?php echo htmlspecialchars($log['new_values'] ?? '—'); ?></code></td>
                    <td><?php echo htmlspecialchars($log['ip_address'] ?? '—'); ?></td>
                    <td><?php echo date('M d, Y H:i', strtotime($log['created_at'])); ?></td>
                </tr>
                <?php
    endwhile; ?>
            </tbody>
        </table>

        <script>
            // Automatically open browser print dialog so the admin
            // can save the page as a PDF.
            window.onload = function() { window.print(); };
        </script>
    </body>
    </html>
    <?php
    exit; // don't render the dashboard layout
}

// --------------------------------------------------------
// NORMAL MODE: dashboard layout
// --------------------------------------------------------
require_once '../includes/header.php';
?>

<div class="page-header">
    <h2>Audit Logs</h2>
    <a href="audit_logs.php?<?php echo http_build_query(array_merge($_GET, ['pdf' => 1])); ?>"
       class="btn btn-outline btn-sm" target="_blank">
        <img src="https://cdn-icons-png.flaticon.com/512/337/337946.png"
             alt="PDF" style="filter:invert(1);width:16px;height:16px;">
        Export PDF
    </a>
</div>

<!-- Filter bar -->
<div class="filter-bar">
    <!-- Action filter -->
    <select onchange="applyFilter('action', this.value)" class="form-control" style="width:auto;min-width:130px;padding:8px 12px;">
        <option value="">All Actions</option>
        <option value="Update" <?php echo($filter_action === 'Update') ? 'selected' : ''; ?>>Update</option>
        <option value="Delete" <?php echo($filter_action === 'Delete') ? 'selected' : ''; ?>>Delete</option>
    </select>

    <!-- Table filter -->
    <select onchange="applyFilter('table', this.value)" class="form-control" style="width:auto;min-width:130px;padding:8px 12px;">
        <option value="">All Tables</option>
        <option value="users"    <?php echo($filter_table === 'users') ? 'selected' : ''; ?>>Users</option>
        <option value="units"    <?php echo($filter_table === 'units') ? 'selected' : ''; ?>>Units</option>
        <option value="payments" <?php echo($filter_table === 'payments') ? 'selected' : ''; ?>>Payments</option>
        <option value="tickets"  <?php echo($filter_table === 'tickets') ? 'selected' : ''; ?>>Tickets</option>
    </select>

    <?php if ($filter_action || $filter_table): ?>
        <a href="audit_logs.php" class="btn btn-outline btn-sm">Clear Filters</a>
    <?php
endif; ?>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Action</th>
                <th>Table</th>
                <th>Record</th>
                <th>Old Values</th>
                <th>New Values</th>
                <th>IP</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($log = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo $log['id']; ?></td>
                    <td><?php echo htmlspecialchars(($log['first_name'] ?? 'System') . ' ' . ($log['last_name'] ?? '')); ?></td>
                    <td>
                        <span class="badge <?php echo($log['action'] === 'Delete') ? 'badge-danger' : 'badge-info'; ?>">
                            <?php echo $log['action']; ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars($log['table_name']); ?></td>
                    <td>#<?php echo $log['record_id']; ?></td>
                    <td>
                        <code style="font-size:0.75rem;color:var(--color-text-muted);word-break:break-all;">
                            <?php echo htmlspecialchars($log['old_values'] ?? '—'); ?>
                        </code>
                    </td>
                    <td>
                        <code style="font-size:0.75rem;color:var(--color-text-muted);word-break:break-all;">
                            <?php echo htmlspecialchars($log['new_values'] ?? '—'); ?>
                        </code>
                    </td>
                    <td><?php echo htmlspecialchars($log['ip_address'] ?? '—'); ?></td>
                    <td style="white-space:nowrap;"><?php echo date('M d, Y H:i', strtotime($log['created_at'])); ?></td>
                </tr>
                <?php
    endwhile; ?>
            <?php
else: ?>
                <tr><td colspan="9" style="text-align:center;color:var(--color-text-muted);padding:32px;">No audit logs found.</td></tr>
            <?php
endif; ?>
        </tbody>
    </table>
</div>

<script>
// Helper: update URL params when filter dropdowns change.
function applyFilter(key, value) {
    var url = new URL(window.location.href);
    if (value) {
        url.searchParams.set(key, value);
    } else {
        url.searchParams.delete(key);
    }
    window.location.href = url.toString();
}
</script>

<?php require_once '../includes/footer.php'; ?>
=======
<?php
// ============================================================
// admin/audit_logs.php — View Audit Trail + PDF Export
// ============================================================
// Displays a paginated, filterable table of every UPDATE
// and DELETE action.  Includes an "Export PDF" button that
// generates a print-friendly page the admin can save as PDF.
// ============================================================

$page_title = 'Audit Logs';
require_once '../includes/auth.php';
require_role('admin');
require_once '../db_connect.php';

// --------------------------------------------------------
// FILTERS
// --------------------------------------------------------
$filter_action = $_GET['action'] ?? '';
$filter_table = $_GET['table'] ?? '';

// Build query with optional filters.
$sql = "SELECT a.*, u.first_name, u.last_name
        FROM audit_logs a
        LEFT JOIN users u ON a.user_id = u.id
        WHERE 1=1";

$params = [];
$types = '';

if ($filter_action === 'Update' || $filter_action === 'Delete') {
    $sql .= " AND a.action = ?";
    $params[] = $filter_action;
    $types .= 's';
}

if ($filter_table !== '') {
    $sql .= " AND a.table_name = ?";
    $params[] = $filter_table;
    $types .= 's';
}

$sql .= " ORDER BY a.created_at DESC";

$stmt = mysqli_prepare($conn, $sql);
if ($types !== '') {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// --------------------------------------------------------
// PDF mode: output a clean table for printing.
// --------------------------------------------------------
if (isset($_GET['pdf'])) {
?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Audit Logs — HomeEase</title>
        <style>
            body { font-family: 'Inter', Arial, sans-serif; font-size: 12px; color: #222; padding: 20px; }
            h1 { font-size: 18px; margin-bottom: 4px; }
            p { color: #666; margin-bottom: 16px; font-size: 11px; }
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #ddd; padding: 8px 10px; text-align: left; font-size: 11px; }
            th { background: #f5f5f5; font-weight: 600; }
            tr:nth-child(even) { background: #fafafa; }
            @media print { body { padding: 0; } }
        </style>
    </head>
    <body>
        <h1>HomeEase — Audit Logs Report</h1>
        <p>Generated on <?php echo date('F d, Y \a\t h:i A'); ?></p>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Table</th>
                    <th>Record</th>
                    <th>Old Values</th>
                    <th>New Values</th>
                    <th>IP</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($log = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo $log['id']; ?></td>
                    <td><?php echo htmlspecialchars(($log['first_name'] ?? 'System') . ' ' . ($log['last_name'] ?? '')); ?></td>
                    <td><?php echo $log['action']; ?></td>
                    <td><?php echo htmlspecialchars($log['table_name']); ?></td>
                    <td>#<?php echo $log['record_id']; ?></td>
                    <td><code><?php echo htmlspecialchars($log['old_values'] ?? '—'); ?></code></td>
                    <td><code><?php echo htmlspecialchars($log['new_values'] ?? '—'); ?></code></td>
                    <td><?php echo htmlspecialchars($log['ip_address'] ?? '—'); ?></td>
                    <td><?php echo date('M d, Y H:i', strtotime($log['created_at'])); ?></td>
                </tr>
                <?php
    endwhile; ?>
            </tbody>
        </table>

        <script>
            // Automatically open browser print dialog so the admin
            // can save the page as a PDF.
            window.onload = function() { window.print(); };
        </script>
    </body>
    </html>
    <?php
    exit; // don't render the dashboard layout
}

// --------------------------------------------------------
// NORMAL MODE: dashboard layout
// --------------------------------------------------------
require_once '../includes/header.php';
?>

<div class="page-header">
    <h2>Audit Logs</h2>
    <a href="audit_logs.php?<?php echo http_build_query(array_merge($_GET, ['pdf' => 1])); ?>"
       class="btn btn-outline btn-sm" target="_blank">
        <img src="https://cdn-icons-png.flaticon.com/512/337/337946.png"
             alt="PDF" style="filter:invert(1);width:16px;height:16px;">
        Export PDF
    </a>
</div>

<!-- Filter bar -->
<div class="filter-bar">
    <!-- Action filter -->
    <select onchange="applyFilter('action', this.value)" class="form-control" style="width:auto;min-width:130px;padding:8px 12px;">
        <option value="">All Actions</option>
        <option value="Update" <?php echo($filter_action === 'Update') ? 'selected' : ''; ?>>Update</option>
        <option value="Delete" <?php echo($filter_action === 'Delete') ? 'selected' : ''; ?>>Delete</option>
    </select>

    <!-- Table filter -->
    <select onchange="applyFilter('table', this.value)" class="form-control" style="width:auto;min-width:130px;padding:8px 12px;">
        <option value="">All Tables</option>
        <option value="users"    <?php echo($filter_table === 'users') ? 'selected' : ''; ?>>Users</option>
        <option value="units"    <?php echo($filter_table === 'units') ? 'selected' : ''; ?>>Units</option>
        <option value="payments" <?php echo($filter_table === 'payments') ? 'selected' : ''; ?>>Payments</option>
        <option value="tickets"  <?php echo($filter_table === 'tickets') ? 'selected' : ''; ?>>Tickets</option>
    </select>

    <?php if ($filter_action || $filter_table): ?>
        <a href="audit_logs.php" class="btn btn-outline btn-sm">Clear Filters</a>
    <?php
endif; ?>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Action</th>
                <th>Table</th>
                <th>Record</th>
                <th>Old Values</th>
                <th>New Values</th>
                <th>IP</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($log = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo $log['id']; ?></td>
                    <td><?php echo htmlspecialchars(($log['first_name'] ?? 'System') . ' ' . ($log['last_name'] ?? '')); ?></td>
                    <td>
                        <span class="badge <?php echo($log['action'] === 'Delete') ? 'badge-danger' : 'badge-info'; ?>">
                            <?php echo $log['action']; ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars($log['table_name']); ?></td>
                    <td>#<?php echo $log['record_id']; ?></td>
                    <td>
                        <code style="font-size:0.75rem;color:var(--color-text-muted);word-break:break-all;">
                            <?php echo htmlspecialchars($log['old_values'] ?? '—'); ?>
                        </code>
                    </td>
                    <td>
                        <code style="font-size:0.75rem;color:var(--color-text-muted);word-break:break-all;">
                            <?php echo htmlspecialchars($log['new_values'] ?? '—'); ?>
                        </code>
                    </td>
                    <td><?php echo htmlspecialchars($log['ip_address'] ?? '—'); ?></td>
                    <td style="white-space:nowrap;"><?php echo date('M d, Y H:i', strtotime($log['created_at'])); ?></td>
                </tr>
                <?php
    endwhile; ?>
            <?php
else: ?>
                <tr><td colspan="9" style="text-align:center;color:var(--color-text-muted);padding:32px;">No audit logs found.</td></tr>
            <?php
endif; ?>
        </tbody>
    </table>
</div>

<script>
// Helper: update URL params when filter dropdowns change.
function applyFilter(key, value) {
    var url = new URL(window.location.href);
    if (value) {
        url.searchParams.set(key, value);
    } else {
        url.searchParams.delete(key);
    }
    window.location.href = url.toString();
}
</script>

<?php require_once '../includes/footer.php'; ?>
>>>>>>> 047237f57cd0fd2e115602c620d70bf3bd6e29a0
