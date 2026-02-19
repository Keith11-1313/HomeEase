<?php
// ============================================================
// tenant/notifications.php — View & Manage Notifications
// ============================================================
// Shows all notifications for the logged-in user.  Includes
// "Mark as Read" and "Mark All as Read" actions.
// ============================================================

$page_title = 'Notifications';
require_once '../includes/auth.php';
require_login(); // both admin and tenant can see notifications
require_once '../db_connect.php';
require_once '../includes/notification_helper.php';

$user_id = $_SESSION['user_id'];

// --------------------------------------------------------
// Handle "mark as read" actions.
// --------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['mark_all'])) {
        mark_all_read($conn, $user_id);
        $_SESSION['flash_success'] = 'All notifications marked as read.';
        header('Location: notifications.php');
        exit;
    }
    if (isset($_POST['mark_id'])) {
        mark_read($conn, (int)$_POST['mark_id'], $user_id);
        header('Location: notifications.php');
        exit;
    }
}

// Fetch all notifications.
$notifications = get_notifications($conn, $user_id, 50);
$unread_count = count_unread($conn, $user_id);

require_once '../includes/header.php';
?>

<div class="page-header">
    <h2>Notifications
        <?php if ($unread_count > 0): ?>
            <span class="badge badge-danger" style="font-size:0.75rem;vertical-align:middle;margin-left:8px;">
                <?php echo $unread_count; ?> unread
            </span>
        <?php
endif; ?>
    </h2>
    <?php if ($unread_count > 0): ?>
    <form method="POST" action="">
        <input type="hidden" name="mark_all" value="1">
        <button type="submit" class="btn btn-outline btn-sm">
            <img src="https://cdn-icons-png.flaticon.com/512/845/845646.png"
                 alt="Read" style="filter:invert(1);width:14px;height:14px;">
            Mark All as Read
        </button>
    </form>
    <?php
endif; ?>
</div>

<?php if (!empty($notifications)): ?>
<div class="card" style="padding:0;">
    <ul class="notification-list">
        <?php foreach ($notifications as $n): ?>
        <li class="notification-item <?php echo($n['is_read'] == 0) ? 'unread' : ''; ?>">
            <div class="notif-icon">
                <?php
        // Choose icon by notification type.
        $icons = [
            'payment' => 'https://cdn-icons-png.flaticon.com/512/138/138389.png',
            'ticket' => 'https://cdn-icons-png.flaticon.com/512/503/503849.png',
            'system' => 'https://cdn-icons-png.flaticon.com/512/157/157933.png',
            'reminder' => 'https://cdn-icons-png.flaticon.com/512/564/564619.png',
        ];
        $icon = $icons[$n['type']] ?? $icons['system'];
?>
                <img src="<?php echo $icon; ?>" alt="<?php echo $n['type']; ?>">
            </div>
            <div class="notif-content">
                <h4><?php echo htmlspecialchars($n['title']); ?></h4>
                <p><?php echo htmlspecialchars($n['message']); ?></p>
            </div>
            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:6px;min-width:100px;">
                <span class="notif-time">
                    <?php echo date('M d, h:i A', strtotime($n['created_at'])); ?>
                </span>
                <?php if ($n['is_read'] == 0): ?>
                <form method="POST" action="">
                    <input type="hidden" name="mark_id" value="<?php echo $n['id']; ?>">
                    <button type="submit" class="btn btn-outline btn-sm"
                            style="font-size:0.7rem;padding:4px 10px;">
                        Mark Read
                    </button>
                </form>
                <?php
        endif; ?>
            </div>
        </li>
        <?php
    endforeach; ?>
    </ul>
</div>
<?php
else: ?>
<div class="empty-state">
    <img src="https://cdn-icons-png.flaticon.com/512/60/60753.png" alt="No notifications">
    <h3>No Notifications</h3>
    <p>You're all caught up! New notifications will appear here.</p>
</div>
<?php
endif; ?>

<?php require_once '../includes/footer.php'; ?>
