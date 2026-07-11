<?php
// ============================================================
// Student Auth Guard
// Path: cawa/includes/student_auth.php
// Include this at the very top of every page inside student/
// ============================================================

require_once __DIR__ . '/../config/db.php';

// role_id = 2 means Student. If not logged in or not a student, kick to login.
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header("Location: ../login.php");
    exit;
}
?>