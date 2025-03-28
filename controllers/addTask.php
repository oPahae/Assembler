<?php
require_once '../lib/connect.php';
require_once '../models/Task.php';
$pdo = Database::connect();
error_reporting(E_ALL);
ini_set('display_errors', 0);
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!$data) {
            echo json_encode(["status" => "error", "message" => "Invalid JSON"]);
            exit;
        }

        $projectID = $data['projectID'] ?? null;
        $userID = $data['userID'] ?? '';
        $name = $data['name'] ?? '';
        $deadline = $data['deadline'] ?? '';
        $descr = $data['descr'] ?? '';
        if (!$projectID || !$name) {
            echo json_encode(["status" => "error", "message" => "Project ID and name are required."]);
            exit;
        }

        $taskModel = new Task($pdo);
        $result = $taskModel->add($projectID, $userID, $name, $deadline, $descr);
        
        echo json_encode($result);
    }
} catch (Exception $e) {
    error_log('Error adding task: ' . $e->getMessage());
    echo json_encode(["status" => "error", "message" => "Server error: " . $e->getMessage()]);
}
?>
