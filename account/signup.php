<?php
$page_title = "CCS Ranking System";
include_once "../includes/_head.php";
require_once '../tools/functions.php';
require_once '../classes/user.class.php';
require_once '../classes/account.class.php';
require_once '../classes/role.class.php';
require_once '../classes/course.class.php';
require_once '../classes/department.class.php';

session_start();
$userObj = new User();
$accountObj = new Account();
$roleObj = new Role();
$courseObj = new Course();
$departmentObj = new Department();
$roles = $roleObj->renderAllRoles(); // Fetch all roles for dropdown

// Initialize variables
$identifier = $first_name = $middle_name = $last_name = $username = $password = $role_id = $email = $other = '';
$identifierErr = $first_nameErr = $middle_nameErr = $last_nameErr = $usernameErr = $passwordErr = $role_idErr = $emailErr = $otherErr = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Validate identifier
        $identifier = clean_input($_POST['identifier']);
        if (empty($identifier)) {
            $identifierErr = "ID Number is required!";
            throw new Exception("ID Number is required!");
        } elseif ($userObj->identifierExists($identifier)) {
            $identifierErr = "This ID Number is already registered!";
            throw new Exception("This ID Number is already registered!");
        }

        // Clean and validate input
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

        // Add this new validation
        $identifierValidation = $userObj->validateIdentifier($identifier, $role_id);
        if (!$identifierValidation['valid']) {
            $identifierErr = $identifierValidation['message'];
            throw new Exception($identifierErr);
        }

        // Add email validation
        $emailValidation = $userObj->validateEmail($email);
        if (!$emailValidation['valid']) {
            $emailErr = $emailValidation['message'];
            throw new Exception($emailErr);
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

        // Before storing the user
        $userObj->setRoleId($role_id);

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
}?>

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

                <div class="col-md-3">
                    <label for="validationCustom01" class="form-label">ID Number</label>
                    <input type="text" 
                           class="form-control <?php echo !empty($identifierErr) ? 'is-invalid' : ''; ?>" 
                           id="identifier" 
                           name="identifier"
                           value="<?= htmlspecialchars($identifier) ?>" 
                           required>
                    <?php if (!empty($identifierErr)): ?>
                        <div class="invalid-feedback">
                            <?php echo $identifierErr; ?>
                        </div>
                    <?php else: ?>
                        <div class="valid-feedback">
                            Looks good!
                        </div>
                    <?php endif; ?>
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
                           value="<?= htmlspecialchars($last_name) ?>" required>
                    <div class="valid-feedback">
                        Looks good!
                    </div>
                </div>


                <div class="col-md-3">
                    <label for="course" class="form-label">Course</label>
                    <select class="form-control" id="course" name="other">
                        <option value="" disabled selected>Select Course</option>
                        <?php
                        $courses = $courseObj->getAllCourses();
                        foreach ($courses as $course) {
                            $selected = ($other == $course['id']) ? 'selected' : '';
                            echo "<option value='{$course['id']}' {$selected}>{$course['course_name']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="department" class="form-label">Department</label>
                    <select class="form-control" id="department" name="other">
                        <option value="" disabled selected>Select Department</option>
                        <?php
                        $departments = $departmentObj->getAllDepartments();
                        foreach ($departments as $department) {
                            $selected = ($other == $department['id']) ? 'selected' : '';
                            echo "<option value='{$department['id']}' {$selected}>{$department['department_name']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email"
                           value="<?= htmlspecialchars($email) ?>" required>
                    <small class="form-text text-muted">Use your WMSU email: username@wmsu.edu.ph</small>
                    <div class="text-danger" id="emailError"></div>
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    const identifierInput = document.getElementById('identifier');
    const roleSelect = document.getElementById('role');
    const identifierError = document.createElement('div');
    identifierError.className = 'text-danger mt-1';
    identifierInput.parentNode.appendChild(identifierError);

    function validateIdentifierFormat(identifier, roleId) {
        const studentPattern = /^\d{4}-\d{5}$/;
        const staffPattern = /^\d{9}$/;

        if (roleId == '3') { // Student
            if (!studentPattern.test(identifier)) {
                return 'Student ID must be in 0000-00000 format';
            }
        } else if (roleId == '2') { // Staff
            if (!staffPattern.test(identifier)) {
                return 'Staff ID must be 9 digits without spaces or dashes';
            }
        }
        return '';
    }

    function formatIdentifier(input, roleId) {
        let value = input.value.replace(/\D/g, ''); // Remove non-digits

        if (roleId == '3' && value.length >= 4) {
            // Format as 0000-00000 for students
            value = value.substr(0, 4) + '-' + value.substr(4, 5);
        }
        
        // Limit length based on role
        if (roleId == '3') {
            value = value.substr(0, 10); // 9 digits + 1 dash
        } else if (roleId == '2') {
            value = value.substr(0, 9); // 9 digits
        }

        input.value = value;
    }

    // Add event listeners
    identifierInput.addEventListener('input', function() {
        formatIdentifier(this, roleSelect.value);
        const error = validateIdentifierFormat(this.value, roleSelect.value);
        identifierError.textContent = error;
    });

    roleSelect.addEventListener('change', function() {
        identifierInput.value = ''; // Clear identifier when role changes
        identifierError.textContent = '';
    });

    const emailInput = document.getElementById('email');
    const emailError = document.getElementById('emailError');

    function validateEmailFormat(email) {
        // Basic email format validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            return 'Please enter a valid email address';
        }

        // WMSU domain validation
        if (!email.toLowerCase().endsWith('@wmsu.edu.ph')) {
            return 'Please use a valid WMSU email address (@wmsu.edu.ph)';
        }

        return '';
    }

    // Real-time validation as user types
    emailInput.addEventListener('input', function() {
        const email = this.value.trim();
        const error = validateEmailFormat(email);
        emailError.textContent = error;
        
        // Add or remove invalid class for styling
        if (error) {
            emailInput.classList.add('is-invalid');
            emailInput.classList.remove('is-valid');
        } else {
            emailInput.classList.remove('is-invalid');
            emailInput.classList.add('is-valid');
        }
    });

    // Check for duplicate email when focus leaves the field
    emailInput.addEventListener('blur', async function() {
        const email = this.value.trim();
        if (email && !validateEmailFormat(email)) {
            try {
                const response = await fetch('check_email.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'email=' + encodeURIComponent(email)
                });
                
                const result = await response.json();
                if (!result.valid) {
                    emailError.textContent = result.message;
                    emailInput.classList.add('is-invalid');
                    emailInput.classList.remove('is-valid');
                }
            } catch (error) {
                console.error('Error checking email:', error);
            }
        }
    });
});
</script>
<?php include_once '../includes/_footer.php'; ?>
<script>
document.getElementById('role').addEventListener('change', function() {
    const selectedRole = this.value;
    const courseSelect = document.getElementById('course');
    const departmentSelect = document.getElementById('department');
    
    // Reset both selects
    courseSelect.value = '';
    departmentSelect.value = '';
    
    // Hide both initially
    courseSelect.parentElement.style.display = 'none';
    departmentSelect.parentElement.style.display = 'none';
    
    // Remove required attribute from both
    courseSelect.removeAttribute('required');
    departmentSelect.removeAttribute('required');
    
    // Show appropriate select based on role
    if (selectedRole === '3') { // Student
        courseSelect.parentElement.style.display = 'block';
        courseSelect.setAttribute('required', 'required');
        departmentSelect.name = ''; // Remove name attribute from department
        courseSelect.name = 'other'; // Set name attribute for course
    } else if (selectedRole === '2') { // Staff
        departmentSelect.parentElement.style.display = 'block';
        departmentSelect.setAttribute('required', 'required');
        courseSelect.name = ''; // Remove name attribute from course
        departmentSelect.name = 'other'; // Set name attribute for department
    }
});

// Trigger change event on page load if role is selected
window.addEventListener('load', function() {
    const roleSelect = document.getElementById('role');
    if (roleSelect.value) {
        roleSelect.dispatchEvent(new Event('change'));
    }
});
</script>
</body>

</html>
