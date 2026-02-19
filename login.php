<?php
// ============================================================
// login.php — User Login Page
// ============================================================
// Shows a centred login card.  When the form is submitted,
// we check the email and hashed password against the
// database.  On success, we store user data in the session
// and redirect to the appropriate dashboard.
//
// NOTE: Only admins can create accounts.  This login page
//       includes a "Contact the Owner" section for inquiries.
// ============================================================

// Start session so we can store login data.
session_start();

// If the user is already logged in, skip the login page and
// send them straight to their dashboard.
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: /Apartment Management System/admin/dashboard.php');
    }
    else {
        header('Location: /Apartment Management System/tenant/dashboard.php');
    }
    exit;
}

// Pull in the database connection.
require_once 'db_connect.php';

// This variable will hold any error message we need to show.
$error = '';

// --------------------------------------------------------
// HANDLE FORM SUBMISSION  (POST request)
// --------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Grab the email and password from the form.
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Basic validation: make sure both fields are filled in.
    if ($email === '' || $password === '') {
        $error = 'Please enter both your email and password.';
    }
    else {

        // Look up the user by email.  We use a prepared
        // statement to prevent SQL injection.
        $stmt = mysqli_prepare(
            $conn,
            "SELECT `id`, `first_name`, `last_name`, `email`,
                    `password`, `role`, `is_active`
             FROM `users`
             WHERE `email` = ?
             LIMIT 1"
        );

        // Bind the email value to the ? placeholder.
        mysqli_stmt_bind_param($stmt, 's', $email);

        // Execute the query.
        mysqli_stmt_execute($stmt);

        // Fetch the result row (if any).
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);

        if (!$user) {
            // No user found with that email.
            $error = 'Invalid email or password.';

        }
        elseif ($user['is_active'] == 0) {
            // Account exists but has been disabled by admin.
            $error = 'Your account has been deactivated. Contact the admin.';

        }
        elseif (!password_verify($password, $user['password'])) {
            // Email exists but the password doesn't match.
            // password_verify() safely compares the plain-text
            // password against the stored bcrypt hash.
            $error = 'Invalid email or password.';

        }
        else {
            // ============================================
            // LOGIN SUCCESS — store user info in session
            // ============================================
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on role.
            if ($user['role'] === 'admin') {
                header('Location: /Apartment Management System/admin/dashboard.php');
            }
            elseif ($user['role'] === 'tenant') {
                header('Location: /Apartment Management System/tenant/dashboard.php');
            }
            else {
                // Guest users go to the public landing page.
                header('Location: /Apartment Management System/index.php');
            }
            exit;
        }
    }
}

// Check if there's a flash error from a redirect
// (e.g., from require_login()).
if (isset($_SESSION['flash_error'])) {
    $error = $_SESSION['flash_error'];
    unset($_SESSION['flash_error']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — HomeEase</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="/Apartment Management System/assets/css/style.css">
</head>
<body>

<!-- Full-page centred login card -->
<div class="auth-page">
    <div class="auth-card">

        <!-- Logo & branding -->
        <div class="logo">
            <img src="https://cdn-icons-png.flaticon.com/512/25/25694.png"
                 alt="HomeEase Logo"
                 style="filter:invert(1);">
            <h1>Home<span>Ease</span></h1>
            <p>Sign in to your account</p>
        </div>

        <!-- Show error message if login failed -->
        <?php if ($error): ?>
            <div class="flash-message flash-error">
                <img src="https://cdn-icons-png.flaticon.com/512/399/399274.png"
                     alt="Error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php
endif; ?>

        <!-- Login form -->
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email"
                       id="email"
                       name="email"
                       class="form-control"
                       placeholder="you@homeease.com"
                       value="<?php echo htmlspecialchars($email ?? ''); ?>"
                       required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password"
                       id="password"
                       name="password"
                       class="form-control"
                       placeholder="Enter your password"
                       required>
            </div>

            <button type="submit" class="btn btn-primary">
                Sign In
            </button>
        </form>

        <!-- Contact the Owner section -->
        <div class="auth-footer">
            <p style="margin-bottom:8px;color:var(--color-text-muted);font-size:0.85rem;">
                Don't have an account?<br>
                Accounts are created by the building admin.
            </p>
            <div class="contact-owner" style="margin-top:12px;padding:14px 18px;background:var(--color-bg-input);border:1px solid var(--color-border);border-radius:var(--radius-sm);text-align:center;">
                <p style="font-size:0.78rem;color:var(--color-text-muted);margin-bottom:6px;">
                    <img src="https://cdn-icons-png.flaticon.com/512/561/561127.png"
                         alt="Email" style="width:14px;height:14px;filter:invert(1);opacity:0.6;vertical-align:middle;margin-right:4px;">
                    Contact the Owner
                </p>
                <a href="mailto:admin@homeease.com"
                   style="font-size:0.92rem;font-weight:600;color:var(--color-accent);">
                   admin@homeease.com
                </a>
            </div>
            <br>
            <a href="/Apartment Management System/index.php"
               style="color:var(--color-text-muted);font-size:0.85rem;">
                &larr; Back to Home
            </a>
        </div>

    </div>
</div>

<!-- Load JS for auto-dismiss flash messages -->
<script src="/Apartment Management System/assets/js/main.js"></script>
</body>
</html>
