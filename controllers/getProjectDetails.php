<?php
require_once '../lib/connect.php'; 
require_once '../models/Project.php'; 
$input = file_get_contents("php://input");
$data = json_decode($input, true);

$projectID = isset($data['projectID']) ? $data['projectID'] : null;
if (!isset($data['projectID'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Aucun ID de projet fourni.'
    ]);
    exit;
}

$projectID = $data['projectID'];

try {
    $project = new Project();
    $result = $project->getProjectDetails($projectID);

    if ($result['status'] === 'success') {
        echo json_encode([
            'status' => 'success',
            'project' => $result['project'] 
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => $result['message']
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Erreur : ' . $e->getMessage()
    ]);
}
