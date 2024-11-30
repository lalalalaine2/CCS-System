<?php
require_once "../includes/authentication.php";  // This will handle session start

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: ../account/loginwcss.php");
exit();
