<?php
session_start();

if (!isset($_SESSION['id'])) {
    echo json_encode(["status" => "error", "message" => "You must be logged in to delete your account."]);
    exit();
}

require_once '../models/User.php';
require_once '../lib/connect.php';

$userID = $_SESSION['id']; // bach njibbo user ID mn session
$pdo = Database::connect();
// $userModel = new User(Database::connect());

try {
    // Delete the user's account
    // $deleteResult = $userModel->deleteAccount($userID);
    $stmt = $pdo->prepare("UPDATE users SET password = 'yyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyy' WHERE id = ?");
    if ($stmt->execute([$userID])) {
        // Log the user out
        session_destroy();
        echo json_encode(["status" => "success", "message" => "Account deleted successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to delete account."]);
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}