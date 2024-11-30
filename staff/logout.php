<?php
session_start();

// Destroy the session
session_unset();
session_destroy();

// Redirect to login page
header("Location: ../account/loginwcss.php?message=logged_out");
exit;
