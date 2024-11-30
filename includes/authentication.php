<?php
// Only start session if one hasn't been started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function check_user_login() {
    if (!isset($_SESSION['account'])) {
        header("Location: ../account/loginwcss.php");
        exit();
    }
}

function prevent_logged_in_access() {
    if (isset($_SESSION['account'])) {
        header("Location: ../user/home.php");
        exit();
    }
}
?>
