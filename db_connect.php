<?php
// ============================================================
// db_connect.php — HomeEase Database Connection
// ============================================================
// This file creates a connection between PHP and our MySQL
// database.  Every other PHP page will "include" this file
// so it can talk to the database.
// ============================================================


// --------------------------------------------------------
// STEP 1: Store the connection details in variables
// --------------------------------------------------------
// These four values tell PHP WHERE the database lives and
// HOW to log in.  If you change your MySQL username or
// password inside XAMPP, update them here too.
// --------------------------------------------------------

// "localhost" means the database is on the same computer
// where XAMPP is running (your own machine).
$db_host = 'localhost';

// "root" is the default MySQL username that XAMPP ships with.
$db_user = 'root';

// XAMPP's default MySQL password is empty (no password).
// In a real production server you would NEVER leave this blank.
$db_pass = '';

// This is the name of the database we created in homeease.sql.
$db_name = 'homeease';


// --------------------------------------------------------
// STEP 2: Open the connection with mysqli_connect()
// --------------------------------------------------------
// mysqli_connect() tries to connect to MySQL using the four
// values we just defined.  It returns a "connection object"
// that we store in the variable $conn.  Every future
// database call will pass $conn so PHP knows which
// connection to use.
// --------------------------------------------------------
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);


// --------------------------------------------------------
// STEP 3: Check if the connection was successful
// --------------------------------------------------------
// If something went wrong (e.g., MySQL is not running),
// $conn will be FALSE and mysqli_connect_error() will
// contain the reason.  We show a friendly message and stop
// the script so the rest of the page doesn't crash.
// --------------------------------------------------------
if (!$conn) {
    // die() prints a message and immediately stops PHP.
    die(
        '<h2 style="color:#c0392b;font-family:sans-serif;">'
        . 'Database Connection Failed</h2>'
        . '<p style="font-family:sans-serif;">'
        . 'Could not connect to MySQL. Please make sure '
        . 'XAMPP is running and the <strong>homeease</strong> '
        . 'database exists.</p>'
        . '<p style="font-family:monospace;color:#888;">'
        . 'Error: ' . mysqli_connect_error()
        . '</p>'
        );
}


// --------------------------------------------------------
// STEP 4: Set the character encoding to UTF-8
// --------------------------------------------------------
// This makes sure that special characters (like ñ, ü, or
// accented letters) are stored and retrieved correctly.
// Always do this right after connecting.
// --------------------------------------------------------
mysqli_set_charset($conn, 'utf8mb4');


// ============================================================
// DONE!  The variable $conn is now ready to use.
// Any PHP file that does:
//     require_once 'db_connect.php';
// will have access to $conn and can run queries like:
//     $result = mysqli_query($conn, "SELECT * FROM users");
// ============================================================
