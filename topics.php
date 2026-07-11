<?php
$active = 'topics';
require_once '../includes/student_auth.php';

$selected_category = isset($_GET['category']) ? (int) $_GET['category'] : 0;

$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY category_name ASC");

if ($selected_category > 0) {
    $topics = mysqli_query($conn, "
        SELECT t.*, c.category_name FROM topics t
        JOIN categories c ON t.category_id = c.category_id
        WHERE t.category_id = $selected_category
        ORDER BY t.title ASC
    ");
} else {
    $topics = mysqli_query($conn, "
        SELECT t.*, c.category_name FROM topics t
        JOIN categories c ON t.category_id = c.category_id
        ORDER BY t.title ASC
    ");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learning Content - CAWA</title>
    <link rel="stylesheet" href="../assets/css/student.css">
</head>
<body>
    <div class="admin-shell">
        <?php include 'includes_sidebar.php'; ?>

        <div class="main-area">
            <div class="topbar">
                <h1>Learning Content</h1>
                <div class="who">
                    <div class="avatar"><?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?></div>
                    <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                </div>
            </div>

            <div class="content">

                <!-- Category filter chips -->
                <div style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:24px;">
                    <a href="topics.php" class="btn-secondary" style="<?php echo $selected_category === 0 ? 'border-color:var(--accent-cyan); color:var(--accent-cyan);' : ''; ?>">All Topics</a>
                    <?php while ($c = mysqli_fetch_assoc($categories)): ?>
                        <a href="topics.php?category=<?php echo $c['category_id']; ?>" class="btn-secondary"
                           style="<?php echo $selected_category === (int)$c['category_id'] ? 'border-color:var(--accent-cyan); color:var(--accent-cyan);' : ''; ?>">
                            <?php echo htmlspecialchars($c['category_name']); ?>
                        </a>
                    <?php endwhile; ?>
                </div>

                <?php if (mysqli_num_rows($topics) === 0): ?>
                    <div class="panel"><p style="color:var(--text-muted); margin:0;">No topics found in this category yet.</p></div>
                <?php else: ?>
                    <div class="card-grid">
                        <?php while ($t = mysqli_fetch_assoc($topics)): ?>
                            <a href="topic_detail.php?id=<?php echo $t['topic_id']; ?>" class="content-card">
                                <span class="tag"><?php echo htmlspecialchars($t['category_name']); ?></span>
                                <h3><?php echo htmlspecialchars($t['title']); ?></h3>
                                <p><?php echo htmlspecialchars(mb_strimwidth($t['content'], 0, 110, '...')); ?></p>
                            </a>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>