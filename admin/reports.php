<?php
session_start();
require_once "../auth/session_timeout.php";
require_once "../config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php"); exit();
}

$results          = null;
$report_type      = "";
$selected_facility = "";
$selected_status   = "";
$selected_method   = "";

$facilities_list = $conn->query("SELECT facility_id, facility_name FROM facilities ORDER BY facility_name");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $report_type       = $_POST['report_type']     ?? '';
    $start_date        = $_POST['start_date']       ?? '';
    $end_date          = $_POST['end_date']         ?? '';
    $selected_facility = $_POST['facility_id']      ?? '';
    $selected_status   = $_POST['status_filter']    ?? '';
    $selected_method   = $_POST['method_filter']    ?? '';

    // Audit log
    $log = $conn->prepare("INSERT INTO reports (report_type, user_id) VALUES (?,?)");
    $log->bind_param("si", $report_type, $_SESSION['user_id']);
    $log->execute();

    if ($report_type === 'booking') {
        $q  = "SELECT b.booking_id, f.facility_name, b.booking_date, b.time_slot, b.total_hours, b.booking_status
               FROM bookings b JOIN facilities f ON b.facility_id=f.facility_id
               WHERE b.booking_date BETWEEN ? AND ?";
        $p  = [$start_date, $end_date]; $t = "ss";
        if ($selected_facility) { $q .= " AND b.facility_id=?"; $p[] = $selected_facility; $t .= "s"; }
        if ($selected_status)   { $q .= " AND b.booking_status=?"; $p[] = $selected_status; $t .= "s"; }
        $q .= " ORDER BY b.booking_date DESC";

    } elseif ($report_type === 'payment') {
        $q  = "SELECT p.payment_id, p.booking_id, p.amount, p.payment_method, p.payment_status,
                      p.payment_type, f.facility_name, p.payment_date
               FROM payments p
               JOIN bookings b ON p.booking_id=b.booking_id
               JOIN facilities f ON b.facility_id=f.facility_id
               WHERE p.payment_date BETWEEN ? AND ?";
        $p  = [$start_date, $end_date]; $t = "ss";
        if ($selected_facility) { $q .= " AND b.facility_id=?"; $p[] = $selected_facility; $t .= "s"; }
        if ($selected_status)   { $q .= " AND p.payment_status=?"; $p[] = $selected_status; $t .= "s"; }
        if ($selected_method)   { $q .= " AND p.payment_method=?"; $p[] = $selected_method; $t .= "s"; }
        $q .= " ORDER BY p.payment_date DESC";

    } elseif ($report_type === 'facility') {
        $q = "SELECT f.facility_name, f.facility_type, f.price_per_hour,
                     COUNT(b.booking_id) AS total_bookings,
                     COALESCE(SUM(CASE WHEN p.payment_status='Verified' THEN p.amount ELSE 0 END),0) AS revenue
              FROM facilities f
              LEFT JOIN bookings b ON f.facility_id=b.facility_id AND b.booking_date BETWEEN ? AND ?
              LEFT JOIN payments p ON b.booking_id=p.booking_id
              GROUP BY f.facility_id, f.facility_name, f.facility_type, f.price_per_hour
              ORDER BY total_bookings DESC";
        $p = [$start_date, $end_date]; $t = "ss";
    }

    if (isset($q)) {
        $stmt = $conn->prepare($q);
        $stmt->bind_param($t, ...$p);
        $stmt->execute();
        $results = $stmt->get_result();
    }
}

$page_title = 'Reports';

// Helper to condense multiple time slots into a single range

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
    <title>Reports – Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin/admin_base.css">
    <link rel="stylesheet" href="../assets/css/admin/reports.css">
</head>
<body>


<div class="admin-layout" id="adminLayout">
    <?php include "../includes/admin_sidebar.php"; ?>

    <main class="admin-content">
        <?php include "../includes/admin_header.php"; ?>

        <section class="content-area">

            <div class="orb orb-1"></div>
            <div class="orb orb-2"></div>

            <!-- Page heading -->
            <div class="page-top">
                <span class="page-label"><i class="bi bi-bar-chart-line-fill"></i> Analytics</span>
                <h1 class="page-title">Generate <span>Reports</span></h1>
                <p class="page-sub">Filter, analyse, and export data across bookings, payments, and facilities.</p>
            </div>

            <!-- Filter card -->
            <div class="filter-card">
                <form method="POST" id="reportForm">

                    <div class="filter-main-row">

                        <div class="filter-group">
                            <label><i class="bi bi-grid"></i> Report Type</label>
                            <select name="report_type" id="reportType" required onchange="toggleSubFilters()">
                                <option value="" disabled <?= empty($report_type) ? 'selected' : '' ?>>Select type…</option>
                                <option value="booking"  <?= $report_type === 'booking'  ? 'selected' : '' ?>>Booking Report</option>
                                <option value="payment"  <?= $report_type === 'payment'  ? 'selected' : '' ?>>Payment Report</option>
                                <option value="facility" <?= $report_type === 'facility' ? 'selected' : '' ?>>Facility Usage</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label><i class="bi bi-calendar-event"></i> Start Date</label>
                            <input type="date" name="start_date"
                                   value="<?= htmlspecialchars($_POST['start_date'] ?? '') ?>" required>
                        </div>

                        <div class="filter-group">
                            <label><i class="bi bi-calendar-check"></i> End Date</label>
                            <input type="date" name="end_date"
                                   value="<?= htmlspecialchars($_POST['end_date'] ?? '') ?>" required>
                        </div>

                        <button type="submit" class="btn-generate">
                            <i class="bi bi-play-fill"></i> Generate
                        </button>

                    </div>

                    <!-- Sub-filters (shown based on report type) -->
                    <div class="filter-sub-row" id="subFilters" style="display:none">

                        <div class="filter-group" id="facilityFilter">
                            <label><i class="bi bi-building"></i> Facility</label>
                            <select name="facility_id">
                                <option value="">All Facilities</option>
                                <?php
                                if ($facilities_list) {
                                    $facilities_list->data_seek(0);
                                    while ($f = $facilities_list->fetch_assoc()):
                                        $sel = ($selected_facility == $f['facility_id']) ? 'selected' : '';
                                ?>
                                    <option value="<?= $f['facility_id'] ?>" <?= $sel ?>><?= htmlspecialchars($f['facility_name']) ?></option>
                                <?php endwhile; } ?>
                            </select>
                        </div>

                        <div class="filter-group" id="statusFilter">
                            <label><i class="bi bi-toggle-on"></i> Status</label>
                            <select name="status_filter">
                                <option value="">All Statuses</option>
                                <option value="Pending"   <?= $selected_status === 'Pending'   ? 'selected' : '' ?>>Pending</option>
                                <option value="Approved"  <?= $selected_status === 'Approved'  ? 'selected' : '' ?>>Approved</option>
                                <option value="Confirmed"  <?= $selected_status === 'Confirmed'  ? 'selected' : '' ?>>Confirmed</option>
                                <option value="Cancelled" <?= $selected_status === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                <option value="Rejected"  <?= $selected_status === 'Rejected'  ? 'selected' : '' ?>>Rejected</option>
                            </select>
                        </div>

                        <div class="filter-group" id="methodFilter" style="display:none">
                            <label><i class="bi bi-credit-card"></i> Payment Method</label>
                            <select name="method_filter">
                                <option value="">All Methods</option>
                                <option value="Card"           <?= $selected_method === 'Card'           ? 'selected' : '' ?>>Card</option>
                                <option value="EVC Plus"       <?= $selected_method === 'EVC Plus'       ? 'selected' : '' ?>>EVC Plus</option>
                                <option value="Zaad"           <?= $selected_method === 'Zaad'           ? 'selected' : '' ?>>Zaad</option>
                                <option value="Sahal"          <?= $selected_method === 'Sahal'          ? 'selected' : '' ?>>Sahal</option>
                                <option value="E-Dahab"        <?= $selected_method === 'E-Dahab'        ? 'selected' : '' ?>>E-Dahab</option>
                                <option value="Premier Bank"   <?= $selected_method === 'Premier Bank'   ? 'selected' : '' ?>>Premier Bank</option>
                                <option value="Salaam Bank"    <?= $selected_method === 'Salaam Bank'    ? 'selected' : '' ?>>Salaam Bank</option>
                                <option value="Online Banking" <?= $selected_method === 'Online Banking' ? 'selected' : '' ?>>Online Banking</option>
                            </select>
                        </div>

                    </div>

                </form>
            </div>

            <!-- Results -->
            <?php if ($results && $results->num_rows > 0): ?>
            <div class="results-wrap">

                <div class="results-header">
                    <div class="results-left">
                        <h3>
                            <i class="bi bi-table"></i>
                            <?= ucfirst($report_type) ?> Report
                        </h3>
                        <span class="results-count"><?= $results->num_rows ?> records</span>
                    </div>
                    <form method="POST" action="print_report.php" target="_blank">
                        <input type="hidden" name="report_type"    value="<?= htmlspecialchars($report_type) ?>">
                        <input type="hidden" name="start_date"     value="<?= htmlspecialchars($_POST['start_date']) ?>">
                        <input type="hidden" name="end_date"       value="<?= htmlspecialchars($_POST['end_date']) ?>">
                        <input type="hidden" name="facility_id"    value="<?= htmlspecialchars($selected_facility) ?>">
                        <input type="hidden" name="status_filter"  value="<?= htmlspecialchars($selected_status) ?>">
                        <input type="hidden" name="method_filter"  value="<?= htmlspecialchars($selected_method) ?>">
                        <button type="submit" class="btn-print">
                            <i class="bi bi-printer"></i> Print / PDF
                        </button>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <?php if ($report_type === 'booking'): ?>
                                    <th>#</th><th>Facility</th><th>Date</th><th>Time Slot</th><th>Hours</th><th>Status</th>
                                <?php elseif ($report_type === 'payment'): ?>
                                    <th>Pay ID</th><th>Booking</th><th>Facility</th><th>Amount</th><th>Method</th><th>Type</th><th>Status</th>
                                <?php else: ?>
                                    <th>Facility</th><th>Type</th><th>Rate/hr</th><th>Total Bookings</th><th>Revenue</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $max_usage = 1;
                            if ($report_type === 'facility') {
                                $all_rows = $results->fetch_all(MYSQLI_ASSOC);
                                $max_usage = max(array_column($all_rows, 'total_bookings') ?: [1]);
                            } else {
                                $all_rows = [];
                                while ($r = $results->fetch_assoc()) $all_rows[] = $r;
                            }
                            foreach ($all_rows as $row):
                            ?>
                            <tr>
                                <?php if ($report_type === 'booking'): ?>
                                    <td><span class="id-cell">#<?= $row['booking_id'] ?></span></td>
                                    <td><span class="facility-name"><?= htmlspecialchars($row['facility_name']) ?></span></td>
                                    <td><span class="meta-cell"><i class="bi bi-calendar3"></i><?= date('M d, Y', strtotime($row['booking_date'])) ?></span></td>
                                    <td><span class="meta-cell"><i class="bi bi-clock"></i><?= htmlspecialchars(condenseTimeSlots($row['time_slot'])) ?></span></td>
                                    <td><span class="hours-cell"><?= $row['total_hours'] ?? 1 ?> hr<?= ($row['total_hours'] ?? 1) > 1 ? 's' : '' ?></span></td>
                                    <td><span class="badge badge-<?= strtolower($row['booking_status']) ?>"><?= $row['booking_status'] ?></span></td>

                                <?php elseif ($report_type === 'payment'): ?>
                                    <td><span class="id-cell">#<?= $row['payment_id'] ?></span></td>
                                    <td><span class="id-cell">#<?= $row['booking_id'] ?></span></td>
                                    <td><span class="facility-name"><?= htmlspecialchars($row['facility_name']) ?></span></td>
                                    <td><span class="amount-cell">$ <?= number_format($row['amount'], 2) ?></span></td>
                                    <td><span class="method-tag"><?= htmlspecialchars($row['payment_method']) ?></span></td>
                                    <td><span class="type-tag"><?= ucfirst($row['payment_type'] ?? 'deposit') ?></span></td>
                                    <td><span class="badge badge-<?= strtolower($row['payment_status']) ?>"><?= $row['payment_status'] ?></span></td>

                                <?php else: ?>
                                    <td><span class="facility-name"><?= htmlspecialchars($row['facility_name']) ?></span></td>
                                    <td><span class="type-tag"><?= htmlspecialchars($row['facility_type']) ?></span></td>
                                    <td><span class="rate-cell">$ <?= number_format($row['price_per_hour'], 2) ?></span></td>
                                    <td>
                                        <div class="usage-wrap">
                                            <div class="usage-bg">
                                                <div class="usage-fill" style="width:<?= $max_usage > 0 ? round(($row['total_bookings']/$max_usage)*100) : 0 ?>%"></div>
                                            </div>
                                            <span><?= $row['total_bookings'] ?></span>
                                        </div>
                                    </td>
                                    <td><span class="amount-cell">$ <?= number_format($row['revenue'], 2) ?></span></td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <div class="empty-state">
                <i class="bi bi-search"></i>
                <h3>No records found</h3>
                <p>No data matches the selected filters and date range.</p>
            </div>
            <?php endif; ?>

        </section>
    </main>
</div>

<script>
const layout = document.getElementById('adminLayout');
document.getElementById('toggleSidebar')?.addEventListener('click', () => {
    layout.classList.toggle('collapsed');
    localStorage.setItem('sidebarCollapsed', layout.classList.contains('collapsed'));
});
if (localStorage.getItem('sidebarCollapsed') === 'true') layout.classList.add('collapsed');

function toggleSubFilters() {
    const type        = document.getElementById('reportType').value;
    const sub         = document.getElementById('subFilters');
    const facFilter   = document.getElementById('facilityFilter');
    const statFilter  = document.getElementById('statusFilter');
    const methFilter  = document.getElementById('methodFilter');

    if (type === 'facility') {
        sub.style.display = 'none';
    } else {
        sub.style.display = 'grid';
        facFilter.style.display  = 'block';
        statFilter.style.display = 'block';
        methFilter.style.display = type === 'payment' ? 'block' : 'none';
    }
}
document.addEventListener('DOMContentLoaded', toggleSubFilters);
</script>
</body>
</html>