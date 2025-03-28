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

$userID = $_GET['userID'] ?? $_SESSION['user_id'];

if (!$userID) {
    header('Content-Type: application/json');
    echo json_encode(["error" => "ID utilisateur manquant."]);
    exit();
}

$userModel = new User($pdo);

try {
    $profileData = $userModel->getById($userID);

    if (!$profileData) {
        header('Content-Type: application/json');
        echo json_encode(["error" => "Aucune donnée trouvée pour cet utilisateur."]);
        exit();
    }
    //print_r($profileData);
    header('Content-Type: application/json');
    echo json_encode($profileData);
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(["error" => "Une erreur s'est produite : " . $e->getMessage()]);
}
?>