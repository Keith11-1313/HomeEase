<<<<<<< HEAD
<?php
// ============================================================
// admin/unit_form.php — Add / Edit Unit
// ============================================================
// If $_GET['id'] is present, we are EDITING an existing unit.
// Otherwise, we are ADDING a new one.  The same form is used
// for both actions to keep the code DRY.
// ============================================================
require_once '../includes/auth.php';
require_role('admin');
require_once '../db_connect.php';
require_once '../includes/audit_helper.php';
// Determine if we are editing or adding.
$is_edit = isset($_GET['id']);
$unit    = null;
if ($is_edit) {
    // Fetch the existing unit from the database.
    $stmt = mysqli_prepare($conn, "SELECT * FROM units WHERE id = ?");
    $id   = (int) $_GET['id'];
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $unit = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    if (!$unit) {
        $_SESSION['flash_error'] = 'Unit not found.';
        header('Location: units.php');
        exit;
    }
}
$page_title = $is_edit ? 'Edit Unit' : 'Add Unit';
// Fetch tenants who don't currently have a unit, plus the
// currently assigned tenant (for edit mode).
$tenants_sql = "SELECT id, first_name, last_name, email
                FROM users
                WHERE role = 'tenant'
                  AND (id NOT IN (SELECT tenant_id FROM units WHERE tenant_id IS NOT NULL)";
if ($is_edit && $unit['tenant_id']) {
    $tenants_sql .= " OR id = " . (int) $unit['tenant_id'];
}
$tenants_sql .= ") ORDER BY first_name";
$tenants = mysqli_query($conn, $tenants_sql);
// --------------------------------------------------------
// HANDLE FORM SUBMISSION
// --------------------------------------------------------
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $unit_number  = trim($_POST['unit_number'] ?? '');
    $type         = $_POST['type'] ?? 'Studio';
    $description  = trim($_POST['description'] ?? '');
    $rent_price   = (float) ($_POST['rent_price'] ?? 0);
    $status       = $_POST['status'] ?? 'Vacant';
    $tenant_id    = ($_POST['tenant_id'] !== '') ? (int) $_POST['tenant_id'] : null;
    $floor_number = ($_POST['floor_number'] !== '') ? (int) $_POST['floor_number'] : null;
    // If occupied, a tenant must be assigned.
    if ($status === 'Occupied' && !$tenant_id) {
        $error = 'Please assign a tenant for an occupied unit.';
    }
    // If vacant, clear the tenant.
    if ($status === 'Vacant') {
        $tenant_id = null;
    }
    if ($unit_number === '') {
        $error = 'Unit number is required.';
    }
    if ($error === '') {
        if ($is_edit) {
            // --- UPDATE existing unit ---
            $old_values = $unit; // snapshot before changes
            $stmt = mysqli_prepare($conn,
                "UPDATE units
                 SET unit_number = ?, type = ?, description = ?,
                     rent_price = ?, status = ?, tenant_id = ?,
                     floor_number = ?
                 WHERE id = ?"
            );
            mysqli_stmt_bind_param($stmt, 'sssdsiii',
                $unit_number, $type, $description,
                $rent_price, $status, $tenant_id,
                $floor_number, $unit['id']
            );
            if (mysqli_stmt_execute($stmt)) {
                // Log the audit trail.
                log_audit($conn, 'Update', 'units', $unit['id'],
                    $old_values,
                    ['unit_number'=>$unit_number,'type'=>$type,
                     'rent_price'=>$rent_price,'status'=>$status,
                     'tenant_id'=>$tenant_id]
                );
                $_SESSION['flash_success'] = 'Unit updated successfully.';
                header('Location: units.php');
                exit;
            } else {
                $error = 'Failed to update unit: ' . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        } else {
            // --- INSERT new unit ---
            $stmt = mysqli_prepare($conn,
                "INSERT INTO units
                    (unit_number, type, description, rent_price,
                     status, tenant_id, floor_number)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            mysqli_stmt_bind_param($stmt, 'sssdsii',
                $unit_number, $type, $description,
                $rent_price, $status, $tenant_id, $floor_number
            );
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['flash_success'] = 'Unit added successfully.';
                header('Location: units.php');
                exit;
            } else {
                $error = 'Failed to add unit: ' . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
    }
}
require_once '../includes/header.php';
?>
<div class="page-header">
    <h2><?php echo $is_edit ? 'Edit Unit' : 'Add New Unit'; ?></h2>
    <a href="units.php" class="btn btn-outline btn-sm">&larr; Back to Units</a>
</div>
<?php if ($error): ?>
    <div class="flash-message flash-error">
        <img src="https://cdn-icons-png.flaticon.com/512/399/399274.png" alt="Error">
        <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>
<div class="card" style="max-width:720px;">
    <form method="POST" action="">
        <div class="form-row">
            <div class="form-group">
                <label for="unit_number">Unit Number *</label>
                <input type="text" id="unit_number" name="unit_number"
                       class="form-control" placeholder="e.g. 101"
                       value="<?php echo htmlspecialchars($unit['unit_number'] ?? $_POST['unit_number'] ?? ''); ?>"
                       required>
            </div>
            <div class="form-group">
                <label for="type">Type *</label>
                <select id="type" name="type" class="form-control">
                    <?php
                    $types = ['Studio', 'One Bedroom', 'Two Bedroom', 'Loft'];
                    $sel = $unit['type'] ?? $_POST['type'] ?? 'Studio';
                    foreach ($types as $t):
                    ?>
                    <option value="<?php echo $t; ?>"
                            <?php echo ($sel === $t) ? 'selected' : ''; ?>>
                        <?php echo $t; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description"
                      class="form-control"
                      placeholder="Short description of the unit..."
            ><?php echo htmlspecialchars($unit['description'] ?? $_POST['description'] ?? ''); ?></textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="rent_price">Monthly Rent (PHP) *</label>
                <input type="number" id="rent_price" name="rent_price"
                       class="form-control" step="0.01" min="0"
                       value="<?php echo htmlspecialchars($unit['rent_price'] ?? $_POST['rent_price'] ?? '0'); ?>"
                       required>
            </div>
            <div class="form-group">
                <label for="floor_number">Floor Number</label>
                <input type="number" id="floor_number" name="floor_number"
                       class="form-control" min="1"
                       value="<?php echo htmlspecialchars($unit['floor_number'] ?? $_POST['floor_number'] ?? ''); ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="status">Status *</label>
                <select id="status" name="status" class="form-control">
                    <?php $sel_status = $unit['status'] ?? $_POST['status'] ?? 'Vacant'; ?>
                    <option value="Vacant"   <?php echo ($sel_status === 'Vacant')   ? 'selected' : ''; ?>>Vacant</option>
                    <option value="Occupied" <?php echo ($sel_status === 'Occupied') ? 'selected' : ''; ?>>Occupied</option>
                </select>
            </div>
            <div class="form-group">
                <label for="tenant_id">Assign Tenant</label>
                <select id="tenant_id" name="tenant_id" class="form-control">
                    <option value="">— None —</option>
                    <?php
                    $sel_tenant = $unit['tenant_id'] ?? $_POST['tenant_id'] ?? '';
                    while ($tn = mysqli_fetch_assoc($tenants)):
                    ?>
                    <option value="<?php echo $tn['id']; ?>"
                            <?php echo ($sel_tenant == $tn['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($tn['first_name'] . ' ' . $tn['last_name'] . ' (' . $tn['email'] . ')'); ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
        <div style="margin-top:12px;">
            <button type="submit" class="btn btn-primary">
                <img src="https://cdn-icons-png.flaticon.com/512/845/845646.png" alt="Save">
                <?php echo $is_edit ? 'Update Unit' : 'Add Unit'; ?>
            </button>
        </div>
    </form>
</div>
=======
<?php
// ============================================================
// admin/unit_form.php — Add / Edit Unit
// ============================================================
// If $_GET['id'] is present, we are EDITING an existing unit.
// Otherwise, we are ADDING a new one.  The same form is used
// for both actions to keep the code DRY.
// ============================================================
require_once '../includes/auth.php';
require_role('admin');
require_once '../db_connect.php';
require_once '../includes/audit_helper.php';
// Determine if we are editing or adding.
$is_edit = isset($_GET['id']);
$unit    = null;
if ($is_edit) {
    // Fetch the existing unit from the database.
    $stmt = mysqli_prepare($conn, "SELECT * FROM units WHERE id = ?");
    $id   = (int) $_GET['id'];
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $unit = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    if (!$unit) {
        $_SESSION['flash_error'] = 'Unit not found.';
        header('Location: units.php');
        exit;
    }
}
$page_title = $is_edit ? 'Edit Unit' : 'Add Unit';
// Fetch tenants who don't currently have a unit, plus the
// currently assigned tenant (for edit mode).
$tenants_sql = "SELECT id, first_name, last_name, email
                FROM users
                WHERE role = 'tenant'
                  AND (id NOT IN (SELECT tenant_id FROM units WHERE tenant_id IS NOT NULL)";
if ($is_edit && $unit['tenant_id']) {
    $tenants_sql .= " OR id = " . (int) $unit['tenant_id'];
}
$tenants_sql .= ") ORDER BY first_name";
$tenants = mysqli_query($conn, $tenants_sql);
// --------------------------------------------------------
// HANDLE FORM SUBMISSION
// --------------------------------------------------------
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $unit_number  = trim($_POST['unit_number'] ?? '');
    $type         = $_POST['type'] ?? 'Studio';
    $description  = trim($_POST['description'] ?? '');
    $rent_price   = (float) ($_POST['rent_price'] ?? 0);
    $status       = $_POST['status'] ?? 'Vacant';
    $tenant_id    = ($_POST['tenant_id'] !== '') ? (int) $_POST['tenant_id'] : null;
    $floor_number = ($_POST['floor_number'] !== '') ? (int) $_POST['floor_number'] : null;
    // If occupied, a tenant must be assigned.
    if ($status === 'Occupied' && !$tenant_id) {
        $error = 'Please assign a tenant for an occupied unit.';
    }
    // If vacant, clear the tenant.
    if ($status === 'Vacant') {
        $tenant_id = null;
    }
    if ($unit_number === '') {
        $error = 'Unit number is required.';
    }
    if ($error === '') {
        if ($is_edit) {
            // --- UPDATE existing unit ---
            $old_values = $unit; // snapshot before changes
            $stmt = mysqli_prepare($conn,
                "UPDATE units
                 SET unit_number = ?, type = ?, description = ?,
                     rent_price = ?, status = ?, tenant_id = ?,
                     floor_number = ?
                 WHERE id = ?"
            );
            mysqli_stmt_bind_param($stmt, 'sssdsiii',
                $unit_number, $type, $description,
                $rent_price, $status, $tenant_id,
                $floor_number, $unit['id']
            );
            if (mysqli_stmt_execute($stmt)) {
                // Log the audit trail.
                log_audit($conn, 'Update', 'units', $unit['id'],
                    $old_values,
                    ['unit_number'=>$unit_number,'type'=>$type,
                     'rent_price'=>$rent_price,'status'=>$status,
                     'tenant_id'=>$tenant_id]
                );
                $_SESSION['flash_success'] = 'Unit updated successfully.';
                header('Location: units.php');
                exit;
            } else {
                $error = 'Failed to update unit: ' . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        } else {
            // --- INSERT new unit ---
            $stmt = mysqli_prepare($conn,
                "INSERT INTO units
                    (unit_number, type, description, rent_price,
                     status, tenant_id, floor_number)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            mysqli_stmt_bind_param($stmt, 'sssdsii',
                $unit_number, $type, $description,
                $rent_price, $status, $tenant_id, $floor_number
            );
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['flash_success'] = 'Unit added successfully.';
                header('Location: units.php');
                exit;
            } else {
                $error = 'Failed to add unit: ' . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
    }
}
require_once '../includes/header.php';
?>
<div class="page-header">
    <h2><?php echo $is_edit ? 'Edit Unit' : 'Add New Unit'; ?></h2>
    <a href="units.php" class="btn btn-outline btn-sm">&larr; Back to Units</a>
</div>
<?php if ($error): ?>
    <div class="flash-message flash-error">
        <img src="https://cdn-icons-png.flaticon.com/512/399/399274.png" alt="Error">
        <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>
<div class="card" style="max-width:720px;">
    <form method="POST" action="">
        <div class="form-row">
            <div class="form-group">
                <label for="unit_number">Unit Number *</label>
                <input type="text" id="unit_number" name="unit_number"
                       class="form-control" placeholder="e.g. 101"
                       value="<?php echo htmlspecialchars($unit['unit_number'] ?? $_POST['unit_number'] ?? ''); ?>"
                       required>
            </div>
            <div class="form-group">
                <label for="type">Type *</label>
                <select id="type" name="type" class="form-control">
                    <?php
                    $types = ['Studio', 'One Bedroom', 'Two Bedroom', 'Loft'];
                    $sel = $unit['type'] ?? $_POST['type'] ?? 'Studio';
                    foreach ($types as $t):
                    ?>
                    <option value="<?php echo $t; ?>"
                            <?php echo ($sel === $t) ? 'selected' : ''; ?>>
                        <?php echo $t; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description"
                      class="form-control"
                      placeholder="Short description of the unit..."
            ><?php echo htmlspecialchars($unit['description'] ?? $_POST['description'] ?? ''); ?></textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="rent_price">Monthly Rent (PHP) *</label>
                <input type="number" id="rent_price" name="rent_price"
                       class="form-control" step="0.01" min="0"
                       value="<?php echo htmlspecialchars($unit['rent_price'] ?? $_POST['rent_price'] ?? '0'); ?>"
                       required>
            </div>
            <div class="form-group">
                <label for="floor_number">Floor Number</label>
                <input type="number" id="floor_number" name="floor_number"
                       class="form-control" min="1"
                       value="<?php echo htmlspecialchars($unit['floor_number'] ?? $_POST['floor_number'] ?? ''); ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="status">Status *</label>
                <select id="status" name="status" class="form-control">
                    <?php $sel_status = $unit['status'] ?? $_POST['status'] ?? 'Vacant'; ?>
                    <option value="Vacant"   <?php echo ($sel_status === 'Vacant')   ? 'selected' : ''; ?>>Vacant</option>
                    <option value="Occupied" <?php echo ($sel_status === 'Occupied') ? 'selected' : ''; ?>>Occupied</option>
                </select>
            </div>
            <div class="form-group">
                <label for="tenant_id">Assign Tenant</label>
                <select id="tenant_id" name="tenant_id" class="form-control">
                    <option value="">— None —</option>
                    <?php
                    $sel_tenant = $unit['tenant_id'] ?? $_POST['tenant_id'] ?? '';
                    while ($tn = mysqli_fetch_assoc($tenants)):
                    ?>
                    <option value="<?php echo $tn['id']; ?>"
                            <?php echo ($sel_tenant == $tn['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($tn['first_name'] . ' ' . $tn['last_name'] . ' (' . $tn['email'] . ')'); ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
        <div style="margin-top:12px;">
            <button type="submit" class="btn btn-primary">
                <img src="https://cdn-icons-png.flaticon.com/512/845/845646.png" alt="Save">
                <?php echo $is_edit ? 'Update Unit' : 'Add Unit'; ?>
            </button>
        </div>
    </form>
</div>
>>>>>>> 047237f57cd0fd2e115602c620d70bf3bd6e29a0
<?php require_once '../includes/footer.php'; ?>