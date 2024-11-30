<?php
require_once '../classes/user.class.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['identifier']) && isset($_POST['role_id'])) {
    $userObj = new User();
    $result = $userObj->validateIdentifier($_POST['identifier'], $_POST['role_id']);
    
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
} 