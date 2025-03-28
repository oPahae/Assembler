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
require_once '../models/Project.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception("Invalid request method");
    }

    $projectID = $_GET['projectID'] ?? null;
    if (!$projectID) {
        throw new Exception("projectID is required");
    }

    $projectModel = new Project();
    $result = $projectModel->getID($projectID);
    
    if ($result['status'] !== 'success') {
        throw new Exception($result['message'] ?? "Project not found or error retrieving project");
    }

    $projectUsers = $projectModel->getUsers($projectID);
    $response = $result['data'];
    $response['users'] = $projectUsers['status'] === 'success' ? $projectUsers['users'] : [];
    $response['status'] = 'success';

    echo json_encode($response);
    exit;

} catch (Exception $e) {
    error_log('Error in getProjectInfos: ' . $e->getMessage());
    echo json_encode([
        "status" => "error", 
        "message" => $e->getMessage()
    ]);
    exit;
}
?>