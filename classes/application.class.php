<?php
require_once 'database.php';

class Application {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getPendingApplications() {
        $sql = "SELECT a.*, s.firstname, s.lastname, s.student_id, c.course_name as course 
                FROM applications a 
                JOIN students s ON a.student_id = s.id 
                JOIN courses c ON s.course_id = c.id 
                WHERE a.status = 'pending' 
                ORDER BY a.date_applied DESC";
        
        return $this->db->getRows($sql);
    }

    public function approveApplication($id) {
        $sql = "UPDATE applications SET status = 'approved', updated_at = NOW() WHERE id = ?";
        return $this->db->update($sql, [$id]);
    }

    public function rejectApplication($id) {
        $sql = "UPDATE applications SET status = 'rejected', updated_at = NOW() WHERE id = ?";
        return $this->db->update($sql, [$id]);
    }

    public function getApplicationDetails($id) {
        $sql = "SELECT a.*, s.*, c.course_name as course 
                FROM applications a 
                JOIN students s ON a.student_id = s.id 
                JOIN courses c ON s.course_id = c.id 
                WHERE a.id = ?";
        
        return $this->db->getRow($sql, [$id]);
    }
}