<?php
session_start();
require_once "../auth/session_timeout.php";
require_once "../config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* =========================
   TOTAL BOOKINGS
========================= */

$totalQuery = "SELECT COUNT(*) as total FROM bookings WHERE user_id = ?";
$stmt = $conn->prepare($totalQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$totalResult = $stmt->get_result()->fetch_assoc();
$totalBookings = $totalResult['total'];

/* =========================
   CANCELED SESSIONS
========================= */

$cancelQuery = "
    SELECT COUNT(*) AS totalCanceled
    FROM bookings
    WHERE user_id = ?
    AND booking_status = 'Cancelled'
";

$stmt = $conn->prepare($cancelQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$cancelResult = $stmt->get_result();
$cancelData = $cancelResult->fetch_assoc();

$canceledSessions = $cancelData['totalCanceled'];

/* =========================
   PAYMENTS MADE
========================= */

$paymentQuery = "
    SELECT COUNT(*) AS totalPayments
    FROM payments p
    JOIN bookings b ON p.booking_id = b.booking_id
    WHERE b.user_id = ?
";

$stmt2 = $conn->prepare($paymentQuery);

if (!$stmt2) {
    die("Payment Query Error: " . $conn->error);
}

$stmt2->bind_param("i", $user_id);
$stmt2->execute();

$paymentResult = $stmt2->get_result();
$paymentData = $paymentResult->fetch_assoc();

$paymentsMade = $paymentData['totalPayments'];

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Dashboard – Beerta Daarusalaam</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
        <link rel="stylesheet" href="../assets/css/dashboard.css">
        <link rel="stylesheet" href="../assets/css/customer_sidebar.css">
    </head>
    <body class="customer-page customer-sidebar-collapsed">
        <?php include "../includes/customer_header.php"; ?>
        <?php include "../includes/customer_sidebar.php"; ?>

            <!-- MAIN -->
            <main class="main">
    
                <!-- Welcome -->
                <div class="welcome-banner">
                    <div class="welcome-label">&#9889; Your Dashboard</div>
                    <div class="welcome-title">Welcome back,<br><span><?php echo $_SESSION['name']; ?></span></div>
                    <div class="welcome-sub">Ready to book your next session? Let's go.</div>
                </div>

                <!-- Stats -->
                <div class="stats-row">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="bi bi-calendar2-check"></i></div>
                        <div>
                            <div class="stat-num"><?php echo $totalBookings; ?></div>
                            <div class="stat-label">Total Bookings</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="bi bi-clock-history"></i></div>
                        <div>
                            <div class="stat-num"><?php echo $canceledSessions; ?></div>
                            <div class="stat-label">Canceled Sessions</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="bi bi-credit-card"></i></div>
                        <div>
                            <div class="stat-num"><?php echo $paymentsMade; ?></div>
                            <div class="stat-label">Payments Made</div>
                        </div>
                    </div>
                </div>

                <!-- Nav Cards -->
                <div class="cards-title">Quick Actions</div>
                <div class="nav-grid">

                    <a href="../customer/view_facilities.php" class="nav-card">
                    <div class="card-icon"><i class="bi bi-building"></i></div>
                    <div class="card-text">
                        <h3>View Facilities</h3>
                        <p>Browse courts, fields & pools with live availability</p>
                    </div>
                    <i class="bi bi-arrow-up-right card-arrow"></i>
                    </a>

                    <a href="../customer/booking.php" class="nav-card">
                        <div class="card-icon"><i class="bi bi-calendar-plus"></i></div>
                        <div class="card-text">
                            <h3>Make a Booking</h3>
                            <p>Pick your slot and reserve it in seconds</p>
                        </div>
                        <i class="bi bi-arrow-up-right card-arrow"></i>
                    </a>

                    <a href="../customer/booking_history.php" class="nav-card">
                    <div class="card-icon"><i class="bi bi-receipt"></i></div>
                    <div class="card-text">
                        <h3>Booking History</h3>
                        <p>Review and manage all your past sessions</p>
                    </div>
                    <i class="bi bi-arrow-up-right card-arrow"></i>
                    </a>

                    <a href="../customer/manage_payment.php" class="nav-card">
                    <div class="card-icon"><i class="bi bi-wallet2"></i></div>
                    <div class="card-text">
                        <h3>Manage Payments</h3>
                        <p>Track deposits, status and transaction history</p>
                    </div>
                    <i class="bi bi-arrow-up-right card-arrow"></i>
                    </a>

                </div>
            </main>
        
        <!-- Chatbot -->
        <a class="chat-float" href="../chatbot/chatbot.php" title="Chat Support">💬</a>

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
