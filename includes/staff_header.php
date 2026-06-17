<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$page_title = $page_title ?? 'Staff Dashboard';

// Check if user is actually an staff
if (($_SESSION['role'] ?? '') !== 'staff') {
    header("Location: ../auth/login.php");
    exit;
}

$pic      = $_SESSION['profile_pic'] ?? '';
$name     = $_SESSION['name'] ?? 'Staff';
$initials = strtoupper(substr($name,0,1) . (strpos($name,' ')!==false ? substr(strrchr($name,' '),1,1) : substr($name,1,1)));
$pic_path = (!empty($pic) && file_exists("../assets/uploads/profiles/{$pic}")) ? "../assets/uploads/profiles/" . htmlspecialchars($pic) : null;
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($page_title) ?></title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
        <link rel="stylesheet" href="../assets/css/staff.css">
        <link rel="stylesheet" href="../assets/css/profile_popups.css"> 
    </head>
    <body>
        <header class="staff-topbar">
            <div class="topbar-left">
                <span class="topbar-page"><?= htmlspecialchars($page_title) ?></span>
            </div>
            <div class="topbar-right" style="display: flex; align-items: center; gap: 15px;">
                
                <div class="topbar-badge">
                    <i class="bi bi-shield-fill-check"></i> Staff Panel
                </div>

                <button type="button" id="staffProfileBtn" class="profile-icon-btn" style="background:none; border:none; cursor:pointer; padding:0; position:relative;">
                    <?php if ($pic_path): ?>
                        <img src="<?= $pic_path ?>" alt="Staff" style="width:40px; height:40px; border-radius:50%; object-fit:cover;">
                    <?php else: ?>
                        <span style="width:40px; height:40px; border-radius:50%; background:#dc3545; color:white; display:inline-flex; align-items:center; justify-content:center; font-weight:bold;"><?= $initials ?></span>
                    <?php endif; ?>
                </button>
            </div>
        </header>

        <div id="staffProfileModal" class="profile-modal">
            <div class="modal-header-card">
                <?php if ($pic_path): ?>
                    <img src="<?= $pic_path ?>" alt="Staff Avatar" class="modal-avatar">
                <?php else: ?>
                    <div class="modal-avatar-initials" style="background:#dc3545;"><?= $initials ?></div>
                <?php endif; ?>
                <h4 class="modal-user-name"><?= htmlspecialchars($name) ?></h4>
                <span class="modal-user-role">Staff Member</span>
            </div>
            <div class="modal-body-links">
                <a href="../staff/profile.php" class="modal-link">
                    <i class="bi bi-person-workspace"></i> Staff Settings
                </a>
                <a href="../staff/staff_dashboard.php" class="modal-link">
                    <i class="bi bi-people"></i> Staff Dashboard
                </a>
                <a href="../auth/logout.php" class="modal-link logout">
                    <i class="bi bi-box-arrow-right"></i> Log Out
                </a>
            </div>
        </div>

        <script>
            const staffBtn = document.getElementById("staffProfileBtn");
            const staffModal = document.getElementById("staffProfileModal");

            staffBtn.addEventListener("click", function (e) {
                e.stopPropagation();
                staffModal.style.display = staffModal.style.display === "block" ? "none" : "block";
            });

            window.addEventListener("click", function (e) {
                if (!staffModal.contains(e.target) && e.target !== staffBtn) {
                    staffModal.style.display = "none";
                }
            });
        </script>
    </body>
</html>