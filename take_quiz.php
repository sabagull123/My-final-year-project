<?php
$active = 'quizzes';
require_once '../includes/student_auth.php';

if (!isset($_GET['quiz_id']) && !isset($_POST['quiz_id'])) {
    header("Location: quizzes.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// ---------- Handle Quiz Submission ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quiz_id = (int) $_POST['quiz_id'];
    $questions = mysqli_query($conn, "SELECT * FROM questions WHERE quiz_id = $quiz_id");

    $score = 0;
    $total = mysqli_num_rows($questions);
    $answers_to_save = []; // [question_id => selected_option]

    while ($q = mysqli_fetch_assoc($questions)) {
        $qid = $q['question_id'];
        $selected = isset($_POST['q_' . $qid]) ? $_POST['q_' . $qid] : '';
        $answers_to_save[$qid] = $selected;
        if ($selected === $q['correct_option']) {
            $score++;
        }
    }

    // Save the attempt
    mysqli_query($conn, "INSERT INTO quiz_attempts (quiz_id, user_id, score) VALUES ($quiz_id, $user_id, $score)");
    $attempt_id = mysqli_insert_id($conn);

    // Save each answer
    foreach ($answers_to_save as $qid => $selected) {
        $selected_safe = $selected === '' ? '' : mysqli_real_escape_string($conn, $selected);
        if ($selected_safe !== '') {
            mysqli_query($conn, "INSERT INTO quiz_answers (attempt_id, question_id, selected_option)
                                  VALUES ($attempt_id, $qid, '$selected_safe')");
        }
    }

    header("Location: quiz_result.php?attempt_id=$attempt_id");
    exit;
}

// ---------- Show the quiz form ----------
$quiz_id = (int) $_GET['quiz_id'];
$quiz_res = mysqli_query($conn, "SELECT * FROM quizzes WHERE quiz_id = $quiz_id");
if (mysqli_num_rows($quiz_res) === 0) {
    header("Location: quizzes.php");
    exit;
}
$quiz = mysqli_fetch_assoc($quiz_res);
$questions = mysqli_query($conn, "SELECT * FROM questions WHERE quiz_id = $quiz_id ORDER BY question_id ASC");

if (mysqli_num_rows($questions) === 0) {
    header("Location: quizzes.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($quiz['quiz_title']); ?> - CAWA</title>
    <link rel="stylesheet" href="../assets/css/student.css">
</head>
<body>
    <div class="admin-shell">
        <?php include 'includes_sidebar.php'; ?>

        <div class="main-area">
            <div class="topbar">
                <h1><?php echo htmlspecialchars($quiz['quiz_title']); ?></h1>
                <div class="who">
                    <div class="avatar"><?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?></div>
                    <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                </div>
            </div>

            <div class="content" style="max-width:700px;">
                <form method="POST" action="take_quiz.php">
                    <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">

                    <?php $i = 1; while ($q = mysqli_fetch_assoc($questions)): ?>
                        <div class="quiz-q">
                            <h3><?php echo $i++; ?>. <?php echo htmlspecialchars($q['question']); ?></h3>

                            <label class="quiz-option">
                                <input type="radio" name="q_<?php echo $q['question_id']; ?>" value="A" required>
                                <?php echo htmlspecialchars($q['option_a']); ?>
                            </label>
                            <label class="quiz-option">
                                <input type="radio" name="q_<?php echo $q['question_id']; ?>" value="B">
                                <?php echo htmlspecialchars($q['option_b']); ?>
                            </label>
                            <label class="quiz-option">
                                <input type="radio" name="q_<?php echo $q['question_id']; ?>" value="C">
                                <?php echo htmlspecialchars($q['option_c']); ?>
                            </label>
                            <label class="quiz-option">
                                <input type="radio" name="q_<?php echo $q['question_id']; ?>" value="D">
                                <?php echo htmlspecialchars($q['option_d']); ?>
                            </label>
                        </div>
                    <?php endwhile; ?>

                    <button type="submit" class="btn-primary" style="width:100%; padding:14px; justify-content:center; font-size:15px;">
                        Submit Quiz
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>