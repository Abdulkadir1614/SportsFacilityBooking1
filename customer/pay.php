<?php
session_start();
require_once "../auth/session_timeout.php";
require_once "../config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php"); exit();
}

$user_id    = $_SESSION['user_id'];
$booking_id = intval($_GET['booking_id'] ?? 0);

// Fetch booking with payment summary
$stmt = $conn->prepare("
    SELECT 
        b.*,

        f.facility_name,
        f.facility_type,
        f.price_per_hour,

        (
            SELECT COALESCE(SUM(p1.amount),0)
            FROM payments p1
            WHERE p1.booking_id = b.booking_id
            AND p1.payment_status = 'Verified'
        ) AS paid_amount,

        (
            SELECT p2.payment_status
            FROM payments p2
            WHERE p2.booking_id = b.booking_id
            ORDER BY p2.payment_id DESC
            LIMIT 1
        ) AS latest_status,

        (
            SELECT p3.payment_type
            FROM payments p3
            WHERE p3.booking_id = b.booking_id
            ORDER BY p3.payment_id DESC
            LIMIT 1
        ) AS latest_type

    FROM bookings b

    JOIN facilities f 
        ON b.facility_id = f.facility_id

    WHERE b.booking_id = ?
    AND b.user_id = ?
    AND (b.booking_status = 'Confirmed'
        OR  b.booking_status = 'Approved'
    )
");
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();



if (!$booking) { header("Location: manage_payment.php"); exit(); }
if (
    $booking['latest_status'] === 'Pending'
    && $booking['latest_type'] === 'remaining'
) {
    header("Location: booking_history.php?msg=pending");
    exit();
}

$total     = (float)($booking['total_price'] ?: $booking['price_per_hour']);
$paid      = (float)$booking['paid_amount'];
$remaining = round($total - $paid, 2);

if ($remaining <= 0) { header("Location: booking_history.php?msg=paid"); exit(); }

// Is this the first payment (deposit) or remaining?
$is_deposit   = ($paid == 0);
$deposit_amt  = round($total * 0.5, 2);
$pay_amount   = $is_deposit ? $deposit_amt : $remaining;
$pay_type     = $is_deposit ? 'deposit' : 'remaining';
$pay_label    = $is_deposit ? '50% Deposit' : 'Remaining Balance';

$error = "";

// Handle submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay'])) {
    $method = trim($_POST['payment_method'] ?? '');

    // Validate per method
    if ($method === 'Card') {
        $card_num = preg_replace('/\s+/', '', $_POST['card_number'] ?? '');
        if (strlen($card_num) < 16)        $error = "Enter a valid 16-digit card number.";
        elseif (empty($_POST['card_holder'])) $error = "Card holder name is required.";
        elseif (!preg_match('/^\d{2}\s*\/\s*\d{2}$/', $_POST['card_expiry'] ?? '')) $error = "Enter expiry as MM / YY.";
        elseif (strlen($_POST['card_cvv'] ?? '') < 3) $error = "CVV must be 3–4 digits.";
    } elseif (in_array($method, ['EVC Plus','Zaad','Sahal','E-Dahab'])) {
        $phone = preg_replace('/\D/', '', $_POST['wallet_phone'] ?? '');
        if (strlen($phone) < 9) $error = "Enter a valid Somali phone number.";
    } elseif (in_array($method, ['Premier Bank','Salaam Bank','Amal Bank','IBS Bank'])) {
        if (empty($_POST['bank_account'])) $error = "Enter your account number.";
    } else {
        $error = "Please select a payment method.";
    }

    if (!$error) {
        $reference = "PAY-" . strtoupper(substr(md5(uniqid()), 0, 8));

        $ins = $conn->prepare("
            INSERT INTO payments (booking_id, amount, payment_method, payment_status, payment_type, payment_date, reference_code)
            VALUES (?, ?, ?, 'Pending', ?, NOW(), ?)
        ");
        $ins->bind_param("idsss", $booking_id, $pay_amount, $method, $pay_type, $reference);
        $ins->execute();

        // Notification
        $msg_txt = $is_deposit
            ? "✅ Deposit of $ {$pay_amount} submitted for {$booking['facility_name']}. Awaiting verification. Remaining: $ {$remaining}."
            : "✅ Final payment of $ {$pay_amount} submitted for {$booking['facility_name']}. Awaiting verification.";
        $notif = $conn->prepare("INSERT INTO notifications (user_id, message, notification_date) VALUES (?,?,NOW())");
        $notif->bind_param("is", $user_id, $msg_txt);
        $notif->execute();

        header("Location: booking_history.php?success=1"); exit();
    }
}

// helper to condense multiple time slots into a single range
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
        <title><?= $pay_label ?> – Beerta Daarusalaam</title>
        <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
        <link rel="stylesheet" href="../assets/css/customer_header.css">
        <link rel="stylesheet" href="../assets/css/pay.css">
    </head>
    <body>
        <?php include "../includes/customer_header.php"; ?>

        <div class="pay-page">
        <div class="pay-card">

            <!-- Header -->
            <div class="pay-card-header">
                <div class="pay-lock"><i class="bi bi-shield-fill-check"></i></div>
                <div>
                    <h2><?= $is_deposit ? 'Pay Deposit' : 'Complete Payment' ?></h2>
                    <p>Beerta Daarusalaam · SSL Encrypted</p>
                </div>
            </div>

            <!-- Payment breakdown banner -->
            <div class="pay-breakdown <?= $is_deposit ? 'breakdown-deposit' : 'breakdown-remaining' ?>">
                <?php if ($is_deposit): ?>
                    <div class="breakdown-row">
                        <span><i class="bi bi-circle-fill" style="color:var(--accent);font-size:8px"></i> Total booking cost</span>
                        <span>$ <?= number_format($total, 2) ?></span>
                    </div>
                    <div class="breakdown-row highlight">
                        <span><i class="bi bi-credit-card-fill"></i> <strong>Pay now (50% deposit)</strong></span>
                        <span><strong>$ <?= number_format($deposit_amt, 2) ?></strong></span>
                    </div>
                    <div class="breakdown-row muted-row">
                        <span><i class="bi bi-clock"></i> Remaining (pay at venue or later)</span>
                        <span>$ <?= number_format($total - $deposit_amt, 2) ?></span>
                    </div>
                <?php else: ?>
                    <div class="breakdown-row">
                        <span><i class="bi bi-check-circle-fill" style="color:var(--accent)"></i> Already paid</span>
                        <span>$ <?= number_format($paid, 2) ?></span>
                    </div>
                    <div class="breakdown-row">
                        <span>Total booking cost</span>
                        <span>$ <?= number_format($total, 2) ?></span>
                    </div>
                    <div class="breakdown-row highlight">
                        <span><i class="bi bi-credit-card-fill"></i> <strong>Remaining balance due</strong></span>
                        <span><strong>$ <?= number_format($remaining, 2) ?></strong></span>
                    </div>
                    <!-- Progress bar -->
                    <div style="margin-top:10px;">
                        <div style="display:flex;justify-content:space-between;font-size:11px;color:var(--muted);margin-bottom:5px;">
                            <span>Payment progress</span>
                            <span><?= round(($paid/$total)*100) ?>% paid</span>
                        </div>
                        <div class="pay-prog-bg">
                            <div class="pay-prog-fill" style="width:<?= round(($paid/$total)*100) ?>%"></div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Order summary -->
            <div class="pay-summary">
                <div class="pay-summary-row">
                    <span><i class="bi bi-building"></i> <?= htmlspecialchars($booking['facility_name']) ?></span>
                    <span class="pay-summary-badge"><?= htmlspecialchars($booking['facility_type'] ?? '') ?></span>
                </div>
                <div class="pay-summary-row">
                    <span><i class="bi bi-calendar3"></i> <?= date('M d, Y', strtotime($booking['booking_date'])) ?></span>
                    <span><i class="bi bi-clock"></i> <?= condenseTimeSlots($booking['time_slot']) ?></span>
                </div>
                <?php if ($booking['total_hours'] > 1): ?>
                <div class="pay-summary-row">
                    <span><i class="bi bi-hourglass"></i> Duration</span>
                    <span><?= $booking['total_hours'] ?> hours</span>
                </div>
                <?php endif; ?>
                <div class="pay-divider"></div>
                <div class="pay-summary-row pay-total-row">
                    <span><?= $pay_label ?></span>
                    <span class="pay-amount">$ <?= number_format($pay_amount, 2) ?></span>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="pay-alert"><i class="bi bi-exclamation-circle-fill"></i> <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <!-- Method tabs -->
            <div class="pay-tabs">
                <button class="pay-tab active" onclick="switchTab(this,'card')" type="button">
                    <i class="bi bi-credit-card"></i> Card
                </button>
                <button class="pay-tab" onclick="switchTab(this,'wallet')" type="button">
                    <i class="bi bi-phone-fill"></i> E-Wallet
                </button>
                <button class="pay-tab" onclick="switchTab(this,'bank')" type="button">
                    <i class="bi bi-bank2"></i> Bank
                </button>
            </div>

            <form method="POST" id="payForm">
                <input type="hidden" name="payment_method" id="payment_method_input" value="Card">

                <!-- CARD TAB -->
                <div class="pay-tab-content" id="tab-card">
                    <div class="mini-card" id="miniCard">
                        <div class="mini-card-top">
                            <div class="mini-chip"></div>
                            <span class="mini-brand"><i class="bi bi-credit-card-2-front"></i></span>
                        </div>
                        <div class="mini-card-num" id="miniCardNum">•••• •••• •••• ••••</div>
                        <div class="mini-card-bottom">
                            <div>
                                <div class="mini-label">Holder</div>
                                <div class="mini-val" id="miniHolder">YOUR NAME</div>
                            </div>
                            <div>
                                <div class="mini-label">Expires</div>
                                <div class="mini-val" id="miniExpiry">MM/YY</div>
                            </div>
                        </div>
                    </div>

                    <div class="pay-field">
                        <label>Card Number</label>
                        <div class="pay-input-wrap">
                            <i class="bi bi-credit-card"></i>
                            <input type="text" id="cardNumber" name="card_number"
                                placeholder="1234 1234 1234 1234" maxlength="19">
                            <div class="brand-icons">
                                <span id="visaIcon" class="brand-icon dim">VISA</span>
                                <span id="mcIcon"   class="brand-icon dim mc">MC</span>
                            </div>
                        </div>
                    </div>
                    <div class="pay-field">
                        <label>Cardholder Name</label>
                        <div class="pay-input-wrap">
                            <i class="bi bi-person"></i>
                            <input type="text" id="cardHolder" name="card_holder" placeholder="Full name on card">
                        </div>
                    </div>
                    <div class="pay-row">
                        <div class="pay-field">
                            <label>Expiry</label>
                            <div class="pay-input-wrap">
                                <i class="bi bi-calendar"></i>
                                <input type="text" id="cardExpiry" name="card_expiry" placeholder="MM / YY" maxlength="7">
                            </div>
                        </div>
                        <div class="pay-field">
                            <label>CVV</label>
                            <div class="pay-input-wrap">
                                <i class="bi bi-lock"></i>
                                <input type="password" id="cardCvv" name="card_cvv" placeholder="•••" maxlength="4">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- WALLET TAB -->
                <div class="pay-tab-content hidden" id="tab-wallet">
                    <p class="tab-desc">Select your Somali mobile wallet</p>
                    <div class="option-grid">
                        <?php foreach ([
                            ['EVC Plus','bi-phone-fill','#e60000','Hormuud Telecom'],
                            ['Zaad',    'bi-phone-fill','#009a44','Telesom'],
                            ['Sahal',   'bi-phone-fill','#f7941d','Golis Telecom'],
                            ['E-Dahab', 'bi-phone-fill','#8B4513','Somtel'],
                        ] as [$name,$icon,$color,$sub]): ?>
                        <label class="option-card">
                            <input type="radio" name="wallet_select" value="<?= $name ?>"
                                onchange="document.getElementById('payment_method_input').value='<?= $name ?>'">
                            <div class="option-body">
                                <div class="option-icon" style="color:<?= $color ?>;background:<?= $color ?>22;">
                                    <i class="<?= $icon ?>"></i>
                                </div>
                                <div>
                                    <div class="option-name"><?= $name ?></div>
                                    <div class="option-sub"><?= $sub ?></div>
                                </div>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <div class="pay-field" style="margin-top:14px;">
                        <label>Phone Number</label>
                        <div class="pay-input-wrap">
                            <span class="input-prefix-flag">🇸🇴 +252</span>
                            <input type="text" name="wallet_phone" placeholder="61X XXX XXX" maxlength="12">
                        </div>
                    </div>
                </div>

                <!-- BANK TAB -->
                <div class="pay-tab-content hidden" id="tab-bank">
                    <p class="tab-desc">Select your Somali bank</p>
                    <div class="option-grid">
                        <?php foreach ([
                            ['Premier Bank','bi-bank2','#1a3a6b',"Somalia's largest"],
                            ['Salaam Bank', 'bi-bank2','#006b3c','Islamic banking'],
                            ['Amal Bank',   'bi-bank2','#8b0000','Nationwide'],
                            ['IBS Bank',    'bi-bank2','#4b0082','International'],
                        ] as [$name,$icon,$color,$sub]): ?>
                        <label class="option-card">
                            <input type="radio" name="bank_select" value="<?= $name ?>"
                                onchange="document.getElementById('payment_method_input').value='<?= $name ?>'">
                            <div class="option-body">
                                <div class="option-icon" style="color:<?= $color ?>;background:<?= $color ?>22;">
                                    <i class="<?= $icon ?>"></i>
                                </div>
                                <div>
                                    <div class="option-name"><?= $name ?></div>
                                    <div class="option-sub"><?= $sub ?></div>
                                </div>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <div class="pay-field" style="margin-top:14px;">
                        <label>Account Number</label>
                        <div class="pay-input-wrap">
                            <i class="bi bi-hash"></i>
                            <input type="text" name="bank_account" placeholder="Enter account number">
                        </div>
                    </div>
                </div>

                <button type="submit" name="pay" class="btn-pay-now">
                    <i class="bi bi-lock-fill"></i>
                    Pay $ <?= number_format($pay_amount, 2) ?> · <?= $pay_label ?>
                </button>

                <div class="pay-footer-note">
                    <i class="bi bi-shield-check"></i> 256-bit SSL &nbsp;·&nbsp;
                    <i class="bi bi-eye-slash"></i> Card details never stored
                </div>
            </form>
        </div>
        </div>

        

        <script>
            function switchTab(btn, tab) {
                document.querySelectorAll('.pay-tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.pay-tab-content').forEach(t => t.classList.add('hidden'));
                btn.classList.add('active');
                document.getElementById('tab-' + tab).classList.remove('hidden');
                if (tab === 'card') document.getElementById('payment_method_input').value = 'Card';
                else document.getElementById('payment_method_input').value = '';
            }
            document.getElementById('cardNumber').addEventListener('input', function () {
                let v = this.value.replace(/\D/g,'').substring(0,16);
                this.value = v.match(/.{1,4}/g)?.join(' ') || v;
                document.getElementById('miniCardNum').textContent = v.padEnd(16,'•').match(/.{1,4}/g).join(' ');
                document.getElementById('visaIcon').classList.toggle('dim', v[0] !== '4');
                document.getElementById('mcIcon').classList.toggle('dim', !['5','2'].includes(v[0]));
            });
            document.getElementById('cardHolder').addEventListener('input', function () {
                document.getElementById('miniHolder').textContent = this.value.toUpperCase() || 'YOUR NAME';
            });
            document.getElementById('cardExpiry').addEventListener('input', function () {
                let v = this.value.replace(/\D/g,'').substring(0,4);
                if (v.length >= 2) v = v.slice(0,2)+' / '+v.slice(2);
                this.value = v;
                document.getElementById('miniExpiry').textContent = v || 'MM/YY';
            });
            document.getElementById('cardCvv').addEventListener('focus',
                () => document.getElementById('miniCard').classList.add('flipped'));
            document.getElementById('cardCvv').addEventListener('blur',
                () => document.getElementById('miniCard').classList.remove('flipped'));
        </script>
    </body>
</html>