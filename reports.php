<?php
$active = 'reports';
require_once '../includes/admin_auth.php';

$attempts = mysqli_query($conn, "
    SELECT a.attempt_id, u.full_name, q.quiz_title, a.score, a.attempt_date,
           (SELECT COUNT(*) FROM questions WHERE quiz_id = q.quiz_id) AS total_questions
    FROM quiz_attempts a
    JOIN users u ON a.user_id = u.user_id
    JOIN quizzes q ON a.quiz_id = q.quiz_id
    ORDER BY a.attempt_date DESC
");

$feedback = mysqli_query($conn, "
    SELECT f.*, u.full_name, u.email
    FROM feedback f
    JOIN users u ON f.user_id = u.user_id
    ORDER BY f.submitted_at DESC
");

// Quick summary numbers
$total_attempts = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM quiz_attempts"))['c'];
$avg_score_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(score) a FROM quiz_attempts"));
$avg_score = $avg_score_row['a'] !== null ? round($avg_score_row['a'], 1) : 0;
$total_feedback = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM feedback"))['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Feedback - CAWA</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-shell">
        <?php include 'includes_sidebar.php'; ?>

        <div class="main-area">
            <div class="topbar">
                <h1>Reports & Feedback</h1>
                <div class="who">
                    <div class="avatar"><?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?></div>
                    <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                </div>
            </div>

            <div class="content">

                <div class="stat-grid" style="margin-bottom:32px;">
                    <div class="stat-card">
                        <div class="label">Total Quiz Attempts <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M9 11l3 3L22 4"/></svg></div>
                        <div class="value"><?php echo $total_attempts; ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="label">Average Score <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M4 20V10M12 20V4M20 20v-7"/></svg></div>
                        <div class="value"><?php echo $avg_score; ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="label">Feedback Received <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M21 11.5a8.4 8.4 0 01-8.9 8.4 8.5 8.5 0 01-4.3-1.2L3 20l1.3-4.8a8.4 8.4 0 01-1.2-4.4A8.4 8.4 0 0111.9 3a8.4 8.4 0 019.1 8.5z"/></svg></div>
                        <div class="value"><?php echo $total_feedback; ?></div>
                    </div>
                </div>

                <div class="panel" style="margin-bottom:28px;">
                    <h2>Quiz Attempts</h2>
                    <table>
                        <tr>
                            <th>Student</th>
                            <th>Quiz</th>
                            <th>Score</th>
                            <th>Date</th>
                        </tr>
                        <?php if (mysqli_num_rows($attempts) === 0): ?>
                            <tr><td colspan="4" style="color:var(--text-muted)">No quiz attempts yet.</td></tr>
                        <?php else: ?>
                            <?php while ($a = mysqli_fetch_assoc($attempts)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($a['full_name']); ?></td>
                                    <td style="color:var(--text-muted);"><?php echo htmlspecialchars($a['quiz_title']); ?></td>
                                    <td><?php echo (int) $a['score']; ?> / <?php echo (int) $a['total_questions']; ?></td>
                                    <td><?php echo date('d M Y, h:i A', strtotime($a['attempt_date'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </table>
                </div>

                <div class="panel">
                    <h2>Student Feedback</h2>
                    <table>
                        <tr>
                            <th>Student</th>
                            <th>Message</th>
                            <th>Date</th>
                        </tr>
                        <?php if (mysqli_num_rows($feedback) === 0): ?>
                            <tr><td colspan="3" style="color:var(--text-muted)">No feedback submitted yet.</td></tr>
                        <?php else: ?>
                            <?php while ($f = mysqli_fetch_assoc($feedback)): ?>
                                <tr>
                                    <td>
                                        <?php echo htmlspecialchars($f['full_name']); ?><br>
                                        <span style="color:var(--text-muted); font-size:12px;"><?php echo htmlspecialchars($f['email']); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($f['message']); ?></td>
                                    <td><?php echo date('d M Y', strtotime($f['submitted_at'])); ?></td>
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