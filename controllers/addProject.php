<?php
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
    $name = $data['name'] ?? null;
    $deadline = $data['deadline'] ?? null;
    $maxMembers = $data['maxMembers'] ?? null;
    $slogan = $data['slogan'] ?? null;
    $type = $data['type'] ?? null;

    // Validation des champs
    if (empty($name) || empty($deadline) || empty($maxMembers) || empty($slogan) || empty($type)) {
        throw new Exception("Tous les champs sont obligatoires.");
    }

    // Vérifier que maxMembers est un entier positif
    if (!is_numeric($maxMembers) || $maxMembers <= 0 || !is_int($maxMembers + 0)) {
        throw new Exception("Le nombre maximum de membres doit être un entier positif.");
    }

    // Vérifier que la date limite est valide
    if (!strtotime($deadline)) {
        throw new Exception("La date limite est invalide.");
    }

    // Connexion à la base de données
    $pdo = Database::connect();

    // Créer une instance de projectModel
    $projectModel = new Project($pdo);

    // Créer le projet
    $projectId = $projectModel->create($userID, $name, $deadline, $maxMembers, $slogan, $type);

    // Retourner une réponse JSON en cas de succès
    echo json_encode([
        'status' => 'success',
        'message' => 'Projet créé avec succès',
        'projectId' => $projectId
    ]);
} catch (Exception $e) {
    // Retourner une réponse JSON en cas d'erreur
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}