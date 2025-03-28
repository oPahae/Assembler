<?php
require_once "../lib/connect.php";

class Task {
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
                (SELECT u.img FROM Users u WHERE u.id = t.userID) AS img,
                (SELECT COUNT(*) FROM Subtasks s WHERE s.taskID = t.id) AS total_subtasks,
                (SELECT COUNT(*) FROM Subtasks s WHERE s.taskID = t.id AND s.status = 'done') AS completed_subtasks,
                IFNULL(
                    (SELECT COUNT(*) FROM Subtasks s WHERE s.taskID = t.id AND s.status = 'done') / 
                    NULLIF((SELECT COUNT(*) FROM Subtasks s WHERE s.taskID = t.id), 0) * 100, 
                0) AS progress
            FROM Tasks t
            WHERE t.projectID = ?"
        );
        $stmt->execute([$projectID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUsers($taskID) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT u.id, u.firstname, u.lastname, u.email, TO_BASE64(u.img)
                FROM Users u
                JOIN UsTsk ut ON u.id = ut.userID
                WHERE ut.taskID = ?
            ");
            $stmt->execute([$taskID]);
            
            return [
                "status" => "success", 
                "users" => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];
        } catch (PDOException $e) {
            error_log('Error fetching task users: ' . $e->getMessage());
            return [
                "status" => "error", 
                "message" => "Database error: " . $e->getMessage()
            ];
        }
    }

    public function getID($taskID) {
        try {
            // infos
            $stmt = $this->pdo->prepare("
                SELECT id, projectID, name, deadline, descr, status, userID
                FROM Tasks WHERE id = ?"
            );
            $stmt->execute([$taskID]);
            $task = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$task) {
                return ["status" => "error", "message" => "Task not found"];
            }
    
            // subtasks
            $subtasksStmt = $this->pdo->prepare("
                SELECT id, name, status 
                FROM Subtasks 
                WHERE taskID = ?"
            );
            $subtasksStmt->execute([$taskID]);
            $subtasks = $subtasksStmt->fetchAll(PDO::FETCH_ASSOC);
    
            // cmnts
            $commentsStmt = $this->pdo->prepare("
                SELECT c.id, u.firstname, u.lastname, c.content, c.createdAt, u.img
                FROM Cmnts c
                JOIN Users u ON c.userID = u.id
                WHERE c.taskID = ?
                ORDER BY c.createdAt DESC"
            );
            $commentsStmt->execute([$taskID]);
            $comments = $commentsStmt->fetchAll(PDO::FETCH_ASSOC);
    
            // docs
            $attachmentsStmt = $this->pdo->prepare("
                SELECT name, TO_BASE64(content) AS content, size, createdAt 
                FROM Docs 
                WHERE taskID = ?"
            );
            $attachmentsStmt->execute([$taskID]);
            $attachments = $attachmentsStmt->fetchAll(PDO::FETCH_ASSOC);
    
            return [
                "status" => "success",
                "task" => $task,
                "subtasks" => $subtasks ?: [],
                "cmnts" => $comments ?: [],
                "docs" => $attachments ?: []
            ];
        } catch (PDOException $e) {
            error_log('Error fetching task details: ' . $e->getMessage());
            return ["status" => "error", "message" => "Database error: " . $e->getMessage()];
        }
    }

    public function add($projectID, $userID, $name, $deadline, $descr) {
        $stmt = $this->pdo->prepare("INSERT INTO Tasks (projectID, userID, name, deadline, descr) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$projectID, $userID, $name, $deadline, $descr]);
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