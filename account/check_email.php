<?php
require_once '../classes/user.class.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $userObj = new User();
    $result = $userObj->validateEmail($_POST['email']);
    
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
} 