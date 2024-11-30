<?php
require_once '../classes/account.class.php'; // Include the Account class
$page_title = "CCS Ranking System - Edit Account";

// Get the ID from the query parameter
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: dashboard.accounts.php?error=1&message=Invalid account ID");
    exit;
}

$id = $_GET['id'];

$accountObj = new Account();
$accountDetails = $accountObj->getAccountDetails($id);

if (!$accountDetails) {
    header("Location: dashboard.accounts.php?error=1&message=Account not found");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve data from the form
    $identifier = $_POST['identifier'];
    $firstname = $_POST['firstname'];
    $middlename = $_POST['middlename'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $other = $_POST['other']; // Course/Department
    $username = $_POST['username'];
    $status = $_POST['status'];

    try {
        // Update the account details
        $isUpdated = $accountObj->updateAccount($id, [
            'identifier' => $identifier,
            'firstname' => $firstname,
            'middlename' => $middlename,
            'lastname' => $lastname,
            'email' => $email,
            'other' => $other,
            'username' => $username,
            'status' => $status,
            'role_id' => $accountDetails['role_id']
        ]);

        if ($isUpdated) {
            header("Location: dashboard.accounts.php?success=1&message=Account updated successfully");
            exit;
        } else {
            $error = "Failed to update the account. Please try again.";
        }
    } catch (Exception $e) {
        error_log("Error updating account: " . $e->getMessage());
        $error = "An error occurred while updating the account.";
    }
}

// Include header file
$header_file = 'includes/header.php';
if (file_exists($header_file)) {
    require_once($header_file);
} else {
    die("An error occurred while loading the dashboard. Please try again later.");
}
?>

<div class="wrapper">
    <?php include('includes/sidebar.php') ?>
    <div class="main p-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="card-title pb-2 mb-2">
                        Edit Account | ID Number: <span class="text-primary text-bold"><?= htmlspecialchars($accountDetails['identifier']) ?></span>
                    </h4>
                    <a href="dashboard.accounts.php" class="btn btn-secondary">
                        <span>Back</span>
                    </a>
                </div>
                <hr>
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <div class="edit-form">
                    <form class="row g-3 needs-validation" method="POST" action="">
                        <div class="col-md-2 position-relative">
                            <label for="identifier" class="form-label">ID Number</label>
                            <input type="text" class="form-control" id="identifier" name="identifier" value="<?= htmlspecialchars($accountDetails['identifier']) ?>" required>
                        </div>
                        <div class="col-md-3 position-relative">
                            <label for="firstname" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="firstname" name="firstname" value="<?= htmlspecialchars($accountDetails['firstname']) ?>" required>
                        </div>
                        <div class="col-md-3 position-relative">
                            <label for="middlename" class="form-label">Middle Name</label>
                            <input type="text" class="form-control" id="middlename" name="middlename" value="<?= htmlspecialchars($accountDetails['middlename']) ?>" required>
                        </div>
                        <div class="col-md-3 position-relative">
                            <label for="lastname" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lastname" name="lastname" value="<?= htmlspecialchars($accountDetails['lastname']) ?>" required>
                        </div>
                        <div class="col-md-4 position-relative">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($accountDetails['email']) ?>" required>
                        </div>
                        <div class="col-md-6 position-relative">
                            <label for="other" class="form-label">Course/Department</label>
                            <input type="text" class="form-control" id="other" name="other" value="<?= $accountDetails['role_id'] == 2 ? $accountDetails['department'] : $accountDetails['course'] ?>" required>
                        </div>
                        <div class="col-md-4 position-relative">
                            <label for="username" class="form-label">Username</label>
                            <div class="input-group has-validation">
                                <span class="input-group-text">@</span>
                                <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($accountDetails['username']) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-2 position-relative">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="Active" <?= $accountDetails['status'] === 'Active' ? 'selected' : '' ?>>Active</option>
                                <option value="Inactive" <?= $accountDetails['status'] === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                                <option value="Suspended" <?= $accountDetails['status'] === 'Suspended' ? 'selected' : '' ?>>Suspended</option>
                                <option value="Pending" <?= $accountDetails['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary" type="submit">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php') ?>
