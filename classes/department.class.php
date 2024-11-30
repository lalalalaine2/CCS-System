<?php
require_once 'database.class.php';

class Department {
    protected $db;

    function __construct() {
        $this->db = new Database();
    }

    public function getAllDepartments() {
        try {
            $sql = "SELECT * FROM departments ORDER BY department_name";
            $query = $this->db->connect()->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching departments: " . $e->getMessage());
            return [];
        }
    }
} 