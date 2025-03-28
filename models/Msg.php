<?php
require_once "../lib/connect.php";

class Msg {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::connect();
    }

    public function get($projectID) {
        $stmt = $this->pdo->prepare(
            "SELECT t.*, 
                (SELECT u.id FROM Users u WHERE u.id = t.userID) AS userID,
                (SELECT u.firstname FROM Users u WHERE u.id = t.userID) AS firstname,
                (SELECT u.lastname FROM Users u WHERE u.id = t.userID) AS lastname,
                (SELECT u.email FROM Users u WHERE u.id = t.userID) AS email,
                (SELECT u.img FROM Users u WHERE u.id = t.userID) AS img
            FROM Msgs t
            WHERE t.projectID = ?"
        );
        $stmt->execute([$projectID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function add($projectID, $userID, $content) {
        $stmt = $this->pdo->prepare("INSERT INTO Msgs (projectID, userID, content) VALUES (?, ?, ?)");
        return $stmt->execute([$projectID, $userID, $content]);
    }

    public function delete($taskID) {
        $stmtSubtasks = $this->pdo->prepare("DELETE FROM Subtasks WHERE taskID = ?");
        $stmtSubtasks->execute([$taskID]);
        
        $stmt = $this->pdo->prepare("DELETE FROM Tasks WHERE id = ?");
        return $stmt->execute([$taskID]);
    }

    public function update($taskID, $name, $deadline, $status, $userID, $descr = '') {
        $stmt = $this->pdo->prepare("UPDATE Tasks SET name = ?, deadline = ?, status = ?, descr = ?, userID = ? WHERE id = ?");
        return $stmt->execute([$name, $deadline, $status, $descr, $userID, $taskID]);
    }
}