<?php
require_once "../lib/connect.php";

class Subtask {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::connect();
    }

    public function add($taskID, $name) {
        $stmt = $this->pdo->prepare("INSERT INTO Subtasks (taskID, name) VALUES (?, ?)");
        $stmt->execute([$taskID, $name]);
        return ["status" => "success", "message" => "Subtask added successfully"];
    }    

    public function delete($taskID) {
        $stmtSubtasks = $this->pdo->prepare("DELETE FROM Subtasks WHERE taskID = ?");
        $stmtSubtasks->execute([$taskID]);
        
        $stmt = $this->pdo->prepare("DELETE FROM Tasks WHERE id = ?");
        return $stmt->execute([$taskID]);
    }

    public function update($subtaskID, $status) {
        $stmt = $this->pdo->prepare("UPDATE Subtasks SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $subtaskID]);
    }
}