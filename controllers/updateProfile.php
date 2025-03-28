<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../auth/");
    exit();
}

require_once '../models/User.php';
require_once '../lib/connect.php';

$userID = $_GET['userID'] ?? $_SESSION['id'];

if (!$userID) {
    echo json_encode(["error" => "User ID is missing."]);
    exit();
}

$userModel = new User(Database::connect());

try {
    $firstName = $_POST['firstname'] ?? null;
    $lastName = $_POST['lastname'] ?? null;
    $email = $_POST['email'] ?? null;

    if (empty($firstName) || empty($lastName) || empty($email)) {
        throw new Exception("All fields are required.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email address.");
    }

    // Bach ndirro handll l image
    $profileImagePath = null;
    if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profileImage'];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_ext, $allowed_extensions)) {
            $upload_dir = '../../uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $new_file_name = uniqid('', true) . '.' . $file_ext;
            if (move_uploaded_file($file['tmp_name'], $upload_dir . $new_file_name)) {
                $profileImagePath = $upload_dir . $new_file_name;
            } else {
                throw new Exception("Failed to upload image.");
            }
        } else {
            throw new Exception("Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.");
        }
    }

    // henna n9dro ndiro update l profile f data base
    $updateResult = $userModel->updateProfile($userID, $firstName, $lastName, $email, $profileImagePath);

    if (!$updateResult) {
        throw new Exception("Failed to update profile.");
    }

    // upditiw session data
    $_SESSION['firstName'] = $firstName;
    $_SESSION['lastName'] = $lastName;
    $_SESSION['email'] = $email;

    echo json_encode([
        'status' => 'success',
        'message' => 'Profile updated successfully.',
        'profileImage' => $profileImagePath // returniw url img l frontend
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}