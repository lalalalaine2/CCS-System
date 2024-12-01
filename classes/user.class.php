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

    public $email = '';

    protected $db;

<<<<<<< HEAD
    private $role_id;
=======
    const STUDENT_ID_PATTERN = '/^\d{4}-\d{5}$/';
    const STAFF_ID_PATTERN = '/^\d{9}$/';
>>>>>>> 0170f39e89d0deabc3ca13d394751958af93c61e

    function __construct()
    {
        $this->db = new Database();
    }

    public function store()
    {
        try {
            $sql = "INSERT INTO user (
                identifier, 
                firstname, 
                middlename, 
                lastname, 
                email, 
                course_id, 
                department_id
            ) VALUES (
                :identifier,
                :firstname,
                :middlename,
                :lastname,
                :email,
                :course_id,
                :department_id
            )";

            $stmt = $this->db->connect()->prepare($sql);
            
            // Convert course/department to IDs based on role
            $course_id = ($this->role_id == 3) ? $this->course : null;
            $department_id = ($this->role_id == 2) ? $this->department : null;

            $stmt->bindParam(':identifier', $this->identifier);
            $stmt->bindParam(':firstname', $this->firstname);
            $stmt->bindParam(':middlename', $this->middlename);
            $stmt->bindParam(':lastname', $this->lastname);
            $stmt->bindParam(':email', $this->email);
            $stmt->bindParam(':course_id', $course_id);
            $stmt->bindParam(':department_id', $department_id);

            $stmt->execute();
            
            // Get the last inserted ID from the PDO connection
            return $this->db->connect()->lastInsertId();
            
        } catch (PDOException $e) {
            error_log("Error in User::store: " . $e->getMessage());
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

<<<<<<< HEAD
    public function getCourseOptions() {
        try {
            $sql = "SELECT course_name as name FROM course ORDER BY course_name";
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting course options: " . $e->getMessage());
            return [];
        }
    }

    public function getDepartmentOptions() {
        try {
            $sql = "SELECT department_name as name FROM department ORDER BY department_name";
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting department options: " . $e->getMessage());
            return [];
        }
    }

    public function identifierExists($identifier) {
        try {
            $sql = "SELECT COUNT(*) FROM user WHERE identifier = :identifier";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':identifier', $identifier);
            $stmt->execute();
            
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error checking identifier: " . $e->getMessage());
            return false;
        }
    }

    public function setRoleId($role_id) {
        $this->role_id = $role_id;
=======
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

            // Check if ID exists for the same role
            $sql = "SELECT u.identifier, a.role_id 
                   FROM user u 
                   JOIN account a ON u.id = a.user_id 
                   WHERE u.identifier = :identifier AND a.role_id = :role_id";

            $query = $this->db->connect()->prepare($sql);
            $query->bindParam(':identifier', $identifier);
            $query->bindParam(':role_id', $role_id);
            $query->execute();

            if ($query->fetch()) {
                $roleType = ($role_id == 3) ? 'student' : 'staff';
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
>>>>>>> 0170f39e89d0deabc3ca13d394751958af93c61e
    }

}
