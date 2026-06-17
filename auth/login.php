<?php
session_start();
require_once "../auth/session_timeout.php";
require_once "../config/db.php";

/* Initialize message */
$message = "";

if (isset($_POST['login'])) {

    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $message = "Please enter both email and password.";
    } else {

        /* Get user by email */
        $sql = "SELECT user_id, name, password, role, profile_pic FROM users WHERE email = ? AND status = 'active' LIMIT 1";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {

            /* Verify hashed password */
            if (password_verify($password, $row['password'])) {

                /* Store session */
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['name']    = $row['name'];
                $email = $_POST['email'] ?? '';
                $password = $_POST['password'] ?? '';
                $_SESSION['role']    = $row['role'];
                $_SESSION['profile_pic'] = $row['profile_pic'];

                /* Role-based redirect */
                if ($row['role'] === "admin") {
                    header("Location: ../admin/dashboard.php");
                } elseif ($row['role'] === "staff") {
                    header("Location: ../staff/staff_dashboard.php");
                } else {
                    header("Location: ../customer/dashboard.php");
                }
                exit();

            } else {
                $message = "Invalid email or password.";
            }

        } else {
            $message = "Invalid email or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login – Beerta Daarusalaam</title>
        <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
        <link rel="stylesheet" href="../assets/css/auth.css">
    </head>
    <body>

        <div class="auth-page">

            <!-- LEFT PANEL -->
            <div class="auth-left">
                <div class="auth-left-content">
                    <a href="../index.php" class="auth-logo">
                        <div class="logo-icon">
                            <img src="../assets/logo_bd.png" alt="Logo">
                        </div>
                        <span class="logo-name">Beerta <em>Daarusalaam</em></span>
                    </a>
                    <h1 class="auth-tagline">Your Game.<br><span>Your Arena.</span><br>Your Schedule.</h1>
                    <p class="auth-tagline-sub">Book football fields, basketball courts, and swimming pools — anytime, anywhere.</p>
                    <div class="auth-stats">
                        <div class="auth-stat"><span>20+</span><small>Bookings</small></div>
                        <div class="auth-stat"><span>3</span><small>Facilities</small></div>
                        <div class="auth-stat"><span>24/7</span><small>Available</small></div>
                    </div>
                </div>
                <div class="auth-left-bg"></div>
            </div>

            <!-- RIGHT PANEL -->
            <div class="auth-right">
                <div class="auth-card">

                    <div class="auth-card-header">
                        <h2>Welcome Back</h2>
                        <p>Sign in to your BD Sports account</p>
                    </div>

                    <?php if (!empty($message)): ?>
                        <div class="auth-alert auth-alert-error">
                            <i class="bi bi-exclamation-circle-fill"></i>
                            <?= htmlspecialchars($message) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">

                        <div class="form-group">
                            <label>Email Address</label>
                            <div class="input-wrap">
                                <i class="bi bi-envelope"></i>
                                <input type="email" name="email" placeholder="you@example.com" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Password</label>
                            <div class="input-wrap">
                                <i class="bi bi-lock"></i>
                                <input type="password" name="password" id="passwordInput" placeholder="••••••••" required>
                                <button type="button" class="toggle-pw" onclick="togglePw('passwordInput',this)">
                                    <i class="bi bi-eye-slash"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-options">
                            <label class="checkbox-label">
                                <input type="checkbox" name="remember" id="remember">
                                <span class="checkmark"></span>
                                Remember me
                            </label>
                            <a href="forgot_password.php" class="forgot-link">Forgot Password?</a>
                        </div>

                        <button type="submit" name="login" class="btn-auth">
                            <i class="bi bi-box-arrow-in-right"></i> Sign In
                        </button>

                    </form>

                    <div class="auth-divider"><span>or</span></div>

                    <div class="auth-switch">
                        Don't have an account?
                        <a href="register.php">Create one free</a>
                    </div>

                </div>
            </div>

        </div>
        <script>
            function togglePw(id, btn) {
                const input = document.getElementById(id);
                const icon  = btn.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.className = 'bi bi-eye';
                } else {
                    input.type = 'password';
                    icon.className = 'bi bi-eye-slash';
                }
            }
        </script>
    </body>
</html>
