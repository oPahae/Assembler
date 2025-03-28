<?php
// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

// Inclure les fichiers nécessaires
require_once '../lib/connect.php';
require_once '../models/Project.php';

try {
    // Récupérer les données du formulaire
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    // Valider les données
    if (empty($data)) {
        throw new Exception("Données manquantes.");
    }

    $userID = $data['userID'] ?? null;
    $code = $data['code'] ?? null;
    

    

    // Connexion à la base de données
    $pdo = Database::connect();

    // Créer une instance de Project
    $projectModel = new Project($pdo);

    // Rejoindre le projet
    $projectId = $projectModel->join($userID,$code);

    // Retourner une réponse JSON en cas de succès
    echo json_encode([
        'status' => 'success',
        'message' => 'Projet rejoint avec succès.',
        'projectId' => $projectId
    ]);
} catch (Exception $e) {
    // Retourner une réponse JSON en cas d'erreur
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} finally {
    // Fermer la connexion à la base de données
    if (isset($pdo)) {
        $pdo = null;
    }
}
?>