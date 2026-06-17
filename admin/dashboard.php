<?php
session_start();
require_once "../auth/session_timeout.php";
require_once "../config/db.php";

if (($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../auth/login.php"); exit();
}

// ── Stats ──────────────────────────────────────────────────
$totalFacilities  = $conn->query("SELECT COUNT(*) FROM facilities")->fetch_row()[0];
$totalBookings    = $conn->query("SELECT COUNT(*) FROM bookings")->fetch_row()[0];
$pendingBookings  = $conn->query("SELECT COUNT(*) FROM bookings WHERE booking_status='Pending'")->fetch_row()[0];
$totalStaff       = $conn->query("SELECT COUNT(*) FROM users WHERE role='staff'")->fetch_row()[0];
$totalCustomers   = $conn->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetch_row()[0];
$verifiedPayments = $conn->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE payment_status='Verified'")->fetch_row()[0];
$pendingPayments  = $conn->query("SELECT COUNT(*) FROM payments WHERE payment_status='Pending'")->fetch_row()[0];
$pendingStaff     = $conn->query("SELECT COUNT(*) FROM users WHERE role='staff' AND status='pending'")->fetch_row()[0];


// ── Monthly bookings (fixed 6 months) ──────────────────────

$months = [];
$counts = [];

// Default last 6 months
for ($i = 5; $i >= 0; $i--) {

    $monthKey = date('Y-m', strtotime("-$i months"));
    $monthLabel = date('M', strtotime("-$i months"));

    $months[$monthKey] = $monthLabel;
    $counts[$monthKey] = 0;
}

// Fetch real booking totals
$monthly = $conn->query("
    SELECT 
        DATE_FORMAT(booking_date, '%Y-%m') as month_key,
        COUNT(*) as total
    FROM bookings
    WHERE booking_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY month_key
    ORDER BY month_key
");

while ($r = $monthly->fetch_assoc()) {

    $counts[$r['month_key']] = (int)$r['total'];
}

// Final arrays for Chart.js
$chartMonths = array_values($months);
$chartCounts = array_values($counts);


// ── Booking status breakdown ───────────────────────────────
$statusData = $conn->query("
    SELECT booking_status, COUNT(*) as cnt FROM bookings GROUP BY booking_status
");
$statusLabels = []; $statusCounts = [];
while ($r = $statusData->fetch_assoc()) { $statusLabels[] = $r['booking_status']; $statusCounts[] = $r['cnt']; }

// ── Recent bookings ────────────────────────────────────────
$recentBookings = $conn->query("
    SELECT b.booking_id, u.name AS customer, f.facility_name, b.booking_date,
           b.booking_status, b.total_price
    FROM bookings b
    JOIN users u ON b.user_id = u.user_id
    JOIN facilities f ON b.facility_id = f.facility_id
    ORDER BY b.booking_id DESC LIMIT 5
");

// ── Pending staff applications ─────────────────────────────
$pendingStaffList = $conn->query("
    SELECT user_id, name, email, phone_number, created_at
    FROM users WHERE role='staff' AND status='pending'
    ORDER BY created_at DESC LIMIT 5
");
$page_title = 'Dashboard';
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Dashboard – Beerta Daarusalaam</title>
        <link rel="stylesheet" href="../assets/css/admin/admin_base.css">
        <link rel="stylesheet" href="../assets/css/admin/admin_dashboard.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    </head>
    <body>


        <div class="admin-layout" id="adminLayout">
            <?php include "../includes/admin_sidebar.php"; ?>

            <main class="admin-content">
                <?php include "../includes/admin_header.php"; ?>

                <section class="content-area">

                    <!-- Ambient orbs -->
                    <div class="orb orb-1"></div>
                    <div class="orb orb-2"></div>

                    <!-- Page heading -->
                    <div class="dash-heading">
                        <span class="dash-label"><i class="bi bi-lightning-charge-fill"></i> Control Center</span>
                        <h1 class="dash-title">Admin <span>Dashboard</span></h1>
                        <p class="dash-sub">Full operational overview of Beerta Daarusalaam Sports System.</p>
                    </div>

                    <!-- ── STAT CARDS ── -->
                    <div class="stat-grid">

                        <div class="stat-card" style="--delay:.05s">
                            <div class="stat-left">
                                <span class="stat-label">Total Bookings</span>
                                <div class="stat-num"><?= $totalBookings ?></div>
                                <span class="stat-sub"><i class="bi bi-arrow-up-short"></i><?= $pendingBookings ?> pending</span>
                            </div>
                            <div class="stat-icon si-green"><i class="bi bi-calendar-check"></i></div>
                        </div>

                        <div class="stat-card" style="--delay:.1s">
                            <div class="stat-left">
                                <span class="stat-label">Revenue (Verified)</span>
                                <div class="stat-num">$ <?= number_format($verifiedPayments, 0) ?></div>
                                <span class="stat-sub"><i class="bi bi-clock"></i> <?= $pendingPayments ?> awaiting</span>
                            </div>
                            <div class="stat-icon si-blue"><i class="bi bi-wallet2"></i></div>
                        </div>

                        <div class="stat-card" style="--delay:.15s">
                            <div class="stat-left">
                                <span class="stat-label">Facilities</span>
                                <div class="stat-num"><?= $totalFacilities ?></div>
                                <span class="stat-sub"><i class="bi bi-check-circle"></i> All venues</span>
                            </div>
                            <div class="stat-icon si-gold"><i class="bi bi-building"></i></div>
                        </div>

                        <div class="stat-card" style="--delay:.2s">
                            <div class="stat-left">
                                <span class="stat-label">Customers</span>
                                <div class="stat-num"><?= $totalCustomers ?></div>
                                <span class="stat-sub"><i class="bi bi-person-plus"></i> Registered</span>
                            </div>
                            <div class="stat-icon si-purple"><i class="bi bi-people"></i></div>
                        </div>

                        <div class="stat-card" style="--delay:.25s">
                            <div class="stat-left">
                                <span class="stat-label">Staff</span>
                                <div class="stat-num"><?= $totalStaff ?></div>
                                <?php if ($pendingStaff > 0): ?>
                                    <span class="stat-sub stat-sub-alert"><i class="bi bi-hourglass-split"></i> <?= $pendingStaff ?> pending approval</span>
                                <?php else: ?>
                                    <span class="stat-sub"><i class="bi bi-check-circle"></i> All approved</span>
                                <?php endif; ?>
                            </div>
                            <div class="stat-icon si-rose"><i class="bi bi-person-badge"></i></div>
                        </div>

                    </div>

                    <!-- ── CHARTS ROW ── -->
                    <div class="charts-row">

                        <div class="chart-card chart-main">
                            <div class="chart-header">
                                <div>
                                    <h3>Booking Trends</h3>
                                    <p>Last 6 months activity</p>
                                </div>
                                <span class="chart-badge">Monthly</span>
                            </div>
                            <div class="chart-body">
                                <canvas id="lineChart"></canvas>
                            </div>
                        </div>

                        <div class="chart-card chart-side">
                            <div class="chart-header">
                                <div>
                                    <h3>Booking Status</h3>
                                    <p>Current distribution</p>
                                </div>
                            </div>
                            <div class="chart-body donut-body">
                                <canvas id="donutChart"></canvas>
                            </div>
                        </div>

                    </div>

                    <!-- ── BOTTOM GRID: Recent Bookings + Pending Staff ── -->
                    <div class="bottom-grid">

                        <!-- Recent Bookings -->
                        <div class="dash-panel">
                            <div class="panel-header">
                                <div>
                                    <h3>Recent Bookings</h3>
                                    <p>Latest 5 bookings</p>
                                </div>
                            </div>
                            <div class="mini-table-wrap">
                                <table class="mini-table">
                                    <thead>
                                        <tr><th>Customer</th><th>Facility</th><th>Date</th><th>Status</th></tr>
                                    </thead>
                                    <tbody>
                                    <?php while ($r = $recentBookings->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <div class="mini-avatar-row">
                                                    <div class="mini-avatar"><?= strtoupper(substr($r['customer'],0,2)) ?></div>
                                                    <span><?= htmlspecialchars($r['customer']) ?></span>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($r['facility_name']) ?></td>
                                            <td><?= date('M d', strtotime($r['booking_date'])) ?></td>
                                            <td><span class="badge badge-<?= strtolower($r['booking_status']) ?>"><?= $r['booking_status'] ?></span></td>
                                        </tr>
                                    <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Pending Staff -->
                        <div class="dash-panel">
                            <div class="panel-header">
                                <div>
                                    <h3>Staff Applications</h3>
                                    <p>Awaiting your approval</p>
                                </div>
                                <a href="manage_staff.php" class="panel-link">
                                    View all <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                            <?php if ($pendingStaffList->num_rows > 0): ?>
                            <div class="staff-list">
                                <?php while ($s = $pendingStaffList->fetch_assoc()): ?>
                                <div class="staff-row">
                                    <div class="staff-avatar"><?= strtoupper(substr($s['name'],0,2)) ?></div>
                                    <div class="staff-info">
                                        <span class="staff-name"><?= htmlspecialchars($s['name']) ?></span>
                                        <span class="staff-email"><?= htmlspecialchars($s['email']) ?></span>
                                    </div>
                                    <div class="staff-actions">
                                        <a href="approve_staff.php?id=<?= $s['user_id'] ?>&action=approve"
                                        class="staff-btn btn-approve-s"
                                        onclick="return confirm('Approve <?= htmlspecialchars($s['name']) ?>?')">
                                            <i class="bi bi-check-lg"></i>
                                        </a>
                                        <a href="approve_staff.php?id=<?= $s['user_id'] ?>&action=reject"
                                        class="staff-btn btn-reject-s"
                                        onclick="return confirm('Reject this application?')">
                                            <i class="bi bi-x-lg"></i>
                                        </a>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                            <?php else: ?>
                            <div class="panel-empty">
                                <i class="bi bi-person-check"></i>
                                <span>No pending applications</span>
                            </div>
                            <?php endif; ?>
                        </div>

                    </div>

                    <!-- ── QUICK ACCESS CARDS ── -->
                    <div class="section-label-row">Quick Access</div>
                    <div class="admin-cards">
                        <a href="manage_facilities.php" class="admin-card">
                            <div class="ac-icon si-green"><i class="bi bi-building"></i></div>
                            <div class="ac-body">
                                <h4>Facilities</h4>
                                <p>Manage venues & availability</p>
                            </div>
                            <i class="bi bi-arrow-up-right ac-arrow"></i>
                        </a>
                        <a href="manage_staff.php" class="admin-card">
                            <div class="ac-icon si-purple"><i class="bi bi-people"></i></div>
                            <div class="ac-body">
                                <h4>Staff</h4>
                                <p>Accounts & approvals</p>
                            </div>
                            <i class="bi bi-arrow-up-right ac-arrow"></i>
                        </a>
                        <a href="reports.php" class="admin-card">
                            <div class="ac-icon si-rose"><i class="bi bi-bar-chart-line"></i></div>
                            <div class="ac-body">
                                <h4>Reports</h4>
                                <p>Generate & export data</p>
                            </div>
                            <i class="bi bi-arrow-up-right ac-arrow"></i>
                        </a>
                    </div>

                </section>
            </main>
        </div>

        <script>
            // Sidebar
            const layout = document.getElementById('adminLayout');
            document.getElementById('toggleSidebar')?.addEventListener('click', () => {
                layout.classList.toggle('collapsed');
                localStorage.setItem('sidebarCollapsed', layout.classList.contains('collapsed'));
            });
            if (localStorage.getItem('sidebarCollapsed') === 'true') layout.classList.add('collapsed');

            // ── Chart defaults ──
            Chart.defaults.color = '#6b7fa0';
            Chart.defaults.font.family = "'Outfit', sans-serif";

            // ── Line chart ──
            const months = <?= json_encode($chartMonths) ?>;
            const counts = <?= json_encode($chartCounts) ?>;
            const ctx1 = document.getElementById('lineChart').getContext('2d');
            const grad = ctx1.createLinearGradient(0,0,0,280);
            grad.addColorStop(0, 'rgba(0,229,160,.25)');
            grad.addColorStop(1, 'rgba(0,229,160,0)');

            new Chart(ctx1, {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'Bookings',
                        data: counts,
                        borderColor: '#00e5a0',
                        backgroundColor: grad,
                        borderWidth: 2.5,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#00e5a0',
                        pointRadius: 5,
                        pointBorderWidth: 2,
                        pointBorderColor: '#fff',
                        pointHoverRadius: 7
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false, aspectRatio: 2,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: '#6b7fa0' } },
                        y: { grid: { color: 'rgba(255,255,255,.04)' }, ticks: { color: '#6b7fa0', stepSize: 1 }, beginAtZero: true }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    animation: {
                        duration: 1800
                    }
                }
            });

            // ── Donut chart ──
            const sLabels = <?= json_encode($statusLabels ?: ['Pending','Approved','Cancelled']) ?>;
            const sCounts = <?= json_encode($statusCounts ?: [0,0,0]) ?>;
            const sColors = { 'Pending':'#f0c060','Approved':'#00e5a0','Confirmed':'#00e5a0','Cancelled':'#ff6b6b','Rejected':'#ff6b6b','Paid':'#0af' };
            const colors  = sLabels.map(l => sColors[l] || '#6b7fa0');

            new Chart(document.getElementById('donutChart'), {
                type: 'doughnut',
                data: {
                    labels: sLabels,
                    datasets: [{ data: sCounts, backgroundColor: colors, borderWidth: 3, borderColor: '#121b28', hoverOffset: 6 }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    cutout: '72%',
                    plugins: { legend: { position: 'bottom', labels: { color: '#6b7fa0', boxWidth: 10, padding: 16 } } }
                }
            });
        </script>
    </body>
</html>