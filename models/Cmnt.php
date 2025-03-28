<?php
require_once "../lib/connect.php";

class Cmnt {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::connect();
    }

    public function get($projectID) {
        $stmt = $this->pdo->prepare(
            "SELECT c.*, 
                (SELECT u.id FROM Users u WHERE u.id = c.userID) AS userID,
                (SELECT u.firstname FROM Users u WHERE u.id = c.userID) AS firstname,
                (SELECT u.lastname FROM Users u WHERE u.id = c.userID) AS lastname,
                (SELECT u.email FROM Users u WHERE u.id = c.userID) AS email,
                (SELECT u.img FROM Users u WHERE u.id = c.userID) AS img,
                (SELECT t.id FROM Tasks t WHERE t.id = c.taskID) AS taskID,
                (SELECT t.name FROM Tasks t WHERE t.id = c.taskID) AS taskName
            FROM Cmnts c
            WHERE c.taskID IN (SELECT id FROM Tasks t WHERE t.projectID = ?)"
        );
        $stmt->execute([$projectID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function add($taskID, $userID, $content) {
        $stmt = $this->pdo->prepare("INSERT INTO Cmnts (taskID, userID, content) VALUES (?, ?, ?)");
        $stmt->execute([$taskID, $userID, $content]);
        return ["status" => "success", "message" => "Cmnt added successfully"];
    }    

    public function delete($taskID) {
        $stmtSubtasks = $this->pdo->prepare("DELETE FROM Subtasks WHERE taskID = ?");
        $stmtSubtasks->execute([$taskID]);
        
        $stmt = $this->pdo->prepare("DELETE FROM Tasks WHERE id = ?");
        return $stmt->execute([$taskID]);
    }

    public function update($taskID, $name, $status) {
        $stmt = $this->pdo->prepare("UPDATE Tasks SET name = ?, status = ? WHERE id = ?");
        return $stmt->execute([$name, $status, $taskID]);
    }
}