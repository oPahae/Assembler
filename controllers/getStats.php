<?php
require_once "../lib/connect.php";

header("Content-Type: application/json");

if (!isset($_GET['projectID'])) {
    echo json_encode(["error" => "Project ID is required"]);
    exit;
}

$projectID = intval($_GET['projectID']);
$db = Database::connect();

try {
    $stmt = $db->prepare("SELECT status, COUNT(*) as count FROM Tasks WHERE projectID = ? GROUP BY status");
    $stmt->execute([$projectID]);
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $db->prepare("SELECT Users.firstname, COUNT(Tasks.id) as taskCount FROM Tasks 
                          JOIN Users ON Tasks.userID = Users.id 
                          WHERE Tasks.projectID = ? GROUP BY Users.id");
    $stmt->execute([$projectID]);
    $userTasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $db->prepare("SELECT DATE(createdAt) as date, COUNT(id) as count FROM Tasks 
                          WHERE projectID = ? GROUP BY DATE(createdAt) ORDER BY date ASC");
    $stmt->execute([$projectID]);
    $tasksOverTime = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "tasks" => $tasks,
        "userTasks" => $userTasks,
        "tasksOverTime" => $tasksOverTime
    ]);
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>