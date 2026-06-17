<?php

$host     = "localhost";
$username = "root";
$password = "";              // default for XAMPP
$database = "sports_facility_db";
                                                             
// Create database connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}


// Set character encoding to UTF-8
mysqli_set_charset($conn, "utf8");
?>
