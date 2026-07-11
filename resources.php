<?php
$active = 'resources';
require_once '../includes/student_auth.php';

$resources = mysqli_query($conn, "
    SELECT r.*, t.title AS topic_title, c.category_name
    FROM resources r
    JOIN topics t ON r.topic_id = t.topic_id
    JOIN categories c ON t.category_id = c.category_id
    ORDER BY r.resource_id DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resources - CAWA</title>
    <link rel="stylesheet" href="../assets/css/student.css">
</head>
<body>
    <div class="admin-shell">
        <?php include 'includes_sidebar.php'; ?>

        <div class="main-area">
            <div class="topbar">
                <h1>Resources</h1>
                <div class="who">
                    <div class="avatar"><?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?></div>
                    <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                </div>
            </div>

            <div class="content">
                <div class="panel">
                    <table>
                        <tr>
                            <th>Title</th>
                            <th>Topic</th>
                            <th>Category</th>
                            <th style="text-align:right;">Action</th>
                        </tr>
                        <?php if (mysqli_num_rows($resources) === 0): ?>
                            <tr><td colspan="4" style="color:var(--text-muted)">No resources available yet.</td></tr>
                        <?php else: ?>
                            <?php while ($r = mysqli_fetch_assoc($resources)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($r['title']); ?></td>
                                    <td style="color:var(--text-muted);"><?php echo htmlspecialchars($r['topic_title']); ?></td>
                                    <td><span class="tag"><?php echo htmlspecialchars($r['category_name']); ?></span></td>
                                    <td style="text-align:right;">
                                        <a href="download.php?id=<?php echo $r['resource_id']; ?>" class="btn-primary">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><path d="M12 3v12m0 0l-4-4m4 4l4-4"/><path d="M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2"/></svg>
                                            Download
                                        </a>
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