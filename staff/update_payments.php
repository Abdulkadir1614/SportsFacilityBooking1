<?php
require_once "../config/db.php";

$id = $_GET['id'];

mysqli_query($conn, "UPDATE payments SET payment_status='Verified' WHERE payment_id= ?");

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
    $notif_msg = "✅ Your payment for {$info_row['facility_name']} on {$info_row['booking_date']} ({$info_row['time_slot']}) has been verified. Your booking is confirmed!";
    $notif = $conn->prepare("INSERT INTO notifications (user_id, message, status, notification_date) VALUES (?, ?, 'unread', NOW())");
    $notif->bind_param("is", $info_row['user_id'], $notif_msg);
    $notif->execute();
}

header("Location: manage_payments.php");
?>