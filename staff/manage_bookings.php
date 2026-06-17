<?php
session_start();
require_once "../auth/session_timeout.php";
require_once "../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'staff') {
    header("Location: ../auth/login.php");
    exit();
}

$result = mysqli_query($conn, "
    SELECT 
    b.booking_id,
    b.booking_date,
    b.time_slot,
    b.booking_status,
    u.name AS customer_name,
    f.facility_name
FROM bookings b
JOIN users u ON b.user_id = u.user_id
JOIN facilities f ON b.facility_id = f.facility_id
ORDER BY b.created_at DESC
");

// helper function to condense multiple time slots into a single range 
function condenseTimeSlots($time_slot_string) {
    if (empty($time_slot_string)) return '—';
    
    // Split the slots by comma and clean up whitespace
    $slots = array_map('trim', explode(',', $time_slot_string));
    
    if (count($slots) <= 1) {
        return $time_slot_string; // No need to condense if it's just 1 hour
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
        <title>Manage Bookings – Staff</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
        <link rel="stylesheet" href="../assets/css/staff.css">
    </head>
    <body>
        <?php $page_title = 'Manage Bookings'; ?>

        <div class="staff-layout" id="staffLayout">
            <?php include "../includes/staff_sidebar.php"; ?>

            <main class="staff-main">
                <?php include "../includes/staff_header.php"; ?>
                <section class="content-area">

                    <div class="page-top">
                        <div>
                            <span class="page-label">Reservations</span>
                            <h1 class="page-title">Manage <span>Bookings</span></h1>
                            <p class="page-sub">Approve or reject customer booking requests.</p>
                        </div>
                    </div>

                    <div class="table-wrap">
                        <div class="table-responsive">
                            <table class="staff-table">
                                <thead>
                                    <tr>
                                        <th>Customer</th>
                                        <th>Facility</th>
                                        <th>Date</th>
                                        <th>Time Slot</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result->num_rows > 0): ?>
                                        <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td data-label="Customer">
                                                <div class="customer-cell">
                                                    <span><?= htmlspecialchars($row['customer_name']) ?></span>
                                                </div>
                                            </td>
                                            <td data-label="Facility">
                                                <span class="facility-name"><?= htmlspecialchars($row['facility_name']) ?></span>
                                            </td>
                                            <td data-label="Date">
                                                <span class="meta-cell">
                                                    <i class="bi bi-calendar3"></i>
                                                    <?= date('M d, Y', strtotime($row['booking_date'])) ?>
                                                </span>
                                            </td>
                                            <td data-label="Time">
                                                <span class="meta-cell">
                                                    <i class="bi bi-clock"></i>
                                                    <?= condenseTimeSlots($row['time_slot']) ?>
                                                </span>
                                            </td>
                                            <td data-label="Status">
                                                <span class="badge badge-<?= strtolower($row['booking_status']) ?>">
                                                    <?= htmlspecialchars($row['booking_status']) ?>
                                                </span>
                                            </td>
                                            <td data-label="Action">
                                                <?php if ($row['booking_status'] === 'Pending'): ?>
                                                    <div class="action-btns">
                                                        <a href="update_booking.php?id=<?= $row['booking_id'] ?>&status=Approved"
                                                        class="btn-action btn-approve">
                                                            <i class="bi bi-check-lg"></i> Approve
                                                        </a>
                                                        <a href="update_booking.php?id=<?= $row['booking_id'] ?>&status=Rejected"
                                                        onclick="return confirm('Reject this booking?')"
                                                        class="btn-action btn-reject">
                                                            <i class="bi bi-x-lg"></i> Reject
                                                        </a>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="no-action">—</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6">
                                                <div class="empty-state">
                                                    <i class="bi bi-calendar-x"></i>
                                                    <h3>No booking requests</h3>
                                                    <p>New bookings will appear here.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
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