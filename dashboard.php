<?php
$active = 'dashboard';
require_once '../includes/admin_auth.php';

// Count stats for the overview cards
$total_students = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM users WHERE role_id = 2"))['c'];
$total_topics   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM topics"))['c'];
$total_quizzes  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM quizzes"))['c'];
$total_resources = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM resources"))['c'];
$total_feedback = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM feedback"))['c'];

// Recently registered students (latest 5)
$recent = mysqli_query($conn, "SELECT full_name, email, status, created_at FROM users WHERE role_id = 2 ORDER BY user_id DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CAWA</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-shell">
        <?php include 'includes_sidebar.php'; ?>

        <div class="main-area">
            <div class="topbar">
                <h1>Dashboard</h1>
                <div class="who">
                    <div class="avatar"><?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?></div>
                    <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                </div>
            </div>

            <div class="content">
                <div class="stat-grid">
                    <div class="stat-card">
                        <div class="label">Students <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="8" r="3.5"/><path d="M5 21c0-4 3-6.5 7-6.5s7 2.5 7 6.5"/></svg></div>
                        <div class="value"><?php echo $total_students; ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="label">Topics <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M4 4h16v16H4z"/></svg></div>
                        <div class="value"><?php echo $total_topics; ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="label">Quizzes <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M9 11l3 3L22 4"/></svg></div>
                        <div class="value"><?php echo $total_quizzes; ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="label">Resources <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 3v12"/></svg></div>
                        <div class="value"><?php echo $total_resources; ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="label">Feedback <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M4 20V10M12 20V4M20 20v-7"/></svg></div>
                        <div class="value"><?php echo $total_feedback; ?></div>
                    </div>
                </div>

                <div class="panel">
                    <h2>Recently Registered Students</h2>
                    <table>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Joined</th>
                        </tr>
                        <?php if (mysqli_num_rows($recent) === 0): ?>
                            <tr><td colspan="4" style="color:var(--text-muted)">No students registered yet.</td></tr>
                        <?php else: ?>
                            <?php while ($row = mysqli_fetch_assoc($recent)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><span class="badge <?php echo strtolower($row['status']); ?>"><?php echo $row['status']; ?></span></td>
                                    <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
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