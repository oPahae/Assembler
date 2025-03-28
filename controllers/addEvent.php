<?php
require_once '../lib/connect.php';
require_once '../models/Event.php';
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
        $name = $data['name'] ?? null;
        $deadline = $data['deadline'] ?? '';
        if (!$projectID || !$name || !$deadline) {
            echo json_encode(["status" => "error", "message" => "Missing infos"]);
            exit;
        }

        $eventModel = new Event($pdo);
        $result = $eventModel->add($projectID, $name, $deadline);
        
        echo json_encode($result);
    }
} catch (Exception $e) {
    error_log('Error adding task: ' . $e->getMessage());
    echo json_encode(["status" => "error", "message" => "Server error: " . $e->getMessage()]);
}
?>
