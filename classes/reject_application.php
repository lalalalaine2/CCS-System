<?php
session_start();
require_once('../classes/application.class.php');

// Check staff authentication
if (!isset($_SESSION['account']) || $_SESSION['account']['role_id'] != 2) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (isset($_GET['id'])) {
    $applicationObj = new Application();
    $result = $applicationObj->rejectApplication($_GET['id']);
    
    echo json_encode(['success' => $result]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}