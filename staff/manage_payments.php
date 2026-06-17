<?php
session_start();
require_once "../auth/session_timeout.php";
require_once "../config/db.php";

// Check admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'staff') {
    header("Location: ../auth/login.php");
    exit();
}

// HANDLE APPROVE / REJECT
if (isset($_GET['action']) && isset($_GET['payment_id'])) {

    $payment_id = $_GET['payment_id'];
    $action = $_GET['action'];

    if ($action == 'approve') {
        $status = "Verified";

          $conn->query("
        UPDATE bookings b
        JOIN payments p ON b.booking_id = p.booking_id
        SET b.booking_status = 'Confirmed'
        WHERE p.payment_id = $payment_id
    ");
    
    } elseif ($action == 'reject') {
        $status = "Rejected";
    }

    $stmt = $conn->prepare("UPDATE payments SET payment_status = ? WHERE payment_id = ?");
    $stmt->bind_param("si", $status, $payment_id);
    $stmt->execute();

    $info = $conn->prepare("
    SELECT b.user_id, f.facility_name, b.booking_date, b.time_slot
    FROM payments p
    JOIN bookings b ON p.booking_id = b.booking_id
    JOIN facilities f ON b.facility_id = f.facility_id
    WHERE p.payment_id = ?
");
    $info->bind_param("i", $payment_id);
    $info->execute();
    $info_row = $info->get_result()->fetch_assoc();
    
    if ($info_row) {
        if ($action == 'approve') {
        $notif_msg = "✅ Your payment for {$info_row['facility_name']} on {$info_row['booking_date']} ({$info_row['time_slot']}) has been verified. Your booking is confirmed!";
        $notif = $conn->prepare("INSERT INTO notifications (user_id, message, status, notification_date) VALUES (?, ?, 'unread', NOW())");
        $notif->bind_param("is", $info_row['user_id'], $notif_msg);
        $notif->execute();
    }
    
    elseif ($action == 'reject') {
        $notif_msg = "❌ Your payment for {$info_row['facility_name']} on {$info_row['booking_date']} ({$info_row['time_slot']}) has been rejected. Please contact support for assistance.";
        $notif = $conn->prepare("INSERT INTO notifications (user_id, message, status, notification_date) VALUES (?, ?, 'unread', NOW())");
        $notif->bind_param("is", $info_row['user_id'], $notif_msg);
        $notif->execute();
    }
    }
    header("Location: manage_payments.php");
    exit();
}

// FETCH PAYMENTS
$sql = "
SELECT 
    p.payment_id,
    p.amount,
    p.payment_method,
    p.payment_status,
    p.payment_date,
    p.reference_code,
    b.booking_date,
    b.time_slot,
    f.facility_name,
    u.name AS customer_name
FROM payments p
JOIN bookings b ON p.booking_id = b.booking_id
JOIN facilities f ON b.facility_id = f.facility_id
JOIN users u ON b.user_id = u.user_id
ORDER BY p.payment_date DESC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Manage Payments – Staff</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
        <link rel="stylesheet" href="../assets/css/staff.css">
        
    </head>
    <body>
        <?php $page_title = 'Manage Payments'; ?>

        <div class="staff-layout" id="staffLayout">
            <?php include "../includes/staff_sidebar.php"; ?>

            <main class="staff-main">
                <?php include "../includes/staff_header.php"; ?>
                <section class="content-area">

                    <div class="page-top">
                        <div>
                            <span class="page-label">Transactions</span>
                            <h1 class="page-title">Manage <span>Payments</span></h1>
                            <p class="page-sub">Verify and manage customer payment submissions.</p>
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
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Reference</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
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
                                        <td data-label="Amount">
                                            <span class="amount-cell">
                                                $ <?= number_format($row['amount'], 2) ?>
                                            </span>
                                        </td>
                                        <td data-label="Method">
                                            <span class="method-tag"><?= htmlspecialchars($row['payment_method']) ?></span>
                                        </td>
                                        <td data-label="Reference">
                                            <span class="ref-code"><?= htmlspecialchars($row['reference_code']) ?></span>
                                        </td>
                                        <td data-label="Status">
                                            <span class="badge badge-<?= strtolower($row['payment_status']) ?>">
                                                <?= htmlspecialchars($row['payment_status']) ?>
                                            </span>
                                        </td>
                                        <td data-label="Action">
                                            <?php if ($row['payment_status'] === 'Pending'): ?>
                                                <div class="action-btns">
                                                    <a href="?action=approve&payment_id=<?= $row['payment_id'] ?>"
                                                    class="btn-action btn-approve">
                                                        <i class="bi bi-check-lg"></i> Approve
                                                    </a>
                                                    <a href="?action=reject&payment_id=<?= $row['payment_id'] ?>"
                                                    onclick="return confirm('Reject this payment?')"
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