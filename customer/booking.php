<?php
session_start();
require_once "../auth/session_timeout.php";
require_once '../config/db.php';

$message = "";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php"); exit();
}

$user_id     = $_SESSION['user_id'];
$facility_id = intval($_GET['facility_id'] ?? 0);

if (!$facility_id) {
    header("Location: view_facilities.php"); exit();
}

// Fetch facility
$fq = $conn->prepare("SELECT * FROM facilities WHERE facility_id = ?");
$fq->bind_param("i", $facility_id);
$fq->execute();
$facility = $fq->get_result()->fetch_assoc();

if (!$facility) {
    header("Location: view_facilities.php"); exit();
}

// Work hours: 08:00 – 23:00 (each slot = 1 hr)
$work_hours = [
    '08:00','09:00','10:00','11:00','12:00','13:00',
    '14:00','15:00','16:00','17:00','18:00','19:00',
    '20:00','21:00','22:00'
];
// Labels shown as "08:00 – 09:00"
function nextHour($h) {
    $t = strtotime($h);
    return date('H:i', $t + 3600);
}

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book'])) {

    $booking_date   = trim($_POST['booking_date']   ?? '');
    $selected_slots = $_POST['slots'] ?? [];

    if (empty($booking_date)) {
        $message = "Please select a booking date.";
    } elseif (count($selected_slots) < 1) {
        $message = "Please select at least one time slot.";
    } else {
        // Validate slots are consecutive
        sort($selected_slots);

        // Check each slot for conflicts
        $conflict = false;
        foreach ($selected_slots as $slot) {

            $slot_label = $slot . ' - ' . nextHour($slot);

            // Search all bookings for same facility/date
            $chk = $conn->prepare("
                SELECT time_slot FROM bookings
                WHERE facility_id=? 
                AND booking_date=?
                AND booking_status NOT IN ('Cancelled','Rejected')
            ");

            $chk->bind_param("is", $facility_id, $booking_date);
            $chk->execute();

            $result = $chk->get_result();

            while ($row = $result->fetch_assoc()) {

                preg_match_all('/(\d{2}:\d{2})\s-\s\d{2}:\d{2}/', $row['time_slot'], $matches);

                if (in_array($slot, $matches[1])) {

                    $message = "Slot <strong>{$slot_label}</strong> is already booked.";

                    $conflict = true;
                    break 2;
                }
            }
        }

        if (!$conflict) {
            // Calculate totals
            $total_hours = count($selected_slots);
            $price_per_hour = $facility['price_per_hour'];
            $total_price    = $total_hours * $price_per_hour;

            // Build time_slot string: "08:00-09:00, 09:00-10:00" etc.
            $slot_labels = array_map(fn($s) => $s . ' - ' . nextHour($s), $selected_slots);
            $time_slot_str = implode(', ', $slot_labels);

            // Insert one booking record with combined slots
            $ins = $conn->prepare("
                INSERT INTO bookings (user_id, facility_id, booking_date, time_slot, total_hours, total_price)
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            // If total_hours / total_price columns don't exist yet, fall back gracefully
            if ($ins) {
                $ins->bind_param("iissid", $user_id, $facility_id, $booking_date, $time_slot_str, $total_hours, $total_price);
                $ins->execute();
            } else {
                // Fallback: old schema without total columns
                $ins2 = $conn->prepare("INSERT INTO bookings (user_id, facility_id, booking_date, time_slot) VALUES (?,?,?,?)");
                $ins2->bind_param("iiss", $user_id, $facility_id, $booking_date, $time_slot_str);
                $ins2->execute();
            }

            header("Location: booking_history.php"); exit();
        }
    }
}

$price_per_hour = $facility['price_per_hour'];

// Fetch already-booked slots for the selected date (AJAX)
if (isset($_GET['fetch_booked']) && isset($_GET['date'])) {

    header('Content-Type: application/json');

    $date = trim($_GET['date']);

    $bq = $conn->prepare("
        SELECT time_slot FROM bookings
        WHERE facility_id=? 
        AND booking_date=?
        AND booking_status NOT IN ('Cancelled','Rejected')
    ");

    $bq->bind_param("is", $facility_id, $date);
    $bq->execute();

    $rows = $bq->get_result()->fetch_all(MYSQLI_ASSOC);

    // Extract ONLY starting hours
    $booked = [];

    foreach ($rows as $r) {

        preg_match_all(
            '/(\d{2}:\d{2})\s-\s\d{2}:\d{2}/',
            $r['time_slot'],
            $matches
        );

        foreach ($matches[1] as $startHour) {
            $booked[] = trim($startHour);
        }
    }

    $booked = array_unique($booked);

    // RETURN JSON TO JS
    echo json_encode(array_values($booked));

    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Book Facility – Beerta Daarusalaam</title>
        <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
        <link rel="stylesheet" href="../assets/css/customer_header.css">
        <link rel="stylesheet" href="../assets/css/booking.css">
        <link rel="stylesheet" href="../assets/css/customer_sidebar.css">
    </head>
    <body class="customer-page customer-sidebar-collapsed">
        <?php include '../includes/customer_header.php'; ?>
        <?php include '../includes/customer_sidebar.php'; ?>

        <div class="page">

            <div class="page-header">
                <a href="view_facilities.php" class="btn-back">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
                <div>
                    <span class="page-label">Reserve a Slot</span>
                    <h1 class="page-title">Make a <span>Booking</span></h1>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert-error">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <div class="booking-layout">

                <!-- LEFT: Facility info -->
                <div class="facility-card">
                    <div class="facility-img">
                        <img src="<?= (!empty($facility['facility_image']) && file_exists('../assets/uploads/facilities/' . $facility['facility_image']))
                            ? '../assets/uploads/facilities/' . htmlspecialchars($facility['facility_image'])
                            : '../assets/default_facility.png' ?>"
                            alt="<?= htmlspecialchars($facility['facility_name']) ?>">
                        <div class="img-overlay"></div>
                        <span class="status-badge">● Available</span>
                    </div>
                    <div class="facility-info">
                        <div class="facility-type"><?= htmlspecialchars($facility['facility_type']) ?></div>
                        <h2 class="facility-name"><?= htmlspecialchars($facility['facility_name']) ?></h2>
                        <div class="facility-price">
                            <i class="bi bi-tag-fill"></i>
                            $ <?= number_format($price_per_hour, 2) ?> <span>/hour</span>
                        </div>
                        <div class="facility-meta">
                            <div class="meta-item"><i class="bi bi-clock"></i> Select 1–8 hours</div>
                            <div class="meta-item"><i class="bi bi-calendar-check"></i> Instant confirmation</div>
                            <div class="meta-item"><i class="bi bi-shield-check"></i> Secure payment</div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT: Booking form -->
                <div class="booking-form-card">
                    <div class="form-title">
                        <i class="bi bi-calendar-plus"></i> Select Date & Hours
                    </div>

                    <form method="POST" id="bookingForm">

                        <!-- Date picker -->
                        <div class="form-group">
                            <label><i class="bi bi-calendar3"></i> Booking Date</label>
                            <input type="date" name="booking_date" id="bookingDate"
                                min="<?= date('Y-m-d') ?>"
                                value="<?= htmlspecialchars($_POST['booking_date'] ?? '') ?>"
                                required>
                        </div>

                        <!-- Time slot grid -->
                        <div class="form-group">
                            <label><i class="bi bi-clock"></i> Select Time Slots
                                <span class="slots-hint">Click to select, click again to deselect</span>
                            </label>

                            <div class="slots-loading hidden" id="slotsLoading">
                                <i class="bi bi-arrow-repeat spin"></i> Checking availability…
                            </div>

                            <div class="slots-grid" id="slotsGrid">
                                <?php foreach ($work_hours as $h): ?>
                                    <button type="button"
                                            class="slot-btn"
                                            data-hour="<?= $h ?>"
                                            data-label="<?= $h ?> - <?= nextHour($h) ?>">
                                        <span class="slot-time"><?= $h ?></span>
                                        <span class="slot-end">– <?= nextHour($h) ?></span>
                                    </button>
                                <?php endforeach; ?>
                            </div>

                            <div class="slots-legend">
                                <span class="legend-item"><span class="legend-dot dot-free"></span> Available</span>
                                <span class="legend-item"><span class="legend-dot dot-selected"></span> Selected</span>
                                <span class="legend-item"><span class="legend-dot dot-booked"></span> Booked</span>
                            </div>

                            <!-- Hidden inputs populated by JS -->
                            <div id="hiddenSlots"></div>
                        </div>

                        <!-- Live price summary -->
                        <div class="price-summary" id="priceSummary">
                            <div class="price-row">
                                <span>Rate</span>
                                <span>$ <?= number_format($price_per_hour, 2) ?>/hr</span>
                            </div>
                            <div class="price-row">
                                <span>Selected hours</span>
                                <span id="summaryHours">0 hr</span>
                            </div>
                            <div class="price-row">
                                <span>Time</span>
                                <span id="summaryTime">—</span>
                            </div>
                            <div class="price-divider"></div>
                            <div class="price-row price-total">
                                <span>Total</span>
                                <span id="summaryTotal">$ 0.00</span>
                            </div>
                        </div>

                        <div class="selection-hint" id="selectionHint">
                            <i class="bi bi-info-circle"></i>
                            Select a date first, then pick your time slots.
                        </div>

                        <button type="submit" name="book" class="btn-confirm" id="btnConfirm" disabled>
                            <i class="bi bi-check-circle-fill"></i>
                            <span id="btnText">Select time slots to continue</span>
                        </button>

                    </form>
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
            const PRICE_PER_HOUR = <?= (float)$price_per_hour ?>;
            const FACILITY_ID    = <?= $facility_id ?>;
            let selectedSlots    = [];
            let bookedSlots      = [];

            const dateInput   = document.getElementById('bookingDate');
            const slotsGrid   = document.getElementById('slotsGrid');
            const hiddenSlots = document.getElementById('hiddenSlots');
            const btnConfirm  = document.getElementById('btnConfirm');
            const btnText     = document.getElementById('btnText');
            const hint        = document.getElementById('selectionHint');

            // ── Date change → fetch booked slots ──
            dateInput.addEventListener('change', function () {
                const date = this.value;
                if (!date) return;

                document.getElementById('slotsLoading').classList.remove('hidden');
                slotsGrid.style.opacity = '.4';
                selectedSlots = [];
                renderGrid([]);

                fetch(`booking.php?facility_id=${FACILITY_ID}&fetch_booked=1&date=${date}`)
                    .then(r => r.json())
                    .then(booked => {
                        bookedSlots = booked;
                        document.getElementById('slotsLoading').classList.add('hidden');
                        slotsGrid.style.opacity = '1';
                        renderGrid(booked);
                        updateSummary();
                        hint.style.display = 'none';
                    })
                    .catch(() => {
                        document.getElementById('slotsLoading').classList.add('hidden');
                        slotsGrid.style.opacity = '1';
                    });
            });

            // ── Render slot states ──
            function renderGrid(booked) {
                document.querySelectorAll('.slot-btn').forEach(btn => {
                    const hour = btn.dataset.hour;
                    btn.className = 'slot-btn';
                    if (booked.includes(hour)) {
                        btn.classList.add('booked');
                        btn.disabled = true;
                    } else if (selectedSlots.includes(hour)) {
                        btn.classList.add('selected');
                    }
                });
            }

            // ── Slot click ──
            slotsGrid.addEventListener('click', function (e) {
                const btn = e.target.closest('.slot-btn');
                if (!btn || btn.disabled || !dateInput.value) {
                    if (!dateInput.value) {
                        dateInput.focus();
                        dateInput.classList.add('shake');
                        setTimeout(() => dateInput.classList.remove('shake'), 500);
                    }
                    return;
                }

                const hour = btn.dataset.hour;
                if (selectedSlots.includes(hour)) {
                    selectedSlots = selectedSlots.filter(s => s !== hour);
                    btn.classList.remove('selected');
                } else {
                    selectedSlots.push(hour);
                    selectedSlots.sort();
                    btn.classList.add('selected');
                }
                updateSummary();
                updateHiddenInputs();
            });

            // ── Update price summary ──
            function updateSummary() {
                const hours = selectedSlots.length;
                const total = hours * PRICE_PER_HOUR;

                document.getElementById('summaryHours').textContent = hours + ' hr' + (hours !== 1 ? 's' : '');
                document.getElementById('summaryTotal').textContent  = '$ ' + total.toFixed(2);

                // Time range display
                if (hours > 0) {
                    const sorted = [...selectedSlots].sort();
                    const first  = sorted[0];
                    const last   = sorted[sorted.length - 1];
                    const endH   = last.split(':')[0];
                    const end    = (parseInt(endH) + 1).toString().padStart(2,'0') + ':00';
                    document.getElementById('summaryTime').textContent = first + ' – ' + end;
                } else {
                    document.getElementById('summaryTime').textContent = '—';
                }

                // Button state
                if (hours > 0) {
                    btnConfirm.disabled = false;
                    btnText.textContent = `Confirm ${hours} hr${hours>1?'s':''} · $ ${total.toFixed(2)}`;
                } else {
                    btnConfirm.disabled = true;
                    btnText.textContent = 'Select time slots to continue';
                }
            }

            // ── Populate hidden inputs for form submission ──
            function updateHiddenInputs() {
                hiddenSlots.innerHTML = '';
                selectedSlots.forEach(h => {
                    const inp = document.createElement('input');
                    inp.type  = 'hidden';
                    inp.name  = 'slots[]';
                    inp.value = h;
                    hiddenSlots.appendChild(inp);
                });
            }
        </script>
    </body>
</html>