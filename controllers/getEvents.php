<?php
require_once '../lib/connect.php';
require_once '../models/Event.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!$data || !isset($data['projectID'])) {
            echo json_encode(["status" => "error", "message" => "Invalid request. ProjectID is required."]);
            exit;
        }

        $projectID = $data['projectID'];

        $eventModel = new Event();
        $events = $eventModel->get($projectID);

        echo json_encode(["status" => "success", "events" => $events]);
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid request method."]);
    }
} catch (Exception $e) {
    error_log('Error fetching messages: ' . $e->getMessage());
    echo json_encode(["status" => "error", "message" => "Server error: " . $e->getMessage()]);
}
?>