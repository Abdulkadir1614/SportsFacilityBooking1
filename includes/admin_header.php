<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$page_title = $page_title ?? 'Admin Dashboard';

// Check if user is actually an admin
if (($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$pic      = $_SESSION['profile_pic'] ?? '';
$name     = $_SESSION['name'] ?? 'Admin';
$initials = strtoupper(substr($name,0,1) . (strpos($name,' ')!==false ? substr(strrchr($name,' '),1,1) : substr($name,1,1)));
$pic_path = (!empty($pic) && file_exists("../assets/uploads/profiles/{$pic}")) ? "../assets/uploads/profiles/" . htmlspecialchars($pic) : null;
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($page_title) ?></title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
        <link rel="stylesheet" href="../assets/css/admin/admin_base.css">
        <link rel="stylesheet" href="../assets/css/profile_popups.css"> 
    </head>
    <body>
        <header class="admin-topbar">
            <div class="topbar-left">
                <span class="topbar-page"><?= htmlspecialchars($page_title) ?></span>
            </div>
            <div class="topbar-right" style="display: flex; align-items: center; gap: 15px;">
                
                <div class="topbar-badge">
                    <i class="bi bi-shield-fill-check"></i> Admin Panel
                </div>

                <button type="button" id="adminProfileBtn" class="profile-icon-btn" style="background:none; border:none; cursor:pointer; padding:0; position:relative;">
                    <?php if ($pic_path): ?>
                        <img src="<?= $pic_path ?>" alt="Admin" style="width:40px; height:40px; border-radius:50%; object-fit:cover;">
                    <?php else: ?>
                        <span style="width:40px; height:40px; border-radius:50%; background:#dc3545; color:white; display:inline-flex; align-items:center; justify-content:center; font-weight:bold;"><?= $initials ?></span>
                    <?php endif; ?>
                </button>
            </div>
        </header>

        <div id="adminProfileModal" class="profile-modal">
            <div class="modal-header-card">
                <?php if ($pic_path): ?>
                    <img src="<?= $pic_path ?>" alt="Admin Avatar" class="modal-avatar">
                <?php else: ?>
                    <div class="modal-avatar-initials" style="background:#dc3545;"><?= $initials ?></div>
                <?php endif; ?>
                <h4 class="modal-user-name"><?= htmlspecialchars($name) ?></h4>
                <span class="modal-user-role">Administrator</span>
            </div>
            <div class="modal-body-links">
                <a href="../admin/profile.php" class="modal-link">
                    <i class="bi bi-person-workspace"></i> Admin Settings
                </a>
                <a href="../admin/manage_staff.php" class="modal-link">
                    <i class="bi bi-people"></i> Staff Management
                </a>
                <a href="../auth/logout.php" class="modal-link logout">
                    <i class="bi bi-box-arrow-right"></i> Log Out
                </a>
            </div>
        </div>

        <script>
            const adminBtn = document.getElementById("adminProfileBtn");
            const adminModal = document.getElementById("adminProfileModal");

            adminBtn.addEventListener("click", function (e) {
                e.stopPropagation();
                adminModal.style.display = adminModal.style.display === "block" ? "none" : "block";
            });

            window.addEventListener("click", function (e) {
                if (!adminModal.contains(e.target) && e.target !== adminBtn) {
                    adminModal.style.display = "none";
                }
            });
        </script>
    </body>
</html>