<?php
session_start();
require_once "../auth/session_timeout.php";
require_once "../config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "
SELECT
    b.booking_id,
    b.booking_date,
    b.time_slot,
    b.booking_status,
    b.total_hours,
    b.total_price,
    f.facility_name,
    f.price_per_hour,
    COALESCE(SUM(CASE WHEN p.payment_status = 'Verified' THEN p.amount ELSE 0 END), 0) AS paid_amount,
    MAX(CASE WHEN p.payment_status = 'Pending' THEN 1 ELSE 0 END) AS has_pending,
    CASE
        WHEN SUM(CASE WHEN p.payment_status = 'Verified' THEN p.amount ELSE 0 END) >= b.total_price THEN 'Fully Paid'
        WHEN SUM(CASE WHEN p.payment_status = 'Verified' THEN p.amount ELSE 0 END) > 0 THEN 'Partial'
        WHEN MAX(CASE WHEN p.payment_status = 'Pending' THEN 1 ELSE 0 END) = 1 THEN 'Pending Verification'
        ELSE 'Unpaid'
    END AS payment_label
FROM bookings b
JOIN facilities f ON b.facility_id = f.facility_id
LEFT JOIN payments p ON b.booking_id = p.booking_id
WHERE b.user_id = ?
GROUP BY b.booking_id, b.booking_date, b.time_slot, b.booking_status,
         b.total_hours, b.total_price, f.facility_name, f.price_per_hour
ORDER BY b.booking_id DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

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
        <title>Booking History - Beerta Daarusalaam</title>
        <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
        <link rel="stylesheet" href="../assets/css/customer_header.css">
        <link rel="stylesheet" href="../assets/css/customer_sidebar.css">
        <link rel="stylesheet" href="../assets/css/booking_history.css">
    </head>
    <body class="customer-page customer-sidebar-collapsed">
        <?php include "../includes/customer_header.php"; ?>
        <?php include "../includes/customer_sidebar.php"; ?>

        <main class="page">

            <div class="page-header">
                <div class="page-icon"><i class="bi bi-clock-history"></i></div>
                <div>
                    <span class="page-label">Your Activity</span>
                    <h1 class="page-title">Booking <span>History</span></h1>
                </div>
            </div>

            <div class="table-wrap">
                <div class="table-responsive">
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th>Facility</th>
                                <th>Date</th>
                                <th>Time Slot</th>
                                <th>Hours</th>
                                <th>Total</th>
                                <th>Booking</th>
                                <th>Payment</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()):
                                $total = (float)($row['total_price'] ?: $row['price_per_hour']);
                                $paid = (float)$row['paid_amount'];
                                $remaining = max(0, $total - $paid);
                                $pct = $total > 0 ? min(100, round(($paid / $total) * 100)) : 0;
                                $pay_label = $row['payment_label'];
                                $booking_status = $row['booking_status'];
                                $status_class = strtolower(str_replace(' ', '-', $booking_status));
                                $can_pay = $booking_status === 'Approved'
                                    && $remaining > 0
                                    && (int)$row['has_pending'] === 0;
                            ?>
                            <tr>
                                <td data-label="Facility">
                                    <span class="facility-name"><?= htmlspecialchars($row['facility_name']) ?></span>
                                </td>
                                <td data-label="Date">
                                    <span class="date-cell">
                                        <i class="bi bi-calendar3"></i>
                                        <?= date('M d, Y', strtotime($row['booking_date'])) ?>
                                    </span>
                                </td>
                                <td data-label="Time Slot">
                                    <span class="time-cell" title="<?= htmlspecialchars($row['time_slot']) ?>">
                                        <i class="bi bi-clock"></i>
                                        <?= htmlspecialchars(condenseTimeSlots($row['time_slot'])) ?>
                                    </span>
                                </td>
                                <td data-label="Hours">
                                    <span class="hours-cell">
                                        <?= $row['total_hours'] ?? 1 ?> hr<?= ($row['total_hours'] ?? 1) > 1 ? 's' : '' ?>
                                    </span>
                                </td>
                                <td data-label="Total">
                                    <span class="amount-cell">$ <?= number_format($total, 2) ?></span>
                                </td>
                                <td data-label="Booking">
                                    <span class="badge badge-<?= htmlspecialchars($status_class) ?>">
                                        <?= htmlspecialchars($booking_status) ?>
                                    </span>
                                </td>
                                <td data-label="Payment">
                                    <?php
                                    $pay_class = match($pay_label) {
                                        'Fully Paid' => 'pay-status-full',
                                        'Partial' => 'pay-status-partial',
                                        'Pending Verification' => 'pay-status-pending',
                                        default => 'pay-status-unpaid',
                                    };
                                    ?>
                                    <div class="pay-status-wrap <?= $pay_class ?>">
                                        <div class="pay-status-label">
                                            <?php if ($pay_label === 'Fully Paid'): ?>
                                                <i class="bi bi-patch-check-fill"></i> Fully Paid
                                            <?php elseif ($pay_label === 'Partial'): ?>
                                                <i class="bi bi-hourglass-split"></i> Partial (<?= $pct ?>%)
                                            <?php elseif ($pay_label === 'Pending Verification'): ?>
                                                <i class="bi bi-clock-fill"></i> Verifying
                                            <?php else: ?>
                                                <i class="bi bi-x-circle-fill"></i> Unpaid
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($pay_label === 'Partial'): ?>
                                            <div class="pay-progress-bar" aria-hidden="true">
                                                <div class="pay-progress-fill" style="width: <?= $pct ?>%"></div>
                                            </div>
                                            <div class="pay-remaining">
                                                $ <?= number_format($remaining, 2) ?> remaining
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td data-label="Action">
                                    <div class="action-col">

                                        <?php if ($booking_status === 'Pending'): ?>

                                            <a href="cancel_booking.php?id=<?= $row['booking_id'] ?>"
                                            class="btn-cancel"
                                            onclick="return confirm('Cancel this booking?')">
                                                <i class="bi bi-x-circle"></i> Cancel
                                            </a>

                                        <?php elseif ($pay_label === 'Fully Paid'): ?>

                                            <span class="status-chip chip-done">
                                                <i class="bi bi-check2-circle"></i> Done
                                            </span>

                                        <?php elseif ($row['has_pending']): ?>

                                            <span class="status-chip chip-pending">
                                                <i class="bi bi-clock"></i> Under Review
                                            </span>

                                        <?php elseif ($pay_label === 'Partial' && $remaining > 0): ?>

                                            <a href="pay.php?booking_id=<?= $row['booking_id'] ?>"
                                            class="btn-pay-now btn-pay-remaining">
                                                <i class="bi bi-credit-card"></i>
                                                Complete Payment
                                            </a>

                                        <?php elseif ($can_pay): ?>

                                            <a href="pay.php?booking_id=<?= $row['booking_id'] ?>"
                                            class="btn-pay-now">
                                                <i class="bi bi-credit-card"></i>
                                                Pay Now
                                            </a>

                                        <?php else: ?>

                                            <span class="no-action">-</span>

                                        <?php endif; ?>

                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8">
                                    <div class="empty-state">
                                        <i class="bi bi-calendar-x"></i>
                                        <h3>No bookings yet</h3>
                                        <p>Your booking history will appear here.</p>
                                        <a href="view_facilities.php" class="btn-browse">
                                            <i class="bi bi-search"></i> Browse Facilities
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
        <script>
            // Sidebar
            document.addEventListener('DOMContentLoaded', () => {
            const toggle = document.getElementById('toggleCustomerSidebar');

            if (!toggle) return;

            toggle.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                document.body.classList.toggle('customer-sidebar-open');
                } else {
                document.body.classList.toggle('customer-sidebar-collapsed');
                }
            });
            });
        </script>
    </body>
</html>
<?php include "../includes/footer.php"; ?>