<<<<<<< HEAD
<?php
// ============================================================
// index.php — HomeEase Landing Page (Guest / Public)
// ============================================================
// This is the first page anyone sees.  It shows a hero
// section, feature highlights, and links to browse units
// and FAQs.  No self-registration — admin creates accounts.
// ============================================================

session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HomeEase — Apartment Management System</title>
    <meta name="description"
          content="HomeEase is a digital apartment management system for small-scale owners. Browse units, manage payments, and submit maintenance tickets easily.">
    <link rel="stylesheet" href="/Apartment Management System/assets/css/style.css">
</head>
<body>

<!-- ====================================================
     NAVIGATION BAR (fixed at top)
     ==================================================== -->
<nav class="landing-nav">
    <div class="logo">
        <img src="https://cdn-icons-png.flaticon.com/512/25/25694.png"
             alt="HomeEase Logo"
             style="filter:invert(1);">
        <h2>Home<span>Ease</span></h2>
    </div>
    <ul class="nav-links">
        <li><a href="#features">Features</a></li>
        <li>
            <a href="/Apartment Management System/units_browse.php">
                Browse Units
            </a>
        </li>
        <li>
            <a href="/Apartment Management System/faq.php">
                FAQs
            </a>
        </li>

        <?php if (isset($_SESSION['user_id'])): ?>
            <!-- User is logged in — show dashboard link -->
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <li>
                    <a href="/Apartment Management System/admin/dashboard.php"
                       class="btn btn-primary btn-sm">Dashboard</a>
                </li>
            <?php
    else: ?>
                <li>
                    <a href="/Apartment Management System/tenant/dashboard.php"
                       class="btn btn-primary btn-sm">Dashboard</a>
                </li>
            <?php
    endif; ?>
        <?php
else: ?>
            <!-- Not logged in — show login only -->
            <li>
                <a href="/Apartment Management System/login.php"
                   class="btn btn-primary btn-sm">Sign In</a>
            </li>
        <?php
endif; ?>
    </ul>
</nav>


<!-- ====================================================
     HERO SECTION
     ==================================================== -->
<section class="hero">
    <div class="hero-content">
        <h1>
            Modern Apartment<br>
            Management,<br>
            <span>Made Simple.</span>
        </h1>
        <p>
            HomeEase replaces paper-based logging with a digital
            ledger.  Track payments, manage maintenance tickets,
            and keep your tenants happy — all from one dashboard.
        </p>
        <div class="hero-buttons">
            <a href="/Apartment Management System/units_browse.php"
               class="btn btn-primary">
                <img src="https://cdn-icons-png.flaticon.com/512/622/622669.png"
                     alt="Browse">
                Browse Units
            </a>
            <a href="/Apartment Management System/login.php"
               class="btn btn-outline">
                <img src="https://cdn-icons-png.flaticon.com/512/159/159616.png"
                     alt="Login"
                     style="filter:invert(1);">
                Sign In
            </a>
        </div>
    </div>
</section>


<!-- ====================================================
     FEATURES SECTION
     ==================================================== -->
<section class="section" id="features">
    <div class="section-header">
        <h2>Everything You Need</h2>
        <p>From payment tracking to maintenance requests,
           HomeEase covers the essentials of apartment management.</p>
    </div>

    <div class="features-grid">
        <!-- Feature 1: Payment Tracking -->
        <div class="feature-card">
            <div class="icon-wrap">
                <img src="https://cdn-icons-png.flaticon.com/512/138/138389.png"
                     alt="Payments">
            </div>
            <h3>Payment Tracking</h3>
            <p>Tenants submit payments digitally.  Admins verify
               each transaction and keep a complete financial
               history.</p>
        </div>

        <!-- Feature 2: Maintenance Tickets -->
        <div class="feature-card">
            <div class="icon-wrap">
                <img src="https://cdn-icons-png.flaticon.com/512/503/503849.png"
                     alt="Tickets">
            </div>
            <h3>Maintenance Tickets</h3>
            <p>Report issues with a few clicks.  Track progress
               from Pending to In Progress to Resolved — with
               full history.</p>
        </div>

        <!-- Feature 3: Unit Management -->
        <div class="feature-card">
            <div class="icon-wrap">
                <img src="https://cdn-icons-png.flaticon.com/512/25/25694.png"
                     alt="Units">
            </div>
            <h3>Unit Management</h3>
            <p>List all apartments, mark them vacant or occupied,
               and assign tenants.  Guests can browse available
               units online.</p>
        </div>

        <!-- Feature 4: Notifications -->
        <div class="feature-card">
            <div class="icon-wrap">
                <img src="https://cdn-icons-png.flaticon.com/512/60/60753.png"
                     alt="Notifications">
            </div>
            <h3>Smart Notifications</h3>
            <p>In-app reminders for overdue payments, ticket
               updates, and system announcements keep everyone
               informed.</p>
        </div>

        <!-- Feature 5: Audit Logs -->
        <div class="feature-card">
            <div class="icon-wrap">
                <img src="https://cdn-icons-png.flaticon.com/512/751/751381.png"
                     alt="Audit Logs">
            </div>
            <h3>Audit Trail</h3>
            <p>Every update and delete is logged automatically.
               View the history or export it as a PDF for your
               records.</p>
        </div>

        <!-- Feature 6: Role-Based Access -->
        <div class="feature-card">
            <div class="icon-wrap">
                <img src="https://cdn-icons-png.flaticon.com/512/1077/1077063.png"
                     alt="Roles">
            </div>
            <h3>Role-Based Access</h3>
            <p>Admins, tenants, and guests each see only what
               they need.  Secure, organised, and hassle-free.</p>
        </div>
    </div>
</section>


<!-- ====================================================
     CONTACT SECTION
     ==================================================== -->
<section class="section" style="padding-bottom:60px;">
    <div class="section-header">
        <h2>Interested?</h2>
        <p>Contact the building owner to inquire about units
           or request an account.</p>
    </div>
    <div style="text-align:center;max-width:400px;margin:0 auto;">
        <div style="padding:28px 24px;background:var(--color-bg-card);border:1px solid var(--color-border);border-radius:var(--radius-md);">
            <img src="https://cdn-icons-png.flaticon.com/512/561/561127.png"
                 alt="Email"
                 style="width:32px;height:32px;filter:invert(1);opacity:0.5;margin-bottom:12px;">
            <h3 style="margin-bottom:8px;font-size:1.1rem;">Email the Owner</h3>
            <a href="mailto:admin@homeease.com"
               style="font-size:1rem;font-weight:600;color:var(--color-accent);">
               admin@homeease.com
            </a>
        </div>
    </div>
</section>


<!-- ====================================================
     FOOTER
     ==================================================== -->
<footer class="landing-footer">
    <p>&copy; <?php echo date('Y'); ?> HomeEase.
       Apartment Management System. All rights reserved.</p>
</footer>

<script src="/Apartment Management System/assets/js/main.js"></script>
</body>
</html>
=======
<?php
// ============================================================
// index.php — HomeEase Landing Page (Guest / Public)
// ============================================================
// This is the first page anyone sees.  It shows a hero
// section, feature highlights, and links to browse units
// and FAQs.  No self-registration — admin creates accounts.
// ============================================================

session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HomeEase — Apartment Management System</title>
    <meta name="description"
          content="HomeEase is a digital apartment management system for small-scale owners. Browse units, manage payments, and submit maintenance tickets easily.">
    <link rel="stylesheet" href="/Apartment Management System/assets/css/style.css">
</head>
<body>

<!-- ====================================================
     NAVIGATION BAR (fixed at top)
     ==================================================== -->
<nav class="landing-nav">
    <div class="logo">
        <img src="https://cdn-icons-png.flaticon.com/512/25/25694.png"
             alt="HomeEase Logo"
             style="filter:invert(1);">
        <h2>Home<span>Ease</span></h2>
    </div>
    <ul class="nav-links">
        <li><a href="#features">Features</a></li>
        <li>
            <a href="/Apartment Management System/units_browse.php">
                Browse Units
            </a>
        </li>
        <li>
            <a href="/Apartment Management System/faq.php">
                FAQs
            </a>
        </li>

        <?php if (isset($_SESSION['user_id'])): ?>
            <!-- User is logged in — show dashboard link -->
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <li>
                    <a href="/Apartment Management System/admin/dashboard.php"
                       class="btn btn-primary btn-sm">Dashboard</a>
                </li>
            <?php
    else: ?>
                <li>
                    <a href="/Apartment Management System/tenant/dashboard.php"
                       class="btn btn-primary btn-sm">Dashboard</a>
                </li>
            <?php
    endif; ?>
        <?php
else: ?>
            <!-- Not logged in — show login only -->
            <li>
                <a href="/Apartment Management System/login.php"
                   class="btn btn-primary btn-sm">Sign In</a>
            </li>
        <?php
endif; ?>
    </ul>
</nav>


<!-- ====================================================
     HERO SECTION
     ==================================================== -->
<section class="hero">
    <div class="hero-content">
        <h1>
            Modern Apartment<br>
            Management,<br>
            <span>Made Simple.</span>
        </h1>
        <p>
            HomeEase replaces paper-based logging with a digital
            ledger.  Track payments, manage maintenance tickets,
            and keep your tenants happy — all from one dashboard.
        </p>
        <div class="hero-buttons">
            <a href="/Apartment Management System/units_browse.php"
               class="btn btn-primary">
                <img src="https://cdn-icons-png.flaticon.com/512/622/622669.png"
                     alt="Browse">
                Browse Units
            </a>
            <a href="/Apartment Management System/login.php"
               class="btn btn-outline">
                <img src="https://cdn-icons-png.flaticon.com/512/159/159616.png"
                     alt="Login"
                     style="filter:invert(1);">
                Sign In
            </a>
        </div>
    </div>
</section>


<!-- ====================================================
     FEATURES SECTION
     ==================================================== -->
<section class="section" id="features">
    <div class="section-header">
        <h2>Everything You Need</h2>
        <p>From payment tracking to maintenance requests,
           HomeEase covers the essentials of apartment management.</p>
    </div>

    <div class="features-grid">
        <!-- Feature 1: Payment Tracking -->
        <div class="feature-card">
            <div class="icon-wrap">
                <img src="https://cdn-icons-png.flaticon.com/512/138/138389.png"
                     alt="Payments">
            </div>
            <h3>Payment Tracking</h3>
            <p>Tenants submit payments digitally.  Admins verify
               each transaction and keep a complete financial
               history.</p>
        </div>

        <!-- Feature 2: Maintenance Tickets -->
        <div class="feature-card">
            <div class="icon-wrap">
                <img src="https://cdn-icons-png.flaticon.com/512/503/503849.png"
                     alt="Tickets">
            </div>
            <h3>Maintenance Tickets</h3>
            <p>Report issues with a few clicks.  Track progress
               from Pending to In Progress to Resolved — with
               full history.</p>
        </div>

        <!-- Feature 3: Unit Management -->
        <div class="feature-card">
            <div class="icon-wrap">
                <img src="https://cdn-icons-png.flaticon.com/512/25/25694.png"
                     alt="Units">
            </div>
            <h3>Unit Management</h3>
            <p>List all apartments, mark them vacant or occupied,
               and assign tenants.  Guests can browse available
               units online.</p>
        </div>

        <!-- Feature 4: Notifications -->
        <div class="feature-card">
            <div class="icon-wrap">
                <img src="https://cdn-icons-png.flaticon.com/512/60/60753.png"
                     alt="Notifications">
            </div>
            <h3>Smart Notifications</h3>
            <p>In-app reminders for overdue payments, ticket
               updates, and system announcements keep everyone
               informed.</p>
        </div>

        <!-- Feature 5: Audit Logs -->
        <div class="feature-card">
            <div class="icon-wrap">
                <img src="https://cdn-icons-png.flaticon.com/512/751/751381.png"
                     alt="Audit Logs">
            </div>
            <h3>Audit Trail</h3>
            <p>Every update and delete is logged automatically.
               View the history or export it as a PDF for your
               records.</p>
        </div>

        <!-- Feature 6: Role-Based Access -->
        <div class="feature-card">
            <div class="icon-wrap">
                <img src="https://cdn-icons-png.flaticon.com/512/1077/1077063.png"
                     alt="Roles">
            </div>
            <h3>Role-Based Access</h3>
            <p>Admins, tenants, and guests each see only what
               they need.  Secure, organised, and hassle-free.</p>
        </div>
    </div>
</section>


<!-- ====================================================
     CONTACT SECTION
     ==================================================== -->
<section class="section" style="padding-bottom:60px;">
    <div class="section-header">
        <h2>Interested?</h2>
        <p>Contact the building owner to inquire about units
           or request an account.</p>
    </div>
    <div style="text-align:center;max-width:400px;margin:0 auto;">
        <div style="padding:28px 24px;background:var(--color-bg-card);border:1px solid var(--color-border);border-radius:var(--radius-md);">
            <img src="https://cdn-icons-png.flaticon.com/512/561/561127.png"
                 alt="Email"
                 style="width:32px;height:32px;filter:invert(1);opacity:0.5;margin-bottom:12px;">
            <h3 style="margin-bottom:8px;font-size:1.1rem;">Email the Owner</h3>
            <a href="mailto:admin@homeease.com"
               style="font-size:1rem;font-weight:600;color:var(--color-accent);">
               admin@homeease.com
            </a>
        </div>
    </div>
</section>


<!-- ====================================================
     FOOTER
     ==================================================== -->
<footer class="landing-footer">
    <p>&copy; <?php echo date('Y'); ?> HomeEase.
       Apartment Management System. All rights reserved.</p>
</footer>

<script src="/Apartment Management System/assets/js/main.js"></script>
</body>
</html>
>>>>>>> 047237f57cd0fd2e115602c620d70bf3bd6e29a0
