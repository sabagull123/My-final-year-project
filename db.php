<?php
// ============================================================
// Database Connection File
// Path: cawa/config/db.php
// ============================================================

$host = "localhost";
$db_user = "root";
$db_pass = "";        // XAMPP default password is blank
$db_name = "cawa_db";

$conn = mysqli_connect($host, $db_user, $db_pass, $db_name);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Start session here so it's available on every page that includes this file
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>