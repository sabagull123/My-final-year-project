<?php
// ============================================================
// Admin Auth Guard
// Path: cawa/includes/admin_auth.php
// Include this at the very top of every page inside admin/
// ============================================================

require_once __DIR__ . '/../config/db.php';

// role_id = 1 means Admin. If not logged in or not admin, kick to login.
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login.php");
    exit;
}
?>