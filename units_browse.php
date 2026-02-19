<?php
// ============================================================
// units_browse.php — Public Vacant Unit Listing (Guest)
// ============================================================
// Anyone (including guests not logged in) can see this page.
// It shows a card grid of all VACANT apartments so potential
// tenants can see what is available.
// ============================================================

session_start();
require_once 'db_connect.php';

// --------------------------------------------------------
// Fetch all vacant units from the database
// --------------------------------------------------------
$query = "SELECT * FROM `units` WHERE `status` = 'Vacant' ORDER BY `floor_number`, `unit_number`";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Units — HomeEase</title>
    <meta name="description" content="Browse all available vacant apartment units at HomeEase.">
    <link rel="stylesheet" href="/Apartment Management System/assets/css/style.css">
</head>
<body>

<!-- Top navigation bar (same as landing page) -->
<nav class="landing-nav">
    <div class="logo">
        <img src="https://cdn-icons-png.flaticon.com/512/25/25694.png"
             alt="HomeEase Logo"
             style="filter:invert(1);">
        <h2>Home<span>Ease</span></h2>
    </div>
    <ul class="nav-links">
        <li><a href="/Apartment Management System/index.php">Home</a></li>
        <li><a href="/Apartment Management System/units_browse.php" style="color:var(--color-text);">Browse Units</a></li>
        <li><a href="/Apartment Management System/faq.php">FAQs</a></li>
        <?php if (isset($_SESSION['user_id'])): ?>
            <li>
                <a href="/Apartment Management System/<?php echo($_SESSION['role'] === 'admin') ? 'admin' : 'tenant'; ?>/dashboard.php"
                   class="btn btn-primary btn-sm">Dashboard</a>
            </li>
        <?php
else: ?>
            <li><a href="/Apartment Management System/login.php" class="btn btn-primary btn-sm">Sign In</a></li>
        <?php
endif; ?>
    </ul>
</nav>

<!-- Page content with top padding for the fixed navbar -->
<div class="section" style="padding-top: 120px;">
    <div class="section-header">
        <h2>Available Units</h2>
        <p>Browse our currently vacant apartments.
           Contact the building admin for inquiries.</p>
    </div>

    <?php if (mysqli_num_rows($result) > 0): ?>
    <div class="units-grid" style="max-width:1100px; margin:0 auto;">
        <?php while ($unit = mysqli_fetch_assoc($result)): ?>
        <div class="unit-card">
            <!-- Card header with unit number and type badge -->
            <div class="unit-header">
                <h3>Unit <?php echo htmlspecialchars($unit['unit_number']); ?></h3>
                <span class="badge badge-vacant">Vacant</span>
            </div>

            <div class="unit-body">
                <!-- Description -->
                <p><?php echo htmlspecialchars($unit['description'] ?? 'No description available.'); ?></p>

                <!-- Details (type, floor) -->
                <div class="unit-details">
                    <div class="detail">
                        <img src="https://cdn-icons-png.flaticon.com/512/25/25694.png"
                             alt="Type">
                        <?php echo htmlspecialchars($unit['type']); ?>
                    </div>
                    <?php if ($unit['floor_number']): ?>
                    <div class="detail">
                        <img src="https://cdn-icons-png.flaticon.com/512/747/747846.png"
                             alt="Floor">
                        Floor <?php echo (int)$unit['floor_number']; ?>
                    </div>
                    <?php
        endif; ?>
                </div>

                <!-- Monthly rent -->
                <div class="unit-price">
                    PHP <?php echo number_format($unit['rent_price'], 2); ?>
                    <span>/ month</span>
                </div>
            </div>
        </div>
        <?php
    endwhile; ?>
    </div>
    <?php
else: ?>
    <!-- No vacant units -->
    <div class="empty-state">
        <img src="https://cdn-icons-png.flaticon.com/512/25/25694.png" alt="No units">
        <h3>All Units Occupied</h3>
        <p>There are currently no vacant apartments.
           Please check back later.</p>
    </div>
    <?php
endif; ?>
</div>

<footer class="landing-footer">
    <p>&copy; <?php echo date('Y'); ?> HomeEase. All rights reserved.</p>
</footer>

<script src="/Apartment Management System/assets/js/main.js"></script>
</body>
</html>
