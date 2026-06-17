<?php
$current = basename($_SERVER['PHP_SELF']);
function adminActive($page) {
    global $current;
    return $current === $page ? 'active' : '';
}
?>
<aside class="sidebar" id="sidebar">

    <button class="sidebar-toggle" id="toggleSidebar" title="Toggle sidebar">
        <i class="bi bi-layout-sidebar"></i>
    </button>

    <div class="sidebar-profile">
        <div class="profile-avatar">
            <img src="../assets/logo_bd.png" alt="Profile">
        </div>
        <div class="profile-info">
            <span class="profile-role">Administrator</span>
            <span class="profile-name"><?= htmlspecialchars($_SESSION['name'] ?? 'Admin') ?></span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <span class="nav-section-label">Main</span>

        <a href="dashboard.php" data-tooltip="Dashboard" class="nav-link <?= adminActive('dashboard.php') ?>">
            <i class="bi bi-speedometer2"></i><span>Dashboard</span>
        </a>
        <a href="manage_facilities.php" data-tooltip="Facilities" class="nav-link <?= adminActive('manage_facilities.php') ?>">
            <i class="bi bi-building"></i><span>Facilities</span>
        </a>

        <span class="nav-section-label">Management</span>

        <a href="manage_staff.php" data-tooltip="Staff" class="nav-link <?= adminActive('manage_staff.php') ?>">
            <i class="bi bi-people"></i><span>Staff</span>
        </a>
        <a href="reports.php" data-tooltip="Reports" class="nav-link <?= adminActive('reports.php') ?>">
            <i class="bi bi-bar-chart-line"></i><span>Reports</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="../auth/logout.php" data-tooltip="Logout" class="nav-link logout-link">
            <i class="bi bi-box-arrow-right"></i><span>Logout</span>
        </a>
    </div>

</aside>