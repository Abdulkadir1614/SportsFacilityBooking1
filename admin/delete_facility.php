<?php
require_once "../config/db.php";
$id = $_GET['id'];

$conn->query("DELETE FROM facilities WHERE facility_id = $id");

header("Location: manage_facilities.php");
exit;
