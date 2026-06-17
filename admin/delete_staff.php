<?php
require_once "../config/db.php";

$id = $_GET['id'];

mysqli_query($conn, "DELETE FROM users WHERE user_id=$id AND role='staff'");

header("Location: manage_staff.php");