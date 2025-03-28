<?php
header('Content-Type: application/json');

require_once "../models/Subtask.php";
require_once "../lib/connect.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!isset($data['subtaskID']) || empty($data['subtaskID'])) {
        throw new Exception("subtaskID is required");
    }

    if (!isset($data['status']) || empty($data['status'])) {
        throw new Exception("Task status is required");
    }

    $subtaskID = filter_var($data['subtaskID'], FILTER_VALIDATE_INT);
    $status = htmlspecialchars(trim($data['status']));

    $subtask = new Subtask();

    $updateResult = $subtask->update($subtaskID, $status);

    $response = [
        'status' => 'success',
        'message' => 'Status updated successfully'
    ];

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}