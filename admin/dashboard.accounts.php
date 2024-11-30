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
            <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_GET['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif (isset($_GET['error']) && $_GET['error'] == 1): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_GET['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <script>
                // Remove query parameters after the alert is displayed
                window.addEventListener('load', function () {
                    const url = new URL(window.location);
                    url.searchParams.delete('success');
                    url.searchParams.delete('error');
                    url.searchParams.delete('message'); // Remove the message parameter as well
                    window.history.replaceState({}, document.title, url); // Update the URL without refreshing
                });
            </script>


            <div class="card">
                <div class="card-body">
                   <div class="d-flex justify-content-between align-items-center">
                       <h4 class="card-title pb-2 mb-2">Account Management</h4>
                       <a href="dashboard.add.accounts.php" class="btn btn-success">
                           <i class="lni lni-plus fs-6 font-semibold"></i>
                            <span>
                                Add New User
                            </span>
                       </a>
                   </div>
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
                                        <td class="text-center">
                                            <a href="view.account.php?id=<?php echo urlencode($account['id']); ?>" class="btn btn-primary btn-sm"><i class="lni lni-eye"></i></a>
                                            <a href="edit.account.php?id=<?php echo urlencode($account['id']); ?>" class="btn btn-warning btn-sm"><i class="lni lni-pencil"></i></a>
                                            <a href="delete.account.php?id=<?php echo urlencode($account['id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this account?');">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="#343C54" idxmlns="http://www.w3.org/2000/svg" transform="rotate(0 0 0)">
                                                    <path d="M14.7223 12.7585C14.7426 12.3448 14.4237 11.9929 14.01 11.9726C13.5963 11.9522 13.2444 12.2711 13.2241 12.6848L12.9999 17.2415C12.9796 17.6552 13.2985 18.0071 13.7122 18.0274C14.1259 18.0478 14.4778 17.7289 14.4981 17.3152L14.7223 12.7585Z" fill="#ffffff"/>
                                                    <path d="M9.98802 11.9726C9.5743 11.9929 9.25542 12.3448 9.27577 12.7585L9.49993 17.3152C9.52028 17.7289 9.87216 18.0478 10.2859 18.0274C10.6996 18.0071 11.0185 17.6552 10.9981 17.2415L10.774 12.6848C10.7536 12.2711 10.4017 11.9522 9.98802 11.9726Z" fill="#ffffff"/>
                                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M10.249 2C9.00638 2 7.99902 3.00736 7.99902 4.25V5H5.5C4.25736 5 3.25 6.00736 3.25 7.25C3.25 8.28958 3.95503 9.16449 4.91303 9.42267L5.54076 19.8848C5.61205 21.0729 6.59642 22 7.78672 22H16.2113C17.4016 22 18.386 21.0729 18.4573 19.8848L19.085 9.42267C20.043 9.16449 20.748 8.28958 20.748 7.25C20.748 6.00736 19.7407 5 18.498 5H15.999V4.25C15.999 3.00736 14.9917 2 13.749 2H10.249ZM14.499 5V4.25C14.499 3.83579 14.1632 3.5 13.749 3.5H10.249C9.83481 3.5 9.49902 3.83579 9.49902 4.25V5H14.499ZM5.5 6.5C5.08579 6.5 4.75 6.83579 4.75 7.25C4.75 7.66421 5.08579 8 5.5 8H18.498C18.9123 8 19.248 7.66421 19.248 7.25C19.248 6.83579 18.9123 6.5 18.498 6.5H5.5ZM6.42037 9.5H17.5777L16.96 19.7949C16.9362 20.191 16.6081 20.5 16.2113 20.5H7.78672C7.38995 20.5 7.06183 20.191 7.03807 19.7949L6.42037 9.5Z" fill="#ffffff"/>
                                                </svg>

                                            </a>
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