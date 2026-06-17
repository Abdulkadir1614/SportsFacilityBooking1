<?php
session_start();
require_once "../auth/session_timeout.php";
require_once "../config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php"); 
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$role    = $_SESSION['role'] ?? 'customer';

// Fetch user data
$sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$success = "";
$error = "";

// ── Handle profile update ───────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {

    $new_name  = trim($_POST['name']  ?? '');
    $new_email = trim($_POST['email'] ?? '');
    $new_phone = trim($_POST['phone'] ?? '');

    // Validations
    if (strlen($new_name) < 3 || !preg_match("/^[a-zA-Z\s]+$/", $new_name)) {
        $error = "Name must be at least 3 letters only.";
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (!ctype_digit($new_phone) || strlen($new_phone) < 9) {
        $error = "Phone must be digits only, min 9 digits.";
    } else {
        // Check email not taken by another user
        $chk = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
        $chk->bind_param("si", $new_email, $user_id);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $error = "This email is already used by another account.";
        } else {
            // Handle profile picture upload
            $pic_filename = $user['profile_pic'];
            if (!empty($_FILES['profile_pic']['name'])) {
                $ext      = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
                $allowed  = ['jpg','jpeg','png','webp'];
                $max_size = 2 * 1024 * 1024; // 2MB

                if (!in_array($ext, $allowed)) {
                    $error = "Only JPG, PNG, WEBP images allowed.";
                } elseif ($_FILES['profile_pic']['size'] > $max_size) {
                    $error = "Image must be under 2MB.";
                } else {
                    $pic_filename = "user_{$user_id}_" . time() . ".{$ext}";
                    $upload_path  = "../assets/uploads/profiles/" . $pic_filename;
                    if (!is_dir("../assets/uploads/profiles/")) {
                        mkdir("../assets/uploads/profiles/", 0755, true);
                    }
                    if (!move_uploaded_file($_FILES['profile_pic']['tmp_name'], $upload_path)) {
                        $error = "Failed to upload image.";
                        $pic_filename = $user['profile_pic'];
                    }
                }
            }

            if (!$error) {
                $upd = $conn->prepare("UPDATE users SET name=?, email=?, phone_number=?, profile_pic=? WHERE user_id=?");
                $upd->bind_param("ssssi", $new_name, $new_email, $new_phone, $pic_filename, $user_id);
                $upd->execute();

                // Refresh session & state data
                $_SESSION['name']        = $new_name;
                $_SESSION['profile_pic'] = $pic_filename;
                $user['name']        = $new_name;
                $user['email']       = $new_email;
                $user['phone_number']= $new_phone;
                $user['profile_pic'] = $pic_filename;

                $success = "Profile updated successfully!";
            }
        }
    }
}

// ── Handle password change ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {

    $old_pw  = $_POST['old_password']     ?? '';
    $new_pw  = $_POST['new_password']     ?? '';
    $conf_pw = $_POST['confirm_password'] ?? '';

    $pw_stmt = $conn->prepare("SELECT password FROM users WHERE user_id=?");
    $pw_stmt->bind_param("i", $user_id);
    $pw_stmt->execute();
    $pw_row = $pw_stmt->get_result()->fetch_assoc();

    if (!password_verify($old_pw, $pw_row['password'])) {
        $error = "Current password is incorrect.";
    } elseif (strlen($new_pw) < 6 || !preg_match("/[A-Za-z]/", $new_pw) || !preg_match("/[0-9]/", $new_pw)) {
        $error = "New password must be 6+ characters with letters and numbers.";
    } elseif ($new_pw !== $conf_pw) {
        $error = "New passwords do not match.";
    } else {
        $hash    = password_hash($new_pw, PASSWORD_DEFAULT);
        $pw_upd  = $conn->prepare("UPDATE users SET password=? WHERE user_id=?");
        $pw_upd->bind_param("si", $hash, $user_id);
        $pw_upd->execute();
        $success = "Password changed successfully!";
    }
}

// Role color configurations
$role_config = [
    'customer' => ['label'=>'Customer',      'color'=>'#00e5a0', 'bg'=>'rgba(0,229,160,.12)', 'icon'=>'bi-person-fill'],
    'staff'    => ['label'=>'Staff Member',  'color'=>'#00aaff', 'bg'=>'rgba(0,170,255,.12)', 'icon'=>'bi-person-badge-fill'],
    'admin'    => ['label'=>'Administrator', 'color'=>'#f0c060', 'bg'=>'rgba(240,192,96,.12)', 'icon'=>'bi-shield-fill'],
];
$rc = $role_config[$role] ?? $role_config['customer'];

// Contextual Back link routing based on role
$dashboard = match($role) {
    'admin'  => '../admin/dashboard.php',
    'staff'  => '../staff/staff_dashboard.php',
    default  => '../customer/dashboard.php',
};

$pic_path = (!empty($user['profile_pic']) && file_exists("../assets/uploads/profiles/" . $user['profile_pic']))
    ? "../assets/uploads/profiles/" . htmlspecialchars($user['profile_pic'])
    : null;

// Initial builder fallback logic
$initials = "U";
if (!empty($user['name'])) {
    $words = explode(' ', trim($user['name']));
    $first = substr($words[0], 0, 1);
    $last = (count($words) > 1) ? substr(end($words), 0, 1) : substr($words[0], 1, 1);
    $initials = strtoupper($first . $last);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile – Beerta Daarusalaam</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/profile.css">
    <style>
        /* Applies context accent injection seamlessly matching roles */
        :root {
            --role-color: <?= $rc['color'] ?>;
            --role-bg: <?= $rc['bg'] ?>;
        }
    </style>
</head>
<body>

<div class="topbar">
    <div class="topbar-left">
        <a href="<?= $dashboard ?>" class="btn-back"><i class="bi bi-arrow-left"></i> Dashboard</a>
        <span class="topbar-title">My Profile</span>
    </div>
    <div class="role-badge">
        <i class="bi <?= $rc['icon'] ?>"></i>
        <?= $rc['label'] ?>
    </div>
</div>

<div class="page">

    <div class="profile-hero">
        <div class="avatar-wrap">
            <div class="avatar" id="avatarDisplay">
                <?php if ($pic_path): ?>
                    <img src="<?= $pic_path ?>" id="avatarImg" alt="Profile">
                <?php else: ?>
                    <span id="avatarInitials"><?= $initials ?></span>
                <?php endif; ?>
            </div>
            <div class="avatar-edit-btn" onclick="document.getElementById('avatarInput').click()" title="Change photo">
                <i class="bi bi-camera-fill"></i>
            </div>
            <input type="file" id="avatarInput" accept="image/*" onchange="previewAvatar(this)">
        </div>

        <div class="hero-info">
            <div class="hero-name"><?= htmlspecialchars($user['name']) ?></div>
            <div class="hero-email"><i class="bi bi-envelope-fill"></i> <?= htmlspecialchars($user['email']) ?></div>
            <div class="role-badge" style="margin-top: 8px; color: var(--role-color); background: var(--role-bg);">
                <i class="bi <?= $rc['icon'] ?>"></i> <?= $rc['label'] ?>
            </div>
            <div class="joined-text">
                <i class="bi bi-calendar3"></i>
                Member since <?= isset($user['created_at']) ? date('F Y', strtotime($user['created_at'])) : date('F Y') ?>
            </div>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><i class="bi bi-exclamation-circle-fill"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="profile-tabs">
        <button class="ptab active" onclick="switchTab('info',this)">
            <i class="bi bi-person-fill"></i> Personal Info
        </button>
        <button class="ptab" onclick="switchTab('password',this)">
            <i class="bi bi-lock-fill"></i> Password
        </button>
        <button class="ptab" onclick="switchTab('account',this)">
            <i class="bi bi-gear-fill"></i> Account
        </button>
    </div>

    <div class="form-card">

        <div class="tab-pane active" id="pane-info">
            <div class="form-section-title"><i class="bi bi-person-fill"></i> Personal Information</div>

            <form method="POST" enctype="multipart/form-data" id="profileForm">
                <input type="file" name="profile_pic" id="hiddenPicInput" accept="image/*" style="display:none">

                <div class="form-row">
                    <div class="form-group">
                        <label><i class="bi bi-person"></i> Full Name</label>
                        <div class="input-wrap">
                            <i class="bi bi-person prefix-icon"></i>
                            <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label><i class="bi bi-telephone"></i> Phone Number</label>
                        <div class="input-wrap">
                            <i class="bi bi-telephone prefix-icon"></i>
                            <input type="text" name="phone" value="<?= htmlspecialchars($user['phone_number'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label><i class="bi bi-envelope"></i> Email Address</label>
                    <div class="input-wrap">
                        <i class="bi bi-envelope prefix-icon"></i>
                        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label><i class="bi bi-person-badge"></i> System Access Role</label>
                    <div class="input-wrap content-disabled">
                        <i class="bi <?= $rc['icon'] ?> prefix-icon" style="color:var(--role-color)"></i>
                        <input type="text" value="<?= $rc['label'] ?>" readonly>
                    </div>
                </div>

                <div class="action-footer">
                    <button type="submit" name="update_profile" class="btn-save">
                        <i class="bi bi-check-lg"></i> Save Changes
                    </button>
                    <span class="action-hint">
                        <i class="bi bi-camera" style="color:var(--role-color)"></i>
                        Use the camera icon on your avatar image container above to stage profile photos.
                    </span>
                </div>
            </form>
        </div>

        <div class="tab-pane" id="pane-password">
            <div class="form-section-title"><i class="bi bi-lock-fill"></i> Change Account Password</div>

            <form method="POST">
                <div class="form-group">
                    <label><i class="bi bi-lock"></i> Current Password</label>
                    <div class="input-wrap">
                        <i class="bi bi-lock prefix-icon"></i>
                        <input type="password" name="old_password" id="oldPw" placeholder="Enter current password" required>
                        <button type="button" class="toggle-pw" onclick="togglePw('oldPw',this)">
                            <i class="bi bi-eye-slash"></i>
                        </button>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="bi bi-lock-fill"></i> New Password</label>
                        <div class="input-wrap">
                            <i class="bi bi-lock-fill prefix-icon"></i>
                            <input type="password" name="new_password" id="newPw" placeholder="Min 6 chars" required>
                            <button type="button" class="toggle-pw" onclick="togglePw('newPw',this)">
                                <i class="bi bi-eye-slash"></i>
                            </button>
                        </div>
                        <div class="pw-bar"><div class="pw-fill" id="pwFill"></div></div>
                        <div class="pw-label" id="pwLabel">Password strength</div>
                    </div>
                    <div class="form-group">
                        <label><i class="bi bi-lock-fill"></i> Confirm New Password</label>
                        <div class="input-wrap">
                            <i class="bi bi-lock-fill prefix-icon"></i>
                            <input type="password" name="confirm_password" id="confPw" placeholder="Repeat password" required>
                            <button type="button" class="toggle-pw" onclick="togglePw('confPw',this)">
                                <i class="bi bi-eye-slash"></i>
                            </button>
                        </div>
                        <div class="match-msg" id="matchMsg"></div>
                    </div>
                </div>
                <button type="submit" name="change_password" class="btn-save">
                    <i class="bi bi-key-fill"></i> Update Password
                </button>
            </form>
        </div>

        <div class="tab-pane" id="pane-account">
            <div class="form-section-title"><i class="bi bi-gear-fill"></i> Meta Data Settings</div>

            <div class="meta-container">
                <div class="meta-box">
                    <div class="meta-header">Account System Index ID</div>
                    <div class="meta-value">#<?= $user_id ?></div>
                </div>
                <div class="meta-box">
                    <div class="meta-header">Authorization Clearance Level</div>
                    <div class="role-badge" style="display:inline-flex; color: var(--role-color); background: var(--role-bg);">
                        <i class="bi <?= $rc['icon'] ?>"></i> <?= $rc['label'] ?>
                    </div>
                </div>
                <div class="meta-box">
                    <div class="meta-header">Registration Timestamp</div>
                    <div class="meta-value"><?= isset($user['created_at']) ? date('F d, Y', strtotime($user['created_at'])) : date('F d, Y') ?></div>
                </div>
            </div>

            <div class="danger-zone">
                <div class="danger-info">
                    <h4><i class="bi bi-exclamation-triangle-fill"></i> Log Out Account Session</h4>
                    <p>End your system dashboard context cleanly and securely.</p>
                </div>
                <a href="../auth/logout.php" class="btn-danger">
                    <i class="bi bi-box-arrow-right"></i> Sign Out
                </a>
            </div>
        </div>

    </div>
</div>

<script>
function switchTab(tab, btn) {
    document.querySelectorAll('.ptab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('pane-' + tab).classList.add('active');
}

function togglePw(id, btn) {
    const input = document.getElementById(id);
    const icon  = btn.querySelector('i');
    input.type = input.type === 'password' ? 'text' : 'password';
    icon.className = input.type === 'password' ? 'bi bi-eye-slash' : 'bi bi-eye';
}

document.getElementById('newPw')?.addEventListener('input', function () {
    const v = this.value;
    const fill = document.getElementById('pwFill');
    const label = document.getElementById('pwLabel');
    let score = 0;
    if (v.length >= 6)           score++;
    if (/[A-Z]/.test(v))         score++;
    if (/[0-9]/.test(v))         score++;
    if (/[^A-Za-z0-9]/.test(v))  score++;
    const colors = ['#ff6b6b','#f0c060','#00aaff','#00e5a0'];
    const labels = ['Weak','Fair','Good','Strong'];
    fill.style.width      = (score/4*100) + '%';
    fill.style.background = colors[score-1] || '#333';
    label.textContent     = v.length ? (labels[score-1]||'Very weak') : 'Password strength';
    label.style.color     = colors[score-1] || 'var(--muted)';
    checkMatch();
});

document.getElementById('confPw')?.addEventListener('input', checkMatch);

function checkMatch() {
    const msg = document.getElementById('matchMsg');
    const p1  = document.getElementById('newPw').value;
    const p2  = document.getElementById('confPw').value;
    if (!p2) { msg.textContent=''; return; }
    msg.textContent = p1===p2 ? '✓ Passwords match' : '✗ Do not match';
    msg.style.color = p1===p2 ? '#00e5a0' : '#ff6b6b';
}

function previewAvatar(input) {
    const file = input.files[0];
    if (!file) return;

    // Cross-inject values into hidden file elements belonging inside form boundaries
    const dt = new DataTransfer();
    dt.items.add(file);
    document.getElementById('hiddenPicInput').files = dt.files;

    const reader = new FileReader();
    reader.onload = e => {
        const display = document.getElementById('avatarDisplay');
        display.innerHTML = `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">`;
    };
    reader.readAsDataURL(file);
}
</script>
</body>
</html>