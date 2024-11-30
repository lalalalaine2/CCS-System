<?php
$page_title = "CSS_RankingSystem - Reset Password";
include_once "../includes/_head.php";
require_once '../tools/functions.php';
require_once '../classes/account.class.php';

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['username']) && isset($_POST['new_password']) && isset($_POST['confirm_password'])) {
        $username = clean_input($_POST['username']);
        $new_password = clean_input($_POST['new_password']);
        $confirm_password = clean_input($_POST['confirm_password']);
        $account = new Account();
        
        try {
            // Check if passwords match
            if($new_password !== $confirm_password) {
                throw new Exception("Passwords do not match");
            }

            // Check if username exists and update password
            if($account->resetPassword($username, $new_password)) {
                $success_message = "Password has been successfully reset. You can now login with your new password.";
            } else {
                $error_message = "Username not found.";
            }
        } catch (Exception $e) {
            $error_message = $e->getMessage();
            error_log("Password reset error: " . $e->getMessage());
        }
    }
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

        <h1 class="h3 mb-3 fw-normal">Reset Password</h1>
        
        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <?php if ($success_message): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <div class="form-floating mb-3">
            <input type="text" class="form-control" id="username" name="username" 
                   placeholder="Username" required>
            <label for="username">Username</label>
        </div>

        <div class="form-floating mb-3">
            <input type="password" class="form-control" id="new_password" name="new_password" 
                   placeholder="New Password" required>
            <label for="new_password">New Password</label>
        </div>

        <div class="form-floating mb-3">
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                   placeholder="Confirm Password" required>
            <label for="confirm_password">Confirm Password</label>
        </div>

        <button class="btn btn-primary w-100 py-2" type="submit">Reset Password</button>
        
        <p class="mt-3 text-center">
            <a href="loginwcss.php">Back to Login</a>
        </p>
    </form>
</main>

<?php require_once '../includes/_footer.php'; ?>
</body>
</html> 