<?php
// ============================================================
// register.php — Guest Self-Registration
// ============================================================
// Allows a new visitor to create a guest account so they
// can access basic features.  Uses password_hash() for
// secure storage.
// ============================================================

session_start();

// Redirect logged-in users away from the register page.
if (isset($_SESSION['user_id'])) {
    header('Location: /Apartment Management System/index.php');
    exit;
}

require_once 'db_connect.php';

$error = '';
$success = '';

// --------------------------------------------------------
// HANDLE FORM SUBMISSION
// --------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Collect and sanitise inputs.
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    // --- Validation ---
    if ($first_name === '' || $last_name === '' || $email === '' || $password === '') {
        $error = 'Please fill in all required fields.';

    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // filter_var checks that the email looks valid.
        $error = 'Please enter a valid email address.';

    }
    elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';

    }
    elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';

    }
    else {
        // Check if this email is already in the database.
        $check = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($check, 's', $email);
        mysqli_stmt_execute($check);
        $check_result = mysqli_stmt_get_result($check);

        if (mysqli_fetch_assoc($check_result)) {
            $error = 'An account with this email already exists.';
        }
        else {
            // Hash the password with bcrypt.
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            // Insert the new user as a guest.
            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO users
                    (first_name, last_name, email, phone, password, role)
                 VALUES (?, ?, ?, ?, ?, 'guest')"
            );
            mysqli_stmt_bind_param($stmt, 'sssss',
                $first_name, $last_name, $email, $phone, $hashed
            );

            if (mysqli_stmt_execute($stmt)) {
                $success = 'Account created! You can now log in.';
            }
            else {
                $error = 'Something went wrong. Please try again.';
            }
            mysqli_stmt_close($stmt);
        }
        mysqli_stmt_close($check);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — HomeEase</title>
    <link rel="stylesheet" href="/Apartment Management System/assets/css/style.css">
</head>
<body>

<div class="auth-page">
    <div class="auth-card">

        <div class="logo">
            <img src="https://cdn-icons-png.flaticon.com/512/25/25694.png"
                 alt="HomeEase Logo"
                 style="filter:invert(1);">
            <h1>Home<span>Ease</span></h1>
            <p>Create a new account</p>
        </div>

        <!-- Error / success messages -->
        <?php if ($error): ?>
            <div class="flash-message flash-error">
                <img src="https://cdn-icons-png.flaticon.com/512/399/399274.png" alt="Error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php
endif; ?>
        <?php if ($success): ?>
            <div class="flash-message flash-success">
                <img src="https://cdn-icons-png.flaticon.com/512/845/845646.png" alt="Success">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php
endif; ?>

        <form method="POST" action="">
            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">First Name *</label>
                    <input type="text" id="first_name" name="first_name"
                           class="form-control" placeholder="Juan"
                           value="<?php echo htmlspecialchars($first_name ?? ''); ?>"
                           required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name *</label>
                    <input type="text" id="last_name" name="last_name"
                           class="form-control" placeholder="Dela Cruz"
                           value="<?php echo htmlspecialchars($last_name ?? ''); ?>"
                           required>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email"
                       class="form-control" placeholder="you@email.com"
                       value="<?php echo htmlspecialchars($email ?? ''); ?>"
                       required>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="text" id="phone" name="phone"
                       class="form-control" placeholder="09171234567"
                       value="<?php echo htmlspecialchars($phone ?? ''); ?>">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password"
                           class="form-control" placeholder="Min 6 characters"
                           required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password"
                           class="form-control" placeholder="Repeat password"
                           required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Create Account</button>
        </form>

        <div class="auth-footer">
            Already have an account?
            <a href="/Apartment Management System/login.php">Sign in</a>
        </div>
    </div>
</div>

<script src="/Apartment Management System/assets/js/main.js"></script>
</body>
</html>
