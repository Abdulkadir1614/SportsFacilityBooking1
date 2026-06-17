<?php
session_start();
require_once "../config/db.php";

// 1️ Check login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: ../auth/login.php");
    exit;
}

// 2️ Validate booking_id
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: booking_history.php");
    exit;
}

$booking_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// 3️ Check if booking exists & belongs to this user
$sql = "SELECT booking_status 
        FROM bookings 
        WHERE booking_id = ? AND user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Booking not found or not owned by user
    header("Location: booking_history.php?error=notfound");
    exit;
}

$booking = $result->fetch_assoc();

// 4️ Prevent cancelling already cancelled bookings
if ($booking['booking_status'] === 'Cancelled') {
    header("Location: booking_history.php?error=already_cancelled");
    exit;
}

// 5️ Update booking status to Cancelled
$update = "UPDATE bookings 
           SET booking_status = 'Cancelled' 
           WHERE booking_id = ?";

$updateStmt = $conn->prepare($update);
$updateStmt->bind_param("i", $booking_id);

if ($updateStmt->execute()) {

    // 6️ Insert notification 
    $message = "Your booking (ID: $booking_id) has been cancelled successfully.";

    $notify = "INSERT INTO notifications (user_id, message, notification_date)
               VALUES (?, ?, NOW())";

    $notifyStmt = $conn->prepare($notify);
    $notifyStmt->bind_param("is", $user_id, $message);
    $notifyStmt->execute();

    header("Location: booking_history.php?success=cancelled");
    exit;
} else {
    header("Location: booking_history.php?error=failed");
    exit;
}
?>
