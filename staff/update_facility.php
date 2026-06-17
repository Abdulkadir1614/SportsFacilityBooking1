<?php
session_start();
require_once "../auth/session_timeout.php";
require_once "../config/db.php";

// Security check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'staff') {
    header("Location: ../auth/login.php");
    exit();
}

// Validate inputs
if (!isset($_GET['id']) || !isset($_GET['status'])) {
    die("Invalid request");
}

$id = intval($_GET['id']);
$status = $_GET['status'];

// Only allow valid values
$allowed = ['Available', 'Unavailable'];

if (!in_array($status, $allowed)) {
    die("Invalid status");
}

// Update query
$query = "UPDATE facilities 
          SET availability_status = '$status' 
          WHERE facility_id = $id";

mysqli_query($conn, $query);

// Redirect back
header("Location: manage_facilities.php");
exit();
?>