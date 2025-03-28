<?php
require_once '../lib/connect.php';
require_once '../models/User.php';
$pdo = Database::connect();
error_reporting(E_ALL);
ini_set('display_errors', 0);
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!$data) {
            echo json_encode(["status" => "error", "message" => "Invalid JSON"]);
            exit;
        }

        $firstName = trim($data['firstName'] ?? '');
        $lastName = trim($data['lastName'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $confirmPassword = $data['confirmPassword'] ?? '';
        if (!$firstName || !$lastName || !$email || !$password || !$confirmPassword) {
            echo json_encode(["status" => "error", "message" => "All fields are required."]);
            exit;
        }
        if ($password !== $confirmPassword) {
            echo json_encode(["status" => "error", "message" => "Passwords do not match."]);
            exit;
        }

        $user = new User($pdo);
        $result = $user->register($firstName, $lastName, $email, $password, NULL);
        echo json_encode($result);
    }
} catch (Exception $e) {
    error_log('Registration error: ' . $e->getMessage());
    echo json_encode(["status" => "error", "message" => "Server error: " . $e->getMessage()]);
}
?>