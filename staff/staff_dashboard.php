<?php
session_start();
require_once "../auth/session_timeout.php";
require_once "../config/db.php";
$page_title = 'Staff Dashboard';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../auth/login.php"); exit();
}

$staff_id = $_SESSION['user_id'];
$pending_bookings = $conn->query("SELECT COUNT(*) FROM bookings WHERE booking_status='Pending'")->fetch_row()[0];
$pending_payments = $conn->query("SELECT COUNT(*) FROM payments WHERE payment_status='Pending'")->fetch_row()[0];
$total_facilities = $conn->query("SELECT COUNT(*) FROM facilities")->fetch_row()[0];
$avail_facilities = $conn->query("SELECT COUNT(*) FROM facilities WHERE availability_status='Available'")->fetch_row()[0];
$recent = $conn->query("
    SELECT b.booking_id, u.name AS customer, f.facility_name,
           b.booking_date, b.time_slot, b.booking_status
    FROM bookings b
    JOIN users u ON b.user_id=u.user_id
    JOIN facilities f ON b.facility_id=f.facility_id
    ORDER BY b.booking_id DESC LIMIT 5
");

$current = basename($_SERVER['PHP_SELF']);
function isActive($page) {
    global $current;
    return $current === $page ? 'active' : '';
}

$pic      = $_SESSION['profile_pic'] ?? '';
$name     = $_SESSION['name'] ?? 'Staff';
$initials = strtoupper(substr($name,0,1) . (strpos($name,' ')!==false ? substr(strrchr($name,' '),1,1) : substr($name,1,1)));
$pic_path = (!empty($pic) && file_exists("../assets/uploads/profiles/{$pic}")) ? "../assets/uploads/profiles/" . htmlspecialchars($pic) : null;

function condenseTimeSlots($time_slot_string) {
    if (empty($time_slot_string)) return '—';
    
    // Split the slots by comma and clean up whitespace
    $slots = array_map('trim', explode(',', $time_slot_string));
    
    if (count($slots) <= 1) {
        return $time_slot_string; 
    }
    
    $all_times = [];
    foreach ($slots as $slot) {
        // Split individual slot "08:00 - 09:00" into start and end times
        $parts = array_map('trim', explode('-', $slot));
        if (count($parts) === 2) {
            $all_times[] = $parts[0];
            $all_times[] = $parts[1];
        }
    }
    
    if (empty($all_times)) return $time_slot_string;
    
    // Sort times sequentially to find the true earliest start and latest end
    sort($all_times);
    
    $startTime = reset($all_times);
    $endTime = end($all_times);
    
    return $startTime . ' - ' . $endTime;
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Staff Dashboard – Beerta Daarusalaam</title>
        <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
        <link rel="stylesheet" href="../assets/css/staff.css">
        <link rel="stylesheet" href="../assets/css/profile_popups.css">
    </head>
    <body>


        <div class="staff-layout" id="staffLayout">
            <?php include "../includes/staff_sidebar.php"; ?>
            
            <!-- MAIN -->
            <main class="staff-main">
                

                <?php include "../includes/staff_header.php"; ?>

                <section class="content-area">

                    <!-- Welcome -->
                    <div class="welcome-banner">
                        <div>
                            <span class="page-label"><i class="bi bi-lightning-charge-fill"></i> Welcome back</span>
                            <h1 class="page-title">Hi, <span><?= htmlspecialchars($name) ?></span> 👋</h1>
                            <p class="page-sub">Here's what needs your attention today.</p>
                        </div>
                        <div class="welcome-emoji">🏟</div>
                    </div>

                    <!-- Stats -->
                    <div class="stat-grid">
                        <div class="stat-card sc-green">
                            <div class="stat-icon"><i class="bi bi-calendar-check"></i></div>
                            <div class="stat-body">
                                <div class="stat-num"><?= $pending_bookings ?></div>
                                <div class="stat-label">Pending Bookings</div>
                            </div>
                            <?php if($pending_bookings>0):?><span class="stat-chip">Action needed</span><?php endif;?>
                        </div>
                        <div class="stat-card sc-blue">
                            <div class="stat-icon"><i class="bi bi-credit-card"></i></div>
                            <div class="stat-body">
                                <div class="stat-num"><?= $pending_payments ?></div>
                                <div class="stat-label">Pending Payments</div>
                            </div>
                            <?php if($pending_payments>0):?><span class="stat-chip">Verify now</span><?php endif;?>
                        </div>
                        <div class="stat-card sc-gold">
                            <div class="stat-icon"><i class="bi bi-building"></i></div>
                            <div class="stat-body">
                                <div class="stat-num"><?= $total_facilities ?></div>
                                <div class="stat-label">Total Facilities</div>
                            </div>
                        </div>
                        <div class="stat-card sc-purple">
                            <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
                            <div class="stat-body">
                                <div class="stat-num"><?= $avail_facilities ?></div>
                                <div class="stat-label">Available Now</div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick actions -->
                    <div class="section-label">Quick Actions</div>
                    <div class="action-grid">
                        <a href="manage_bookings.php" class="action-card ac-green">
                            <div class="ac-icon"><i class="bi bi-calendar-check"></i></div>
                            <div class="ac-body">
                                <h3>Manage Bookings</h3>
                                <p>Approve or reject customer booking requests</p>
                            </div>
                            <?php if($pending_bookings>0):?>
                                <span class="ac-badge"><?=$pending_bookings?> pending</span>
                            <?php endif;?>
                            <i class="bi bi-arrow-up-right ac-arrow"></i>
                        </a>
                        <a href="manage_payments.php" class="action-card ac-blue">
                            <div class="ac-icon"><i class="bi bi-credit-card"></i></div>
                            <div class="ac-body">
                                <h3>Verify Payments</h3>
                                <p>Review and confirm customer payment submissions</p>
                            </div>
                            <?php if($pending_payments>0):?>
                                <span class="ac-badge ac-badge-gold"><?=$pending_payments?> to verify</span>
                            <?php endif;?>
                            <i class="bi bi-arrow-up-right ac-arrow"></i>
                        </a>
                        <a href="manage_facilities.php" class="action-card ac-gold">
                            <div class="ac-icon"><i class="bi bi-building"></i></div>
                            <div class="ac-body">
                                <h3>Manage Facilities</h3>
                                <p>Update venue availability and status</p>
                            </div>
                            <i class="bi bi-arrow-up-right ac-arrow"></i>
                        </a>
                    </div>

                    <!-- Recent bookings -->
                    <div class="section-label" style="margin-top:28px">Recent Bookings</div>
                    <div class="recent-card">
                        <div class="recent-header">
                            <span>Latest 5 bookings</span>
                            <a href="manage_bookings.php" class="view-all-link">View all <i class="bi bi-arrow-right"></i></a>
                        </div>
                        <div class="table-responsive">
                            <table class="staff-table">
                                <thead>
                                    <tr><th>Customer</th><th>Facility</th><th>Date</th><th>Time</th><th>Status</th></tr>
                                </thead>
                                <tbody>
                                    <?php while($r=$recent->fetch_assoc()):?>
                                    <tr>
                                        <td>
                                            <div class="mini-user">
                                                <div class="mini-av"><?=strtoupper(substr($r['customer'],0,2))?></div>
                                                <span><?=htmlspecialchars($r['customer'])?></span>
                                            </div>
                                        </td>
                                        <td class="facility-name"><?=htmlspecialchars($r['facility_name'])?></td>
                                        <td>
                                            <span class="meta-cell">
                                                <i class="bi bi-calendar3"></i>
                                                <?= date('M d, Y', strtotime($r['booking_date'])) ?>
                                            </span>
                                        </td>

                                        <td>
                                            <span class="meta-cell">
                                                <i class="bi bi-clock"></i>
                                                <?= htmlspecialchars(condenseTimeSlots($r['time_slot'])) ?>
                                            </span>
                                        </td>
                                        <td><span class="badge badge-<?=strtolower($r['booking_status'])?>"><?=$r['booking_status']?></span></td>
                                    </tr>
                                    <?php endwhile;?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </section>
            </main>
        </div>

        <script>
        
            const layout  = document.getElementById('staffLayout');
            const sidebar = document.getElementById('staffSidebar');
            const overlay = document.getElementById('sidebarOverlay');

            document.getElementById('toggleSidebar').addEventListener('click', () => {
                layout.classList.toggle('collapsed');
                localStorage.setItem('staffCollapsed', layout.classList.contains('collapsed'));
            });
            if (localStorage.getItem('staffCollapsed') === 'true') layout.classList.add('collapsed');

            document.getElementById('mobileMenuBtn').addEventListener('click', () => {
                sidebar.classList.add('mobile-open');
                overlay.classList.add('active');
            });
            function closeMobileSidebar() {
                sidebar.classList.remove('mobile-open');
                overlay.classList.remove('active');
            }
        </script>
    </body>
</html>