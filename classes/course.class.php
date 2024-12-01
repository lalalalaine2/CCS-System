<?php
require_once 'database.class.php';

class Course {
    protected $db;

    function __construct() {
        $this->db = new Database();
    }

    public function getAllCourses() {
        try {
            error_log("Fetching courses...");
            
            $sql = "SELECT * FROM course";
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("Courses found: " . print_r($results, true));
            
            return $results;
        } catch (PDOException $e) {
            error_log("Failed to get courses: " . $e->getMessage());
            return [];
        }
    }
} 