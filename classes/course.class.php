<?php
require_once 'database.class.php';

class Course {
    protected $db;

    function __construct() {
        $this->db = new Database();
    }

    public function getAllCourses() {
        try {
            $sql = "SELECT * FROM courses ORDER BY course_name";
            $query = $this->db->connect()->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching courses: " . $e->getMessage());
            return [];
        }
    }
} 