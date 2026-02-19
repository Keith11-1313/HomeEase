<<<<<<< HEAD
<?php
// ============================================================
// admin/unit_delete.php — Delete a Unit (POST only)
// ============================================================
// Receives the unit ID via POST, deletes it, logs the audit
// trail, and redirects back to the units list.
// ============================================================

require_once '../includes/auth.php';
require_role('admin');
require_once '../db_connect.php';
require_once '../includes/audit_helper.php';

// Only allow POST requests (not direct URL visits).
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: units.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);

if ($id > 0) {
    // Fetch the current unit data BEFORE deleting so we can
    // log what was removed.
    $stmt = mysqli_prepare($conn, "SELECT * FROM units WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $old = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if ($old) {
        // Delete the unit.
        $del = mysqli_prepare($conn, "DELETE FROM units WHERE id = ?");
        mysqli_stmt_bind_param($del, 'i', $id);
        mysqli_stmt_execute($del);
        mysqli_stmt_close($del);

        // Record deletion in the audit log.
        log_audit($conn, 'Delete', 'units', $id, $old, null);

        $_SESSION['flash_success'] = 'Unit #' . $old['unit_number'] . ' deleted successfully.';
    }
    else {
        $_SESSION['flash_error'] = 'Unit not found.';
    }
}
else {
    $_SESSION['flash_error'] = 'Invalid unit ID.';
}

header('Location: units.php');
exit;
=======
<?php
// ============================================================
// admin/unit_delete.php — Delete a Unit (POST only)
// ============================================================
// Receives the unit ID via POST, deletes it, logs the audit
// trail, and redirects back to the units list.
// ============================================================

require_once '../includes/auth.php';
require_role('admin');
require_once '../db_connect.php';
require_once '../includes/audit_helper.php';

// Only allow POST requests (not direct URL visits).
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: units.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);

if ($id > 0) {
    // Fetch the current unit data BEFORE deleting so we can
    // log what was removed.
    $stmt = mysqli_prepare($conn, "SELECT * FROM units WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $old = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if ($old) {
        // Delete the unit.
        $del = mysqli_prepare($conn, "DELETE FROM units WHERE id = ?");
        mysqli_stmt_bind_param($del, 'i', $id);
        mysqli_stmt_execute($del);
        mysqli_stmt_close($del);

        // Record deletion in the audit log.
        log_audit($conn, 'Delete', 'units', $id, $old, null);

        $_SESSION['flash_success'] = 'Unit #' . $old['unit_number'] . ' deleted successfully.';
    }
    else {
        $_SESSION['flash_error'] = 'Unit not found.';
    }
}
else {
    $_SESSION['flash_error'] = 'Invalid unit ID.';
}

header('Location: units.php');
exit;
>>>>>>> 047237f57cd0fd2e115602c620d70bf3bd6e29a0
