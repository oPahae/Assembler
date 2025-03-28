<?php
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL);

function customErrorHandler($errno, $errstr, $errfile, $errline) {
    error_log("Error [$errno]: $errstr in $errfile on line $errline");
    
    echo json_encode([
        "status" => "error", 
        "message" => "An internal error occurred",
        "error_details" => "$errstr (File: $errfile, Line: $errline)"
    ]);
    exit;
}

set_error_handler('customErrorHandler');

require_once '../lib/connect.php';
require_once '../models/Task.php';
require_once '../models/Project.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception("Invalid request method");
    }

    $taskID = $_GET['taskID'] ?? null;
    if (!$taskID) {
        throw new Exception("Task ID is required");
    }

    $pdo = Database::connect();

    $taskModel = new Task($pdo);
    $projectModel = new Project();

    $result = $taskModel->getID($taskID);
    
    if ($result['status'] !== 'success') {
        throw new Exception("Task not found or error retrieving task");
    }

    $projectID = $result['task']['projectID'];
    $projectUsers = $projectModel->getUsers($projectID, $taskID);
    $result['availableUsers'] = $projectUsers['status'] === 'success' ? $projectUsers['users'] : [];

    echo json_encode($result);
    exit;

} catch (Exception $e) {
    error_log('Error in getTaskInfos: ' . $e->getMessage());
    echo json_encode([
        "status" => "error", 
        "message" => $e->getMessage()
    ]);
    exit;
}
?>