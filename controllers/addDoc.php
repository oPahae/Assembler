<?php
header('Content-Type: application/json');
require_once '../models/Doc.php';

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["error" => "Invalid request method"]);
    exit;
}

// Check if file and taskID are provided
if (!isset($_FILES['file']) || !isset($_POST['taskID'])) {
    echo json_encode(["error" => "Missing infos"]);
    exit;
}

$file = $_FILES['file'];
$taskID = intval($_POST['taskID']);

// Validate file
if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(["error" => "File upload error: " . $file['error']]);
    exit;
}

// Get file details
$name = $file['name'];
$size = $file['size'] / (1024 * 1024); // Convert to MB
$content = file_get_contents($file['tmp_name']);

if (!$content) {
    echo json_encode(["error" => "Failed to read file content"]);
    exit;
}

// Add document to database
$result = Doc::add($name, $content, $size, $taskID);

echo json_encode($result);
?>