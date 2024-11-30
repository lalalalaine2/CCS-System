<?php
$page_title = "CCS Ranking System - Dashboard";
session_start();

// Check if session is set
if (isset($_SESSION['account'])) {
    // Verify role_id existence and value
    if (empty($_SESSION['account']['role_id'])) {
        // Log error message for debugging
        error_log("Access attempt with missing or invalid role_id.");
        // Redirect to login page with an error message
        header('location: ../account/loginwcss.php?error=missing_role');
        exit;
    }
} else {
    // Log error message for debugging
    error_log("Unauthorized access attempt. Session not set.");
    // Redirect to login page with an error message
    header('location: ../account/loginwcss.php?error=unauthorized');
    exit;
}
$account = $_SESSION['account'];

// Include header file with error handling
$header_file = 'includes/header.php';
if (file_exists($header_file)) {
    require_once($header_file);
} else {
    // Log error message for debugging
    error_log("Header file ($header_file) not found.");
    // Display user-friendly error message
    die("An error occurred while loading the dashboard. Please try again later.");
}

require_once '../classes/user.class.php'; // Include the User class
$userObj = new User(); // Instantiate the User class

// Fetch user details using the user ID stored in the session
$userDetails = $userObj->getUserDetails($account['user_id']);

// Check if user details are fetched successfully
if ($userDetails) {
    $username = isset($userDetails['username']) ? $userDetails['username'] : 'Guest'; // Replace 'username' with the actual column name in your DB
} else {
    // Log error message if user details are not fetched
    error_log("Failed to fetch user details for user ID: " . $account['user_id']);
    $username = "Unknown User";
}
$name = $userDetails['firstname'];

?>
    <div class="wrapper">
    <?php require_once('includes/sidebar.php')?>
    <div class="main p-3">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Dashboard</h4>
                <h1>Hello, <?= $name ?>!</h1>
            </div>
        </div>
    </div>
</div>

<?php require_once('includes/footer.php')?>