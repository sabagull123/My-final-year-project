<?php
$active = 'quizzes';
require_once '../includes/admin_auth.php';

$error = "";
$success = "";
$edit_question = null;

if (!isset($_GET['quiz_id'])) {
    header("Location: quizzes.php");
    exit;
}
$quiz_id = (int) $_GET['quiz_id'];

$quiz_res = mysqli_query($conn, "SELECT q.*, t.title AS topic_title FROM quizzes q
                                  JOIN topics t ON q.topic_id = t.topic_id
                                  WHERE q.quiz_id = $quiz_id");
if (mysqli_num_rows($quiz_res) === 0) {
    header("Location: quizzes.php");
    exit;
}
$quiz = mysqli_fetch_assoc($quiz_res);

// Helper: keep total_questions count in sync with the questions table
function refresh_question_count($conn, $quiz_id) {
    $count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM questions WHERE quiz_id = $quiz_id"))['c'];
    mysqli_query($conn, "UPDATE quizzes SET total_questions = $count WHERE quiz_id = $quiz_id");
}

// ---------- Add Question ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['form_action'] === 'add_question') {
    $question = trim($_POST['question']);
    $option_a = trim($_POST['option_a']);
    $option_b = trim($_POST['option_b']);
    $option_c = trim($_POST['option_c']);
    $option_d = trim($_POST['option_d']);
    $correct_option = $_POST['correct_option'];

    if ($question === "" || $option_a === "" || $option_b === "" || $option_c === "" || $option_d === "") {
        $error = "All fields are required.";
    } else {
        mysqli_query($conn, "INSERT INTO questions (quiz_id, question, option_a, option_b, option_c, option_d, correct_option)
                              VALUES ($quiz_id, '$question', '$option_a', '$option_b', '$option_c', '$option_d', '$correct_option')");
        refresh_question_count($conn, $quiz_id);
        $success = "Question added successfully.";
    }
}

// ---------- Edit Question ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['form_action'] === 'edit_question') {
    $question_id = (int) $_POST['question_id'];
    $question = trim($_POST['question']);
    $option_a = trim($_POST['option_a']);
    $option_b = trim($_POST['option_b']);
    $option_c = trim($_POST['option_c']);
    $option_d = trim($_POST['option_d']);
    $correct_option = $_POST['correct_option'];

    if ($question === "" || $option_a === "" || $option_b === "" || $option_c === "" || $option_d === "") {
        $error = "All fields are required.";
    } else {
        mysqli_query($conn, "UPDATE questions SET question='$question', option_a='$option_a', option_b='$option_b',
                              option_c='$option_c', option_d='$option_d', correct_option='$correct_option'
                              WHERE question_id = $question_id");
        $success = "Question updated successfully.";
    }
}

// ---------- Delete Question ----------
if (isset($_GET['delete_question'])) {
    $qid = (int) $_GET['delete_question'];
    mysqli_query($conn, "DELETE FROM questions WHERE question_id = $qid");
    refresh_question_count($conn, $quiz_id);
    header("Location: quiz_questions.php?quiz_id=$quiz_id&deleted=1");
    exit;
}

// ---------- Load question into edit modal ----------
if (isset($_GET['edit_question'])) {
    $qid = (int) $_GET['edit_question'];
    $res = mysqli_query($conn, "SELECT * FROM questions WHERE question_id = $qid");
    if ($res && mysqli_num_rows($res) === 1) $edit_question = mysqli_fetch_assoc($res);
}

$questions = mysqli_query($conn, "SELECT * FROM questions WHERE quiz_id = $quiz_id ORDER BY question_id ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Questions - CAWA</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
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

            <div class="content">
                <a href="quizzes.php" class="switch-link" style="display:inline-block; margin-bottom:18px; text-align:left;">&larr; Back to all quizzes</a>

                <?php if ($error): ?><div class="alert alert-error" style="margin-bottom:16px;"><?php echo $error; ?></div><?php endif; ?>
                <?php if ($success): ?><div class="alert alert-success" style="margin-bottom:16px;"><?php echo $success; ?></div><?php endif; ?>
                <?php if (isset($_GET['deleted'])): ?><div class="alert alert-success" style="margin-bottom:16px;">Question deleted successfully.</div><?php endif; ?>

                <div class="page-header">
                    <span style="color:var(--text-muted); font-size:14px;">
                        Topic: <?php echo htmlspecialchars($quiz['topic_title']); ?> &nbsp;•&nbsp;
                        <?php echo (int) $quiz['total_questions']; ?> question(s)
                    </span>
                    <button class="btn-primary" onclick="document.getElementById('addQModal').classList.add('open')">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                        Add Question
                    </button>
                </div>

                <?php if (mysqli_num_rows($questions) === 0): ?>
                    <div class="panel"><p style="color:var(--text-muted); margin:0;">No questions yet. Click "Add Question" to create the first one.</p></div>
                <?php else: ?>
                    <?php $i = 1; while ($q = mysqli_fetch_assoc($questions)): ?>
                    <div class="panel" style="margin-bottom:16px;">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                            <h2 style="max-width:80%;"><?php echo $i++; ?>. <?php echo htmlspecialchars($q['question']); ?></h2>
                            <div>
                                <a href="quiz_questions.php?quiz_id=<?php echo $quiz_id; ?>&edit_question=<?php echo $q['question_id']; ?>" class="icon-btn" title="Edit">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                </a>
                                <button class="icon-btn danger" title="Delete"
                                    onclick="if(confirm('Delete this question?')) window.location='quiz_questions.php?quiz_id=<?php echo $quiz_id; ?>&delete_question=<?php echo $q['question_id']; ?>'">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2m3 0l-1 14a2 2 0 01-2 2H7a2 2 0 01-2-2L4 6"/></svg>
                                </button>
                            </div>
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:12px; font-size:14px;">
                            <div style="padding:10px 12px; border-radius:8px; border:1px solid var(--border-soft); <?php echo $q['correct_option']=='A' ? 'background:var(--accent-cyan-dim); color:var(--accent-cyan);' : 'color:var(--text-muted);'; ?>">A. <?php echo htmlspecialchars($q['option_a']); ?></div>
                            <div style="padding:10px 12px; border-radius:8px; border:1px solid var(--border-soft); <?php echo $q['correct_option']=='B' ? 'background:var(--accent-cyan-dim); color:var(--accent-cyan);' : 'color:var(--text-muted);'; ?>">B. <?php echo htmlspecialchars($q['option_b']); ?></div>
                            <div style="padding:10px 12px; border-radius:8px; border:1px solid var(--border-soft); <?php echo $q['correct_option']=='C' ? 'background:var(--accent-cyan-dim); color:var(--accent-cyan);' : 'color:var(--text-muted);'; ?>">C. <?php echo htmlspecialchars($q['option_c']); ?></div>
                            <div style="padding:10px 12px; border-radius:8px; border:1px solid var(--border-soft); <?php echo $q['correct_option']=='D' ? 'background:var(--accent-cyan-dim); color:var(--accent-cyan);' : 'color:var(--text-muted);'; ?>">D. <?php echo htmlspecialchars($q['option_d']); ?></div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Add Question Modal -->
    <div class="modal-overlay" id="addQModal">
        <div class="modal-box" style="max-width:460px;">
            <h3>Add Question</h3>
            <form method="POST" action="quiz_questions.php?quiz_id=<?php echo $quiz_id; ?>">
                <input type="hidden" name="form_action" value="add_question">
                <div class="field">
                    <label>Question</label>
                    <textarea name="question" rows="2" required></textarea>
                </div>
                <div class="field"><label>Option A</label><input type="text" name="option_a" required></div>
                <div class="field"><label>Option B</label><input type="text" name="option_b" required></div>
                <div class="field"><label>Option C</label><input type="text" name="option_c" required></div>
                <div class="field"><label>Option D</label><input type="text" name="option_d" required></div>
                <div class="field">
                    <label>Correct option</label>
                    <select name="correct_option" required>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                    </select>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="document.getElementById('addQModal').classList.remove('open')">Cancel</button>
                    <button type="submit" class="btn-submit-full">Add Question</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Question Modal -->
    <div class="modal-overlay <?php echo $edit_question ? 'open' : ''; ?>">
        <div class="modal-box" style="max-width:460px;">
            <h3>Edit Question</h3>
            <?php if ($edit_question): ?>
            <form method="POST" action="quiz_questions.php?quiz_id=<?php echo $quiz_id; ?>">
                <input type="hidden" name="form_action" value="edit_question">
                <input type="hidden" name="question_id" value="<?php echo $edit_question['question_id']; ?>">
                <div class="field">
                    <label>Question</label>
                    <textarea name="question" rows="2" required><?php echo htmlspecialchars($edit_question['question']); ?></textarea>
                </div>
                <div class="field"><label>Option A</label><input type="text" name="option_a" value="<?php echo htmlspecialchars($edit_question['option_a']); ?>" required></div>
                <div class="field"><label>Option B</label><input type="text" name="option_b" value="<?php echo htmlspecialchars($edit_question['option_b']); ?>" required></div>
                <div class="field"><label>Option C</label><input type="text" name="option_c" value="<?php echo htmlspecialchars($edit_question['option_c']); ?>" required></div>
                <div class="field"><label>Option D</label><input type="text" name="option_d" value="<?php echo htmlspecialchars($edit_question['option_d']); ?>" required></div>
                <div class="field">
                    <label>Correct option</label>
                    <select name="correct_option" required>
                        <?php foreach (['A','B','C','D'] as $opt): ?>
                            <option value="<?php echo $opt; ?>" <?php echo $edit_question['correct_option'] === $opt ? 'selected' : ''; ?>><?php echo $opt; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="modal-actions">
                    <a href="quiz_questions.php?quiz_id=<?php echo $quiz_id; ?>" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-submit-full">Save Changes</button>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>