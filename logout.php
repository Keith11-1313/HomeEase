<?php
// ============================================================
// logout.php — Destroy session and redirect to login
// ============================================================

// Start the session so we can access and destroy it.
session_start();

// session_unset() removes all data stored in $_SESSION.
session_unset();

// session_destroy() completely deletes the session file on
// the server.  After this, the visitor is no longer logged in.
session_destroy();

// Send the user back to the login page.
header('Location: /Apartment Management System/login.php');
exit;
