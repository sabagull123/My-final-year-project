<?php
$active = 'progress';
require_once '../includes/student_auth.php';

$user_id = $_SESSION['user_id'];

// Overall stats
$quizzes_taken = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM quiz_attempts WHERE user_id = $user_id"))['c'];
$avg_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(score) a FROM quiz_attempts WHERE user_id = $user_id"));
$avg_score = $avg_row['a'] !== null ? round($avg_row['a'], 1) : 0;
$downloads_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM downloads WHERE user_id = $user_id"))['c'];

// Full quiz attempt history
$attempts = mysqli_query($conn, "
    SELECT q.quiz_title,
           (SELECT COUNT(*) FROM questions WHERE quiz_id = q.quiz_id) AS total_questions,
           a.score, a.attempt_date
    FROM quiz_attempts a
    JOIN quizzes q ON a.quiz_id = q.quiz_id
    WHERE a.user_id = $user_id
    ORDER BY a.attempt_date DESC
");

// Download history
$downloads = mysqli_query($conn, "
    SELECT r.title, d.download_date
    FROM downloads d
    JOIN resources r ON d.resource_id = r.resource_id
    WHERE d.user_id = $user_id
    ORDER BY d.download_date DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Progress - CAWA</title>
    <link rel="stylesheet" href="../assets/css/student.css">
</head>
<body>
    <div class="admin-shell">
        <?php include 'includes_sidebar.php'; ?>

        <div class="main-area">
            <div class="topbar">
                <h1>My Progress</h1>
                <div class="who">
                    <div class="avatar"><?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?></div>
                    <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                </div>
            </div>

            <div class="content">
                <div class="stat-grid">
                    <div class="stat-card">
                        <div class="label">Quizzes Taken <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M9 11l3 3L22 4"/></svg></div>
                        <div class="value"><?php echo $quizzes_taken; ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="label">Average Score <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M4 20V10M12 20V4M20 20v-7"/></svg></div>
                        <div class="value"><?php echo $avg_score; ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="label">Resources Downloaded <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 3v12"/></svg></div>
                        <div class="value"><?php echo $downloads_count; ?></div>
                    </div>
                </div>

                <div class="panel" style="margin-bottom:28px;">
                    <h2>Quiz History</h2>
                    <table>
                        <tr><th>Quiz</th><th>Score</th><th>Date</th></tr>
                        <?php if (mysqli_num_rows($attempts) === 0): ?>
                            <tr><td colspan="3" style="color:var(--text-muted)">No quiz attempts yet.</td></tr>
                        <?php else: ?>
                            <?php while ($a = mysqli_fetch_assoc($attempts)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($a['quiz_title']); ?></td>
                                    <td><?php echo (int)$a['score']; ?> / <?php echo (int)$a['total_questions']; ?></td>
                                    <td><?php echo date('d M Y, h:i A', strtotime($a['attempt_date'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </table>
                </div>

                <div class="panel">
                    <h2>Download History</h2>
                    <table>
                        <tr><th>Resource</th><th>Downloaded On</th></tr>
                        <?php if (mysqli_num_rows($downloads) === 0): ?>
                            <tr><td colspan="2" style="color:var(--text-muted)">No downloads yet.</td></tr>
                        <?php else: ?>
                            <?php while ($d = mysqli_fetch_assoc($downloads)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($d['title']); ?></td>
                                    <td><?php echo date('d M Y, h:i A', strtotime($d['download_date'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>