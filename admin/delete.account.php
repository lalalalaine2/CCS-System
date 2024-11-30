<?php
require_once '../classes/account.class.php'; // Include the User class
// Retrieve the account ID from the form
$id = $_GET['id'];

// Initialize the Account class
$accountObj = new Account();

try {
    // Delete the account
    $isDeleted = $accountObj->deleteAccount($id);
    var_dump($isDeleted);
    if ($isDeleted) {
        header("Location: dashboard.accounts.php?success=1&message=Account deleted successfully");
        exit;
    } else {
        header("Location: dashboard.accounts.php?error=1&message=Failed to delete the account");
        exit;
    }
} catch (Exception $e) {
    error_log("Error deleting account: " . $e->getMessage());
    header("Location: dashboard.accounts.php?error=1&message=An error occurred while deleting the account");
    exit;
}

?>