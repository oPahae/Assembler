<?php
require_once '../lib/connect.php';
require_once '../models/Subtask.php';
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

        $taskID = $data['taskID'] ?? null;
        $name = $data['name'] ?? '';
        if (!$taskID || !$name) {
            echo json_encode(["status" => "error", "message" => "Missing infos"]);
            exit;
        }

        $taskModel = new Subtask($pdo);
        $result = $taskModel->add($taskID, $name);
        
        echo json_encode($result);
    }
} catch (Exception $e) {
    error_log('Error adding task: ' . $e->getMessage());
    echo json_encode(["status" => "error", "message" => "Server error: " . $e->getMessage()]);
}
?>
