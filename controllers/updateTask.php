<?php
header('Content-Type: application/json');

require_once "../models/Task.php";
require_once "../lib/connect.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!isset($data['taskID']) || empty($data['taskID'])) {
        throw new Exception("Task ID is required");
    }

    if (!isset($data['name']) || empty($data['name'])) {
        throw new Exception("Task name is required");
    }

    if (!isset($data['deadline']) || empty($data['deadline'])) {
        throw new Exception("Deadline is required");
    }

    $taskID = filter_var($data['taskID'], FILTER_VALIDATE_INT);
    $name = htmlspecialchars(trim($data['name']));
    $deadline = date('Y-m-d', strtotime($data['deadline']));
    $status = htmlspecialchars(trim($data['status']));
    $descr = isset($data['descr']) ? htmlspecialchars(trim($data['descr'])) : null;
    $userID = isset($data['userID']) ? htmlspecialchars(trim($data['userID'])) : null;

    $task = new Task();

    $updateResult = $task->update($taskID, $name, $deadline, $status, $userID, $descr);

    $response = [
        'status' => 'success',
        'message' => 'Task updated successfully'
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