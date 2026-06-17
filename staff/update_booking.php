<?php
require_once "../config/db.php";

$id = $_GET['id'];
$status = $_GET['status'];

mysqli_query($conn, "UPDATE bookings SET booking_status='$status' WHERE booking_id=$id");

header("Location: manage_bookings.php");
?>