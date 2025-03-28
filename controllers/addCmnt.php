<?php
require_once '../lib/connect.php';
require_once '../models/Cmnt.php';
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
        $userID = $data['userID'] ?? null;
        $content = $data['content'] ?? '';
        if (!$taskID || !$userID || !$content) {
            echo json_encode(["status" => "error", "message" => "Missing infos"]);
            exit;
        }

        $cmntModel = new Cmnt($pdo);
        $result = $cmntModel->add($taskID, $userID, $content);
        
        echo json_encode($result);
    }
} catch (Exception $e) {
    error_log('Error adding task: ' . $e->getMessage());
    echo json_encode(["status" => "error", "message" => "Server error: " . $e->getMessage()]);
}
?>
