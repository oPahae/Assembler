<?php
session_start();
session_unset();
session_destroy();
header('Content-Type: application/json');

if (ini_get("session.use_cookies")) {
    setcookie(session_name(), '', time() - 42000, '/');
}

echo json_encode(["success" => true]);
?>