<?php
$active = 'users';
require_once '../includes/admin_auth.php';

$error = "";
$success = "";
$edit_user = null; // holds data when editing

// ---------- Handle Add User ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['form_action'] === 'add') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if ($full_name === "" || $email === "" || $password === "") {
        $error = "All fields are required.";
    } else {
        $check = mysqli_query($conn, "SELECT user_id FROM users WHERE email = '$email'");
        if (mysqli_num_rows($check) > 0) {
            $error = "This email is already registered.";
        } else {
            $sql = "INSERT INTO users (role_id, full_name, email, password, status)
                    VALUES (2, '$full_name', '$email', '$password', 'Active')";
            mysqli_query($conn, $sql);
            $success = "Student added successfully.";
        }
    }
}

// ---------- Handle Edit User ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['form_action'] === 'edit') {
    $user_id = (int) $_POST['user_id'];
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $status = $_POST['status'] === 'Inactive' ? 'Inactive' : 'Active';

    if ($full_name === "" || $email === "") {
        $error = "Name and email are required.";
    } else {
        $sql = "UPDATE users SET full_name='$full_name', email='$email', status='$status'
                WHERE user_id = $user_id";
        mysqli_query($conn, $sql);
        $success = "Student updated successfully.";
    }
}

// ---------- Handle Delete User ----------
if (isset($_GET['delete'])) {
    $user_id = (int) $_GET['delete'];
    mysqli_query($conn, "DELETE FROM users WHERE user_id = $user_id");
    header("Location: users.php?deleted=1");
    exit;
}

// ---------- Load a user into the edit modal if ?edit=ID is present ----------
if (isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];
    $res = mysqli_query($conn, "SELECT * FROM users WHERE user_id = $edit_id");
    if ($res && mysqli_num_rows($res) === 1) {
        $edit_user = mysqli_fetch_assoc($res);
    }
}

// ---------- Fetch all students ----------
$students = mysqli_query($conn, "SELECT * FROM users WHERE role_id = 2 ORDER BY user_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - CAWA</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-shell">
        <?php include 'includes_sidebar.php'; ?>

        <div class="main-area">
            <div class="topbar">
                <h1>Manage Users</h1>
                <div class="who">
                    <div class="avatar"><?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?></div>
                    <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                </div>
            </div>

            <div class="content">

                <?php if ($error): ?>
                    <div class="alert alert-error" style="margin-bottom:16px;"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success" style="margin-bottom:16px;"><?php echo $success; ?></div>
                <?php endif; ?>
                <?php if (isset($_GET['deleted'])): ?>
                    <div class="alert alert-success" style="margin-bottom:16px;">Student deleted successfully.</div>
                <?php endif; ?>

                <div class="page-header">
                    <span></span>
                    <button class="btn-primary" onclick="document.getElementById('addModal').classList.add('open')">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                        Add Student
                    </button>
                </div>

                <div class="panel">
                    <h2>All Students</h2>
                    <table>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                        <?php if (mysqli_num_rows($students) === 0): ?>
                            <tr><td colspan="5" style="color:var(--text-muted)">No students found.</td></tr>
                        <?php else: ?>
                            <?php while ($row = mysqli_fetch_assoc($students)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><span class="badge <?php echo strtolower($row['status']); ?>"><?php echo $row['status']; ?></span></td>
                                    <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                                    <td style="text-align:right;">
                                        <a href="users.php?edit=<?php echo $row['user_id']; ?>" class="icon-btn" title="Edit">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                        </a>
                                        <button class="icon-btn danger" title="Delete"
                                            onclick="if(confirm('Delete this student? This cannot be undone.')) window.location='users.php?delete=<?php echo $row['user_id']; ?>'">
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

    <!-- Add Student Modal -->
    <div class="modal-overlay" id="addModal">
        <div class="modal-box">
            <h3>Add New Student</h3>
            <form method="POST" action="users.php">
                <input type="hidden" name="form_action" value="add">
                <div class="field">
                    <label>Full name</label>
                    <input type="text" name="full_name" required>
                </div>
                <div class="field">
                    <label>Email address</label>
                    <input type="email" name="email" required>
                </div>
                <div class="field">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="document.getElementById('addModal').classList.remove('open')">Cancel</button>
                    <button type="submit" class="btn-submit-full">Add Student</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Student Modal (auto-opens if ?edit=ID in URL) -->
    <div class="modal-overlay <?php echo $edit_user ? 'open' : ''; ?>" id="editModal">
        <div class="modal-box">
            <h3>Edit Student</h3>
            <?php if ($edit_user): ?>
            <form method="POST" action="users.php">
                <input type="hidden" name="form_action" value="edit">
                <input type="hidden" name="user_id" value="<?php echo $edit_user['user_id']; ?>">
                <div class="field">
                    <label>Full name</label>
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($edit_user['full_name']); ?>" required>
                </div>
                <div class="field">
                    <label>Email address</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($edit_user['email']); ?>" required>
                </div>
                <div class="field">
                    <label>Status</label>
                    <select name="status" style="width:100%; padding:12px 14px; background:var(--bg-card); border:1px solid var(--border-soft); border-radius:8px; color:var(--text-primary);">
                        <option value="Active" <?php echo $edit_user['status'] === 'Active' ? 'selected' : ''; ?>>Active</option>
                        <option value="Inactive" <?php echo $edit_user['status'] === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                <div class="modal-actions">
                    <a href="users.php" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-submit-full">Save Changes</button>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>