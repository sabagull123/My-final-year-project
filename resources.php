<?php
$active = 'resources';
require_once '../includes/admin_auth.php';

$error = "";
$success = "";

// Folder where PDFs will physically be stored
$upload_dir = __DIR__ . '/../assets/uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// ---------- Upload Resource ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['form_action'] === 'add_resource') {
    $topic_id = (int) $_POST['topic_id'];
    $title = trim($_POST['title']);

    if ($topic_id === 0 || $title === "") {
        $error = "Topic and title are required.";
    } elseif (!isset($_FILES['pdf_file']) || $_FILES['pdf_file']['error'] !== UPLOAD_ERR_OK) {
        $error = "Please choose a PDF file to upload.";
    } else {
        $file_tmp = $_FILES['pdf_file']['tmp_name'];
        $original_name = $_FILES['pdf_file']['name'];
        $file_ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

        if ($file_ext !== 'pdf') {
            $error = "Only PDF files are allowed.";
        } elseif ($_FILES['pdf_file']['size'] > 10 * 1024 * 1024) { // 10 MB limit
            $error = "File is too large. Maximum size is 10MB.";
        } else {
            // Unique file name so two uploads never overwrite each other
            $safe_name = time() . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $original_name);
            $destination = $upload_dir . $safe_name;

            if (move_uploaded_file($file_tmp, $destination)) {
                $file_path = 'assets/uploads/' . $safe_name; // stored relative to project root
                mysqli_query($conn, "INSERT INTO resources (topic_id, title, file_path)
                                      VALUES ($topic_id, '$title', '$file_path')");
                $success = "Resource uploaded successfully.";
            } else {
                $error = "Failed to upload file. Check folder permissions.";
            }
        }
    }
}

// ---------- Delete Resource (also removes the physical file) ----------
if (isset($_GET['delete_resource'])) {
    $rid = (int) $_GET['delete_resource'];
    $res = mysqli_query($conn, "SELECT file_path FROM resources WHERE resource_id = $rid");
    if ($row = mysqli_fetch_assoc($res)) {
        $full_path = __DIR__ . '/../' . $row['file_path'];
        if (file_exists($full_path)) {
            unlink($full_path);
        }
    }
    mysqli_query($conn, "DELETE FROM downloads WHERE resource_id = $rid");
    mysqli_query($conn, "DELETE FROM resources WHERE resource_id = $rid");
    header("Location: resources.php?deleted=1");
    exit;
}

$topics_dropdown = mysqli_query($conn, "SELECT * FROM topics ORDER BY title ASC");
$resources = mysqli_query($conn, "SELECT r.*, t.title AS topic_title FROM resources r
                                   JOIN topics t ON r.topic_id = t.topic_id
                                   ORDER BY r.resource_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Resources - CAWA</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
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

                <?php if ($error): ?><div class="alert alert-error" style="margin-bottom:16px;"><?php echo $error; ?></div><?php endif; ?>
                <?php if ($success): ?><div class="alert alert-success" style="margin-bottom:16px;"><?php echo $success; ?></div><?php endif; ?>
                <?php if (isset($_GET['deleted'])): ?><div class="alert alert-success" style="margin-bottom:16px;">Resource deleted successfully.</div><?php endif; ?>

                <?php if (mysqli_num_rows($topics_dropdown) === 0): ?>
                    <div class="alert alert-error" style="margin-bottom:16px;">
                        You need to add at least one Topic (in Content & Topics) before uploading a resource.
                    </div>
                <?php endif; ?>

                <div class="page-header">
                    <span></span>
                    <button class="btn-primary" onclick="document.getElementById('addResModal').classList.add('open')">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                        Upload Resource
                    </button>
                </div>

                <div class="panel">
                    <table>
                        <tr>
                            <th>Title</th>
                            <th>Topic</th>
                            <th>Uploaded</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                        <?php if (mysqli_num_rows($resources) === 0): ?>
                            <tr><td colspan="4" style="color:var(--text-muted)">No resources uploaded yet.</td></tr>
                        <?php else: ?>
                            <?php while ($r = mysqli_fetch_assoc($resources)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($r['title']); ?></td>
                                    <td style="color:var(--text-muted);"><?php echo htmlspecialchars($r['topic_title']); ?></td>
                                    <td><?php echo date('d M Y', strtotime($r['uploaded_at'])); ?></td>
                                    <td style="text-align:right;">
                                        <a href="../<?php echo htmlspecialchars($r['file_path']); ?>" target="_blank" class="icon-btn" title="View PDF">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </a>
                                        <button class="icon-btn danger" title="Delete"
                                            onclick="if(confirm('Delete this resource? The file will be permanently removed.')) window.location='resources.php?delete_resource=<?php echo $r['resource_id']; ?>'">
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

    <!-- Upload Resource Modal -->
    <div class="modal-overlay" id="addResModal">
        <div class="modal-box">
            <h3>Upload Resource</h3>
            <form method="POST" action="resources.php" enctype="multipart/form-data">
                <input type="hidden" name="form_action" value="add_resource">
                <div class="field">
                    <label>Topic</label>
                    <select name="topic_id" required>
                        <option value="">Select topic</option>
                        <?php while ($t = mysqli_fetch_assoc($topics_dropdown)): ?>
                            <option value="<?php echo $t['topic_id']; ?>"><?php echo htmlspecialchars($t['title']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="field">
                    <label>Resource title</label>
                    <input type="text" name="title" placeholder="e.g. Phishing Prevention Checklist" required>
                </div>
                <div class="field">
                    <label>PDF file (max 10MB)</label>
                    <input type="file" name="pdf_file" accept=".pdf" required>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="document.getElementById('addResModal').classList.remove('open')">Cancel</button>
                    <button type="submit" class="btn-submit-full">Upload</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>