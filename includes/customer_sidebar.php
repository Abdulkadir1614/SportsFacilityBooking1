<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_url = $_SERVER['PHP_SELF'];
function customerActive($page_path) {
    global $current_url;
    return (basename($current_url) === basename($page_path)) ? 'active' : '';
}

// Dynamic Profile Setup 
$pic      = $_SESSION['profile_pic'] ?? '';
$name     = $_SESSION['name'] ?? 'Customer';
$initials = strtoupper(substr($name, 0, 1) . (strpos($name, ' ') !== false ? substr(strrchr($name, ' '), 1, 1) : substr($name, 1, 1)));
$pic_path = (!empty($pic) && file_exists("../assets/uploads/profiles/{$pic}")) ? "../assets/uploads/profiles/" . htmlspecialchars($pic) : null;
?>
<aside class="customer-sidebar" id="customerSidebar">

    <button class="customer-sidebar-toggle" id="toggleCustomerSidebar" title="Toggle sidebar">
        <i class="bi bi-layout-sidebar"></i>
    </button>

    <div class="customer-sidebar-profile">
        <div class="profile-avatar">
            <?php if ($pic_path): ?>
                <img src="<?= $pic_path ?>" alt="Customer Avatar">
            <?php else: ?>
                <div>
                    <?= $initials ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="profile-info">
            <span class="profile-role">Customer</span>
            <span class="profile-name"><?= htmlspecialchars($name) ?></span>
        </div>
    </div>

    <nav class="customer-sidebar-nav">
        <span class="nav-section-label">Main</span>

        <a href="dashboard.php" data-tooltip="Dashboard" class="nav-link <?= customerActive('dashboard.php') ?>">
            <i class="bi bi-speedometer2"></i><span>Dashboard</span>
        </a>
        <a href="profile.php" data-tooltip="Profile" class="nav-link <?= customerActive('profile.php') ?>">
            <i class="bi bi-person"></i><span>Profile</span>
        </a>
        <a href="view_facilities.php" data-tooltip="Facilities" class="nav-link <?= customerActive('view_facilities.php') ?>">
            <i class="bi bi-building"></i><span>Facilities</span>
        </a>
        <a href="booking_history.php" data-tooltip="Booking History" class="nav-link <?= customerActive('booking_history.php') ?>">
            <i class="bi bi-calendar-check"></i><span>Booking History</span>
        </a>
        <a href="manage_payment.php" data-tooltip="Payments" class="nav-link <?= customerActive('manage_payment.php') ?>">
            <i class="bi bi-credit-card"></i><span>Payments</span>
        </a>

        <span class="nav-section-label">Support</span>
        <a href="../notifications/notify.php" data-tooltip="Notifications" class="nav-link <?= customerActive('../notifications/notify.php') ?>">
            <i class="bi bi-bell"></i><span>Notifications</span>
        </a>
        <a href="../chatbot/chatbot.php" data-tooltip="Chatbot" class="nav-link <?= customerActive('../chatbot/chatbot.php') ?>">
            <i class="bi bi-robot"></i><span>Chatbot</span>
        </a>
    </nav>

    <div class="customer-sidebar-footer">
        <a href="../auth/logout.php" data-tooltip="Logout" class="nav-link logout-link">
            <i class="bi bi-box-arrow-right"></i><span>Logout</span>
        </a>
    </div>
</aside>