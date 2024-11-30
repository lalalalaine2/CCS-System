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
require_once '../classes/account.class.php'; // Include the User class
$accountObj = new Account();
$accounts = $accountObj->getAccounts();
?>

    <div class="wrapper">
        <?php include('includes/sidebar.php') ?>
        <div class="main p-3">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title pb-2 mb-2">Account Management</h4>
                    <hr>
                    <div class="table-wrapper table-responsive">
                        <table id="accounts-table" class="table table-bordered" style="width: 100%">
                            <thead>
                                <tr>
                                    <th>Identifier</th>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Course</th>
                                    <th>Department</th>
                                    <th>Username</th>
                                    <th>Role</th>
                                    <th>Date Registered</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>
                            <?php if (!empty($accounts) && is_array($accounts)): ?>
                                <?php foreach ($accounts as $account): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars(isset($account['identifier']) ? $account['identifier'] : 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($account['firstname'] . ' ' . (isset($account['middlename']) ? $account['middlename'] : '') . ' ' . $account['lastname']); ?></td>
                                        <td><?php echo htmlspecialchars(isset($account['email']) ? $account['email'] : 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars(isset($account['course']) ? $account['course'] : 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars(isset($account['department']) ? $account['department'] : 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars(isset($account['username']) ? $account['username'] : 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars(isset($account['role_id']) ? $account['role_id'] : 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars(date('Y-m-d', strtotime(isset($account['date_registered']) ? $account['date_registered'] : ''))); ?></td>
                                        <td>
                                            <a href="edit_account.php?id=<?php echo urlencode($account['identifier']); ?>" class="btn btn-primary btn-sm">Edit</a>
                                            <a href="delete_account.php?id=<?php echo urlencode($account['identifier']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this account?');">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center">No accounts found.</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>

                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include('includes/footer.php') ?>