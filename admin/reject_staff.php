<?php
require_once "../config/db.php";

if (isset($_GET['id'])) {

    $id = intval($_GET['id']);

    $sql = "UPDATE users 
            SET status='rejected' 
            WHERE user_id=? AND role='staff'";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: manage_staff.php?success=rejected");
        exit;
    }
}
?>