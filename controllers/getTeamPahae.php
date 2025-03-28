<?php
require_once '../lib/connect.php';
require_once '../models/Project.php';
$pdo = Database::connect();
error_reporting(E_ALL);
ini_set('display_errors', 0);
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $projectID = $_GET['projectID'] ?? null;
        if (!$projectID) {
            echo json_encode(["status" => "error", "message" => "Project ID is required."]);
            exit;
        }

        $projectModel = new Project($pdo);
        $users = $projectModel->getUsersByProject($projectID);
        
        echo json_encode(["status" => "success", "users" => $users]);
    }
} catch (Exception $e) {
    error_log('Error fetching projects: ' . $e->getMessage());
    echo json_encode(["status" => "error", "message" => "Server error: " . $e->getMessage()]);
}
?>