<?php
$active = 'feedback';
require_once '../includes/student_auth.php';

$user_id = $_SESSION['user_id'];
$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message']);
    if ($message === "") {
        $error = "Please write your feedback before submitting.";
    } else {
        mysqli_query($conn, "INSERT INTO feedback (user_id, message) VALUES ($user_id, '$message')");
        $success = "Thank you! Your feedback has been submitted.";
    }
}

// Show this student's own past feedback
$my_feedback = mysqli_query($conn, "SELECT * FROM feedback WHERE user_id = $user_id ORDER BY submitted_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback - CAWA</title>
    <link rel="stylesheet" href="../assets/css/student.css">
</head>
<body>
    <div class="admin-shell">
        <?php include 'includes_sidebar.php'; ?>

        <div class="main-area">
            <div class="topbar">
                <h1>Give Feedback</h1>
                <div class="who">
                    <div class="avatar"><?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?></div>
                    <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                </div>
            </div>

            <div class="content" style="max-width:600px;">

                <?php if ($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>
                <?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>

                <div class="panel" style="margin-bottom:24px;">
                    <h2>Share your thoughts</h2>
                    <form method="POST" action="feedback.php">
                        <div class="field">
                            <label>Your feedback</label>
                            <textarea name="message" rows="5" placeholder="Tell us what you think about CAWA, or suggest improvements..." required></textarea>
                        </div>
                        <button type="submit" class="btn-primary" style="width:100%; justify-content:center; padding:12px;">Submit Feedback</button>
                    </form>
                </div>

                <?php if (mysqli_num_rows($my_feedback) > 0): ?>
                <div class="panel">
                    <h2>Your Previous Feedback</h2>
                    <?php while ($f = mysqli_fetch_assoc($my_feedback)): ?>
                        <div style="padding:14px 0; border-bottom:1px solid var(--border-soft);">
                            <p style="margin:0 0 6px;"><?php echo htmlspecialchars($f['message']); ?></p>
                            <span style="color:var(--text-muted); font-size:12px;"><?php echo date('d M Y', strtotime($f['submitted_at'])); ?></span>
                        </div>
                    <?php endwhile; ?>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</body>
</html>