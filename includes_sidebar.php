<?php
// Path: cawa/admin/includes_sidebar.php
// $active variable (set in each page before including this) highlights current nav item
if (!isset($active)) $active = '';
?>
<div class="sidebar">
    <div class="brand">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 2L4 5.5V11C4 16 7.5 20.5 12 22C16.5 20.5 20 16 20 11V5.5L12 2Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
            <path d="M9 12l2 2 4-4.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        CAWA Admin
    </div>

    <span class="eyebrow">Overview</span>
    <a href="dashboard.php" class="nav-link <?php echo $active === 'dashboard' ? 'active' : ''; ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
        Dashboard
    </a>

    <span class="eyebrow" style="margin-top:16px;">Manage</span>
    <a href="users.php" class="nav-link <?php echo $active === 'users' ? 'active' : ''; ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="8" r="3.5"/><path d="M5 21c0-4 3-6.5 7-6.5s7 2.5 7 6.5"/></svg>
        Users
    </a>
    <a href="content.php" class="nav-link <?php echo $active === 'content' ? 'active' : ''; ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M4 4h16v16H4z"/><path d="M8 9h8M8 13h5"/></svg>
        Content & Topics
    </a>
    <a href="quizzes.php" class="nav-link <?php echo $active === 'quizzes' ? 'active' : ''; ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
        Quizzes
    </a>
    <a href="resources.php" class="nav-link <?php echo $active === 'resources' ? 'active' : ''; ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 3v12m0 0l-4-4m4 4l4-4"/><path d="M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2"/></svg>
        Resources
    </a>
    <a href="reports.php" class="nav-link <?php echo $active === 'reports' ? 'active' : ''; ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M4 20V10M12 20V4M20 20v-7"/></svg>
        Reports & Feedback
    </a>

    <div class="spacer"></div>

    <a href="../logout.php" class="nav-link logout">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
        Logout
    </a>
</div>