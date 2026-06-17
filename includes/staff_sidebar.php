<?php
$current = basename($_SERVER['PHP_SELF']);

function sAct($p){
    global $current;
    return $current === $p ? 'active' : '';
}

/* Pending counts */
global $conn;

$pb = 0;
$pp = 0;

if (isset($conn)) {

    $bq = $conn->query("SELECT COUNT(*) FROM bookings WHERE booking_status='Pending'");
    if($bq) $pb = $bq->fetch_row()[0];

    $pq = $conn->query("SELECT COUNT(*) FROM payments WHERE payment_status='Pending'");
    if($pq) $pp = $pq->fetch_row()[0];
}

/* User info */
$name = $_SESSION['name'] ?? 'Staff User';

$pic = $_SESSION['profile_pic'] ?? '';

$init = strtoupper(
    substr($name,0,1) .
    (strpos($name,' ') !== false
        ? substr(strrchr($name,' '),1,1)
        : substr($name,1,1))
);

$pic_path = null;

if (!empty($pic) && file_exists("../assets/uploads/profiles/" . $pic)) {
    $pic_path = "../assets/uploads/profiles/" . htmlspecialchars($pic);
}
?>
<aside class="staff-sidebar" id="staffSidebar">

    <!-- Top -->
    <div class="sidebar-top">
        <div class="sidebar-brand">
            <span class="brand-name">Staff <em>Panel</em></span>
        </div>

        <button class="sidebar-toggle" id="toggleSidebar">
            <i class="bi bi-layout-sidebar"></i>
        </button>
    </div>

    <!-- Profile -->
    <div class="sidebar-profile">
        <div class="profile-avatar">
            <?php if($pic_path): ?>
                <img src="<?= $pic_path ?>" alt="">
            <?php else: ?>
                <?= $init ?>
            <?php endif; ?>
        </div>

        <div class="profile-info">
            <span class="profile-role">Staff Member</span>
            <span class="profile-name"><?= htmlspecialchars($name) ?></span>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">

        <span class="nav-section-label">Menu</span>

        <a href="staff_dashboard.php"
           data-tooltip="Dashboard"
           class="nav-link <?= sAct('staff_dashboard.php') ?>">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </a>

        <a href="manage_bookings.php"
           data-tooltip="Bookings"
           class="nav-link <?= sAct('manage_bookings.php') ?>">
            <i class="bi bi-calendar-check"></i>
            <span>Bookings</span>

            <?php if($pb > 0): ?>
                <b class="nav-badge"><?= $pb ?></b>
            <?php endif; ?>
        </a>

        <a href="manage_payments.php"
           data-tooltip="Payments"
           class="nav-link <?= sAct('manage_payments.php') ?>">
            <i class="bi bi-credit-card"></i>
            <span>Payments</span>

            <?php if($pp > 0): ?>
                <b class="nav-badge nav-badge-gold"><?= $pp ?></b>
            <?php endif; ?>
        </a>

        <a href="manage_facilities.php"
           data-tooltip="Facilities"
           class="nav-link <?= sAct('manage_facilities.php') ?>">
            <i class="bi bi-building"></i>
            <span>Facilities</span>
        </a>

        <a href="../staff/profile.php"
           data-tooltip="Profile"
           class="nav-link <?= sAct('profile.php') ?>">
            <i class="bi bi-person-circle"></i>
            <span>My Profile</span>
        </a>

    </nav>

    <!-- Footer -->
    <div class="sidebar-footer">
        <a href="../auth/logout.php"
           data-tooltip="Logout"
           class="nav-link logout-link">
            <i class="bi bi-box-arrow-right"></i>
            <span>Logout</span>
        </a>
    </div>

</aside>

<!-- Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"
     onclick="closeMobileSidebar()"></div>