<?php
$active = 'quizzes';
require_once '../includes/admin_auth.php';

$error = "";
$success = "";
$edit_quiz = null;

// ---------- Add Quiz ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['form_action'] === 'add_quiz') {
    $topic_id = (int) $_POST['topic_id'];
    $quiz_title = trim($_POST['quiz_title']);

    if ($topic_id === 0 || $quiz_title === "") {
        $error = "Topic and quiz title are required.";
    } else {
        mysqli_query($conn, "INSERT INTO quizzes (topic_id, quiz_title, total_questions) VALUES ($topic_id, '$quiz_title', 0)");
        $success = "Quiz created successfully. Now add questions to it.";
    }
}

// ---------- Edit Quiz ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['form_action'] === 'edit_quiz') {
    $quiz_id = (int) $_POST['quiz_id'];
    $topic_id = (int) $_POST['topic_id'];
    $quiz_title = trim($_POST['quiz_title']);

    if ($topic_id === 0 || $quiz_title === "") {
        $error = "Topic and quiz title are required.";
    } else {
        mysqli_query($conn, "UPDATE quizzes SET topic_id=$topic_id, quiz_title='$quiz_title' WHERE quiz_id=$quiz_id");
        $success = "Quiz updated successfully.";
    }
}

// ---------- Delete Quiz (also deletes its questions, attempts, answers to keep DB clean) ----------
if (isset($_GET['delete_quiz'])) {
    $qid = (int) $_GET['delete_quiz'];
    // Delete answers -> attempts -> questions -> quiz, in dependency order
    mysqli_query($conn, "DELETE qa FROM quiz_answers qa
                          JOIN quiz_attempts att ON qa.attempt_id = att.attempt_id
                          WHERE att.quiz_id = $qid");
    mysqli_query($conn, "DELETE FROM quiz_attempts WHERE quiz_id = $qid");
    mysqli_query($conn, "DELETE FROM questions WHERE quiz_id = $qid");
    mysqli_query($conn, "DELETE FROM quizzes WHERE quiz_id = $qid");
    header("Location: quizzes.php?deleted=1");
    exit;
}

// ---------- Load quiz into edit modal ----------
if (isset($_GET['edit_quiz'])) {
    $qid = (int) $_GET['edit_quiz'];
    $res = mysqli_query($conn, "SELECT * FROM quizzes WHERE quiz_id = $qid");
    if ($res && mysqli_num_rows($res) === 1) $edit_quiz = mysqli_fetch_assoc($res);
}

// ---------- Fetch data ----------
$topics_dropdown = mysqli_query($conn, "SELECT * FROM topics ORDER BY title ASC");
$quizzes = mysqli_query($conn, "SELECT q.*, t.title AS topic_title FROM quizzes q
                                 JOIN topics t ON q.topic_id = t.topic_id
                                 ORDER BY q.quiz_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Quizzes - CAWA</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
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

                <?php if ($error): ?><div class="alert alert-error" style="margin-bottom:16px;"><?php echo $error; ?></div><?php endif; ?>
                <?php if ($success): ?><div class="alert alert-success" style="margin-bottom:16px;"><?php echo $success; ?></div><?php endif; ?>
                <?php if (isset($_GET['deleted'])): ?><div class="alert alert-success" style="margin-bottom:16px;">Quiz deleted successfully.</div><?php endif; ?>

                <?php if (mysqli_num_rows($topics_dropdown) === 0): ?>
                    <div class="alert alert-error" style="margin-bottom:16px;">
                        You need to add at least one Topic (in Content & Topics) before creating a quiz.
                    </div>
                <?php endif; ?>

                <div class="page-header">
                    <span></span>
                    <button class="btn-primary" onclick="document.getElementById('addQuizModal').classList.add('open')">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                        Add Quiz
                    </button>
                </div>

                <div class="panel">
                    <table>
                        <tr>
                            <th>Quiz Title</th>
                            <th>Topic</th>
                            <th>Questions</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                        <?php if (mysqli_num_rows($quizzes) === 0): ?>
                            <tr><td colspan="4" style="color:var(--text-muted)">No quizzes yet.</td></tr>
                        <?php else: ?>
                            <?php while ($qz = mysqli_fetch_assoc($quizzes)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($qz['quiz_title']); ?></td>
                                    <td style="color:var(--text-muted);"><?php echo htmlspecialchars($qz['topic_title']); ?></td>
                                    <td><?php echo (int) $qz['total_questions']; ?></td>
                                    <td style="text-align:right;">
                                        <a href="quiz_questions.php?quiz_id=<?php echo $qz['quiz_id']; ?>" class="icon-btn" title="Manage Questions">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                                        </a>
                                        <a href="quizzes.php?edit_quiz=<?php echo $qz['quiz_id']; ?>" class="icon-btn" title="Edit">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                        </a>
                                        <button class="icon-btn danger" title="Delete"
                                            onclick="if(confirm('Delete this quiz and all its questions?')) window.location='quizzes.php?delete_quiz=<?php echo $qz['quiz_id']; ?>'">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2m3 0l-1 14a2 2 0 01-2 2H7a2 2 0 01-2-2L4 6"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Quiz Modal -->
    <div class="modal-overlay" id="addQuizModal">
        <div class="modal-box">
            <h3>Add Quiz</h3>
            <form method="POST" action="quizzes.php">
                <input type="hidden" name="form_action" value="add_quiz">
                <div class="field">
                    <label>Topic</label>
                    <select name="topic_id" required>
                        <option value="">Select topic</option>
                        <?php
                        mysqli_data_seek($topics_dropdown, 0);
                        while ($t = mysqli_fetch_assoc($topics_dropdown)): ?>
                            <option value="<?php echo $t['topic_id']; ?>"><?php echo htmlspecialchars($t['title']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="field">
                    <label>Quiz title</label>
                    <input type="text" name="quiz_title" placeholder="e.g. Phishing Basics Quiz" required>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="document.getElementById('addQuizModal').classList.remove('open')">Cancel</button>
                    <button type="submit" class="btn-submit-full">Create Quiz</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Quiz Modal -->
    <div class="modal-overlay <?php echo $edit_quiz ? 'open' : ''; ?>">
        <div class="modal-box">
            <h3>Edit Quiz</h3>
            <?php if ($edit_quiz): ?>
            <form method="POST" action="quizzes.php">
                <input type="hidden" name="form_action" value="edit_quiz">
                <input type="hidden" name="quiz_id" value="<?php echo $edit_quiz['quiz_id']; ?>">
                <div class="field">
                    <label>Topic</label>
                    <select name="topic_id" required>
                        <?php
                        mysqli_data_seek($topics_dropdown, 0);
                        while ($t = mysqli_fetch_assoc($topics_dropdown)): ?>
                            <option value="<?php echo $t['topic_id']; ?>" <?php echo $t['topic_id'] == $edit_quiz['topic_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($t['title']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="field">
                    <label>Quiz title</label>
                    <input type="text" name="quiz_title" value="<?php echo htmlspecialchars($edit_quiz['quiz_title']); ?>" required>
                </div>
                <div class="modal-actions">
                    <a href="quizzes.php" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-submit-full">Save Changes</button>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>