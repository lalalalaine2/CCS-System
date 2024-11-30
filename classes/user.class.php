<?php

require_once 'database.class.php';

class User
{
    public $identifier = '';
    public $firstname = '';
    public $middlename = '';
    public $lastname = '';
    public $course = '';
    public $department = '';
    public $year_level = '';

    public $email = '';

    protected $db;

    const STUDENT_ID_PATTERN = '/^\d{4}-\d{5}$/';
    const STAFF_ID_PATTERN = '/^\d{9}$/';
    const YEAR_LEVELS = [
        'First Year',
        'Second Year',
        'Third Year',
        'Fourth Year'
    ];

    function __construct()
    {
        $this->db = new Database();
    }

    public function store()
    {
        try {
            $sql = "INSERT INTO user (identifier, firstname, middlename, lastname, email, course, department, year_level) 
                    VALUES (:identifier, :firstname, :middlename, :lastname, :email, :course, :department, :year_level)";

            $query = $this->db->connect()->prepare($sql);

            $query->bindParam(':identifier', $this->identifier, PDO::PARAM_STR);
            $query->bindParam(':firstname', $this->firstname, PDO::PARAM_STR);
            $query->bindParam(':middlename', $this->middlename, PDO::PARAM_STR);
            $query->bindParam(':lastname', $this->lastname, PDO::PARAM_STR);
            $query->bindParam(':email', $this->email, PDO::PARAM_STR);
            $query->bindParam(':course', $this->course, PDO::PARAM_STR);
            $query->bindParam(':department', $this->department, PDO::PARAM_STR);
            $query->bindParam(':year_level', $this->year_level);

            if ($query->execute()) {
                // Return the ID of the newly inserted record
                return $this->db->connect()->lastInsertId();
            } else {
                // Log any execution errors
                error_log("Failed to execute insert query: " . implode(", ", $query->errorInfo()));
                return false;
            }
        } catch (PDOException $e) {
            // Log the error
            error_log("Failed to store user: " . $e->getMessage());
            return false;
        }
    }

    public function getUserDetails($id) {
        try {
            $sql = "SELECT * FROM user WHERE id = :id";


            $query = $this->db->connect()->prepare($sql);
            $query->bindParam(':id', $id, PDO::PARAM_INT);
            $query->execute();
            // Fetch the user details
            return $query->fetch(PDO::FETCH_ASSOC); // Return the fetched user details
        } catch(PDOException $e) {
            // Log the error
            error_log("Failed to fetch user details: " . $e->getMessage());
            return false; // Return false if there is an error
        }
    }

    public function validateIdentifier($identifier, $role_id) {
        try {
            // First check format based on role
            if ($role_id == 3) { // Student
                if (!preg_match(self::STUDENT_ID_PATTERN, $identifier)) {
                    return ['valid' => false, 'message' => 'Student ID must be in 0000-00000 format'];
                }
            } else if ($role_id == 2) { // Staff
                if (!preg_match(self::STAFF_ID_PATTERN, $identifier)) {
                    return ['valid' => false, 'message' => 'Staff ID must be 9 digits without spaces or dashes'];
                }
            }

            // Check if ID exists for any role
            $sql = "SELECT u.identifier, a.role_id 
                   FROM user u 
                   JOIN account a ON u.id = a.user_id 
                   WHERE u.identifier = :identifier";

            $query = $this->db->connect()->prepare($sql);
            $query->bindParam(':identifier', $identifier);
            $query->execute();

            if ($result = $query->fetch()) {
                $roleType = ($result['role_id'] == 3) ? 'student' : 'staff';
                return ['valid' => false, 'message' => "This ID is already registered as a {$roleType}"];
            }

            return ['valid' => true, 'message' => ''];
        } catch (PDOException $e) {
            error_log("ID validation error: " . $e->getMessage());
            return ['valid' => false, 'message' => 'An error occurred during validation'];
        }
    }

    public function validateEmail($email) {
        try {
            // Check email format and domain
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ['valid' => false, 'message' => 'Please enter a valid email address'];
            }

            // Check if it's a WMSU email
            if (!preg_match('/@wmsu\.edu\.ph$/', $email)) {
                return ['valid' => false, 'message' => 'Please use a valid WMSU email address (@wmsu.edu.ph)'];
            }

            // Check if email already exists
            $sql = "SELECT email FROM user WHERE email = :email";
            $query = $this->db->connect()->prepare($sql);
            $query->bindParam(':email', $email);
            $query->execute();

            if ($query->fetch()) {
                return ['valid' => false, 'message' => 'This email is already registered'];
            }

            return ['valid' => true, 'message' => ''];
        } catch (PDOException $e) {
            error_log("Email validation error: " . $e->getMessage());
            return ['valid' => false, 'message' => 'An error occurred during validation'];
        }
    }

}
