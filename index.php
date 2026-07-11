<?php
session_start();
require_once "config/db.php";

// Agar user pehle se login hai, to seedha uske dashboard pe bhej dein
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'Admin') {
        header("Location: admin/dashboard.php");
        exit();
    } elseif ($_SESSION['role'] === 'Student') {
        header("Location: student/dashboard.php");
        exit();
    }
}

// ---- Live stats from database (safe defaults agar DB abhi connect na ho) ----
$totalTopics    = 0;
$totalQuizzes   = 0;
$totalResources = 0;
$totalUsers     = 0;

if ($conn) {
    if ($r = @mysqli_query($conn, "SELECT COUNT(*) AS c FROM topics"))    { $totalTopics    = mysqli_fetch_assoc($r)['c']; }
    if ($r = @mysqli_query($conn, "SELECT COUNT(*) AS c FROM quizzes"))   { $totalQuizzes   = mysqli_fetch_assoc($r)['c']; }
    if ($r = @mysqli_query($conn, "SELECT COUNT(*) AS c FROM resources")) { $totalResources = mysqli_fetch_assoc($r)['c']; }
    if ($r = @mysqli_query($conn, "SELECT COUNT(*) AS c FROM users"))     { $totalUsers     = mysqli_fetch_assoc($r)['c']; }
}

// ---- Categories preview from database ----
$categories = [];
if ($conn) {
    if ($r = @mysqli_query($conn, "SELECT * FROM categories LIMIT 6")) {
        while ($row = mysqli_fetch_assoc($r)) {
            $categories[] = $row;
        }
    }
}

// Fallback demo categories agar database khali ho (sirf homepage design test ke liye)
if (empty($categories)) {
    $categories = [
        ["category_name" => "Phishing Attacks",     "description" => "Learn to recognize fake emails and messages."],
        ["category_name" => "Malware & Viruses",     "description" => "Protect your device from malicious software."],
        ["category_name" => "Identity Theft",        "description" => "Learn how to keep your personal information secure."],
        ["category_name" => "Social Engineering",     "description" => "Recognize and avoid manipulation tactics."],
        ["category_name" => "Password Security",     "description" => "Strong passwords and safe login habits."],
        ["category_name" => "Safe Browsing",         "description" => "Tips for browsing the internet safely."],
    ];
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CAWA — Cyber Awareness Web Application</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/guest.css">
</head>
<body>

<!-- ===== NAVBAR ===== -->
<header class="navbar">
    <div class="nav-container">
        <a href="index.php" class="logo">
            <i class="fa-solid fa-shield-halved"></i>
            <span>CAWA</span>
        </a>
        <nav class="nav-links">
            <a href="#home">Home</a>
            <a href="#categories">Categories</a>
            <a href="#how-it-works">How It Works</a>
            <a href="#about">About</a>
        </nav>
        <div class="nav-buttons">
            <a href="login.php" class="btn btn-outline">Login</a>
            <a href="register.php" class="btn btn-primary">Register</a>
        </div>
        <button class="nav-toggle" id="navToggle"><i class="fa-solid fa-bars"></i></button>
    </div>
</header>

<!-- ===== HERO ===== -->
<section class="hero" id="home">
    <div class="hero-bg-grid"></div>
    <div class="hero-content">
        <span class="hero-badge"><i class="fa-solid fa-lock"></i> Stay Safe Online</span>
        <h1>Learn to Outsmart <span class="highlight">Cyber Threats</span> Before They Strike</h1>
        <p>CAWA teaches you cybersecurity in a simple, structured, and interactive way — covering phishing, malware, identity theft, and social engineering — with quizzes and free resources included.</p>
        <div class="hero-actions">
            <a href="register.php" class="btn btn-primary btn-lg"><i class="fa-solid fa-user-plus"></i> Get Started Free</a>
            <a href="#categories" class="btn btn-ghost btn-lg"><i class="fa-solid fa-book-open"></i> Explore Topics</a>
        </div>
    </div>
    <div class="hero-visual">
        <i class="fa-solid fa-shield-halved shield-icon"></i>
        <i class="fa-solid fa-lock float-icon icon-1"></i>
        <i class="fa-solid fa-envelope-open-text float-icon icon-2"></i>
        <i class="fa-solid fa-fingerprint float-icon icon-3"></i>
        <i class="fa-solid fa-bug float-icon icon-4"></i>
    </div>
</section>

<!-- ===== STATS ===== -->
<section class="stats">
    <div class="stat-card">
        <i class="fa-solid fa-layer-group"></i>
        <h3><?= (int)$totalTopics ?>+</h3>
        <p>Learning Topics</p>
    </div>
    <div class="stat-card">
        <i class="fa-solid fa-circle-question"></i>
        <h3><?= (int)$totalQuizzes ?>+</h3>
        <p>Interactive Quizzes</p>
    </div>
    <div class="stat-card">
        <i class="fa-solid fa-file-pdf"></i>
        <h3><?= (int)$totalResources ?>+</h3>
        <p>Free Resources</p>
    </div>
    <div class="stat-card">
        <i class="fa-solid fa-users"></i>
        <h3><?= (int)$totalUsers ?>+</h3>
        <p>Registered Learners</p>
    </div>
</section>

<!-- ===== CATEGORIES ===== -->
<section class="categories" id="categories">
    <div class="section-head">
        <span class="section-tag">What You'll Learn</span>
        <h2>Explore Awareness Categories</h2>
        <p>Each category focuses on a real-world cyber threat, explained in simple language.</p>
    </div>
    <div class="category-grid">
        <?php
        $icons = ["fa-user-secret","fa-virus","fa-id-card-clip","fa-people-arrows","fa-key","fa-globe"];
        foreach ($categories as $i => $cat): ?>
        <div class="category-card">
            <div class="cat-icon"><i class="fa-solid <?= $icons[$i % count($icons)] ?>"></i></div>
            <h3><?= htmlspecialchars($cat['category_name']) ?></h3>
            <p><?= htmlspecialchars($cat['description'] ?? '') ?></p>
            <a href="register.php" class="cat-link">Learn More <i class="fa-solid fa-arrow-right"></i></a>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ===== HOW IT WORKS ===== -->
<section class="how-it-works" id="how-it-works">
    <div class="section-head light">
        <span class="section-tag">Simple Process</span>
        <h2>How CAWA Works</h2>
        <p>Start your cybersecurity journey in four simple steps.</p>
    </div>
    <div class="steps">
        <div class="step-card">
            <span class="step-num">01</span>
            <i class="fa-solid fa-user-plus"></i>
            <h3>Create Account</h3>
            <p>Register for free and start your learning journey.</p>
        </div>
        <div class="step-card">
            <span class="step-num">02</span>
            <i class="fa-solid fa-book-open-reader"></i>
            <h3>Learn Topics</h3>
            <p>Understand cyber threats through structured articles and tutorials.</p>
        </div>
        <div class="step-card">
            <span class="step-num">03</span>
            <i class="fa-solid fa-clipboard-check"></i>
            <h3>Take Quizzes</h3>
            <p>Test your knowledge and track your progress.</p>
        </div>
        <div class="step-card">
            <span class="step-num">04</span>
            <i class="fa-solid fa-download"></i>
            <h3>Download Resources</h3>
            <p>Download helpful PDF guides to refer back to anytime.</p>
        </div>
    </div>
</section>

<!-- ===== CTA ===== -->
<section class="cta" id="about">
    <div class="cta-box">
        <i class="fa-solid fa-shield-halved"></i>
        <h2>Ready to Protect Yourself Online?</h2>
        <p>Join CAWA today and learn how to stay safe from cyber threats — completely free.</p>
        <a href="register.php" class="btn btn-primary btn-lg">Join CAWA Now</a>
    </div>
</section>

<!-- ===== FOOTER ===== -->
<footer class="footer">
    <div class="footer-content">
        <div class="footer-brand">
            <i class="fa-solid fa-shield-halved"></i>
            <span>CAWA</span>
            <p>Cyber Awareness Web Application — Learn. Test. Stay Safe.</p>
        </div>
        <div class="footer-links">
            <a href="#home">Home</a>
            <a href="#categories">Categories</a>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        </div>
    </div>
    <div class="footer-bottom">
        &copy; <?= date("Y") ?> CAWA. Final Year Project by Saba Bashir — Govt. Graduate College Sadiqabad.
    </div>
</footer>

<script src="assets/js/guest.js"></script>
</body>
</html>