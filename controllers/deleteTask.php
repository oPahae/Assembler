<?php
require_once '../lib/connect.php';
require_once '../models/Task.php';
$pdo = Database::connect();
error_reporting(E_ALL);
ini_set('display_errors', 0);
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!$data) {
            echo json_encode(["status" => "error", "message" => "Invalid JSON"]);
            exit;
        }

        $taskID = $data['taskID'] ?? null;
        if (!$taskID) {
            echo json_encode(["status" => "error", "message" => "Task ID is required."]);
            exit;
        }

        $taskModel = new Task($pdo);
        $result = $taskModel->delete($taskID);
        
        echo json_encode(["status" => "success", "message" => $result]);
    }
} catch (Exception $e) {
    error_log('Error deleting task: ' . $e->getMessage());
    echo json_encode(["status" => "error", "message" => "Server error: " . $e->getMessage()]);
}
?>
