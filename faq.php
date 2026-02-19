<?php
// ============================================================
// faq.php — Public FAQ Page (Accordion)
// ============================================================
// Displays frequently asked questions from the `faqs` table
// in an accordion layout.  Anyone can view this page.
// ============================================================

session_start();
require_once 'db_connect.php';

// Fetch all active FAQs, ordered by display_order.
$query = "SELECT * FROM `faqs` WHERE `is_active` = 1 ORDER BY `display_order` ASC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQs — HomeEase</title>
    <meta name="description" content="Frequently asked questions about HomeEase Apartment Management System.">
    <link rel="stylesheet" href="/Apartment Management System/assets/css/style.css">
</head>
<body>

<!-- Top navigation bar -->
<nav class="landing-nav">
    <div class="logo">
        <img src="https://cdn-icons-png.flaticon.com/512/25/25694.png"
             alt="HomeEase Logo"
             style="filter:invert(1);">
        <h2>Home<span>Ease</span></h2>
    </div>
    <ul class="nav-links">
        <li><a href="/Apartment Management System/index.php">Home</a></li>
        <li><a href="/Apartment Management System/units_browse.php">Browse Units</a></li>
        <li><a href="/Apartment Management System/faq.php" style="color:var(--color-text);">FAQs</a></li>
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

<div class="section" style="padding-top: 120px;">
    <div class="section-header">
        <h2>Frequently Asked Questions</h2>
        <p>Find answers to common questions about living in and
           managing our apartments.</p>
    </div>

    <?php if (mysqli_num_rows($result) > 0): ?>
    <div class="faq-list">
        <?php while ($faq = mysqli_fetch_assoc($result)): ?>
        <div class="faq-item">
            <!-- The question button toggles the answer panel -->
            <button class="faq-question">
                <?php echo htmlspecialchars($faq['question']); ?>
                <span class="arrow">&#9660;</span>
            </button>
            <!-- Answer panel (hidden by default via CSS max-height: 0) -->
            <div class="faq-answer">
                <p><?php echo htmlspecialchars($faq['answer']); ?></p>
            </div>
        </div>
        <?php
    endwhile; ?>
    </div>
    <?php
else: ?>
    <div class="empty-state">
        <img src="https://cdn-icons-png.flaticon.com/512/157/157933.png" alt="No FAQs">
        <h3>No FAQs Available</h3>
        <p>Check back later for frequently asked questions.</p>
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
