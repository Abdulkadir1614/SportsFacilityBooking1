<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Get unread notification count
$bell_count = 0;
if (isset($_SESSION['user_id']) && isset($conn)) {
    $bs = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND status='unread'");
    $bs->bind_param("i", $_SESSION['user_id']);
    $bs->execute();
    $bs->bind_result($bell_count);
    $bs->fetch();
    $bs->close();
}

$current = basename($_SERVER['PHP_SELF']);
function isActive($page) {
    global $current;
    return $current === $page ? 'active' : '';
}

$pic      = $_SESSION['profile_pic'] ?? '';
$name     = $_SESSION['name'] ?? 'Guest';
$initials = strtoupper(substr($name,0,1) . (strpos($name,' ')!==false ? substr(strrchr($name,' '),1,1) : substr($name,1,1)));
$pic_path = (!empty($pic) && file_exists("../assets/uploads/profiles/{$pic}")) ? "../assets/uploads/profiles/" . htmlspecialchars($pic) : null;
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Beerta Daarusalaam</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link rel="stylesheet" href="../assets/css/customer_header.css">
        <link rel="stylesheet" href="../assets/css/profile_popups.css">
    </head>
    <body>
        <header class="customer-header">
            <div class="header-inner">

                <a href="../customer/dashboard.php" class="logo">
                    <div class="logo-icon">
                        <img src="../assets/logo_bd.png" alt="Logo">
                    </div>
                    <span class="logo-text">Beerta <span>Daarusalaam</span></span>
                </a>

                <button class="hamburger" id="hamburger-btn"><i class="bi bi-list"></i></button>

                <nav class="customer-nav" id="nav-menu">
                    <a href="../customer/dashboard.php" class="<?= isActive('dashboard.php') ?>">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                    <a href="../customer/view_facilities.php" class="<?= isActive('view_facilities.php') ?>">
                        <i class="bi bi-building"></i> Facilities
                    </a>
                    <a href="../customer/booking_history.php" class="<?= isActive('booking_history.php') ?>">
                        <i class="bi bi-clock-history"></i> History
                    </a>
                    
                    <a href="../notifications/notify.php" id="bell-link" class="<?= isActive('notify.php') ?>">
                        <span class="bell-wrap">
                            <i class="bi bi-bell"></i>
                            <?php if ($bell_count > 0): ?>
                                <span class="bell-badge" id="headerBellCount"><?= $bell_count ?></span>
                            <?php endif; ?>
                        </span>
                        Notifications
                    </a>
                </nav>

                <button type="button" id="customerProfileBtn" class="profile-icon-btn" style="background: none; border: none; cursor: pointer; padding: 0; position: relative;">
                    <?php if ($pic_path): ?>
                        <img src="<?= $pic_path ?>" alt="Profile" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                    <?php else: ?>
                        <span style="width: 30px; height: 30px; border-radius: 50%; background: #28a745; color: white; display: inline-flex; align-items: center; justify-content: center; font-weight: bold;"><?= $initials ?></span>
                    <?php endif; ?>
                </button>

            </div>
        </header>

        <div id="customerProfileModal" class="profile-modal">
            <div class="modal-header-card">
                <?php if ($pic_path): ?>
                    <img src="<?= $pic_path ?>" alt="Profile Avatar" class="modal-avatar">
                <?php else: ?>
                    <div class="modal-avatar-initials" style="background:#28a745;"><?= $initials ?></div>
                <?php endif; ?>
                <h4 class="modal-user-name"><?= htmlspecialchars($name) ?></h4>
                <span class="modal-user-role">Customer Account</span>
            </div>
            <div class="modal-body-links">
                <a href="../customer/profile.php" class="modal-link">
                    <i class="bi bi-person-circle"></i> My Profile Details
                </a>
                <a href="../customer/booking_history.php" class="modal-link">
                    <i class="bi bi-journal-check"></i> My Bookings
                </a>
                <a href="../auth/logout.php" class="modal-link logout">
                    <i class="bi bi-box-arrow-right"></i> Log Out
                </a>
            </div>
        </div>

        <script>
            const customerBtn = document.getElementById("customerProfileBtn");
            const customerModal = document.getElementById("customerProfileModal");
            const hamburgerBtn = document.getElementById("hamburger-btn");
            const navMenu = document.getElementById("nav-menu");

            // Mobile Burger Menu Toggle
            hamburgerBtn.addEventListener("click", function () {
                navMenu.classList.toggle("show");
                this.querySelector("i").className = navMenu.classList.contains("show") ? "bi bi-x-lg" : "bi bi-list";
            });

            // Profile Popup Toggle
            customerBtn.addEventListener("click", function (e) {
                e.stopPropagation();
                customerModal.style.display = customerModal.style.display === "block" ? "none" : "block";
            });

            window.addEventListener("click", function (e) {
                if (!customerModal.contains(e.target) && e.target !== customerBtn) {
                    customerModal.style.display = "none";
                }
            });
        </script>
    </body>
</html>