<?php
$active = 'dashboard';
require_once '../includes/student_auth.php';

$user_id = $_SESSION['user_id'];

// Stats for this student
$quizzes_taken = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM quiz_attempts WHERE user_id = $user_id"))['c'];
$avg_score_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(score) a FROM quiz_attempts WHERE user_id = $user_id"));
$avg_score = $avg_score_row['a'] !== null ? round($avg_score_row['a'], 1) : 0;
$total_topics = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM topics"))['c'];
$total_resources = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM resources"))['c'];

// Recent quiz attempts by this student
$recent_attempts = mysqli_query($conn, "
    SELECT q.quiz_title, a.score, a.attempt_date,
           (SELECT COUNT(*) FROM questions WHERE quiz_id = q.quiz_id) AS total_questions
    FROM quiz_attempts a
    JOIN quizzes q ON a.quiz_id = q.quiz_id
    WHERE a.user_id = $user_id
    ORDER BY a.attempt_date DESC LIMIT 5
");

// A few recently added topics to encourage exploring
$latest_topics = mysqli_query($conn, "
    SELECT t.*, c.category_name FROM topics t
    JOIN categories c ON t.category_id = c.category_id
    ORDER BY t.topic_id DESC LIMIT 3
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CAWA</title>
    <link rel="stylesheet" href="../assets/css/student.css">
</head>
<body>
    <div class="admin-shell">
        <?php include 'includes_sidebar.php'; ?>

        <div class="main-area">
            <div class="topbar">
                <h1>Welcome back, <?php echo htmlspecialchars(explode(' ', $_SESSION['full_name'])[0]); ?></h1>
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
                        <div class="label">Topics Available <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M4 4h16v16H4z"/></svg></div>
                        <div class="value"><?php echo $total_topics; ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="label">Resources <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 3v12"/></svg></div>
                        <div class="value"><?php echo $total_resources; ?></div>
                    </div>
                </div>

                <div class="panel" style="margin-bottom:28px;">
                    <h2>Your Recent Quiz Attempts</h2>
                    <table>
                        <tr><th>Quiz</th><th>Score</th><th>Date</th></tr>
                        <?php if (mysqli_num_rows($recent_attempts) === 0): ?>
                            <tr><td colspan="3" style="color:var(--text-muted)">You haven't attempted any quiz yet. <a href="quizzes.php" style="color:var(--accent-cyan);">Start one now &rarr;</a></td></tr>
                        <?php else: ?>
                            <?php while ($a = mysqli_fetch_assoc($recent_attempts)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($a['quiz_title']); ?></td>
                                    <td><?php echo (int) $a['score']; ?> / <?php echo (int) $a['total_questions']; ?></td>
                                    <td><?php echo date('d M Y', strtotime($a['attempt_date'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </table>
                </div>

                <div class="panel">
                    <h2>Newly Added Topics</h2>
                    <div class="card-grid">
                        <?php if (mysqli_num_rows($latest_topics) === 0): ?>
                            <p style="color:var(--text-muted);">No topics available yet.</p>
                        <?php else: ?>
                            <?php while ($t = mysqli_fetch_assoc($latest_topics)): ?>
                                <a href="topic_detail.php?id=<?php echo $t['topic_id']; ?>" class="content-card">
                                    <span class="tag"><?php echo htmlspecialchars($t['category_name']); ?></span>
                                    <h3><?php echo htmlspecialchars($t['title']); ?></h3>
                                    <p><?php echo htmlspecialchars(mb_strimwidth($t['content'], 0, 90, '...')); ?></p>
                                </a>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>