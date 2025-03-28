<?php
require_once '../lib/connect.php';
require_once '../models/User.php';

$pdo = Database::connect();
error_reporting(E_ALL);
ini_set('display_errors', 0);
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"));

if (isset($data->projectID) && isset($data->userID)) {
    $projectID = (int)$data->projectID;  
    $userID = (int)$data->userID;  

    $query = "DELETE FROM uspr WHERE projectID = ? AND userID = ?";
    $stmt = $pdo->prepare($query);

    try {
        $stmt->execute([$projectID, $userID]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Utilisateur retiré du projet avec succès']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Utilisateur non trouvé dans ce projet']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Erreur lors de la suppression de l\'utilisateur : ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Paramètres manquants : projectID ou userID']);
}
?>
