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
    b.total_hours,
    b.total_price,
    f.facility_name,

    COALESCE(
        SUM(
            CASE
                WHEN p.payment_status = 'Verified'
                THEN p.amount
                ELSE 0
            END
        ),
        0
    ) AS total_paid,

    (
        SELECT p2.payment_status
        FROM payments p2
        WHERE p2.booking_id = b.booking_id
        ORDER BY p2.payment_id DESC
        LIMIT 1
    ) AS latest_status

FROM bookings b

JOIN facilities f 
    ON b.facility_id = f.facility_id

LEFT JOIN payments p 
    ON b.booking_id = p.booking_id

WHERE b.user_id = ?
AND (b.booking_status = 'Approved'
    OR b.booking_status = 'Confirmed'
)
GROUP BY b.booking_id

HAVING total_paid < b.total_price

ORDER BY b.booking_date DESC";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Prepare Error: " . $conn->error);
}

$stmt->bind_param("i", $user_id);

if (!$stmt->execute()) {
    die("Execute Error: " . $stmt->error);
}

$result = $stmt->get_result();

if (!$result) {
    die("Get Result Error: " . $stmt->error);
}

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
        <title>Manage Payments – Beerta Daarusalaam</title>
        <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
        <link rel="stylesheet" href="../assets/css/customer_header.css">
        <link rel="stylesheet" href="../assets/css/manage_payment.css">
        <link rel="stylesheet" href="../assets/css/customer_sidebar.css">
    </head>
    <body class="customer-page customer-sidebar-collapsed">
        <?php include "../includes/customer_header.php"; ?>
        <?php include "../includes/customer_sidebar.php"; ?>
        <div class="page">


            <!-- Page header -->
            <div class="page-header">
                <div class="page-icon"><i class="bi bi-credit-card"></i></div>
                <div>
                    <span class="page-label">Pending Payments</span>
                    <h1 class="page-title">Manage <span>Payments</span></h1>
                    <p class="page-sub">Pay for your approved bookings below.</p>
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
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <?php

                                        $total_paid = (float)$row['total_paid'];
                                        $total_price = (float)$row['total_price'];

                                        $remaining = $total_price - $total_paid;

                                        $is_first_payment = ($total_paid <= 0);

                                        $is_partial = (
                                            $total_paid > 0
                                            && $remaining > 0
                                        );

                                    ?>
                                <tr>
                                    <td data-label="Facility">
                                        <span class="facility-name"><?= htmlspecialchars($row['facility_name']) ?></span>
                                    </td>
                                    <td data-label="Date">
                                        <span class="meta-cell">
                                            <i class="bi bi-calendar3"></i>
                                            <?= date('M d, Y', strtotime($row['booking_date'])) ?>
                                        </span>
                                    </td>
                                    <td data-label="Time Slot">
                                        <span class="meta-cell">
                                            <i class="bi bi-clock"></i>
                                            <?= htmlspecialchars(condenseTimeSlots($row['time_slot'])) ?>
                                        </span>
                                    </td>
                                    <td data-label="Amount">

                                        <?php
                                        $display_amount = $is_first_payment
                                            ? ($row['total_price'] * 0.5)
                                            : $remaining;
                                        ?>

                                        <span class="amount-cell">
                                            $ <?= number_format($display_amount, 2) ?>
                                        </span>

                                    </td>
                                    <td data-label="Status">

                                        <?php if ($row['latest_status'] === 'Pending'): ?>

                                            <span class="badge badge-pending">
                                                Under Review
                                            </span>

                                        <?php elseif ($is_first_payment): ?>

                                            <span class="badge badge-unpaid">
                                                Awaiting Deposit
                                            </span>

                                        <?php elseif ($is_partial): ?>

                                            <span class="badge badge-partial">
                                                Deposit Paid
                                            </span>

                                        <?php endif; ?>

                                    </td>
                                    <td data-label="Action">

                                        <?php if ($row['latest_status'] === 'Pending'): ?>

                                            <span class="review-text">
                                                Waiting Approval
                                            </span>

                                        <?php elseif ($is_first_payment): ?>

                                            <a href="pay.php?booking_id=<?= $row['booking_id'] ?>" class="btn-pay">
                                                Pay Deposit
                                            </a>

                                        <?php elseif ($is_partial): ?>

                                            <a href="pay.php?booking_id=<?= $row['booking_id'] ?>" class="btn-pay">
                                                Complete Payment
                                            </a>

                                        <?php endif; ?>

                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6">
                                        <div class="empty-state">
                                            <i class="bi bi-check-circle"></i>
                                            <h3>All payments settled!</h3>
                                            <p>You have no pending payments at the moment.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

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