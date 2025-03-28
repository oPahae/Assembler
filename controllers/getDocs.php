<?php
header('Content-Type: application/json');
require_once '../models/Doc.php';

// Check if request is GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(["error" => "Invalid request method"]);
    exit;
}

// Check if taskID is provided
if (!isset($_GET['taskID'])) {
    echo json_encode(["error" => "Missing taskID parameter"]);
    exit;
}

$taskID = intval($_GET['taskID']);

// Get documents
$docs = Doc::get($taskID);

// Modify the response to not include the binary content in the result
$modifiedDocs = array_map(function($doc) {
    // Remove binary content from response
    if (isset($doc['content'])) {
        unset($doc['content']);
    }
    return $doc;
}, $docs);

echo json_encode($modifiedDocs);