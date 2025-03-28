<?php
require_once '../lib/connect.php';
require_once '../models/Project.php';
header('Content-Type: application/json');

try {  
    $input = file_get_contents("php://input");
    $data = json_decode($input, true);
    $projectID = $data['projectID'];
    $projectModel = new Project();
    $users = $projectModel->getUsersByProject($projectID);

    if ($users) {
        echo json_encode(["status" => "success", "users" => $users]);
    } else {
        echo json_encode(["status" => "error", "message" => "No users found for this project."]);
    }
    
} catch (Exception $e) {
    error_log('Error fetching users: ' . $e->getMessage());
    echo json_encode(["status" => "error", "message" => "Server error: " . $e->getMessage()]);
}
?>
