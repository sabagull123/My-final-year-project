<?php
$active = 'topics';
require_once '../includes/student_auth.php';

if (!isset($_GET['id'])) {
    header("Location: topics.php");
    exit;
}
$topic_id = (int) $_GET['id'];

$res = mysqli_query($conn, "
    SELECT t.*, c.category_name FROM topics t
    JOIN categories c ON t.category_id = c.category_id
    WHERE t.topic_id = $topic_id
");
if (mysqli_num_rows($res) === 0) {
    header("Location: topics.php");
    exit;
}
$topic = mysqli_fetch_assoc($res);

// Resources linked to this topic
$resources = mysqli_query($conn, "SELECT * FROM resources WHERE topic_id = $topic_id ORDER BY resource_id DESC");

// Quizzes linked to this topic
$quizzes = mysqli_query($conn, "SELECT * FROM quizzes WHERE topic_id = $topic_id ORDER BY quiz_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($topic['title']); ?> - CAWA</title>
    <link rel="stylesheet" href="../assets/css/student.css">
</head>
<body>
    <div class="admin-shell">
        <?php include 'includes_sidebar.php'; ?>

        <div class="main-area">
            <div class="topbar">
                <h1>Topic Detail</h1>
                <div class="who">
                    <div class="avatar"><?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?></div>
                    <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                </div>
            </div>

            <div class="content">
                <a href="topics.php" class="btn-secondary" style="display:inline-flex; margin-bottom:20px;">&larr; Back to Learning Content</a>

                <div class="panel" style="margin-bottom:24px;">
                    <span class="tag" style="margin-bottom:14px; display:inline-block;"><?php echo htmlspecialchars($topic['category_name']); ?></span>
                    <h2 style="font-size:24px; margin:0 0 16px;"><?php echo htmlspecialchars($topic['title']); ?></h2>
                    <p style="font-size:15px; line-height:1.7; color:var(--text-primary); margin:0;">
                        <?php echo nl2br(htmlspecialchars($topic['content'])); ?>
                    </p>
                </div>

                <?php if (mysqli_num_rows($quizzes) > 0): ?>
                <div class="panel" style="margin-bottom:24px;">
                    <h2>Test Your Knowledge</h2>
                    <?php while ($q = mysqli_fetch_assoc($quizzes)): ?>
                        <div style="display:flex; justify-content:space-between; align-items:center; padding:14px 0; border-bottom:1px solid var(--border-soft);">
                            <span><?php echo htmlspecialchars($q['quiz_title']); ?> <span style="color:var(--text-muted); font-size:13px;">(<?php echo (int)$q['total_questions']; ?> questions)</span></span>
                            <a href="take_quiz.php?quiz_id=<?php echo $q['quiz_id']; ?>" class="btn-primary">Take Quiz</a>
                        </div>
                    <?php endwhile; ?>
                </div>
                <?php endif; ?>

                <?php if (mysqli_num_rows($resources) > 0): ?>
                <div class="panel">
                    <h2>Related Resources</h2>
                    <?php while ($r = mysqli_fetch_assoc($resources)): ?>
                        <div style="display:flex; justify-content:space-between; align-items:center; padding:14px 0; border-bottom:1px solid var(--border-soft);">
                            <span><?php echo htmlspecialchars($r['title']); ?></span>
                            <a href="download.php?id=<?php echo $r['resource_id']; ?>" class="btn-secondary">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><path d="M12 3v12m0 0l-4-4m4 4l4-4"/><path d="M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2"/></svg>
                                Download
                            </a>
                        </div>
                    <?php endwhile; ?>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</body>
</html>