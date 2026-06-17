<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 5 minutes = 300 seconds
$timeout_duration = 300;

// Check last activity
if (isset($_SESSION['LAST_ACTIVITY'])) {

    if ((time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {

        // Session expired
        session_unset();
        session_destroy();

        header("Location: ../auth/login.php?timeout=1");
        exit();
    }
}

// Update last activity time
$_SESSION['LAST_ACTIVITY'] = time();
?>