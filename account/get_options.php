<?php
// Database connection
require_once '../classes/database.php';

header('Content-Type: application/json');

// Fetch role from query string
$role = isset($_GET['role']) ? intval($_GET['role']) : null;

// Initialize the options array
$options = [];

try {
    if ($role == 3) { // Role ID 3 for "Students"
        // Fetch courses from the database
        $query = "SELECT name FROM courses";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $options = $stmt->fetchAll(PDO::FETCH_COLUMN); // Fetch course names as an array
    } elseif ($role == 2) { // Role ID 2 for "Staff"
        // Fetch departments from the database
        $query = "SELECT name FROM departments";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $options = $stmt->fetchAll(PDO::FETCH_COLUMN); // Fetch department names as an array
    }
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
}

// Output options as JSON
echo json_encode($options);
exit;
?>
