<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../auth/");
    exit();
}

require_once '../models/User.php';
require_once '../lib/connect.php';

try {
    $pdo = Database::connect();
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(["error" => "Erreur de connexion à la base de données : " . $e->getMessage()]);
    exit();
}

$userID = $_SESSION['id'];
$userModel = new User($pdo);

// radi ndiro f had etape kifach na5do data jason lijaya mn frontend
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);

if (!$data) {
    header('Content-Type: application/json');
    echo json_encode(["error" => "Données JSON invalides."]);
    exit();
}

try {
    // radi ndiro wahd validation pour data lijat
    $oldPassword = $data['oldPassword'] ?? null;
    $newPassword = $data['newPassword'] ?? null;

    if (empty($oldPassword) || empty($newPassword)) {
        throw new Exception("Tous les champs sont obligatoires.");
    }

    
    // radi nricipirriw password likayn deja 3nd user 
    $userData = $userModel->getById($userID);

    if (!$userData) {
        throw new Exception("Utilisateur non trouvé.");
    }

    
    // Radi nchofo wach password l9dim Shih awela la
    if (!password_verify($oldPassword, $userData['password'])) {
        throw new Exception("L'ancien mot de passe est incorrect.");
    }

    
    // radi nhachiw new Password o 3aRfin 3alach :)
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    
    // 5asna n5liw maisajoouuur l password f bdd
    $updateResult = $userModel->updatePassword($userID, $hashedPassword);

    if (!$updateResult) {
        throw new Exception("Échec de la mise à jour du mot de passe.");
    }

    
    // jawab likntsnaw o chehal Zwinnn :)
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'message' => 'Mot de passe mis à jour avec succès.',
    ]);
} catch (Exception $e) {
    // Mohim henna ila kan "dna chi errrrrooooorrrr :(  MAIS 3DIIII hadi HIYA HAYATTTTTTT NDHKKO O SAFIIII :)
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}