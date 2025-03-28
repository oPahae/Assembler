<?php
header('Content-Type: application/json');

require_once "../models/Project.php";
require_once "../lib/connect.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
//nchekiw kulchi wach kayn
    if (!isset($data['projectID']) || empty($data['projectID'])) {
        throw new Exception("Project ID is required");
    }

    if (!isset($data['name']) || empty($data['name'])) {
        throw new Exception("Project name is required");
    }

    if (!isset($data['maxMembers']) || empty($data['maxMembers'])) {
        throw new Exception("Max members is required");
    }

    if (!isset($data['slogan']) || empty($data['slogan'])) {
        throw new Exception("Slogan is required");
    }

    $projectID = filter_var($data['projectID'], FILTER_VALIDATE_INT);
    $name = htmlspecialchars(trim($data['name']));
    $maxMembers = filter_var($data['maxMembers'], FILTER_VALIDATE_INT);
    $slogan = htmlspecialchars(trim($data['slogan']));

    $project = new Project();

    $updateResult = $project->updateProject($projectID, $name, $maxMembers, $slogan);

    if ($updateResult) {
        $response = [
            'status' => 'success',
            'message' => 'Project updated successfully'
        ];
        echo json_encode($response);
    } else {
        throw new Exception("Failed to update the project");
    }

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
?>
