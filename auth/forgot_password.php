<?php
require_once "../config/db.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once "../vendor/autoload.php";



$message = "";
$status  = "";
$sent    = false;



if (isset($_POST['submit'])) {

    $email = trim($_POST['email']);

    if (empty($email)) {
        $message = "Email address is required.";
        $status  = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $status  = "error";

    } else {
        $stmt = mysqli_prepare($conn, "SELECT user_id, name FROM users WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        // Always show success (don't reveal if email exists — security)
        $sent   = true;
        $status = "success";

        if ($row) {
            // Generate secure token
            $token  = bin2hex(random_bytes(32));
            $expiry = date("Y-m-d H:i:s", strtotime("+30 minutes"));
            $name   = htmlspecialchars($row['name']);

            // Save token to DB
           $sql = "UPDATE users 
           SET reset_token=?, reset_expiry=? 
           WHERE user_id=?";

           $upd = mysqli_prepare($conn, $sql);

            if (!$upd) {
                die("Prepare failed: " . mysqli_error($conn));
            }

            mysqli_stmt_bind_param($upd, "ssi", $token, $expiry, $row['user_id']);

            mysqli_stmt_execute($upd);
                        

            $resetLink = $_ENV['APP_URL'] . "/auth/reset_password.php?token=" . urlencode($token);

            // ── Send real email ──────────────────────────────
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = getenv('SMTP_HOST');
                $mail->SMTPAuth   = true;
                $mail->Username = getenv('SMTP_USER');
                $mail->Password = getenv('SMTP_PASS');
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = getenv('SMTP_PORT');

                $mail->setFrom($_ENV['SMTP_FROM'], $_ENV['SMTP_FROM_NAME']);
                $mail->addAddress($email, $name);
                $mail->isHTML(true);
                $mail->Subject = 'Reset Your Password – Beerta Daarusalaam';

                // ── Branded HTML email ───────────────────────
                $mail->Body = '
<!DOCTYPE html>
<html>
<body style="margin:0;padding:0;background:#080c10;font-family:Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0">
<tr><td align="center" style="padding:40px 20px;">
<table width="580" cellpadding="0" cellspacing="0" style="background:#0e1520;border-radius:16px;border:1px solid rgba(255,255,255,.08);overflow:hidden;">

  <!-- Header -->
  <tr><td style="background:linear-gradient(135deg,#001a10,#001520);padding:36px 40px 28px;">
    <table cellpadding="0" cellspacing="0"><tr>
      <td style="width:42px;height:42px;background:linear-gradient(135deg,#00e5a0,#0af);border-radius:10px;text-align:center;vertical-align:middle;">
        <span style="font-family:Arial;font-weight:900;font-size:14px;color:#000;">BD</span>
      </td>
      <td style="padding-left:12px;font-family:Arial;font-size:18px;font-weight:700;color:#fff;">
        Beerta <span style="color:#00e5a0;">Daarusalaam</span>
      </td>
    </tr></table>
    <h1 style="margin:22px 0 0;font-size:26px;color:#fff;">Password Reset Request</h1>
  </td></tr>

  <!-- Body -->
  <tr><td style="padding:32px 40px;">
    <p style="color:#e8eef8;font-size:15px;margin:0 0 8px;">Hi <strong>' . $name . '</strong>,</p>
    <p style="color:#6b7fa0;font-size:14px;line-height:1.75;margin:0 0 28px;">
      We received a request to reset the password for your Beerta Daarusalaam account.<br>
      Click the button below to set a new password. This link will expire in <strong style="color:#f0c060;">30 minutes</strong>.
    </p>
    <div style="text-align:center;margin-bottom:28px;">
      <a href="' . $resetLink . '" style="display:inline-block;background:#00e5a0;color:#000;font-weight:700;font-size:15px;padding:14px 40px;border-radius:12px;text-decoration:none;">
        🔑 Reset My Password
      </a>
    </div>
    <p style="color:#6b7fa0;font-size:13px;line-height:1.7;margin:0 0 16px;">
      Or copy and paste this link into your browser:
    </p>
    <p style="background:#121b28;border:1px solid rgba(255,255,255,.07);border-radius:8px;padding:12px 14px;color:#00e5a0;font-size:12px;word-break:break-all;margin:0 0 24px;">
      ' . $resetLink . '
    </p>
    <p style="color:#6b7fa0;font-size:13px;line-height:1.7;margin:0;">
      If you did not request a password reset, you can safely ignore this email.<br>
      Your password will remain unchanged.
    </p>
  </td></tr>

  <!-- Footer -->
  <tr><td style="padding:20px 40px;border-top:1px solid rgba(255,255,255,.07);text-align:center;">
    <p style="color:#6b7fa0;font-size:12px;margin:0;">
      © 2026 Beerta Daarusalaam · Mogadishu, Somalia<br>
      This is an automated email — please do not reply.
    </p>
  </td></tr>

</table>
</td></tr>
</table>
</body>
</html>';

                // Plain text fallback
                $mail->AltBody = "Hi {$name},\n\nReset your password here: {$resetLink}\n\nThis link expires in 30 minutes.\n\nIf you didn't request this, ignore this email.";

                if ($mail->send()) {
                    echo "EMAIL SENT";
                    exit;
                } else {
                    echo $mail->ErrorInfo;
                    exit;
                }

            } catch (Exception $e) {
                die("Mailer Error: " . $mail->ErrorInfo);
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
    <title>Forgot Password – Beerta Daarusalaam</title>
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
            <h1 class="auth-tagline">Locked<br><span>Out?</span><br>No Worries.</h1>
            <p class="auth-tagline-sub">Enter your email and we'll send you a secure reset link directly to your inbox.</p>
            <div class="auth-perks">
                <div class="perk"><i class="bi bi-shield-fill-check"></i> Secure token-based reset</div>
                <div class="perk"><i class="bi bi-clock-fill"></i> Link expires in 30 minutes</div>
                <div class="perk"><i class="bi bi-envelope-fill"></i> Sent to your registered email</div>
            </div>
        </div>
        <div class="auth-left-bg"></div>
    </div>

    <!-- RIGHT -->
    <div class="auth-right">
        <div class="auth-card">

            <?php if ($sent): ?>
            <!-- SUCCESS STATE -->
            <div class="sent-state">
                <div class="sent-icon"><i class="bi bi-envelope-check-fill"></i></div>
                <h2>Check Your Inbox</h2>
                <p>If <strong><?= htmlspecialchars($_POST['email']) ?></strong> is registered, a reset link has been sent.</p>
                <p class="sent-note" style="margin-top:10px;">
                    Didn't receive it? Check your <strong>spam/junk</strong> folder or
                    <a href="forgot_password.php">try again</a>.
                </p>
                <a href="login.php" class="btn-auth" style="margin-top:28px;text-decoration:none;display:flex;justify-content:center;">
                    <i class="bi bi-arrow-left"></i> Back to Login
                </a>
            </div>

            <?php else: ?>
            <!-- FORM STATE -->
            <div class="auth-card-header">
                <h2>Forgot Password</h2>
                <p>Enter your email and we'll send a reset link.</p>
            </div>

            <?php if ($message && $status === 'error'): ?>
                <div class="auth-alert auth-alert-error">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Email Address</label>
                    <div class="input-wrap">
                        <i class="bi bi-envelope"></i>
                        <input type="email" name="email"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                               placeholder="you@example.com" required autofocus>
                    </div>
                </div>

                <button type="submit" name="submit" class="btn-auth">
                    <i class="bi bi-send-fill"></i> Send Reset Link
                </button>
            </form>

            <div class="auth-switch" style="margin-top:24px;">
                <a href="login.php"><i class="bi bi-arrow-left"></i> Back to Login</a>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>
</body>
</html>
