<?php
require_once '../lib/connect.php';
require_once '../models/User.php';
$pdo = Database::connect();
error_reporting(E_ALL);
ini_set('display_errors', 0); // L'erreur c'est ici, tu dois désactiver l'affichage des erreurs pour éviter les HTML dans la réponse
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!$data) {
            echo json_encode(["status" => "error", "message" => "Invalid JSON"]);
            exit;
        }

        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        if (!$email || !$password) {
            echo json_encode(["status" => "error", "message" => "Email and password are required."]);
            exit;
        }

        $user = new User($pdo);
        $result = $user->login($email, $password);
        echo json_encode($result);
    }
} catch (Exception $e) {
    error_log('Login error: ' . $e->getMessage());
    echo json_encode(["status" => "error", "message" => "Server error: " . $e->getMessage()]);
}
?>