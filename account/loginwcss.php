<?php
require_once "../includes/authentication.php";
prevent_logged_in_access();  // Redirects to home if already logged in

$page_title = "CSS_RankingSystem - Login";
include_once "../includes/_head.php";
require_once '../tools/functions.php';
require_once '../classes/account.class.php';

$email_or_username = $password = '';
$accountObj = new Account();
$loginErr = '';

try {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Check if the form fields exist before accessing them
        if(isset($_POST['email_or_username']) && isset($_POST['password'])) {
            // Sanitize input
            $email_or_username = clean_input($_POST['email_or_username']);
            $password = clean_input($_POST['password']);

            // Attempt to log in
            if ($accountObj->login($email_or_username, $password)) {
                // Fetch account details
                $data = $accountObj->fetch($email_or_username);

                // Check if data is fetched successfully
                if ($data) {
                    $_SESSION['account'] = $data;
                    switch ($data['role_id']) {
                        case 1:
                            header('Location: ../admin/dashboard.php');
                            break;
                        case 2:
                            header('Location: ../staff/staff.php');
                            break;
                        case 3:
                            header('Location: ../user/user.home.php');
                            break;
                    }
                    exit();
                } else {
                    // Log error if fetch fails
                    error_log("Failed to fetch user data for: $email_or_username");
                    $loginErr = 'An unexpected error occurred. Please try again.';
                }
            } else {
                // Invalid credentials
                $loginErr = 'Invalid credentials. Please try again.';
            }
        } else {
            $loginErr = 'Please fill in all fields';
        }
    }
} catch (Exception $e) {
    // Log exception details for debugging
    error_log("Login Error: " . $e->getMessage());

    // Display a generic error message to the user
    $loginErr = 'An error occurred during login. Please contact support.';
}
?>

<link rel="stylesheet" href="../css/login.css">
<body class="d-flex align-items-center justify-content-center container" style="height: 100vh">

<main class="form-signin border w-50 p-5 rounded">
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
        <div class="logo-section">
            <div class="d-flex justify-content-center align-items-center logo">
                <img class="text-center" src="../img/ccs_logo.png" alt="" width="150" height="150">
            </div>
            <p class="text-center p-1">COLLEGE OF COMPUTING STUDIES
                OFFICIAL RANKING SYSTEM</p>
        </div>

        <h1 class="h3 mb-3 fw-normal">Please sign in</h1>

        <div class="form-floating mb-2">
            <input type="text" class="form-control" id="email_or_username" name="email_or_username"
                   placeholder="Email or Username" required>
            <label for="email_or_username">Email/Username</label>
        </div>

        <div class="form-floating">
            <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
            <label for="password">Password</label>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <p class="mb-0">Don't have an account?
                <a href="signup.php">Sign up here</a>
            </p>
            <a href="forgot-password.php" class="text-primary">Forgot Password?</a>
        </div>

        <p class="text-danger"><?= $loginErr ?></p>

        <button class="btn btn-primary w-100 py-2" type="submit">Sign in</button>
    </form>
</main>

<?php require_once '../includes/_footer.php'; ?>
</body>
</html>