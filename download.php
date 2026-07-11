<?php
require_once '../includes/student_auth.php';

if (!isset($_GET['id'])) {
    header("Location: resources.php");
    exit;
}

$resource_id = (int) $_GET['id'];
$user_id = $_SESSION['user_id'];

$res = mysqli_query($conn, "SELECT * FROM resources WHERE resource_id = $resource_id");
if (mysqli_num_rows($res) === 0) {
    header("Location: resources.php");
    exit;
}
$resource = mysqli_fetch_assoc($res);

$full_path = __DIR__ . '/../' . $resource['file_path'];

if (!file_exists($full_path)) {
    die("Sorry, this file could not be found on the server.");
}

// Log this download in the history table
mysqli_query($conn, "INSERT INTO downloads (resource_id, user_id) VALUES ($resource_id, $user_id)");

// Serve the file to the browser as a download
header('Content-Description: File Transfer');
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . basename($resource['title']) . '.pdf"');
header('Content-Length: ' . filesize($full_path));
header('Cache-Control: must-revalidate');
readfile($full_path);
exit;
?>