<?php
require_once 'config/db.php';

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if ($full_name === "" || $email === "" || $password === "") {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $check = mysqli_query($conn, "SELECT user_id FROM users WHERE email = '$email'");

        if (mysqli_num_rows($check) > 0) {
            $error = "This email is already registered.";
        } else {
            // role_id = 2 means Student
            $sql = "INSERT INTO users (role_id, full_name, email, password, status)
                    VALUES (2, '$full_name', '$email', '$password', 'Active')";

            if (mysqli_query($conn, $sql)) {
                $success = "Registration successful! You can now log in.";
            } else {
                $error = "Something went wrong: " . mysqli_error($conn);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - CAWA</title>
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
                    <div class="row"><span class="dot"></span> IDENTITY THEFT — resource library open</div>
                    <div class="row"><span class="dot"></span> PASSWORD HYGIENE — topics categorized</div>
                    <div class="row"><span class="dot"></span> PROGRESS TRACKING — enabled on signup</div>
                </div>
            </div>

            <div class="brand-footer">
                <h1>Create your account,<br>start building awareness.</h1>
                <p>Track quiz scores, download resources, and follow structured lessons at your own pace.</p>
            </div>
        </div>

        <!-- Right form panel -->
        <div class="form-panel">
            <div class="form-card">
                <span class="eyebrow">Student Registration</span>
                <h2>Create account</h2>
                <p class="sub">Join CAWA to start learning and testing your knowledge.</p>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <form method="POST" action="register.php">
                    <div class="field">
                        <label>Full name</label>
                        <input type="text" name="full_name" placeholder="Your full name" required>
                    </div>
                    <div class="field">
                        <label>Email address</label>
                        <input type="email" name="email" placeholder="you@example.com" required>
                    </div>
                    <div class="field">
                        <label>Password</label>
                        <input type="password" name="password" placeholder="Create a password" required>
                    </div>
                    <div class="field">
                        <label>Confirm password</label>
                        <input type="password" name="confirm_password" placeholder="Re-enter your password" required>
                    </div>
                    <button type="submit" class="btn-submit">Register</button>
                </form>

                <p class="switch-link">Already have an account? <a href="login.php">Log in</a></p>
            </div>
        </div>

    </div>
</body>
</html>