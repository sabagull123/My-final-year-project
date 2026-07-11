<?php
$active = 'quizzes';
require_once '../includes/student_auth.php';

if (!isset($_GET['attempt_id'])) {
    header("Location: quizzes.php");
    exit;
}
$attempt_id = (int) $_GET['attempt_id'];
$user_id = $_SESSION['user_id'];

// Make sure this attempt belongs to the logged-in student (security check)
$attempt_res = mysqli_query($conn, "
    SELECT a.*, q.quiz_title, q.quiz_id FROM quiz_attempts a
    JOIN quizzes q ON a.quiz_id = q.quiz_id
    WHERE a.attempt_id = $attempt_id AND a.user_id = $user_id
");
if (mysqli_num_rows($attempt_res) === 0) {
    header("Location: quizzes.php");
    exit;
}
$attempt = mysqli_fetch_assoc($attempt_res);

$total_questions = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM questions WHERE quiz_id = " . $attempt['quiz_id']))['c'];
$percentage = $total_questions > 0 ? round(($attempt['score'] / $total_questions) * 100) : 0;

// Breakdown: each question with the student's answer vs correct answer
$breakdown = mysqli_query($conn, "
    SELECT q.question, q.option_a, q.option_b, q.option_c, q.option_d, q.correct_option,
           ans.selected_option
    FROM questions q
    LEFT JOIN quiz_answers ans ON ans.question_id = q.question_id AND ans.attempt_id = $attempt_id
    WHERE q.quiz_id = " . $attempt['quiz_id'] . "
    ORDER BY q.question_id ASC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Result - CAWA</title>
    <link rel="stylesheet" href="../assets/css/student.css">
</head>
<body>
    <div class="admin-shell">
        <?php include 'includes_sidebar.php'; ?>

        <div class="main-area">
            <div class="topbar">
                <h1>Quiz Result</h1>
                <div class="who">
                    <div class="avatar"><?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?></div>
                    <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                </div>
            </div>

            <div class="content" style="max-width:700px;">

                <div class="panel" style="text-align:center; margin-bottom:24px;">
                    <p style="color:var(--text-muted); font-size:13px; text-transform:uppercase; letter-spacing:0.08em; font-family:'JetBrains Mono',monospace;">
                        <?php echo htmlspecialchars($attempt['quiz_title']); ?>
                    </p>
                    <div style="font-family:'Space Grotesk',sans-serif; font-size:48px; font-weight:700; color:var(--accent-cyan); margin:10px 0;">
                        <?php echo (int) $attempt['score']; ?> / <?php echo $total_questions; ?>
                    </div>
                    <p style="color:var(--text-muted); margin:0 0 16px;"><?php echo $percentage; ?>% correct</p>
                    <div class="progress-track" style="max-width:300px; margin:0 auto;">
                        <div class="progress-fill" style="width:<?php echo $percentage; ?>%;"></div>
                    </div>
                </div>

                <div class="panel">
                    <h2>Answer Breakdown</h2>
                    <?php $i = 1; while ($row = mysqli_fetch_assoc($breakdown)): ?>
                        <?php
                        $is_correct = $row['selected_option'] === $row['correct_option'];
                        $options = ['A' => $row['option_a'], 'B' => $row['option_b'], 'C' => $row['option_c'], 'D' => $row['option_d']];
                        ?>
                        <div style="margin-bottom:22px; padding-bottom:18px; border-bottom:1px solid var(--border-soft);">
                            <p style="font-weight:600; margin:0 0 10px;"><?php echo $i++; ?>. <?php echo htmlspecialchars($row['question']); ?></p>
                            <?php foreach ($options as $key => $text): ?>
                                <?php
                                $css_class = '';
                                if ($key === $row['correct_option']) $css_class = 'correct';
                                elseif ($key === $row['selected_option'] && !$is_correct) $css_class = 'incorrect';
                                ?>
                                <div class="quiz-option <?php echo $css_class; ?>" style="cursor:default;">
                                    <?php echo $key; ?>. <?php echo htmlspecialchars($text); ?>
                                    <?php if ($key === $row['correct_option']): ?>
                                        <span style="margin-left:auto; color:var(--accent-cyan); font-size:12px;">Correct answer</span>
                                    <?php elseif ($key === $row['selected_option']): ?>
                                        <span style="margin-left:auto; color:var(--accent-red); font-size:12px;">Your answer</span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endwhile; ?>
                </div>

                <div style="margin-top:20px; display:flex; gap:12px;">
                    <a href="quizzes.php" class="btn-secondary">Back to Quizzes</a>
                    <a href="take_quiz.php?quiz_id=<?php echo $attempt['quiz_id']; ?>" class="btn-primary">Retake Quiz</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>