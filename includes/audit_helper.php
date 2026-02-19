<?php
// ============================================================
// audit_helper.php — Audit Logging Helper
// ============================================================
// Provides a single function, log_audit(), that records
// every UPDATE or DELETE action into the audit_logs table.
// Include this file whenever you need to track changes.
// ============================================================


// --------------------------------------------------------
// log_audit() — Record an action in the audit trail
// --------------------------------------------------------
// Parameters:
//   $conn      — the mysqli database connection
//   $action    — 'Update' or 'Delete'
//   $table     — name of the table that was affected
//   $record_id — the primary key (id) of the affected row
//   $old       — associative array of the OLD values
//   $new       — associative array of the NEW values
//                (pass NULL for delete actions)
// --------------------------------------------------------
function log_audit($conn, $action, $table, $record_id, $old = null, $new = null)
{

    // Grab the ID of the currently-logged-in user so we know
    // WHO performed this action.
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

    // Capture the visitor's IP address for security purposes.
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    // Convert the PHP arrays to JSON strings so they fit in
    // the database column.  json_encode() turns an array like
    // ['status' => 'Pending'] into '{"status":"Pending"}'.
    $old_json = $old ? json_encode($old) : null;
    $new_json = $new ? json_encode($new) : null;

    // Build a prepared statement to safely insert the data.
    // Prepared statements prevent SQL injection attacks by
    // separating the SQL command from the user-supplied data.
    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO `audit_logs`
            (`user_id`, `action`, `table_name`, `record_id`,
             `old_values`, `new_values`, `ip_address`)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );

    // Bind the actual values to the question-mark placeholders.
    // "sssisiss" means: string, string, string, integer,
    // string, string, string.  Actually let's use the right
    // types: i = integer, s = string.
    mysqli_stmt_bind_param(
        $stmt,
        'ississs', // i for user_id, s for action, etc.
        $user_id,
        $action,
        $table,
        $record_id,
        $old_json,
        $new_json,
        $ip
    );

    // Execute the prepared statement (send it to MySQL).
    mysqli_stmt_execute($stmt);

    // Close the statement to free up memory.
    mysqli_stmt_close($stmt);
}
