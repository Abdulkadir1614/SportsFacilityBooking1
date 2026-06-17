<?php
require_once "../config/db.php";

$message = "";
$status  = "";
$valid   = false;
$done    = false;
$user_id = null;
$name    = "";

$token = trim($_GET['token'] ?? '');

if (empty($token)) {

    $valid = false;

} else {

    // Find token
    $sql = "SELECT * FROM users WHERE reset_token=?";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        die("Prepare failed: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "s", $token);

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $row = mysqli_fetch_assoc($result);

    // Check if token exists
    if ($row) {

        // Check expiry manually
        if (strtotime($row['reset_expiry']) > time()) {

            $valid   = true;
            $user_id = $row['user_id'];
            $name    = htmlspecialchars($row['name']);

        } else {

            $valid = false; // expired
        }

    } else {

        $valid = false; // token not found
    }
}

if ($valid && isset($_POST['reset'])) {

    $password = $_POST['password'];
    $confirm  = $_POST['confirm'];

    if (empty($password) || empty($confirm)) {
        $message = "All fields are required.";
        $status  = "error";

    } elseif (strlen($password) < 8) {
        $message = "Password must be at least 8 characters.";
        $status  = "error";

    } elseif ($password !== $confirm) {
        $message = "Passwords do not match.";
        $status  = "error";

    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $upd = mysqli_prepare($conn, "UPDATE users SET password=?, reset_token=NULL, reset_expiry=NULL WHERE user_id=?");
        mysqli_stmt_bind_param($upd, "si", $hashed, $user_id);

        if (mysqli_stmt_execute($upd)) {
            $done   = true;
            $status = "success";
        } else {
            $message = "Something went wrong. Please try again.";
            $status  = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password – Beerta Daarusalaam</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>
<div class="auth-page">

    <!-- LEFT -->
    <div class="auth-left">
        <div class="auth-left-content">
            <a href="../index.php" class="auth-logo">
                <div class="logo-icon">
                    <img src="../assets/logo_bd.png" alt="Logo">
                </div>
                <span class="logo-name">Beerta <em>Daarusalaam</em></span>
            </a>
            <h1 class="auth-tagline">Set Your<br><span>New</span><br>Password.</h1>
            <p class="auth-tagline-sub">Choose a strong password to keep your BD Sports account secure.</p>
            <div class="auth-perks">
                <div class="perk"><i class="bi bi-key-fill"></i> Minimum 8 characters</div>
                <div class="perk"><i class="bi bi-shield-fill-check"></i> Mix letters, numbers & symbols</div>
                <div class="perk"><i class="bi bi-lock-fill"></i> Stored with strong encryption</div>
            </div>
        </div>
        <div class="auth-left-bg"></div>
    </div>

    <!-- RIGHT -->
    <div class="auth-right">
        <div class="auth-card">

            <?php if ($done): ?>
            <!-- SUCCESS -->
            <div class="sent-state">
                <div class="sent-icon sent-icon-green"><i class="bi bi-check-circle-fill"></i></div>
                <h2>Password Updated!</h2>
                <p>Your password has been reset successfully. You can now sign in with your new password.</p>
                <a href="login.php" class="btn-auth" style="margin-top:28px;text-decoration:none;display:flex;justify-content:center;">
                    <i class="bi bi-box-arrow-in-right"></i> Go to Login
                </a>
            </div>

            <?php elseif (!$valid): ?>
            <!-- INVALID / EXPIRED -->
            <div class="sent-state">
                <div class="sent-icon sent-icon-red"><i class="bi bi-x-circle-fill"></i></div>
                <h2>Link Expired</h2>
                <p>This password reset link is invalid or has expired. Reset links are only valid for <strong>30 minutes</strong>.</p>
                <a href="forgot_password.php" class="btn-auth" style="margin-top:28px;text-decoration:none;display:flex;justify-content:center;">
                    <i class="bi bi-arrow-counterclockwise"></i> Request New Link
                </a>
            </div>

            <?php else: ?>
            <!-- RESET FORM -->
            <div class="auth-card-header">
                <h2>Reset Password</h2>
                <p>Hi <strong style="color:#fff;"><?= $name ?></strong>, set your new password below.</p>
            </div>

            <?php if ($message && $status === 'error'): ?>
                <div class="auth-alert auth-alert-error">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                <div class="form-group">
                    <label>New Password</label>
                    <div class="input-wrap">
                        <i class="bi bi-lock"></i>
                        <input type="password" name="password" id="pw1"
                               placeholder="Min. 8 characters" required>
                        <button type="button" class="toggle-pw" onclick="togglePw('pw1',this)">
                            <i class="bi bi-eye-slash"></i>
                        </button>
                    </div>
                </div>

                <!-- Strength meter -->
                <div class="pw-strength-wrap">
                    <div class="pw-strength-bar">
                        <div class="pw-strength-fill" id="pwFill"></div>
                    </div>
                    <span class="pw-strength-label" id="pwLabel">Password strength</span>
                </div>

                <div class="form-group" style="margin-top:18px;">
                    <label>Confirm New Password</label>
                    <div class="input-wrap">
                        <i class="bi bi-lock-fill"></i>
                        <input type="password" name="confirm" id="pw2"
                               placeholder="Repeat your password" required>
                        <button type="button" class="toggle-pw" onclick="togglePw('pw2',this)">
                            <i class="bi bi-eye-slash"></i>
                        </button>
                    </div>
                    <span class="match-msg" id="matchMsg"></span>
                </div>

                <button type="submit" name="reset" class="btn-auth">
                    <i class="bi bi-check-lg"></i> Update Password
                </button>
            </form>
            <?php endif; ?>

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

// Strength meter
document.getElementById('pw1')?.addEventListener('input', function () {
    const v = this.value;
    const fill  = document.getElementById('pwFill');
    const label = document.getElementById('pwLabel');
    let score = 0;
    if (v.length >= 8)            score++;
    if (/[A-Z]/.test(v))          score++;
    if (/[0-9]/.test(v))          score++;
    if (/[^A-Za-z0-9]/.test(v))   score++;
    const colors = ['#ff6b6b','#f0c060','#0af','#00e5a0'];
    const labels = ['Weak','Fair','Good','Strong'];
    fill.style.width      = (score / 4 * 100) + '%';
    fill.style.background = colors[score - 1] || '#333';
    label.textContent     = v.length ? (labels[score - 1] || 'Very weak') : 'Password strength';
    label.style.color     = colors[score - 1] || 'var(--muted)';
    checkMatch();
});

// Match indicator
document.getElementById('pw2')?.addEventListener('input', checkMatch);
function checkMatch() {
    const p1  = document.getElementById('pw1').value;
    const p2  = document.getElementById('pw2').value;
    const msg = document.getElementById('matchMsg');
    if (!p2) { msg.textContent = ''; return; }
    if (p1 === p2) {
        msg.textContent = '✓ Passwords match';
        msg.style.color = '#00e5a0';
    } else {
        msg.textContent = '✗ Passwords do not match';
        msg.style.color = '#ff6b6b';
    }
}
</script>
</body>
</html>