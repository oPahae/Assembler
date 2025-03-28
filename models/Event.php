<?php
require_once "../lib/connect.php";

class Event {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::connect();
    }

    public function get($projectID) {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM Events WHERE projectID = ?"
        );
        $stmt->execute([$projectID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function add($projectID, $name, $deadline) {
        $stmt = $this->pdo->prepare("INSERT INTO Events (projectID, name, deadline) VALUES (?, ?, ?)");
        return $stmt->execute([$projectID, $name, $deadline]);
    }

    public function delete($taskID) {
        $stmtSubtasks = $this->pdo->prepare("DELETE FROM Events WHERE taskID = ?");
        $stmtSubtasks->execute([$taskID]);
        
        $stmt = $this->pdo->prepare("DELETE FROM Tasks WHERE id = ?");
        return $stmt->execute([$taskID]);
    }
}