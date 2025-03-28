<?php
require_once '../lib/connect.php';
require_once '../models/Project.php';
require_once '../models/User.php';

header('Content-Type: application/json');
$pdo = Database::connect();
error_reporting(E_ALL);
ini_set('display_errors', 0);
session_start();

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['userID']) && isset($data['projectID'])) {
    $userID = $data['userID'];
    $projectID = $data['projectID'];

    try {
        $stmt = $pdo->prepare("DELETE FROM uspr WHERE userID = ? AND projectID = ?");//nsupprimiw lrelation us proj
        $stmt->bindValue(1, $userID, PDO::PARAM_INT);//nliwi l 1 para m3a l 1er ?
        $stmt->bindValue(2, $projectID, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Unable to leave the project.']);
        }
    } catch (PDOException $e) {
        // Handle any errors during the query
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid parameters.']);
}
?>
