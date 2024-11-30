<?php
$page_title = "CCS Ranking System - Dashboard";
session_start();

if (isset($_SESSION['account'])) {
    if (empty($_SESSION['account']['role_id'])) {
        error_log("Access attempt with missing or invalid role_id.");
        header('location: ../account/loginwcss.php?error=missing_role');
        exit;
    }
} else {
    error_log("Unauthorized access attempt. Session not set.");
    header('location: ../account/loginwcss.php?error=unauthorized');
    exit;
}

$account = $_SESSION['account'];

$header_file = 'includes/header.php';
if (file_exists($header_file)) {
    require_once($header_file);
} else {
    error_log("Header file ($header_file) not found.");
    die("An error occurred while loading the dashboard. Please try again later.");
}

require_once '../classes/user.class.php';
$userObj = new User();

$userDetails = $userObj->getUserDetails($account['user_id']);

if ($userDetails) {
    $username = isset($userDetails['username']) ? $userDetails['username'] : 'Guest';
} else {
    error_log("Failed to fetch user details for user ID: " . $account['user_id']);
    $username = "Unknown User";
}
$name = $userDetails['firstname'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
        }

        nav {
            background-color: #004225;
            color: #fff;
            padding: 1rem;
        }

        nav a {
            color: #fff;
            margin: 0 15px;
            text-decoration: none;
        }

        .dashboard {
            max-width: auto;
            margin: 10px 20px;
            padding: 0;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            position: relative;
        }

        .dashboard img {
        width: 100%;
        height: 32rem;
        border-radius: 8px; 
        object-fit: cover; 
    }

        .welcome-message {
            color: #004225;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        h1 {
            margin-top: 20px;
            font-size: 2rem;
            font-weight: bold;
        }

        h3 {
            font-size: 25px;
            margin-bottom: 20px;
        }

        .user-info {
            position: absolute;
            top: 20px; 
            left: 50%;
            transform: translateX(-50%);
            padding: 20px;
            z-index: 1;
            text-align: center;
            color: #ffffff;
        }

        .user-info h2 {
            font-size: 1.8rem;
            font-weight: bold;
            color: #ffffff;
            margin-bottom: 15px;
        }

        .user-info p {
            font-size: 1rem;
            color: #ffffff;
            margin: 10px 0;
        }

        .actions {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }

        @media (max-width: 768px) {
            .dashboard {
                padding: 1rem;
            }

            .actions {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>

    <?php include('includes/navbar.php'); ?>

    <main>
    <h1 class="welcome-message">Hello, <?= htmlspecialchars($name) ?></h1>
    <h3 class="welcome-message">Welcome to the CCS Ranking System Dashboard</h3>

    <section class="dashboard">
        <img src="../img/AUTHENTICATION.jpg" alt="Dashboard Image">
        <div class="user-info">
            <h2>Apply For Dean’s Lister</h2>
            <p>Stand out for your academic excellence! The Dean's Lister award recognizes students with outstanding grades and dedication. Apply now to join the ranks of top achievers!</p>
        </div>
    </section>
</main>


</body>

</html>
