<?php

require_once 'database.class.php';

class Role
{
    public $id = '';
    public $name = '';

    protected $db;

    function __construct()
    {
        $this->db = new Database();
    }

    // Fetch and render all roles
    function renderAllRoles()
    {
        $sql = "SELECT * FROM role;"; // Query to get all roles
        $query = $this->db->connect()->prepare($sql);

        $data = null;

        // Execute query and fetch all roles
        if ($query->execute()) {
            $data = $query->fetchAll(PDO::FETCH_ASSOC);
        }

        return $data; // Return roles as an associative array
    }
}
