<?php
// ============================================================
// sidebar.php — Role-Aware Sidebar Navigation
// ============================================================
// Renders different navigation links depending on whether
// the logged-in user is an admin or a tenant.
// Each link uses a clean monochrome icon (line-art).
// ============================================================

// Determine which page is currently active so we can
// highlight the correct sidebar link.
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- ====== SIDEBAR ====== -->
<nav class="sidebar" id="sidebar">

    <!-- Logo area at the top of the sidebar -->
    <div class="sidebar-logo">
        <img src="https://cdn-icons-png.flaticon.com/512/25/25694.png"
             alt="HomeEase Logo"
             style="filter:invert(1);">
        <h2>Home<span>Ease</span></h2>
    </div>

    <!-- Navigation links -->
    <div class="sidebar-nav">

        <?php if (is_admin()): ?>
        <!-- =============================================
             ADMIN NAVIGATION
             ============================================= -->
        <div class="nav-label">Management</div>

        <a href="/Apartment Management System/admin/dashboard.php"
           class="<?php echo($current_page === 'dashboard.php') ? 'active' : ''; ?>">
            <img src="https://cdn-icons-png.flaticon.com/512/1828/1828791.png"
                 alt="Dashboard">
            Dashboard
        </a>

        <a href="/Apartment Management System/admin/units.php"
           class="<?php echo($current_page === 'units.php' || $current_page === 'unit_form.php') ? 'active' : ''; ?>">
            <img src="https://cdn-icons-png.flaticon.com/512/25/25694.png"
                 alt="Units">
            Units
        </a>

        <a href="/Apartment Management System/admin/payments.php"
           class="<?php echo($current_page === 'payments.php') ? 'active' : ''; ?>">
            <img src="https://cdn-icons-png.flaticon.com/512/138/138389.png"
                 alt="Payments">
            Payments
        </a>

        <a href="/Apartment Management System/admin/tickets.php"
           class="<?php echo($current_page === 'tickets.php' || $current_page === 'ticket_update.php') ? 'active' : ''; ?>">
            <img src="https://cdn-icons-png.flaticon.com/512/503/503849.png"
                 alt="Tickets">
            Tickets
        </a>

        <div class="nav-label">Reports</div>

        <a href="/Apartment Management System/admin/audit_logs.php"
           class="<?php echo($current_page === 'audit_logs.php') ? 'active' : ''; ?>">
            <img src="https://cdn-icons-png.flaticon.com/512/751/751381.png"
                 alt="Audit Logs">
            Audit Logs
        </a>

        <?php
elseif (is_tenant()): ?>
        <!-- =============================================
             TENANT NAVIGATION
             ============================================= -->
        <div class="nav-label">My Space</div>

        <a href="/Apartment Management System/tenant/dashboard.php"
           class="<?php echo($current_page === 'dashboard.php') ? 'active' : ''; ?>">
            <img src="https://cdn-icons-png.flaticon.com/512/1828/1828791.png"
                 alt="Dashboard">
            Dashboard
        </a>

        <a href="/Apartment Management System/tenant/payments.php"
           class="<?php echo($current_page === 'payments.php' || $current_page === 'payment_submit.php') ? 'active' : ''; ?>">
            <img src="https://cdn-icons-png.flaticon.com/512/138/138389.png"
                 alt="Payments">
            My Payments
        </a>

        <a href="/Apartment Management System/tenant/tickets.php"
           class="<?php echo($current_page === 'tickets.php' || $current_page === 'ticket_new.php') ? 'active' : ''; ?>">
            <img src="https://cdn-icons-png.flaticon.com/512/503/503849.png"
                 alt="Tickets">
            Maintenance
        </a>

        <div class="nav-label">Account</div>

        <a href="/Apartment Management System/tenant/profile.php"
           class="<?php echo($current_page === 'profile.php') ? 'active' : ''; ?>">
            <img src="https://cdn-icons-png.flaticon.com/512/1077/1077063.png"
                 alt="Profile">
            My Profile
        </a>

        <a href="/Apartment Management System/tenant/notifications.php"
           class="<?php echo($current_page === 'notifications.php') ? 'active' : ''; ?>">
            <img src="https://cdn-icons-png.flaticon.com/512/60/60753.png"
                 alt="Notifications">
            Notifications
        </a>

        <?php
endif; ?>
    </div>

    <!-- Logout link at the bottom of the sidebar -->
    <div class="sidebar-footer">
        <a href="/Apartment Management System/logout.php">
            <img src="https://cdn-icons-png.flaticon.com/512/126/126467.png"
                 alt="Logout"
                 style="width:18px;height:18px;filter:invert(1);opacity:0.6;">
            Logout
        </a>
    </div>
</nav>
