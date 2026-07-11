<?php
require_once 'config/db.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if ($email === "" || $password === "") {
        $error = "Please enter both email and password.";
    } else {
        $sql = "SELECT user_id, role_id, full_name, password, status
                FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);

            if ($user['status'] !== 'Active') {
                $error = "Your account is inactive. Contact admin.";
            } elseif ($password === $user['password']) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role_id'] = $user['role_id'];

                if ($user['role_id'] == 1) {
                    header("Location: admin/dashboard.php");
                    exit;
                } else {
                    header("Location: student/dashboard.php");
                    exit;
                }
            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "No account found with this email.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CAWA</title>
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
    <div class="auth-wrapper">

        <!-- Left brand / visual panel -->
        <div class="brand-panel">
            <div class="brand-mark">
                <svg class="shield" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2L4 5.5V11C4 16 7.5 20.5 12 22C16.5 20.5 20 16 20 11V5.5L12 2Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                    <path d="M9 12l2 2 4-4.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                CAWA
            </div>

            <div class="brand-radar">
                <div class="radar-ring">
                    <div class="ring r1"></div>
                    <div class="ring r2"></div>
                    <div class="ring r3"></div>
                    <div class="sweep"></div>
                    <svg class="core" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2L4 5.5V11C4 16 7.5 20.5 12 22C16.5 20.5 20 16 20 11V5.5L12 2Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/>
                        <path d="M9 12l2 2 4-4.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>

                <div class="threat-ticker">
                    <div class="row"><span class="dot"></span> PHISHING — pattern recognition active</div>
                    <div class="row"><span class="dot"></span> MALWARE — awareness module loaded</div>
                    <div class="row"><span class="dot"></span> SOCIAL ENGINEERING — quiz ready</div>
                </div>
            </div>

            <div class="brand-footer">
                <h1>Learn to spot the threat<br>before it spots you.</h1>
                <p>Structured cybersecurity education, self-assessment quizzes, and resources — built for students and everyday internet users.</p>
            </div>
        </div>

        <!-- Right form panel -->
        <div class="form-panel">
            <div class="form-card">
                <span class="eyebrow">Secure Access</span>
                <h2>Welcome back</h2>
                <p class="sub">Log in to continue your cyber awareness journey.</p>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" action="login.php">
                    <div class="field">
                        <label>Email address</label>
                        <input type="email" name="email" placeholder="you@example.com" required>
                    </div>
                    <div class="field">
                        <label>Password</label>
                        <input type="password" name="password" placeholder="Enter your password" required>
                    </div>
                    <button type="submit" class="btn-submit">Log In</button>
                </form>

                <p class="switch-link">New here? <a href="register.php">Create an account</a></p>
            </div>
        </div>

    </div>
</body>
</html>