<?php
require_once 'database.class.php';

class Department {
    protected $db;

    function __construct() {
        $this->db = new Database();
    }

    public function getAllDepartments() {
        try {
            error_log("Fetching departments...");
            
            $sql = "SELECT * FROM department";
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("Departments found: " . print_r($results, true));
            
            return $results;
        } catch (PDOException $e) {
            error_log("Failed to get departments: " . $e->getMessage());
            return [];
        }
    }
} 