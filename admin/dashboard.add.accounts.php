<?php
$page_title = "CCS Ranking System - Add Account";

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
require_once '../classes/account.class.php'; // Include the User class

// Initialize variables
$error_message = '';
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_account'])) {
    // Validate and process the form submission
    try {
        if ($_POST['password'] !== $_POST['confirm_password']) {
            throw new Exception("Passwords do not match");
        }

        $account = new Account();
        $result = $account->add([
            'username' => $_POST['username'],
            'email' => $_POST['email'],
            'password' => $_POST['password'],
            'confirm_password' => $_POST['confirm_password'],
            'role_id' => $_POST['role_id']
        ]);

        if ($result) {
            $success_message = "Account created successfully!";
        } else {
            $error_message = "Error creating account. Please try again.";
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}
?>

<div class="wrapper">
    <?php include('includes/sidebar.php') ?>
    <div class="main p-3">
        <div class="card">
            <div class="card-body">
                <h2 class="card-title mb-4">Add New Account</h2>
                
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="signup-form">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control custom-input" id="username" name="username" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control custom-input" id="email" name="email" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control custom-input" id="password" name="password" required>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control custom-input" id="confirm_password" name="confirm_password" required>
                    </div>

                    <div class="mb-3">
                        <label for="role_id" class="form-label">Role</label>
                        <select class="form-select custom-input" id="role_id" name="role_id" required>
                            <option value="">Select Role</option>
                            <option value="1">Admin</option>
                            <option value="2">Staff</option>
                            <option value="3">Student</option>
                        </select>
                    </div>

                    <button type="submit" name="add_account" class="btn btn-primary custom-button w-100">Create Account</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.custom-input {
    border: 1px solid #ced4da;
    padding: 10px;
    border-radius: 4px;
    width: 100%;
    margin-bottom: 15px;
}

.custom-input:focus {
    border-color: #0C380D;
    box-shadow: 0 0 0 0.2rem rgba(12, 56, 13, 0.25);
}

.custom-button {
    background-color: #0C380D;
    border-color: #0C380D;
    padding: 10px;
    font-weight: 500;
}

.custom-button:hover {
    background-color: #0a2c0a;
    border-color: #0a2c0a;
}

.card-title {
    color: #0C380D;
    font-weight: bold;
}

.form-label {
    color: #0C380D;
    font-weight: 500;
}
</style>

<?php include_once "../includes/_footer.php"; ?>