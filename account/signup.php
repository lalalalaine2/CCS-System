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
$courses = $courseObj->getAllCourses();
$departments = $departmentObj->getAllDepartments();

// Initialize all variables
$identifier = $first_name = $middle_name = $last_name = $username = $password = $role_id = $email = '';
$identifierErr = $first_nameErr = $middle_nameErr = $last_nameErr = $usernameErr = $passwordErr = $role_idErr = $emailErr = '';

// Initialize course and department variables
$course = $department = '';
$courseErr = $departmentErr = '';

// Initialize error variable
$error = '';

// Add this with your other variable initializations at the top
$year_level = '';
$year_levelErr = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Clean and validate input
        $identifier = clean_input($_POST['identifier'] ?? '');
        $first_name = clean_input($_POST['firstname'] ?? '');
        $middle_name = clean_input($_POST['middlename'] ?? '');
        $last_name = clean_input($_POST['lastname'] ?? '');
        $username = clean_input($_POST['username'] ?? '');
        $password = clean_input($_POST['password'] ?? '');
        $role_id = clean_input($_POST['role'] ?? '');
        $email = clean_input($_POST['email'] ?? '');
        
        // Handle course/department based on role
        if ($role_id == 3) { // Student
            $course = clean_input($_POST['course'] ?? '');
            $department = ''; // Clear department for students
        } else if ($role_id == 2) { // Staff
            $department = clean_input($_POST['department'] ?? '');
            $course = ''; // Clear course for staff
        }

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
        if ($role_id == 3 && empty($course)) { // Assuming '3' is the role ID for students
            $courseErr = "Course is required for students!";
        } elseif ($role_id == 2 && empty($department)) { // Assuming '2' is the role ID for staff
            $departmentErr = "Department is required for staff!";
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
        if (!empty($first_nameErr) || !empty($last_nameErr) || !empty($usernameErr) || !empty($passwordErr) || !empty($role_idErr) || !empty($emailErr) || !empty($courseErr) || !empty($departmentErr)) {
            throw new Exception("Validation errors occurred.");
        }

        // Add year level validation for students
        if ($role_id == 3) { // Student
            $year_level = clean_input($_POST['year_level'] ?? '');
            if (empty($year_level)) {
                throw new Exception("Please select your year level");
            }
            
            // Validate year level options
            $valid_year_levels = ['First Year', 'Second Year', 'Third Year', 'Fourth Year'];
            if (!in_array($year_level, $valid_year_levels)) {
                throw new Exception("Invalid year level selected");
            }
        }

        // Store data in the User model
        $userObj->identifier = $identifier;
        $userObj->firstname = $first_name;
        $userObj->middlename = $middle_name;
        $userObj->lastname = $last_name;
        $userObj->email = $email;

        // Assign course or department based on role
        if ($role_id == 3) {
            $userObj->course = $course;
        } elseif ($role_id == 2) {
            $userObj->department = $department;
        }

        if ($role_id == 3) { // Student
            $year_level = clean_input($_POST['year_level'] ?? '');
            if (empty($year_level)) {
                throw new Exception("Year level is required for students");
            }
            if (!in_array($year_level, User::YEAR_LEVELS)) {
                throw new Exception("Invalid year level selected");
            }
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
<main class="container py-4">
    <div class="card" style="max-width: 1000px; margin: 0 auto; border-radius: 15px;">
        <div class="card-body">
            <div class="text-center mb-4" style="padding-top: 20px;">
                <div class="d-flex justify-content-center align-items-center logo" style="min-height: 120px;">
                    <img class="text-center" src="../img/ccs_logo.png" alt="" width="100" height="100" style="object-fit: contain;">
                </div>
                <p class="text-center m-auto" style="margin-top: 15px !important;">COLLEGE OF COMPUTING STUDIES OFFICIAL RANKING SYSTEM</p>
                <h3 class="card-title mt-3">Sign Up</h3>
            </div>
            <hr>
            <form class="row g-3 needs-validation" method="POST" novalidate>
                <!-- Role and Course/Department Selection in one row -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="role" class="form-label">Role*</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="" selected disabled>Select your role</option>
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= $r['id'] ?>" <?= ($role_id == $r['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($r['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Course Selection (for students) -->
                    <div class="col-md-6" id="courseSection" style="display: none;">
                        <label for="course" class="form-label">Course*</label>
                        <select class="form-select" id="course" name="course">
                            <option value="" selected disabled>Select your course</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?= htmlspecialchars($course['course_name']) ?>">
                                    <?= htmlspecialchars($course['course_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <!-- Add year level here -->
                        <div class="mt-3">
                            <label for="year_level" class="form-label">Year Level*</label>
                            <select class="form-select" id="year_level" name="year_level">
                                <option value="" selected disabled>Select your year level</option>
                                <option value="First Year">First Year</option>
                                <option value="Second Year">Second Year</option>
                                <option value="Third Year">Third Year</option>
                                <option value="Fourth Year">Fourth Year</option>
                            </select>
                        </div>
                    </div>

                    <!-- Department Selection (for staff) -->
                    <div class="col-md-6" id="departmentSection" style="display: none;">
                        <label for="department" class="form-label">Department*</label>
                        <select class="form-select" id="department" name="department">
                            <option value="" selected disabled>Select your department</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?= htmlspecialchars($dept['department_name']) ?>">
                                    <?= htmlspecialchars($dept['department_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Personal Information in one row -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="identifier" class="form-label">ID Number*</label>
                        <input type="text" class="form-control" id="identifier" name="identifier" 
                               value="<?= htmlspecialchars($identifier) ?>" required>
                        <div class="text-danger" id="identifierError"></div>
                    </div>
                    <div class="col-md-8">
                        <label for="email" class="form-label">Email*</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?= htmlspecialchars($email) ?>" required>
                        <small class="form-text text-muted">Use your WMSU email: username@wmsu.edu.ph</small>
                        <div class="text-danger" id="emailError"></div>
                    </div>
                </div>

                <!-- Name fields in one row -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="firstname" class="form-label">First Name*</label>
                        <input type="text" class="form-control" id="firstname" name="firstname" 
                               value="<?= htmlspecialchars($first_name) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="middlename" class="form-label">Middle Name</label>
                        <input type="text" class="form-control" id="middlename" name="middlename" 
                               value="<?= htmlspecialchars($middle_name) ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="lastname" class="form-label">Last Name*</label>
                        <input type="text" class="form-control" id="lastname" name="lastname" 
                               value="<?= htmlspecialchars($last_name) ?>" required>
                    </div>
                </div>

                <!-- Account Information in one row -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="username" class="form-label">Username*</label>
                        <input type="text" class="form-control" id="username" name="username" 
                               value="<?= htmlspecialchars($username) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="password" class="form-label">Password*</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <button class="btn btn-primary w-100 py-2" type="submit">Sign Up</button>
                        <div class="text-center mt-2">
                            Already have an account? <a href="loginwcss.php">Sign in</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</main>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Element declarations
    const roleSelect = document.getElementById('role');
    const courseSection = document.getElementById('courseSection');
    const departmentSection = document.getElementById('departmentSection');
    const courseSelect = document.getElementById('course');
    const departmentSelect = document.getElementById('department');
    const yearLevelSelect = document.getElementById('year_level');
    const identifierInput = document.getElementById('identifier');
    const identifierError = document.getElementById('identifierError');
    const emailInput = document.getElementById('email');
    const emailError = document.getElementById('emailError');

    // Form field display function
    function updateFormFields() {
        const selectedRole = roleSelect.value;
        
        // Hide all sections first
        courseSection.style.display = 'none';
        departmentSection.style.display = 'none';
        courseSelect.required = false;
        departmentSelect.required = false;
        
        if (yearLevelSelect) {
            yearLevelSelect.required = false;
        }

        // Show relevant section based on role
        if (selectedRole === '3') { // Student
            courseSection.style.display = 'block';
            courseSelect.required = true;
            if (yearLevelSelect) {
                yearLevelSelect.required = true;
            }
        } else if (selectedRole === '2') { // Staff
            departmentSection.style.display = 'block';
            departmentSelect.required = true;
        }

        // Clear identifier when role changes
        identifierInput.value = '';
        identifierError.textContent = '';
    }

    // Event listener for role changes
    roleSelect.addEventListener('change', updateFormFields);

    // Initial form setup
    updateFormFields();

    // Validation Functions
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
            value = value.substr(0, 4) + '-' + value.substr(4, 5);
        }
        
        if (roleId == '3') {
            value = value.substr(0, 10); // 9 digits + 1 dash
        } else if (roleId == '2') {
            value = value.substr(0, 9); // 9 digits
        }

        input.value = value;
    }

    function validateEmailFormat(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            return 'Please enter a valid email address';
        }
        if (!email.toLowerCase().endsWith('@wmsu.edu.ph')) {
            return 'Please use a valid WMSU email address (@wmsu.edu.ph)';
        }
        return '';
    }

    // Real-time ID validation
    identifierInput.addEventListener('input', async function() {
        formatIdentifier(this, roleSelect.value);
        const identifier = this.value.trim();
        const roleId = roleSelect.value;

        // Check format first
        const formatError = validateIdentifierFormat(identifier, roleId);
        if (formatError) {
            identifierError.textContent = formatError;
            this.classList.add('is-invalid');
            this.classList.remove('is-valid');
            return;
        }

        // Check for duplicates if format is correct
        const correctLength = (roleId == '3' && identifier.length === 10) || 
                            (roleId == '2' && identifier.length === 9);
        
        if (correctLength) {
            try {
                const response = await fetch('check_identifier.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `identifier=${encodeURIComponent(identifier)}&role_id=${encodeURIComponent(roleId)}`
                });
                
                const result = await response.json();
                identifierError.textContent = result.valid ? '' : result.message;
                this.classList.toggle('is-invalid', !result.valid);
                this.classList.toggle('is-valid', result.valid);
            } catch (error) {
                console.error('Error checking identifier:', error);
            }
        }
    });

    // Real-time email validation
    emailInput.addEventListener('input', async function() {
        const email = this.value.trim();
        
        // Check format first
        const formatError = validateEmailFormat(email);
        if (formatError) {
            emailError.textContent = formatError;
            this.classList.add('is-invalid');
            this.classList.remove('is-valid');
            return;
        }

        // Check for duplicates if format is correct
        if (email.toLowerCase().endsWith('@wmsu.edu.ph')) {
            try {
                const response = await fetch('check_email.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'email=' + encodeURIComponent(email)
                });
                
                const result = await response.json();
                emailError.textContent = result.valid ? '' : result.message;
                this.classList.toggle('is-invalid', !result.valid);
                this.classList.toggle('is-valid', result.valid);
            } catch (error) {
                console.error('Error checking email:', error);
            }
        }
    });
});
</script>
<?php include_once '../includes/_footer.php'; ?>
</body>

</html>