<?php
$active = 'content';
require_once '../includes/admin_auth.php';

$error = "";
$success = "";
$edit_topic = null;

// ---------- Add Category ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['form_action'] === 'add_category') {
    $category_name = trim($_POST['category_name']);
    $description = trim($_POST['description']);

    if ($category_name === "") {
        $error = "Category name is required.";
    } else {
        $sql = "INSERT INTO categories (category_name, description) VALUES ('$category_name', '$description')";
        mysqli_query($conn, $sql);
        $success = "Category added successfully.";
    }
}

// ---------- Add Topic ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['form_action'] === 'add_topic') {
    $category_id = (int) $_POST['category_id'];
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    if ($title === "" || $category_id === 0) {
        $error = "Topic title and category are required.";
    } else {
        $created_by = $_SESSION['user_id'];
        $sql = "INSERT INTO topics (category_id, title, content, created_by)
                VALUES ($category_id, '$title', '$content', $created_by)";
        mysqli_query($conn, $sql);
        $success = "Topic added successfully.";
    }
}

// ---------- Edit Topic ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['form_action'] === 'edit_topic') {
    $topic_id = (int) $_POST['topic_id'];
    $category_id = (int) $_POST['category_id'];
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    if ($title === "" || $category_id === 0) {
        $error = "Topic title and category are required.";
    } else {
        $sql = "UPDATE topics SET category_id=$category_id, title='$title', content='$content'
                WHERE topic_id = $topic_id";
        mysqli_query($conn, $sql);
        $success = "Topic updated successfully.";
    }
}

// ---------- Delete Topic / Category ----------
if (isset($_GET['delete_topic'])) {
    mysqli_query($conn, "DELETE FROM topics WHERE topic_id = " . (int) $_GET['delete_topic']);
    header("Location: content.php?deleted=1");
    exit;
}
if (isset($_GET['delete_category'])) {
    // Prevent deleting a category that still has topics
    $cat_id = (int) $_GET['delete_category'];
    $check = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM topics WHERE category_id = $cat_id"));
    if ($check['c'] > 0) {
        header("Location: content.php?cat_error=1");
        exit;
    }
    mysqli_query($conn, "DELETE FROM categories WHERE category_id = $cat_id");
    header("Location: content.php?deleted=1");
    exit;
}

// ---------- Load topic into edit modal ----------
if (isset($_GET['edit_topic'])) {
    $tid = (int) $_GET['edit_topic'];
    $res = mysqli_query($conn, "SELECT * FROM topics WHERE topic_id = $tid");
    if ($res && mysqli_num_rows($res) === 1) $edit_topic = mysqli_fetch_assoc($res);
}

// ---------- Fetch data ----------
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY category_name ASC");
$categories_for_dropdown = mysqli_query($conn, "SELECT * FROM categories ORDER BY category_name ASC");
$topics = mysqli_query($conn, "SELECT t.*, c.category_name FROM topics t
                                JOIN categories c ON t.category_id = c.category_id
                                ORDER BY t.topic_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Content - CAWA</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-shell">
        <?php include 'includes_sidebar.php'; ?>

        <div class="main-area">
            <div class="topbar">
                <h1>Content & Topics</h1>
                <div class="who">
                    <div class="avatar"><?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?></div>
                    <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                </div>
            </div>

            <div class="content">

                <?php if ($error): ?><div class="alert alert-error" style="margin-bottom:16px;"><?php echo $error; ?></div><?php endif; ?>
                <?php if ($success): ?><div class="alert alert-success" style="margin-bottom:16px;"><?php echo $success; ?></div><?php endif; ?>
                <?php if (isset($_GET['deleted'])): ?><div class="alert alert-success" style="margin-bottom:16px;">Deleted successfully.</div><?php endif; ?>
                <?php if (isset($_GET['cat_error'])): ?><div class="alert alert-error" style="margin-bottom:16px;">Cannot delete category — it still has topics linked to it.</div><?php endif; ?>

                <!-- Categories Panel -->
                <div class="page-header">
                    <span style="font-family:'Space Grotesk',sans-serif; font-size:16px;">Categories</span>
                    <button class="btn-primary" onclick="document.getElementById('addCatModal').classList.add('open')">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                        Add Category
                    </button>
                </div>
                <div class="panel" style="margin-bottom:32px;">
                    <table>
                        <tr><th>Category</th><th>Description</th><th style="text-align:right;">Actions</th></tr>
                        <?php if (mysqli_num_rows($categories) === 0): ?>
                            <tr><td colspan="3" style="color:var(--text-muted)">No categories yet. Add one to get started.</td></tr>
                        <?php else: ?>
                            <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($cat['category_name']); ?></td>
                                    <td style="color:var(--text-muted);"><?php echo htmlspecialchars($cat['description']); ?></td>
                                    <td style="text-align:right;">
                                        <button class="icon-btn danger" title="Delete"
                                            onclick="if(confirm('Delete this category?')) window.location='content.php?delete_category=<?php echo $cat['category_id']; ?>'">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2m3 0l-1 14a2 2 0 01-2 2H7a2 2 0 01-2-2L4 6"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </table>
                </div>

                <!-- Topics Panel -->
                <div class="page-header">
                    <span style="font-family:'Space Grotesk',sans-serif; font-size:16px;">Topics</span>
                    <button class="btn-primary" onclick="document.getElementById('addTopicModal').classList.add('open')">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                        Add Topic
                    </button>
                </div>
                <div class="panel">
                    <table>
                        <tr><th>Title</th><th>Category</th><th>Created</th><th style="text-align:right;">Actions</th></tr>
                        <?php if (mysqli_num_rows($topics) === 0): ?>
                            <tr><td colspan="4" style="color:var(--text-muted)">No topics yet.</td></tr>
                        <?php else: ?>
                            <?php while ($t = mysqli_fetch_assoc($topics)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($t['title']); ?></td>
                                    <td style="color:var(--text-muted);"><?php echo htmlspecialchars($t['category_name']); ?></td>
                                    <td><?php echo date('d M Y', strtotime($t['created_at'])); ?></td>
                                    <td style="text-align:right;">
                                        <a href="content.php?edit_topic=<?php echo $t['topic_id']; ?>" class="icon-btn" title="Edit">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                        </a>
                                        <button class="icon-btn danger" title="Delete"
                                            onclick="if(confirm('Delete this topic?')) window.location='content.php?delete_topic=<?php echo $t['topic_id']; ?>'">
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

    <!-- Add Category Modal -->
    <div class="modal-overlay" id="addCatModal">
        <div class="modal-box">
            <h3>Add Category</h3>
            <form method="POST" action="content.php">
                <input type="hidden" name="form_action" value="add_category">
                <div class="field">
                    <label>Category name</label>
                    <input type="text" name="category_name" placeholder="e.g. Phishing" required>
                </div>
                <div class="field">
                    <label>Description</label>
                    <input type="text" name="description" placeholder="Short description">
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="document.getElementById('addCatModal').classList.remove('open')">Cancel</button>
                    <button type="submit" class="btn-submit-full">Add Category</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Topic Modal -->
    <div class="modal-overlay" id="addTopicModal">
        <div class="modal-box">
            <h3>Add Topic</h3>
            <form method="POST" action="content.php">
                <input type="hidden" name="form_action" value="add_topic">
                <div class="field">
                    <label>Category</label>
                    <select name="category_id" required style="width:100%; padding:12px 14px; background:var(--bg-card); border:1px solid var(--border-soft); border-radius:8px; color:var(--text-primary);">
                        <option value="">Select category</option>
                        <?php
                        mysqli_data_seek($categories_for_dropdown, 0);
                        while ($c = mysqli_fetch_assoc($categories_for_dropdown)): ?>
                            <option value="<?php echo $c['category_id']; ?>"><?php echo htmlspecialchars($c['category_name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="field">
                    <label>Title</label>
                    <input type="text" name="title" required>
                </div>
                <div class="field">
                    <label>Content</label>
                    <textarea name="content" rows="4" style="width:100%; padding:12px 14px; background:var(--bg-card); border:1px solid var(--border-soft); border-radius:8px; color:var(--text-primary); font-family:'Inter',sans-serif; resize:vertical;"></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="document.getElementById('addTopicModal').classList.remove('open')">Cancel</button>
                    <button type="submit" class="btn-submit-full">Add Topic</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Topic Modal -->
    <div class="modal-overlay <?php echo $edit_topic ? 'open' : ''; ?>">
        <div class="modal-box">
            <h3>Edit Topic</h3>
            <?php if ($edit_topic): ?>
            <form method="POST" action="content.php">
                <input type="hidden" name="form_action" value="edit_topic">
                <input type="hidden" name="topic_id" value="<?php echo $edit_topic['topic_id']; ?>">
                <div class="field">
                    <label>Category</label>
                    <select name="category_id" required style="width:100%; padding:12px 14px; background:var(--bg-card); border:1px solid var(--border-soft); border-radius:8px; color:var(--text-primary);">
                        <?php
                        mysqli_data_seek($categories_for_dropdown, 0);
                        while ($c = mysqli_fetch_assoc($categories_for_dropdown)): ?>
                            <option value="<?php echo $c['category_id']; ?>" <?php echo $c['category_id'] == $edit_topic['category_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($c['category_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="field">
                    <label>Title</label>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($edit_topic['title']); ?>" required>
                </div>
                <div class="field">
                    <label>Content</label>
                    <textarea name="content" rows="4" style="width:100%; padding:12px 14px; background:var(--bg-card); border:1px solid var(--border-soft); border-radius:8px; color:var(--text-primary); font-family:'Inter',sans-serif; resize:vertical;"><?php echo htmlspecialchars($edit_topic['content']); ?></textarea>
                </div>
                <div class="modal-actions">
                    <a href="content.php" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-submit-full">Save Changes</button>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>