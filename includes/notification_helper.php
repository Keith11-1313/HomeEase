<<<<<<< HEAD
<?php
// ============================================================
// notification_helper.php — Notification System Helpers
// ============================================================
// Provides functions to create, fetch, count, and mark-read
// in-app notifications.  Notifications appear in the bell
// dropdown and the full notifications page.
// ============================================================


// --------------------------------------------------------
// create_notification() — Insert a new notification
// --------------------------------------------------------
// Parameters:
//   $conn    — mysqli connection
//   $user_id — who should receive this notification
//   $title   — bold headline (e.g. "Payment Overdue")
//   $message — full body text
//   $type    — 'payment', 'ticket', 'system', or 'reminder'
//   $link    — optional in-app URL (e.g. '/payments.php')
// --------------------------------------------------------
function create_notification($conn, $user_id, $title, $message, $type = 'system', $link = null)
{

    // Prepared statement to safely insert the notification.
    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO `notifications`
            (`user_id`, `title`, `message`, `type`, `link`)
         VALUES (?, ?, ?, ?, ?)"
    );

    // Bind values: i = integer, s = string.
    mysqli_stmt_bind_param($stmt, 'issss', $user_id, $title, $message, $type, $link);

    // Run the query.
    mysqli_stmt_execute($stmt);

    // Clean up.
    mysqli_stmt_close($stmt);
}


// --------------------------------------------------------
// count_unread() — How many unread notifications does this
//                  user have?
// --------------------------------------------------------
function count_unread($conn, $user_id)
{

    $stmt = mysqli_prepare(
        $conn,
        "SELECT COUNT(*) AS total
         FROM `notifications`
         WHERE `user_id` = ? AND `is_read` = 0"
    );

    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);

    // Get the result row.
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);

    // Return just the number (e.g., 3).
    return (int)$row['total'];
}


// --------------------------------------------------------
// get_notifications() — Fetch notifications for a user
// --------------------------------------------------------
// Returns the most recent $limit notifications, newest first.
// --------------------------------------------------------
function get_notifications($conn, $user_id, $limit = 20)
{

    $stmt = mysqli_prepare(
        $conn,
        "SELECT * FROM `notifications`
         WHERE `user_id` = ?
         ORDER BY `created_at` DESC
         LIMIT ?"
    );

    mysqli_stmt_bind_param($stmt, 'ii', $user_id, $limit);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    // Build a normal PHP array from the result rows so we
    // can loop through them easily in the template.
    $notifications = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $notifications[] = $row;
    }

    mysqli_stmt_close($stmt);
    return $notifications;
}


// --------------------------------------------------------
// mark_read() — Mark a single notification as read
// --------------------------------------------------------
function mark_read($conn, $notification_id, $user_id)
{

    // We include user_id in the WHERE clause so that one
    // user cannot mark another user's notifications as read.
    $stmt = mysqli_prepare(
        $conn,
        "UPDATE `notifications`
         SET `is_read` = 1
         WHERE `id` = ? AND `user_id` = ?"
    );

    mysqli_stmt_bind_param($stmt, 'ii', $notification_id, $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}


// --------------------------------------------------------
// mark_all_read() — Mark ALL of a user's notifications
//                   as read in one go.
// --------------------------------------------------------
function mark_all_read($conn, $user_id)
{

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE `notifications`
         SET `is_read` = 1
         WHERE `user_id` = ? AND `is_read` = 0"
    );

    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}
=======
<?php
// ============================================================
// notification_helper.php — Notification System Helpers
// ============================================================
// Provides functions to create, fetch, count, and mark-read
// in-app notifications.  Notifications appear in the bell
// dropdown and the full notifications page.
// ============================================================


// --------------------------------------------------------
// create_notification() — Insert a new notification
// --------------------------------------------------------
// Parameters:
//   $conn    — mysqli connection
//   $user_id — who should receive this notification
//   $title   — bold headline (e.g. "Payment Overdue")
//   $message — full body text
//   $type    — 'payment', 'ticket', 'system', or 'reminder'
//   $link    — optional in-app URL (e.g. '/payments.php')
// --------------------------------------------------------
function create_notification($conn, $user_id, $title, $message, $type = 'system', $link = null)
{

    // Prepared statement to safely insert the notification.
    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO `notifications`
            (`user_id`, `title`, `message`, `type`, `link`)
         VALUES (?, ?, ?, ?, ?)"
    );

    // Bind values: i = integer, s = string.
    mysqli_stmt_bind_param($stmt, 'issss', $user_id, $title, $message, $type, $link);

    // Run the query.
    mysqli_stmt_execute($stmt);

    // Clean up.
    mysqli_stmt_close($stmt);
}


// --------------------------------------------------------
// count_unread() — How many unread notifications does this
//                  user have?
// --------------------------------------------------------
function count_unread($conn, $user_id)
{

    $stmt = mysqli_prepare(
        $conn,
        "SELECT COUNT(*) AS total
         FROM `notifications`
         WHERE `user_id` = ? AND `is_read` = 0"
    );

    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);

    // Get the result row.
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);

    // Return just the number (e.g., 3).
    return (int)$row['total'];
}


// --------------------------------------------------------
// get_notifications() — Fetch notifications for a user
// --------------------------------------------------------
// Returns the most recent $limit notifications, newest first.
// --------------------------------------------------------
function get_notifications($conn, $user_id, $limit = 20)
{

    $stmt = mysqli_prepare(
        $conn,
        "SELECT * FROM `notifications`
         WHERE `user_id` = ?
         ORDER BY `created_at` DESC
         LIMIT ?"
    );

    mysqli_stmt_bind_param($stmt, 'ii', $user_id, $limit);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    // Build a normal PHP array from the result rows so we
    // can loop through them easily in the template.
    $notifications = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $notifications[] = $row;
    }

    mysqli_stmt_close($stmt);
    return $notifications;
}


// --------------------------------------------------------
// mark_read() — Mark a single notification as read
// --------------------------------------------------------
function mark_read($conn, $notification_id, $user_id)
{

    // We include user_id in the WHERE clause so that one
    // user cannot mark another user's notifications as read.
    $stmt = mysqli_prepare(
        $conn,
        "UPDATE `notifications`
         SET `is_read` = 1
         WHERE `id` = ? AND `user_id` = ?"
    );

    mysqli_stmt_bind_param($stmt, 'ii', $notification_id, $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}


// --------------------------------------------------------
// mark_all_read() — Mark ALL of a user's notifications
//                   as read in one go.
// --------------------------------------------------------
function mark_all_read($conn, $user_id)
{

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE `notifications`
         SET `is_read` = 1
         WHERE `user_id` = ? AND `is_read` = 0"
    );

    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}
>>>>>>> 047237f57cd0fd2e115602c620d70bf3bd6e29a0
