<?php
require_once '../lib/connect.php';
require '../models/User.php';
$pdo = Database::connect();
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');
$data = json_decode(file_get_contents("php://input"), true);
$token = $data['token'] ?? null;
if (!$token) {
    echo json_encode(["status" => "error", "message" => "No token provided."]);
    exit;
}
$google_client_id = "562386703326-1epijmm8o543ogg59q5gqhjpif9sgi01.apps.googleusercontent.com";
$google_data = file_get_contents("https://oauth2.googleapis.com/tokeninfo?id_token=" . $token);
$google_data = json_decode($google_data, true);
if (!isset($google_data['email'])) {
    echo json_encode(["status" => "error", "message" => "Invalid Google token."]);
    exit;
}

//^...........................................................................................

$email = $google_data['email'];
$firstname = $google_data['given_name'] ?? "";
$lastname = $google_data['family_name'] ?? "";
$img = $google_data['picture'] ?? "";

$userModel = new User($pdo);
$user = $userModel->getByEmail($email);

if (!$user) {
    // CAS 1 : user makinsh = ncréeiw lih compte
    $userId = $userModel->register($firstname, $lastname, $email, password_hash(uniqid(), PASSWORD_BCRYPT), $img);
} else {
    // CAS 2 : user déja kayn, ndiro lih login osf
    $userId = $user['id'];
    $role = $user['role'];
}

ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 7);
ini_set('session.cookie_lifetime', 60 * 60 * 24 * 7);
session_start();
$_SESSION['id'] = $userId;
$_SESSION['email'] = $email;
$_SESSION['firstname'] = $firstname;
$_SESSION['lastname'] = $lastname;
$_SESSION['img'] = $img;
$_SESSION['role'] = $role;
echo json_encode(["status" => "success"]);
exit;
?>