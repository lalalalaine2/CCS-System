<?php

require_once 'database.class.php';

class Account
{
    public $id = '';

    public $user_id = null; // Reference to the user (student, staff, admin)
    public $username = '';

    public $password = '';

    public $role_id = null;

    public $status = '';


    protected $db;

    function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Store a new account in the database
     * 
     * @param int $user_id The ID of the user (student, staff, admin)
     * @return bool True on success, False on failure
     */
    public function store($user_id)
    {
        // Check if username already exists
        if ($this->usernameExist($this->username)) {
            throw new Exception("Username already exists.");
        }

        // Hash the password
        $hashedPassword = password_hash($this->password, PASSWORD_DEFAULT);

        // Insert account into the database
        $sql = "INSERT INTO account ( user_id, username, password, role_id) 
                VALUES (:user_id, :username, :password, :role_id)";
        $query = $this->db->connect()->prepare($sql);

        // Bind parameters
        $query->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $query->bindParam(':username', $this->username);
        $query->bindParam(':password', $hashedPassword);
        $query->bindParam(':role_id', $this->role_id);

        // Execute query
        return $query->execute();
    }

    /**
     * Login function to authenticate a user
     *
     * @param string $email_or_username The email or username
     * @param string $password The raw password entered by the user
     * @return bool True if authentication succeeds, False otherwise
     */
    public function login($email_or_username, $password)
    {
        $sql = "SELECT * FROM account WHERE username = :username OR user_id IN (
                    SELECT id FROM user WHERE email = :email
                )";
        $query = $this->db->connect()->prepare($sql);

        $query->bindParam(':username', $email_or_username, PDO::PARAM_STR);
        $query->bindParam(':email', $email_or_username, PDO::PARAM_STR);

        $query->execute();

        if ($result = $query->fetch(PDO::FETCH_ASSOC)) {
            // Verify the password
            if (password_verify($password, $result['password'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Fetch user data by username or email
     *
     * @param string $email_or_username The email or username
     * @return array|false User data on success, False otherwise
     */
    public function fetch($email_or_username)
    {
        $sql = "SELECT account.*
            FROM account 
            JOIN user ON account.user_id = user.id 
            WHERE account.username = :username OR user.email = :email";

        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':username', $email_or_username, PDO::PARAM_STR);
        $query->bindParam(':email', $email_or_username, PDO::PARAM_STR);

        $query->execute();

        return $query->fetch(PDO::FETCH_ASSOC);
    }


    /**
     * Check if a username already exists
     */
    function usernameExist($username)
    {
        $sql = "SELECT COUNT(*) FROM account WHERE username = :username";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':username', $username);
        $query->execute();
        return $query->fetchColumn() > 0;
    }

    /**
     * Check if an email already exists
     */
    function emailExist($email)
    {
        $sql = "SELECT COUNT(*) FROM user WHERE email = :email";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':email', $email);
        $query->execute();
        return $query->fetchColumn() > 0;
    }

    /**
     * Fetch account details for a specific account
     *
     * @param string|int $identifier The username or ID of the account
     * @return array|false Account details on success, False otherwise
     */
    public function getAccountDetails($id)
    {
        try {
            // Query to fetch account details by ID or username
            $sql = "SELECT 
                        a.id AS account_id,
                        a.username,
                        a.role_id,
                        a.status,
                        a.created_at,
                        u.id AS user_id,
                        u.identifier,
                        u.firstname,
                        u.middlename,
                        u.lastname,
                        u.email,
                        u.course,
                        u.department
                    FROM account a
                    LEFT JOIN user u ON a.user_id = u.id
                    WHERE a.id = :id";

            // Prepare the query
            $query = $this->db->connect()->prepare($sql);

            // Bind parameters
            $query->bindParam(':id', $id, PDO::PARAM_INT);

            // Execute the query
            $query->execute();

            // Fetch the account details
            return $query->fetch(PDO::FETCH_ASSOC); // Return a single row as an associative array
        } catch (PDOException $e) {
            // Log the error
            error_log("Failed to fetch account details: " . $e->getMessage());
            return false; // Return false on error
        }
    }

    public function getAccounts() {
        try {
            // Query to join user and account tables
            $sql = "SELECT 
                    a.id,
                    u.identifier,
                    u.firstname,
                    u.middlename,
                    u.lastname,
                    u.email,
                    u.course,
                    u.department,
                    a.username,
                    a.role_id,
                    a.status,
                    a.created_at
                FROM user u
                LEFT JOIN account a ON u.id = a.user_id
                      ORDER BY a.created_at ASC";

            // Prepare and execute the query
            $query = $this->db->connect()->prepare($sql);
            $query->execute();

            // Fetch all user and account details
            return $query->fetchAll(PDO::FETCH_ASSOC); // Return all fetched rows as an array
        } catch (PDOException $e) {
            // Log the error
            error_log("Failed to fetch user-account details: " . $e->getMessage());
            return false; // Return false if there is an error
        }
    }

    public function updateAccount($id, $data) {
        try {
            // Initialize course and department as null
            $course = null;
            $department = null;

            // Determine which field to update based on role_id
            if (isset($data['role_id'])) {
                if ($data['role_id'] == 3) {
                    $course = $data['other']; // Assign "other" to course for students
                } elseif ($data['role_id'] == 2) {
                    $department = $data['other']; // Assign "other" to department for staff
                }
            }

            $sql = "UPDATE account a
                JOIN user u ON a.user_id = u.id
                SET 
                    u.identifier = :identifier,
                    u.firstname = :firstname,
                    u.middlename = :middlename,
                    u.lastname = :lastname,
                    u.email = :email,
                    u.course = :course,
                    u.department = :department,
                    a.username = :username,
                    a.status = :status
                WHERE a.id = :id";

            $query = $this->db->connect()->prepare($sql);

            // Bind parameters
            $query->bindParam(':identifier', $data['identifier']);
            $query->bindParam(':firstname', $data['firstname']);
            $query->bindParam(':middlename', $data['middlename']);
            $query->bindParam(':lastname', $data['lastname']);
            $query->bindParam(':email', $data['email']);
            $query->bindParam(':course', $course); // Will be null if not updating course
            $query->bindParam(':department', $department); // Will be null if not updating department
            $query->bindParam(':username', $data['username']);
            $query->bindParam(':status', $data['status']);
            $query->bindParam(':id', $id, PDO::PARAM_INT);

            // Execute the query
            return $query->execute();
        } catch (PDOException $e) {
            error_log("Failed to update account: " . $e->getMessage());
            return false;
        }
    }


    public function deleteAccount($id) {
        try {
            // SQL query to delete the account and its related user
            $sql = "DELETE a, u 
                FROM account a
                JOIN user u ON a.user_id = u.id
                WHERE a.id = :id";

            $query = $this->db->connect()->prepare($sql);

            // Bind the account ID
            $query->bindParam(':id', $id, PDO::PARAM_INT);

            // Execute the query
            return $query->execute();
        } catch (PDOException $e) {
            error_log("Failed to delete account: " . $e->getMessage());
            return false;
        }
    }

    public function add($data) {
        try {
            // Validate passwords match
            if ($data['password'] !== $data['confirm_password']) {
                throw new Exception("Passwords do not match");
            }

            // Check if username already exists
            $check_username = "SELECT id FROM accounts WHERE username = ?";
            $stmt = $this->db->prepare($check_username);
            $stmt->execute([$data['username']]);
            if ($stmt->fetch()) {
                throw new Exception("Username already exists");
            }

            // Check if email already exists
            $check_email = "SELECT id FROM accounts WHERE email = ?";
            $stmt = $this->db->prepare($check_email);
            $stmt->execute([$data['email']]);
            if ($stmt->fetch()) {
                throw new Exception("Email already exists");
            }

            // Hash password
            $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);

            // Prepare SQL for insertion
            $sql = "INSERT INTO accounts (username, email, password, role_id) VALUES (?, ?, ?, ?)";
            
            // Execute with prepared statements
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['username'],
                $data['email'],
                $hashed_password,
                $data['role_id']
            ]);

            return $result;
        } catch (Exception $e) {
            // You might want to log the error here
            error_log($e->getMessage());
            throw $e;  // Re-throw the exception to be caught by the calling code
        }
    }

    public function emailExists($email) {
        $sql = "SELECT id FROM accounts WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch() !== false;
    }

    public function saveResetToken($email, $token) {
        $sql = "UPDATE accounts SET reset_token = ?, reset_token_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR) 
                WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$token, $email]);
    }

    public function resetPassword($username, $new_password) {
        try {
            // First check if username exists
            $check_sql = "SELECT id FROM account WHERE username = :username";
            $check_stmt = $this->db->connect()->prepare($check_sql);
            $check_stmt->bindParam(':username', $username);
            $check_stmt->execute();
            
            if ($check_stmt->fetch()) {
                // Username exists, proceed with password update
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                $update_sql = "UPDATE account SET password = :password WHERE username = :username";
                $update_stmt = $this->db->connect()->prepare($update_sql);
                $update_stmt->bindParam(':password', $hashed_password);
                $update_stmt->bindParam(':username', $username);
                return $update_stmt->execute();
            }
            
            return false; // Username not found
            
        } catch (Exception $e) {
            error_log("Reset password error: " . $e->getMessage());
            throw $e;
        }
    }

}
