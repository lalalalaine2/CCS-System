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

    private $role_id;

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
    }

}
