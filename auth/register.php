<?php
session_start();
require_once "../config/db.php";

/* Initialize message */
$message = "";
$status = ""; // success or error


if (isset($_POST['register'])) {

    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $phone    = trim($_POST['phone']);
    $password = $_POST['password'];
    $role     = $_POST['role'];

    // Default status
    $status_value = "active";

    // Staff accounts require admin approval
    if ($role === "staff") {
        $status_value = "pending";
    }

    /* ---------- NAME VALIDATION ---------- */
    if (strlen($name) < 3 || !preg_match("/^[a-zA-Z\s]+$/", $name)) {
        $message = "Name must be at least 3 characters and contain letters only.";
        $status  = "error";

    /* ---------- EMAIL VALIDATION ---------- */
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
        $status  = "error";

    } else {
        // Allowed email domains
        $allowed_domains = [
            "gmail.com",
            "hotmail.com",
            "outlook.com",
            "icloud.com",
            "yahoo.com"
        ];

        $email_domain = substr(strrchr($email, "@"), 1);

        if (!in_array($email_domain, $allowed_domains)) {
            $message = "Please use a valid email provider (Gmail, Outlook, Yahoo, etc.).";
            $status  = "error";

        /* ---------- PASSWORD VALIDATION ---------- */
        } elseif (strlen($password) < 6 || 
                 !preg_match("/[A-Za-z]/", $password) || 
                 !preg_match("/[0-9]/", $password)) {

            $message = "Password must be at least 6 characters and contain letters and numbers.";
            $status  = "error";

        /* ---------- PHONE VALIDATION ---------- */
        } elseif (!ctype_digit($phone) || strlen($phone) < 9) {
            $message = "Phone number must contain digits only and be valid.";
            $status  = "error";

        } else {

            /* ---------- CHECK EMAIL EXISTS ---------- */
            $check_sql = "SELECT user_id FROM users WHERE email = ?";
            $stmt = mysqli_prepare($conn, $check_sql);
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) > 0) {
                $message = "Email already exists. Please login.";
                $status  = "error";

            } else {

                $confirm_password = $_POST['confirm_password'];

                if ($password !== $confirm_password) {
                    $message = "Passwords do not match.";
                    $status = "error";
                }
                /* ---------- HASH PASSWORD ---------- */
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                /* ---------- INSERT USER ---------- */
                $insert_sql = "
                    INSERT INTO users 
                    (name, email, password, role, status, phone_number, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())
                ";

                $stmt = mysqli_prepare($conn, $insert_sql);
                mysqli_stmt_bind_param(
                    $stmt,
                    "ssssss",
                    $name,
                    $email,
                    $hashed_password,
                    $role,
                    $status_value,
                    $phone
                );

                if (mysqli_stmt_execute($stmt)) {
                    if ($role === "staff") {
                        $message = "Staff registration submitted successfully. Awaiting admin approval.";
                    } else {
                        $message = "Registration successful! You may now login.";
                    }
                    $status  = "success";
                } else {
                    $message = "Registration failed. Please try again.";
                    $status  = "error";
                }
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
        <title>Register – Beerta Daarusalaam</title>
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
                    <h1 class="auth-tagline">Join the<br><span>Community.</span><br>Play More.</h1>
                    <p class="auth-tagline-sub">Create a free account and start booking your favourite sports facilities in seconds.</p>
                    <div class="auth-perks">
                        <div class="perk"><i class="bi bi-lightning-charge-fill"></i> Instant booking confirmation</div>
                        <div class="perk"><i class="bi bi-shield-fill-check"></i> Secure online payments</div>
                        <div class="perk"><i class="bi bi-bell-fill"></i> Real-time availability updates</div>
                        <div class="perk"><i class="bi bi-clock-history"></i> Full booking history</div>
                    </div>
                </div>
                <div class="auth-left-bg"></div>
            </div>

            <!-- RIGHT PANEL -->
            <div class="auth-right">
                <div class="auth-card">

                    <div class="auth-card-header">
                        <h2>Create Account</h2>
                        <p>Join BD Sports Facility today — it's free</p>
                    </div>

                    <?php if (!empty($message)): ?>
                        <div class="auth-alert auth-alert-<?= $status === 'success' ? 'success' : 'error' ?>">
                            <i class="bi bi-<?= $status === 'success' ? 'check-circle-fill' : 'exclamation-circle-fill' ?>"></i>
                            <?= htmlspecialchars($message) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">

                        <div class="form-row">
                            <div class="form-group">
                                <label>Full Name</label>
                                <div class="input-wrap">
                                    <i class="bi bi-person"></i>
                                    <input type="text" name="name" placeholder="Ahmed Hassan" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Phone Number</label>
                                <div class="input-wrap">
                                    <i class="bi bi-telephone"></i>
                                    <input type="text" name="phone" placeholder="+252 6XX XXX XXX" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Email Address</label>
                            <div class="input-wrap">
                                <i class="bi bi-envelope"></i>
                                <input type="email" name="email" placeholder="you@example.com" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Register As</label>
                            <div class="input-wrap">
                                <i class="bi bi-person"></i>
                                <select name="role" required>
                                    <option value="customer">Customer</option>
                                    <option value="staff">Staff</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Password</label>
                                <div class="input-wrap">
                                    <i class="bi bi-lock"></i>
                                    <input type="password" name="password" id="pw1" placeholder="••••••••" required>
                                    <button type="button" class="toggle-pw" onclick="togglePw('pw1',this)">
                                        <i class="bi bi-eye-slash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Confirm Password</label>
                                <div class="input-wrap">
                                    <i class="bi bi-lock-fill"></i>
                                    <input type="password" name="confirm_password" id="pw2" placeholder="••••••••" required>
                                    <button type="button" class="toggle-pw" onclick="togglePw('pw2',this)">
                                        <i class="bi bi-eye-slash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Password strength -->
                        <div class="pw-strength-wrap" id="pwStrengthWrap">
                            <div class="pw-strength-bar">
                                <div class="pw-strength-fill" id="pwFill"></div>
                            </div>
                            <span class="pw-strength-label" id="pwLabel">Password strength</span>
                        </div>

                        <div class="form-group terms-group">
                            <label class="checkbox-label">
                                <input type="checkbox" required>
                                <span class="checkmark"></span>
                                I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
                            </label>
                        </div>

                        <button type="submit" name="register" class="btn-auth">
                            <i class="bi bi-person-plus-fill"></i> Create Account
                        </button>

                    </form>

                    <div class="auth-divider"><span>or</span></div>

                    <div class="auth-switch">
                        Already have an account?
                        <a href="login.php">Sign in here</a>
                    </div>

                </div>
            </div>

        </div>

        <script>
            function togglePw(id, btn) {
                const input = document.getElementById(id);
                const icon  = btn.querySelector('i');
                input.type = input.type === 'password' ? 'text' : 'password';
                icon.className = input.type === 'password' ? 'bi bi-eye-slash' : 'bi bi-eye';
            }

            // Password strength meter
            document.getElementById('pw1').addEventListener('input', function () {
                const v = this.value;
                const fill  = document.getElementById('pwFill');
                const label = document.getElementById('pwLabel');
                let score = 0;
                if (v.length >= 8)           score++;
                if (/[A-Z]/.test(v))         score++;
                if (/[0-9]/.test(v))         score++;
                if (/[^A-Za-z0-9]/.test(v))  score++;

                const colors = ['#ff6b6b','#f0c060','#0af','#00e5a0'];
                const labels = ['Weak','Fair','Good','Strong'];
                fill.style.width   = (score / 4 * 100) + '%';
                fill.style.background = colors[score - 1] || '#333';
                label.textContent  = v.length ? labels[score - 1] || 'Very weak' : 'Password strength';
                label.style.color  = colors[score - 1] || 'var(--muted)';
            });
        </script>
    </body>
</html>
