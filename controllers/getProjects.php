<?php
require_once '../lib/connect.php';
require_once '../models/User.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!$data || !isset($data['userID'])) {
            echo json_encode(["status" => "error", "message" => "Invalid request. userID is required."]);
            exit;
        }

        $userID = $data['userID'];

        $userModel = new User();
        $projects = $userModel->getProjects($userID);

        echo json_encode(["status" => "success", "projects" => $projects]);
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid request method."]);
    }
} catch (Exception $e) {
    error_log('Error fetching projects: ' . $e->getMessage());
    echo json_encode(["status" => "error", "message" => "Server error: " . $e->getMessage()]);
}
?>