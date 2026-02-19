<<<<<<< HEAD
<?php
// ============================================================
// seed_data.php — HomeEase Sample Data Seeder
// ============================================================
// Run this file ONCE in your browser after importing the
// homeease.sql schema.  It fills every table with realistic
// demo data so you can test the system right away.
//
// URL:  http://localhost/Apartment Management System/seed_data.php
//
// IMPORTANT:
//   • Passwords are hashed at runtime using password_hash()
//     — they are NEVER hardcoded as plain-text hashes.
//   • Running this file a second time will produce duplicate
//     rows unless you TRUNCATE the tables first.
// ============================================================


// --------------------------------------------------------
// Pull in the database connection we set up in db_connect.php
// "require_once" means: include this file, and if it has
// already been included before, don't include it twice.
// --------------------------------------------------------
require_once 'db_connect.php';


// --------------------------------------------------------
// HELPER: run a query and stop if it fails
// --------------------------------------------------------
// This little function saves us from writing the same
// error-check code after every single INSERT statement.
// --------------------------------------------------------
function seed_query($conn, $sql)
{
    // mysqli_query() sends the SQL command to the database.
    // If it fails it returns FALSE.
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        // Show exactly which query broke so we can debug it.
        die(
            '<p style="color:#c0392b;font-family:sans-serif;">'
            . '<strong>Query failed:</strong><br>'
            . htmlspecialchars($sql) . '<br><br>'
            . '<strong>Error:</strong> ' . mysqli_error($conn)
            . '</p>'
            );
    }
    return $result;
}


// ============================================================
// 1.  SEED USERS
// ============================================================
// password_hash() turns a readable password like "admin123"
// into a long, scrambled string that cannot be reversed.
// We use PASSWORD_DEFAULT which currently picks the bcrypt
// algorithm — the safest default PHP offers.
// ============================================================

// Hash each password at runtime (never store the plain text).
$admin_pass = password_hash('admin123', PASSWORD_DEFAULT);
$tenant1_pass = password_hash('tenant123', PASSWORD_DEFAULT);
$tenant2_pass = password_hash('tenant456', PASSWORD_DEFAULT);
$guest_pass = password_hash('guest123', PASSWORD_DEFAULT);

// We use a multi-row INSERT so everything goes in at once.
// Each row is one user: (first_name, last_name, email, phone,
// password, role, is_active).
$sql_users = "INSERT INTO `users`
    (`first_name`, `last_name`, `email`, `phone`, `password`, `role`, `is_active`)
VALUES
    -- Admin (the building owner / manager)
    ('Jerald',  'Admin',    'admin@homeease.com',
     '09171234567', '{$admin_pass}',   'admin',  1),

    -- Tenant 1
    ('Maria',   'Santos',   'maria@homeease.com',
     '09181234567', '{$tenant1_pass}', 'tenant', 1),

    -- Tenant 2
    ('Juan',    'Dela Cruz','juan@homeease.com',
     '09191234567', '{$tenant2_pass}', 'tenant', 1),

    -- Guest (can only browse vacant units and FAQs)
    ('Ana',     'Reyes',    'ana@homeease.com',
     '09201234567', '{$guest_pass}',   'guest',  1)
";
seed_query($conn, $sql_users);


// ============================================================
// 2.  SEED UNITS
// ============================================================
// Six apartments across three floors.  Some are occupied
// (linked to a tenant), some are vacant.
// ============================================================
$sql_units = "INSERT INTO `units`
    (`unit_number`, `type`, `description`, `rent_price`,
     `status`, `tenant_id`, `floor_number`)
VALUES
    -- Floor 1 -------------------------------------------------
    ('101', 'Studio',       'Cozy studio with a balcony view.',
     8500.00,  'Occupied', 2, 1),
        -- tenant_id = 2 is Maria Santos

    ('102', 'One Bedroom',  'Spacious one-bedroom near the lobby.',
     12000.00, 'Occupied', 3, 1),
        -- tenant_id = 3 is Juan Dela Cruz

    -- Floor 2 -------------------------------------------------
    ('201', 'Two Bedroom',  'Corner unit with natural lighting.',
     18000.00, 'Vacant',   NULL, 2),

    ('202', 'Studio',       'Affordable starter unit.',
     7500.00,  'Vacant',   NULL, 2),

    -- Floor 3 -------------------------------------------------
    ('301', 'Loft',         'Premium loft with a city skyline view.',
     25000.00, 'Vacant',   NULL, 3),

    ('302', 'One Bedroom',  'Quiet unit at the end of the hallway.',
     11000.00, 'Vacant',   NULL, 3)
";
seed_query($conn, $sql_units);


// ============================================================
// 3.  SEED PAYMENTS
// ============================================================
// Three sample rent payments with different statuses.
// ============================================================
$sql_payments = "INSERT INTO `payments`
    (`tenant_id`, `unit_id`, `amount`, `payment_date`, `method`,
     `reference_number`, `status`, `verified_by`, `verified_at`,
     `notes`, `period_covered`)
VALUES
    -- Maria paid January rent via GCash — admin already verified it.
    (2, 1, 8500.00,  '2026-01-05', 'GCash',
     'GCASH-20260105-001', 'Verified', 1, '2026-01-06 09:00:00',
     'On-time payment.', 'January 2026'),

    -- Juan paid January rent via bank transfer — still pending.
    (3, 2, 12000.00, '2026-01-10', 'Bank Transfer',
     'BT-20260110-042',    'Pending',  NULL, NULL,
     NULL,                 'January 2026'),

    -- Maria's February rent — also pending.
    (2, 1, 8500.00,  '2026-02-03', 'GCash',
     'GCASH-20260203-009', 'Pending',  NULL, NULL,
     NULL,                 'February 2026')
";
seed_query($conn, $sql_payments);


// ============================================================
// 4.  SEED TICKETS  (Maintenance Requests)
// ============================================================
// Two tickets: one resolved, one still pending.
// ============================================================
$sql_tickets = "INSERT INTO `tickets`
    (`tenant_id`, `unit_id`, `subject`, `description`,
     `priority`, `status`, `assigned_to`, `resolved_at`)
VALUES
    -- Ticket #1: Maria's leaking faucet — now resolved.
    (2, 1,
     'Leaky faucet in the kitchen',
     'The faucet drips continuously even when turned off. '
     'Water is pooling under the sink.',
     'High', 'Resolved', 1, '2026-01-20 14:30:00'),

    -- Ticket #2: Juan's broken light — still pending.
    (3, 2,
     'Flickering hallway light',
     'The light in the hallway flickers every few seconds '
     'and sometimes goes out completely at night.',
     'Medium', 'Pending', NULL, NULL)
";
seed_query($conn, $sql_tickets);


// ============================================================
// 5.  SEED TICKET UPDATES
// ============================================================
// Shows the status-change history for Ticket #1 (the faucet).
// ============================================================
$sql_ticket_updates = "INSERT INTO `ticket_updates`
    (`ticket_id`, `updated_by`, `old_status`, `new_status`, `comment`)
VALUES
    -- Admin moved the ticket from Pending ➜ In Progress.
    (1, 1, 'Pending', 'In Progress',
     'Plumber scheduled for January 18.'),

    -- Admin marked the ticket as Resolved.
    (1, 1, 'In Progress', 'Resolved',
     'Faucet replaced. Tested — no more leaks.')
";
seed_query($conn, $sql_ticket_updates);


// ============================================================
// 6.  SEED AUDIT LOGS
// ============================================================
// Three sample entries showing different kinds of changes.
// ============================================================
$sql_audit = "INSERT INTO `audit_logs`
    (`user_id`, `action`, `table_name`, `record_id`,
     `old_values`, `new_values`, `ip_address`)
VALUES
    -- Admin verified Maria's January payment (Update).
    (1, 'Update', 'payments', 1,
     '{\"status\": \"Pending\"}',
     '{\"status\": \"Verified\", \"verified_by\": 1}',
     '127.0.0.1'),

    -- Admin assigned himself to Ticket #1 (Update).
    (1, 'Update', 'tickets', 1,
     '{\"assigned_to\": null, \"status\": \"Pending\"}',
     '{\"assigned_to\": 1, \"status\": \"In Progress\"}',
     '127.0.0.1'),

    -- Admin resolved Ticket #1 (Update).
    (1, 'Update', 'tickets', 1,
     '{\"status\": \"In Progress\"}',
     '{\"status\": \"Resolved\"}',
     '127.0.0.1')
";
seed_query($conn, $sql_audit);


// ============================================================
// 7.  SEED NOTIFICATIONS
// ============================================================
// Three sample notifications, including an unpaid-rent reminder.
// ============================================================
$sql_notifications = "INSERT INTO `notifications`
    (`user_id`, `title`, `message`, `type`, `is_read`, `link`)
VALUES
    -- Unpaid-rent reminder for Juan (unread).
    (3, 'Payment Reminder',
     'Your January 2026 rent of PHP 12,000.00 is still pending '
     'verification.  Please ensure your payment has been submitted.',
     'payment', 0, '/payments.php'),

    -- Payment confirmation for Maria (already read).
    (2, 'Payment Verified',
     'Your January 2026 rent payment of PHP 8,500.00 has been '
     'verified by the admin.  Thank you!',
     'payment', 1, '/payments.php'),

    -- System welcome for the guest user (unread).
    (4, 'Welcome to HomeEase!',
     'Browse our available units and check out the FAQ section '
     'for answers to common questions.',
     'system', 0, '/units.php')
";
seed_query($conn, $sql_notifications);


// ============================================================
// 8.  SEED FAQs
// ============================================================
// Four frequently asked questions shown on the public page.
// ============================================================
$sql_faqs = "INSERT INTO `faqs`
    (`question`, `answer`, `display_order`, `is_active`)
VALUES
    ('How do I pay my monthly rent?',
     'You can pay via Cash, Bank Transfer, or GCash. After paying, '
     'log in to your tenant dashboard and submit your payment details '
     'with the reference number. The admin will verify it within '
     '24–48 hours.',
     1, 1),

    ('How do I submit a maintenance request?',
     'Go to the Maintenance section in your dashboard and click '
     '\"New Ticket\". Describe the issue, choose a priority level, '
     'and submit. You will be notified when the status changes.',
     2, 1),

    ('Can I view available units without signing up?',
     'Yes! Guests can browse all vacant units on the Units page. '
     'If you are interested, contact the building admin through '
     'the information provided on the page.',
     3, 1),

    ('How do I update my profile information?',
     'Log in to your tenant account, go to \"My Profile\", and '
     'click the Edit button. You can change your name, phone number, '
     'and upload a new profile picture.',
     4, 1)
";
seed_query($conn, $sql_faqs);


// ============================================================
// SUCCESS MESSAGE
// ============================================================
// If we got this far without die()-ing, everything worked.
// ============================================================
echo '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>HomeEase — Seed Complete</title>
    <style>
        /* Simple centered card so the success message looks clean */
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: "Segoe UI", sans-serif;
            background: #f5f6fa;
        }
        .card {
            background: #fff;
            border-radius: 12px;
            padding: 48px 40px;
            max-width: 520px;
            text-align: center;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
        }
        .card h1 {
            color: #27ae60;
            font-size: 28px;
            margin-bottom: 12px;
        }
        .card p {
            color: #555;
            font-size: 15px;
            line-height: 1.6;
        }
        .card .accounts {
            text-align: left;
            background: #f9f9f9;
            border-radius: 8px;
            padding: 16px 20px;
            margin-top: 20px;
            font-size: 14px;
        }
        .card .accounts strong {
            color: #2c3e50;
        }
        .card .accounts code {
            background: #eee;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="card">
        <img src="https://cdn-icons-png.flaticon.com/512/845/845646.png"
             alt="Success" width="64" height="64"
             style="margin-bottom: 16px;">
        <h1>Seed Data Inserted Successfully!</h1>
        <p>All 8 tables have been populated with demo data.<br>
           You can now log in with one of the accounts below.</p>
        <div class="accounts">
            <p><strong>Admin:</strong><br>
               Email: <code>admin@homeease.com</code><br>
               Password: <code>admin123</code></p>
            <p><strong>Tenant 1:</strong><br>
               Email: <code>maria@homeease.com</code><br>
               Password: <code>tenant123</code></p>
            <p><strong>Tenant 2:</strong><br>
               Email: <code>juan@homeease.com</code><br>
               Password: <code>tenant456</code></p>
            <p><strong>Guest:</strong><br>
               Email: <code>ana@homeease.com</code><br>
               Password: <code>guest123</code></p>
        </div>
    </div>
</body>
</html>
';


// --------------------------------------------------------
// Close the database connection since we are done.
// This frees up the connection for other scripts.
// --------------------------------------------------------
mysqli_close($conn);
=======
<?php
// ============================================================
// seed_data.php — HomeEase Sample Data Seeder
// ============================================================
// Run this file ONCE in your browser after importing the
// homeease.sql schema.  It fills every table with realistic
// demo data so you can test the system right away.
//
// URL:  http://localhost/Apartment Management System/seed_data.php
//
// IMPORTANT:
//   • Passwords are hashed at runtime using password_hash()
//     — they are NEVER hardcoded as plain-text hashes.
//   • Running this file a second time will produce duplicate
//     rows unless you TRUNCATE the tables first.
// ============================================================


// --------------------------------------------------------
// Pull in the database connection we set up in db_connect.php
// "require_once" means: include this file, and if it has
// already been included before, don't include it twice.
// --------------------------------------------------------
require_once 'db_connect.php';


// --------------------------------------------------------
// HELPER: run a query and stop if it fails
// --------------------------------------------------------
// This little function saves us from writing the same
// error-check code after every single INSERT statement.
// --------------------------------------------------------
function seed_query($conn, $sql)
{
    // mysqli_query() sends the SQL command to the database.
    // If it fails it returns FALSE.
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        // Show exactly which query broke so we can debug it.
        die(
            '<p style="color:#c0392b;font-family:sans-serif;">'
            . '<strong>Query failed:</strong><br>'
            . htmlspecialchars($sql) . '<br><br>'
            . '<strong>Error:</strong> ' . mysqli_error($conn)
            . '</p>'
            );
    }
    return $result;
}


// ============================================================
// 1.  SEED USERS
// ============================================================
// password_hash() turns a readable password like "admin123"
// into a long, scrambled string that cannot be reversed.
// We use PASSWORD_DEFAULT which currently picks the bcrypt
// algorithm — the safest default PHP offers.
// ============================================================

// Hash each password at runtime (never store the plain text).
$admin_pass = password_hash('admin123', PASSWORD_DEFAULT);
$tenant1_pass = password_hash('tenant123', PASSWORD_DEFAULT);
$tenant2_pass = password_hash('tenant456', PASSWORD_DEFAULT);
$guest_pass = password_hash('guest123', PASSWORD_DEFAULT);

// We use a multi-row INSERT so everything goes in at once.
// Each row is one user: (first_name, last_name, email, phone,
// password, role, is_active).
$sql_users = "INSERT INTO `users`
    (`first_name`, `last_name`, `email`, `phone`, `password`, `role`, `is_active`)
VALUES
    -- Admin (the building owner / manager)
    ('Jerald',  'Admin',    'admin@homeease.com',
     '09171234567', '{$admin_pass}',   'admin',  1),

    -- Tenant 1
    ('Maria',   'Santos',   'maria@homeease.com',
     '09181234567', '{$tenant1_pass}', 'tenant', 1),

    -- Tenant 2
    ('Juan',    'Dela Cruz','juan@homeease.com',
     '09191234567', '{$tenant2_pass}', 'tenant', 1),

    -- Guest (can only browse vacant units and FAQs)
    ('Ana',     'Reyes',    'ana@homeease.com',
     '09201234567', '{$guest_pass}',   'guest',  1)
";
seed_query($conn, $sql_users);


// ============================================================
// 2.  SEED UNITS
// ============================================================
// Six apartments across three floors.  Some are occupied
// (linked to a tenant), some are vacant.
// ============================================================
$sql_units = "INSERT INTO `units`
    (`unit_number`, `type`, `description`, `rent_price`,
     `status`, `tenant_id`, `floor_number`)
VALUES
    -- Floor 1 -------------------------------------------------
    ('101', 'Studio',       'Cozy studio with a balcony view.',
     8500.00,  'Occupied', 2, 1),
        -- tenant_id = 2 is Maria Santos

    ('102', 'One Bedroom',  'Spacious one-bedroom near the lobby.',
     12000.00, 'Occupied', 3, 1),
        -- tenant_id = 3 is Juan Dela Cruz

    -- Floor 2 -------------------------------------------------
    ('201', 'Two Bedroom',  'Corner unit with natural lighting.',
     18000.00, 'Vacant',   NULL, 2),

    ('202', 'Studio',       'Affordable starter unit.',
     7500.00,  'Vacant',   NULL, 2),

    -- Floor 3 -------------------------------------------------
    ('301', 'Loft',         'Premium loft with a city skyline view.',
     25000.00, 'Vacant',   NULL, 3),

    ('302', 'One Bedroom',  'Quiet unit at the end of the hallway.',
     11000.00, 'Vacant',   NULL, 3)
";
seed_query($conn, $sql_units);


// ============================================================
// 3.  SEED PAYMENTS
// ============================================================
// Three sample rent payments with different statuses.
// ============================================================
$sql_payments = "INSERT INTO `payments`
    (`tenant_id`, `unit_id`, `amount`, `payment_date`, `method`,
     `reference_number`, `status`, `verified_by`, `verified_at`,
     `notes`, `period_covered`)
VALUES
    -- Maria paid January rent via GCash — admin already verified it.
    (2, 1, 8500.00,  '2026-01-05', 'GCash',
     'GCASH-20260105-001', 'Verified', 1, '2026-01-06 09:00:00',
     'On-time payment.', 'January 2026'),

    -- Juan paid January rent via bank transfer — still pending.
    (3, 2, 12000.00, '2026-01-10', 'Bank Transfer',
     'BT-20260110-042',    'Pending',  NULL, NULL,
     NULL,                 'January 2026'),

    -- Maria's February rent — also pending.
    (2, 1, 8500.00,  '2026-02-03', 'GCash',
     'GCASH-20260203-009', 'Pending',  NULL, NULL,
     NULL,                 'February 2026')
";
seed_query($conn, $sql_payments);


// ============================================================
// 4.  SEED TICKETS  (Maintenance Requests)
// ============================================================
// Two tickets: one resolved, one still pending.
// ============================================================
$sql_tickets = "INSERT INTO `tickets`
    (`tenant_id`, `unit_id`, `subject`, `description`,
     `priority`, `status`, `assigned_to`, `resolved_at`)
VALUES
    -- Ticket #1: Maria's leaking faucet — now resolved.
    (2, 1,
     'Leaky faucet in the kitchen',
     'The faucet drips continuously even when turned off. '
     'Water is pooling under the sink.',
     'High', 'Resolved', 1, '2026-01-20 14:30:00'),

    -- Ticket #2: Juan's broken light — still pending.
    (3, 2,
     'Flickering hallway light',
     'The light in the hallway flickers every few seconds '
     'and sometimes goes out completely at night.',
     'Medium', 'Pending', NULL, NULL)
";
seed_query($conn, $sql_tickets);


// ============================================================
// 5.  SEED TICKET UPDATES
// ============================================================
// Shows the status-change history for Ticket #1 (the faucet).
// ============================================================
$sql_ticket_updates = "INSERT INTO `ticket_updates`
    (`ticket_id`, `updated_by`, `old_status`, `new_status`, `comment`)
VALUES
    -- Admin moved the ticket from Pending ➜ In Progress.
    (1, 1, 'Pending', 'In Progress',
     'Plumber scheduled for January 18.'),

    -- Admin marked the ticket as Resolved.
    (1, 1, 'In Progress', 'Resolved',
     'Faucet replaced. Tested — no more leaks.')
";
seed_query($conn, $sql_ticket_updates);


// ============================================================
// 6.  SEED AUDIT LOGS
// ============================================================
// Three sample entries showing different kinds of changes.
// ============================================================
$sql_audit = "INSERT INTO `audit_logs`
    (`user_id`, `action`, `table_name`, `record_id`,
     `old_values`, `new_values`, `ip_address`)
VALUES
    -- Admin verified Maria's January payment (Update).
    (1, 'Update', 'payments', 1,
     '{\"status\": \"Pending\"}',
     '{\"status\": \"Verified\", \"verified_by\": 1}',
     '127.0.0.1'),

    -- Admin assigned himself to Ticket #1 (Update).
    (1, 'Update', 'tickets', 1,
     '{\"assigned_to\": null, \"status\": \"Pending\"}',
     '{\"assigned_to\": 1, \"status\": \"In Progress\"}',
     '127.0.0.1'),

    -- Admin resolved Ticket #1 (Update).
    (1, 'Update', 'tickets', 1,
     '{\"status\": \"In Progress\"}',
     '{\"status\": \"Resolved\"}',
     '127.0.0.1')
";
seed_query($conn, $sql_audit);


// ============================================================
// 7.  SEED NOTIFICATIONS
// ============================================================
// Three sample notifications, including an unpaid-rent reminder.
// ============================================================
$sql_notifications = "INSERT INTO `notifications`
    (`user_id`, `title`, `message`, `type`, `is_read`, `link`)
VALUES
    -- Unpaid-rent reminder for Juan (unread).
    (3, 'Payment Reminder',
     'Your January 2026 rent of PHP 12,000.00 is still pending '
     'verification.  Please ensure your payment has been submitted.',
     'payment', 0, '/payments.php'),

    -- Payment confirmation for Maria (already read).
    (2, 'Payment Verified',
     'Your January 2026 rent payment of PHP 8,500.00 has been '
     'verified by the admin.  Thank you!',
     'payment', 1, '/payments.php'),

    -- System welcome for the guest user (unread).
    (4, 'Welcome to HomeEase!',
     'Browse our available units and check out the FAQ section '
     'for answers to common questions.',
     'system', 0, '/units.php')
";
seed_query($conn, $sql_notifications);


// ============================================================
// 8.  SEED FAQs
// ============================================================
// Four frequently asked questions shown on the public page.
// ============================================================
$sql_faqs = "INSERT INTO `faqs`
    (`question`, `answer`, `display_order`, `is_active`)
VALUES
    ('How do I pay my monthly rent?',
     'You can pay via Cash, Bank Transfer, or GCash. After paying, '
     'log in to your tenant dashboard and submit your payment details '
     'with the reference number. The admin will verify it within '
     '24–48 hours.',
     1, 1),

    ('How do I submit a maintenance request?',
     'Go to the Maintenance section in your dashboard and click '
     '\"New Ticket\". Describe the issue, choose a priority level, '
     'and submit. You will be notified when the status changes.',
     2, 1),

    ('Can I view available units without signing up?',
     'Yes! Guests can browse all vacant units on the Units page. '
     'If you are interested, contact the building admin through '
     'the information provided on the page.',
     3, 1),

    ('How do I update my profile information?',
     'Log in to your tenant account, go to \"My Profile\", and '
     'click the Edit button. You can change your name, phone number, '
     'and upload a new profile picture.',
     4, 1)
";
seed_query($conn, $sql_faqs);


// ============================================================
// SUCCESS MESSAGE
// ============================================================
// If we got this far without die()-ing, everything worked.
// ============================================================
echo '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>HomeEase — Seed Complete</title>
    <style>
        /* Simple centered card so the success message looks clean */
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: "Segoe UI", sans-serif;
            background: #f5f6fa;
        }
        .card {
            background: #fff;
            border-radius: 12px;
            padding: 48px 40px;
            max-width: 520px;
            text-align: center;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
        }
        .card h1 {
            color: #27ae60;
            font-size: 28px;
            margin-bottom: 12px;
        }
        .card p {
            color: #555;
            font-size: 15px;
            line-height: 1.6;
        }
        .card .accounts {
            text-align: left;
            background: #f9f9f9;
            border-radius: 8px;
            padding: 16px 20px;
            margin-top: 20px;
            font-size: 14px;
        }
        .card .accounts strong {
            color: #2c3e50;
        }
        .card .accounts code {
            background: #eee;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="card">
        <img src="https://cdn-icons-png.flaticon.com/512/845/845646.png"
             alt="Success" width="64" height="64"
             style="margin-bottom: 16px;">
        <h1>Seed Data Inserted Successfully!</h1>
        <p>All 8 tables have been populated with demo data.<br>
           You can now log in with one of the accounts below.</p>
        <div class="accounts">
            <p><strong>Admin:</strong><br>
               Email: <code>admin@homeease.com</code><br>
               Password: <code>admin123</code></p>
            <p><strong>Tenant 1:</strong><br>
               Email: <code>maria@homeease.com</code><br>
               Password: <code>tenant123</code></p>
            <p><strong>Tenant 2:</strong><br>
               Email: <code>juan@homeease.com</code><br>
               Password: <code>tenant456</code></p>
            <p><strong>Guest:</strong><br>
               Email: <code>ana@homeease.com</code><br>
               Password: <code>guest123</code></p>
        </div>
    </div>
</body>
</html>
';


// --------------------------------------------------------
// Close the database connection since we are done.
// This frees up the connection for other scripts.
// --------------------------------------------------------
mysqli_close($conn);
>>>>>>> 047237f57cd0fd2e115602c620d70bf3bd6e29a0
