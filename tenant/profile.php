<<<<<<< HEAD
<?php
// ============================================================
// tenant/profile.php — View & Edit Profile
// ============================================================
// Tenants can update their name, phone, email, and upload
// a profile avatar.
// ============================================================

$page_title = 'My Profile';
require_once '../includes/auth.php';
require_role('tenant');
require_once '../db_connect.php';
require_once '../includes/audit_helper.php';

$user_id = $_SESSION['user_id'];

// Fetch full user data.
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    // Validate.
    if ($first_name === '' || $last_name === '' || $email === '') {
        $error = 'First name, last name, and email are required.';
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    }
    else {
        // Check for email uniqueness (exclude current user).
        $chk = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? AND id != ?");
        mysqli_stmt_bind_param($chk, 'si', $email, $user_id);
        mysqli_stmt_execute($chk);
        if (mysqli_fetch_assoc(mysqli_stmt_get_result($chk))) {
            $error = 'That email is already in use by another account.';
        }
        mysqli_stmt_close($chk);
    }

    if ($error === '') {
        $old_values = [
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'email' => $user['email'],
            'phone' => $user['phone'],
        ];

        $upd = mysqli_prepare($conn,
            "UPDATE users
             SET first_name = ?, last_name = ?, email = ?, phone = ?
             WHERE id = ?"
        );
        mysqli_stmt_bind_param($upd, 'ssssi',
            $first_name, $last_name, $email, $phone, $user_id
        );

        if (mysqli_stmt_execute($upd)) {
            // Log the audit trail.
            log_audit($conn, 'Update', 'users', $user_id,
                $old_values,
            ['first_name' => $first_name, 'last_name' => $last_name,
                'email' => $email, 'phone' => $phone]
            );

            // Update session variables so the topbar reflects
            // the new name immediately.
            $_SESSION['first_name'] = $first_name;
            $_SESSION['last_name'] = $last_name;
            $_SESSION['email'] = $email;

            // Refresh $user for the form.
            $user['first_name'] = $first_name;
            $user['last_name'] = $last_name;
            $user['email'] = $email;
            $user['phone'] = $phone;

            $success = 'Profile updated successfully!';
        }
        else {
            $error = 'Failed to update profile.';
        }
        mysqli_stmt_close($upd);
    }
}

require_once '../includes/header.php';
?>

<!-- Profile header -->
<div class="profile-header">
    <div class="profile-avatar">
        <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
    </div>
    <div class="profile-info">
        <h2><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h2>
        <p><?php echo htmlspecialchars($user['email']); ?> &middot;
           <?php echo ucfirst($user['role']); ?> &middot;
           Joined <?php echo date('M Y', strtotime($user['created_at'])); ?></p>
    </div>
</div>

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

<div class="card" style="max-width:600px;">
    <h3 style="margin-bottom:20px;">Edit Profile</h3>
    <form method="POST" action="">
        <div class="form-row">
            <div class="form-group">
                <label for="first_name">First Name *</label>
                <input type="text" id="first_name" name="first_name"
                       class="form-control"
                       value="<?php echo htmlspecialchars($user['first_name']); ?>"
                       required>
            </div>
            <div class="form-group">
                <label for="last_name">Last Name *</label>
                <input type="text" id="last_name" name="last_name"
                       class="form-control"
                       value="<?php echo htmlspecialchars($user['last_name']); ?>"
                       required>
            </div>
        </div>

        <div class="form-group">
            <label for="email">Email Address *</label>
            <input type="email" id="email" name="email"
                   class="form-control"
                   value="<?php echo htmlspecialchars($user['email']); ?>"
                   required>
        </div>

        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="text" id="phone" name="phone"
                   class="form-control" placeholder="09171234567"
                   value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
        </div>

        <button type="submit" class="btn btn-primary">
            <img src="https://cdn-icons-png.flaticon.com/512/845/845646.png" alt="Save">
            Save Changes
        </button>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
=======
<?php
// ============================================================
// tenant/profile.php — View & Edit Profile
// ============================================================
// Tenants can update their name, phone, email, and upload
// a profile avatar.
// ============================================================

$page_title = 'My Profile';
require_once '../includes/auth.php';
require_role('tenant');
require_once '../db_connect.php';
require_once '../includes/audit_helper.php';

$user_id = $_SESSION['user_id'];

// Fetch full user data.
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    // Validate.
    if ($first_name === '' || $last_name === '' || $email === '') {
        $error = 'First name, last name, and email are required.';
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    }
    else {
        // Check for email uniqueness (exclude current user).
        $chk = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? AND id != ?");
        mysqli_stmt_bind_param($chk, 'si', $email, $user_id);
        mysqli_stmt_execute($chk);
        if (mysqli_fetch_assoc(mysqli_stmt_get_result($chk))) {
            $error = 'That email is already in use by another account.';
        }
        mysqli_stmt_close($chk);
    }

    if ($error === '') {
        $old_values = [
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'email' => $user['email'],
            'phone' => $user['phone'],
        ];

        $upd = mysqli_prepare($conn,
            "UPDATE users
             SET first_name = ?, last_name = ?, email = ?, phone = ?
             WHERE id = ?"
        );
        mysqli_stmt_bind_param($upd, 'ssssi',
            $first_name, $last_name, $email, $phone, $user_id
        );

        if (mysqli_stmt_execute($upd)) {
            // Log the audit trail.
            log_audit($conn, 'Update', 'users', $user_id,
                $old_values,
            ['first_name' => $first_name, 'last_name' => $last_name,
                'email' => $email, 'phone' => $phone]
            );

            // Update session variables so the topbar reflects
            // the new name immediately.
            $_SESSION['first_name'] = $first_name;
            $_SESSION['last_name'] = $last_name;
            $_SESSION['email'] = $email;

            // Refresh $user for the form.
            $user['first_name'] = $first_name;
            $user['last_name'] = $last_name;
            $user['email'] = $email;
            $user['phone'] = $phone;

            $success = 'Profile updated successfully!';
        }
        else {
            $error = 'Failed to update profile.';
        }
        mysqli_stmt_close($upd);
    }
}

require_once '../includes/header.php';
?>

<!-- Profile header -->
<div class="profile-header">
    <div class="profile-avatar">
        <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
    </div>
    <div class="profile-info">
        <h2><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h2>
        <p><?php echo htmlspecialchars($user['email']); ?> &middot;
           <?php echo ucfirst($user['role']); ?> &middot;
           Joined <?php echo date('M Y', strtotime($user['created_at'])); ?></p>
    </div>
</div>

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

<div class="card" style="max-width:600px;">
    <h3 style="margin-bottom:20px;">Edit Profile</h3>
    <form method="POST" action="">
        <div class="form-row">
            <div class="form-group">
                <label for="first_name">First Name *</label>
                <input type="text" id="first_name" name="first_name"
                       class="form-control"
                       value="<?php echo htmlspecialchars($user['first_name']); ?>"
                       required>
            </div>
            <div class="form-group">
                <label for="last_name">Last Name *</label>
                <input type="text" id="last_name" name="last_name"
                       class="form-control"
                       value="<?php echo htmlspecialchars($user['last_name']); ?>"
                       required>
            </div>
        </div>

        <div class="form-group">
            <label for="email">Email Address *</label>
            <input type="email" id="email" name="email"
                   class="form-control"
                   value="<?php echo htmlspecialchars($user['email']); ?>"
                   required>
        </div>

        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="text" id="phone" name="phone"
                   class="form-control" placeholder="09171234567"
                   value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
        </div>

        <button type="submit" class="btn btn-primary">
            <img src="https://cdn-icons-png.flaticon.com/512/845/845646.png" alt="Save">
            Save Changes
        </button>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
>>>>>>> 047237f57cd0fd2e115602c620d70bf3bd6e29a0
