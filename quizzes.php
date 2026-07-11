<?php
$active = 'quizzes';
require_once '../includes/student_auth.php';

$user_id = $_SESSION['user_id'];

$quizzes = mysqli_query($conn, "
    SELECT q.*, t.title AS topic_title,
        (SELECT score FROM quiz_attempts WHERE quiz_id = q.quiz_id AND user_id = $user_id ORDER BY attempt_date DESC LIMIT 1) AS last_score,
        (SELECT COUNT(*) FROM quiz_attempts WHERE quiz_id = q.quiz_id AND user_id = $user_id) AS attempt_count
    FROM quizzes q
    JOIN topics t ON q.topic_id = t.topic_id
    ORDER BY q.quiz_id DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quizzes - CAWA</title>
    <link rel="stylesheet" href="../assets/css/student.css">
</head>
<body>
    <div class="admin-shell">
        <?php include 'includes_sidebar.php'; ?>

        <div class="main-area">
            <div class="topbar">
                <h1>Quizzes</h1>
                <div class="who">
                    <div class="avatar"><?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?></div>
                    <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                </div>
            </div>

            <div class="content">
                <div class="panel">
                    <table>
                        <tr>
                            <th>Quiz</th>
                            <th>Topic</th>
                            <th>Questions</th>
                            <th>Your Last Score</th>
                            <th style="text-align:right;">Action</th>
                        </tr>
                        <?php if (mysqli_num_rows($quizzes) === 0): ?>
                            <tr><td colspan="5" style="color:var(--text-muted)">No quizzes available yet.</td></tr>
                        <?php else: ?>
                            <?php while ($q = mysqli_fetch_assoc($quizzes)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($q['quiz_title']); ?></td>
                                    <td style="color:var(--text-muted);"><?php echo htmlspecialchars($q['topic_title']); ?></td>
                                    <td><?php echo (int) $q['total_questions']; ?></td>
                                    <td>
                                        <?php if ($q['attempt_count'] > 0): ?>
                                            <span class="badge active"><?php echo (int)$q['last_score']; ?> / <?php echo (int)$q['total_questions']; ?></span>
                                        <?php else: ?>
                                            <span style="color:var(--text-muted);">Not attempted</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align:right;">
                                        <?php if ((int)$q['total_questions'] === 0): ?>
                                            <span style="color:var(--text-muted); font-size:13px;">No questions yet</span>
                                        <?php else: ?>
                                            <a href="take_quiz.php?quiz_id=<?php echo $q['quiz_id']; ?>" class="btn-primary">
                                                <?php echo $q['attempt_count'] > 0 ? 'Retake' : 'Take Quiz'; ?>
                                            </a>
                                        <?php endif; ?>
                                    </td>
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