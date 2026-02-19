<?php
// ============================================================
// auth.php — Session & Role-Based Access Control (RBAC)
// ============================================================
// Include this file at the top of every protected page.
// It starts the session, and provides helper functions to
// check whether the user is logged in and what role they
// have.
// ============================================================

// session_start() tells PHP to begin tracking this visitor.
// It either creates a new session or resumes an existing one
// based on a cookie stored in the browser.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// --------------------------------------------------------
// HELPER: Get the current logged-in user's data
// --------------------------------------------------------
// Returns the full session array if the user is logged in,
// or NULL if they are a guest (not logged in).
// --------------------------------------------------------
function current_user()
{
    // If $_SESSION['user_id'] exists, the user has logged in.
    if (isset($_SESSION['user_id'])) {
        return [
            'id' => $_SESSION['user_id'],
            'first_name' => $_SESSION['first_name'],
            'last_name' => $_SESSION['last_name'],
            'email' => $_SESSION['email'],
            'role' => $_SESSION['role'],
        ];
    }
    return null;
}


// --------------------------------------------------------
// HELPER: Check if someone is logged in
// --------------------------------------------------------
function is_logged_in()
{
    return isset($_SESSION['user_id']);
}


// --------------------------------------------------------
// HELPER: Check specific roles
// --------------------------------------------------------
function is_admin()
{
    return (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
}

function is_tenant()
{
    return (isset($_SESSION['role']) && $_SESSION['role'] === 'tenant');
}

function is_guest_user()
{
    return (isset($_SESSION['role']) && $_SESSION['role'] === 'guest');
}


// --------------------------------------------------------
// GUARD: Require login
// --------------------------------------------------------
// Call this at the top of any page that requires the user
// to be logged in.  If they aren't, they get kicked back
// to the login page.
// --------------------------------------------------------
function require_login()
{
    if (!is_logged_in()) {
        // Store a flash message so the login page can tell
        // them why they were redirected.
        $_SESSION['flash_error'] = 'Please log in to access that page.';
        header('Location: /Apartment Management System/login.php');
        exit; // stop running the rest of the page
    }
}


// --------------------------------------------------------
// GUARD: Require a specific role
// --------------------------------------------------------
// Call this after require_login() to enforce that only
// certain roles can see a page.
// Example:  require_role('admin');
// --------------------------------------------------------
function require_role($role)
{
    require_login();
    if ($_SESSION['role'] !== $role) {
        // The user IS logged in but does NOT have the right
        // role.  Send them back to their own dashboard.
        $_SESSION['flash_error'] = 'You do not have permission to access that page.';
        if ($_SESSION['role'] === 'admin') {
            header('Location: /Apartment Management System/admin/dashboard.php');
        }
        elseif ($_SESSION['role'] === 'tenant') {
            header('Location: /Apartment Management System/tenant/dashboard.php');
        }
        else {
            header('Location: /Apartment Management System/index.php');
        }
        exit;
    }
}


// --------------------------------------------------------
// HELPER: Set a flash message
// --------------------------------------------------------
// Flash messages are one-time messages that appear on the
// next page load and then disappear.
// --------------------------------------------------------
function set_flash($type, $message)
{
    // $type is 'success', 'error', 'warning', or 'info'.
    $_SESSION['flash_' . $type] = $message;
}


// --------------------------------------------------------
// HELPER: Display flash messages (auto-clears them)
// --------------------------------------------------------
// Call this inside the HTML body of any page.
// --------------------------------------------------------
function display_flash()
{
    $types = ['success', 'error', 'warning', 'info'];
    foreach ($types as $type) {
        $key = 'flash_' . $type;
        if (isset($_SESSION[$key])) {
            // Pick the right icon for each message type.
            $icons = [
                'success' => 'https://cdn-icons-png.flaticon.com/512/845/845646.png',
                'error' => 'https://cdn-icons-png.flaticon.com/512/399/399274.png',
                'warning' => 'https://cdn-icons-png.flaticon.com/512/564/564619.png',
                'info' => 'https://cdn-icons-png.flaticon.com/512/157/157933.png',
            ];
            echo '<div class="flash-message flash-' . $type . '">';
            echo '<img src="' . $icons[$type] . '" alt="' . $type . '">';
            echo htmlspecialchars($_SESSION[$key]);
            echo '</div>';
            // Remove it so it doesn't show again on the
            // next page load.
            unset($_SESSION[$key]);
        }
    }
}
