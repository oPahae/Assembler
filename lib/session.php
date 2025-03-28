<?php
session_start();
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if (!isset($_SESSION['id'])) {
    echo json_encode(["status" => "error", "message" => "No active session."]);
    exit;
}

echo json_encode([
    "status" => "success",
    "user" => [
        "id" => $_SESSION['id'],
        "email" => $_SESSION['email'],
        "firstname" => $_SESSION['firstname'],
        "lastname" => $_SESSION['lastname'],
        "img" => $_SESSION['img'],
        "role" => $_SESSION['role']
    ]
]);
?>