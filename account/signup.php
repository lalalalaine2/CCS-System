<?php
$page_title = "CCS Ranking System";
include_once "../includes/_head.php";
require_once '../tools/functions.php';
require_once '../classes/user.class.php';
require_once '../classes/account.class.php';
require_once '../classes/role.class.php';

session_start();
$userObj = new User();
$accountObj = new Account();
$roleObj = new Role();
$roles = $roleObj->renderAllRoles(); // Fetch all roles for dropdown

// Initialize variables
$identifier = $first_name = $middle_name = $last_name = $username = $password = $role_id = $email = $other = '';
$identifierErr = $first_nameErr = $middle_nameErr = $last_nameErr = $usernameErr = $passwordErr = $role_idErr = $emailErr = $otherErr = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Clean and validate input
        $identifier = clean_input($_POST['identifier']);
        $first_name = clean_input($_POST['firstname']);
        $middle_name = clean_input($_POST['middlename']);
        $last_name = clean_input($_POST['lastname']);
        $username = clean_input($_POST['username']);
        $password = clean_input($_POST['password']);
        $role_id = clean_input($_POST['role']);
        $email = clean_input($_POST['email']);
        $other = clean_input($_POST['other']);

        // Validate first name
        if (empty($first_name)) {
            $first_nameErr = "First name is required!";
        }

        // Validate last name
        if (empty($last_name)) {
            $last_nameErr = "Last name is required!";
        }

        // Validate email
        if (empty($email)) {
            $emailErr = "Email is required!";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailErr = "Invalid email format!";
        }

        // Validate username
        if (empty($username)) {
            $usernameErr = "Username is required!";
        } elseif ($accountObj->usernameExist($username)) {
            $usernameErr = "Username already taken!";
        }

        // Validate password
        if (empty($password)) {
            $passwordErr = "Password is required!";
        } else {
            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        }

        // Validate role
        if (empty($role_id)) {
            $role_idErr = "Role is required!";
        }

        // Validate course/department based on role
        if ($role_id == 3 && empty($other)) { // Assuming '3' is the role ID for students
            $otherErr = "Course is required for students!";
        } elseif ($role_id == 2 && empty($other)) { // Assuming '2' is the role ID for staff
            $otherErr = "Department is required for staff!";
        }

        // Check if there are validation errors
        if (!empty($first_nameErr) || !empty($last_nameErr) || !empty($usernameErr) || !empty($passwordErr) || !empty($role_idErr) || !empty($emailErr) || !empty($otherErr)) {
            throw new Exception("Validation errors occurred.");
        }

        // Store data in the User model
        $userObj->identifier = $identifier;
        $userObj->firstname = $first_name;
        $userObj->middlename = $middle_name;
        $userObj->lastname = $last_name;
        $userObj->email = $email;

        // Assign course or department based on role
        if ($role_id == 3) {
            $userObj->course = $other;
        } elseif ($role_id == 2) {
            $userObj->department = $other;
        }



        // Save the user and get the inserted ID
        $userId = $userObj->store();
        if (!$userId) {
            throw new Exception("Failed to store user in the database.");
        }

        // Store account details
        $accountObj->user_id = $userId;
        $accountObj->username = $username;
        $accountObj->password = $password;
        $accountObj->role_id = $role_id;

        // Save the account
        if (!$accountObj->store($userId)) {
            throw new Exception("Failed to create account in the database.");
        }

        // Redirect to login page on success
        header("Location: loginwcss.php");
        exit();

    } catch (Exception $e) {
        // Log error message for debugging
        error_log("Error: " . $e->getMessage());

        // Provide feedback to the user (optional, do not reveal sensitive info in production)
        $usernameErr = "An error occurred: " . $e->getMessage();
    }
}
?>

<link rel="stylesheet" href="../css/signup.css">
<body class="d-flex align-items-center justify-content-center container" style="height: 100vh">
<main class="form-signup w-75 rounded">
    <div class="card">
        <div class="card-body">
            <div class="card-header">
                <div class="d-flex justify-content-center align-items-center logo">
                    <img class="text-center" src="../img/ccs_logo.png" alt="" width="150" height="150">
                </div>

            </div>
            <p class="text-center w-50 m-auto p-1">COLLEGE OF COMPUTING STUDIES OFFICIAL RANKING SYSTEM</p>
            <h3 class="card-title">
                Sign Up
            </h3>
            <hr>
            <form class="row g-3" action="signup.php" method="post">
                <div class="col-md-3">
                    <label for="validationCustom01" class="form-label">ID Number</label>
                    <input type="text" class="form-control" id="identifier" name="identifier"
                           value="<?= htmlspecialchars($identifier) ?>" required>
                    <div class="valid-feedback">
                        Looks good!
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="validationCustom01" class="form-label">First name</label>
                    <input type="text" class="form-control" id="firstname" name="firstname"
                           value="<?= htmlspecialchars($first_name) ?>" required>
                    <div class="valid-feedback">
                        Looks good!
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="validationCustom01" class="form-label">Middle name</label>
                    <input type="text" class="form-control" id="middlename" name="middlename"
                           value="<?= htmlspecialchars($middle_name) ?>" required>
                    <div class="valid-feedback">
                        Looks good!
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="validationCustom01" class="form-label">Last name</label>
                    <input type="text" class="form-control" id="lastname" name="lastname"
                           value="<?= htmlspecialchars($middle_name) ?>" required>
                    <div class="valid-feedback">
                        Looks good!
                    </div>
                </div>


                <div class="col-md-6">
                    <label for="validationCustom01" class="form-label">Course/Department</label>
                    <input type="text" class="form-control" id="other" name="other"
                           value="<?= htmlspecialchars($other) ?>" required>
                    <div class="valid-feedback">
                        Looks good!
                    </div>
                </div>

                <div class="col-md-3">
                    <label for="validationCustom01" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email"
                           value="<?= htmlspecialchars($email) ?>" required>
                    <div class="valid-feedback">
                        Looks good!
                    </div>
                </div>

                <div class="col-md-3">
                    <label for="validationCustom01" class="form-label">Role</label>
                    <select class="form-control" id="role" name="role" required>
                        <option value="" disabled selected>Select a role</option>
                        <?php
                        foreach ($roles as $r) {
                            $isSelected = ($role_id == $r['name']) ? 'selected' : '';
                            echo "<option value=\"{$r['id']}\" $isSelected>{$r['name']}</option>";
                        }
                        ?>
                    </select>
                    <div class="valid-feedback">
                        Looks good!
                    </div>
                </div>

                <hr>

                <div class="col-md-3">
                    <label for="validationCustom01" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username"
                           value="<?= htmlspecialchars($username) ?>" required>
                    <div class="valid-feedback">
                        Looks good!
                    </div>
                </div>

                <div class="col-md-3">
                    <label for="validationCustom01" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password"
                           required>
                    <div class="valid-feedback">
                        Looks good!
                    </div>
                </div>

                <div class="text-end">
                    <button class="btn btn-primary w-25 py-2" type="submit">Sign Up</button>
                    <div class="text-end mt-2" style="margin: 0">Already have an account? <a href="loginwcss.php"> Sign in</a></div>
                </div>
            </form>
        </div>
    </div>
</main>
<?php include_once '../includes/_footer.php'; ?>
</body>

</html>