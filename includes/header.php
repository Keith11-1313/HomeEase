<?php
// ============================================================
// header.php — Top of every dashboard page
// ============================================================
// Outputs the HTML <head>, opens <body>, and renders the
// topbar (page title, notification bell, user menu).
// ============================================================

// Make sure we have session + RBAC helpers loaded.
require_once __DIR__ . '/auth.php';

// Load notification helper so we can count unread items for
// the bell badge.
require_once __DIR__ . '/notification_helper.php';

// Load database connection so we can query notification count.
require_once __DIR__ . '/../db_connect.php';

// Count unread notifications for the logged-in user.
$unread_count = 0;
if (is_logged_in()) {
    $unread_count = count_unread($conn, $_SESSION['user_id']);
}

// $page_title should be set by the calling page before
// including this header, e.g.:
//   $page_title = 'Dashboard';
//   include '../includes/header.php';
if (!isset($page_title)) {
    $page_title = 'HomeEase';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Tell the browser to use UTF-8 encoding -->
    <meta charset="UTF-8">

    <!-- Make the page scale nicely on phones and tablets -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Page title shown in the browser tab -->
    <title><?php echo htmlspecialchars($page_title); ?> — HomeEase</title>

    <!-- Meta description for SEO -->
    <meta name="description" content="HomeEase Apartment Management System — Digital ledger for small-scale apartment owners.">

    <!-- Google Fonts — Inter is loaded via CSS @import, but
         we preconnect here for faster loading -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Our global stylesheet -->
    <link rel="stylesheet" href="/Apartment Management System/assets/css/style.css">
</head>

<body>
<!-- Dark overlay that appears behind the sidebar on mobile -->
<div class="sidebar-overlay" id="sidebar-overlay"></div>

<!-- ======================================================
     APP WRAPPER — Sidebar + Main Content side by side
     ====================================================== -->
<div class="app-wrapper">
<?php
// The sidebar is included separately (sidebar.php).
// It goes right after the app-wrapper opens.
require_once __DIR__ . '/sidebar.php';
?>

    <!-- ====== MAIN CONTENT AREA ====== -->
    <div class="main-content">

        <!-- ---------- TOP BAR ---------- -->
        <header class="topbar">
            <div class="topbar-left">
                <!-- Hamburger button (visible on mobile only) -->
                <button class="hamburger-btn" id="hamburger-btn">
                    <img src="https://cdn-icons-png.flaticon.com/512/56/56763.png"
                         alt="Menu">
                </button>
                <!-- Current page title -->
                <h1><?php echo htmlspecialchars($page_title); ?></h1>
            </div>

            <div class="topbar-right">
                <!-- Notification bell -->
                <?php if (is_logged_in()): ?>
                <a class="notification-bell"
                   href="/Apartment Management System/tenant/notifications.php">
                    <img src="https://cdn-icons-png.flaticon.com/512/60/60753.png"
                         alt="Notifications">
                    <?php if ($unread_count > 0): ?>
                        <!-- Red badge showing the unread count -->
                        <span class="notification-badge">
                            <?php echo $unread_count; ?>
                        </span>
                    <?php
    endif; ?>
                </a>

                <!-- User avatar + dropdown trigger -->
                <div class="user-menu" id="user-menu">
                    <!-- Circle avatar with initials -->
                    <div class="avatar">
                        <?php
    // Show the first letter of the user's
    // first and last name (e.g., "JS").
    echo strtoupper(
        substr($_SESSION['first_name'], 0, 1)
        . substr($_SESSION['last_name'], 0, 1)
    );
?>
                    </div>
                    <div class="user-info">
                        <div class="name">
                            <?php echo htmlspecialchars(
        $_SESSION['first_name'] . ' ' . $_SESSION['last_name']
    ); ?>
                        </div>
                        <div class="role">
                            <?php echo htmlspecialchars($_SESSION['role']); ?>
                        </div>
                    </div>
                </div>

                <!-- Dropdown menu (hidden by default) -->
                <div class="user-dropdown" id="user-dropdown">
                    <?php if (is_tenant()): ?>
                    <a href="/Apartment Management System/tenant/profile.php">
                        <img src="https://cdn-icons-png.flaticon.com/512/1077/1077063.png"
                             alt="Profile">
                        My Profile
                    </a>
                    <?php
    endif; ?>
                    <div class="divider"></div>
                    <a href="/Apartment Management System/logout.php">
                        <img src="https://cdn-icons-png.flaticon.com/512/126/126467.png"
                             alt="Logout">
                        Logout
                    </a>
                </div>
                <?php
endif; ?>
            </div>
        </header>

        <!-- ---------- PAGE CONTENT ---------- -->
        <div class="page-content">
            <?php
// Display any flash messages (success, error, etc.)
// that were set on the previous page.
display_flash();
?>
