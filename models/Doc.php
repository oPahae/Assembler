<?php
require_once '../lib/connect.php';

class Doc {
    public static function add($name, $content, $size, $taskID) {
        try {
            $pdo = Database::connect();
            $stmt = $pdo->prepare("INSERT INTO Docs (name, content, size, taskID) VALUES (:name, :content, :size, :taskID)");
            
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':content', $content, PDO::PARAM_LOB);
            $stmt->bindParam(':size', $size, PDO::PARAM_STR);
            $stmt->bindParam(':taskID', $taskID, PDO::PARAM_INT);
            
            $stmt->execute();
            return ["success" => true, "id" => $pdo->lastInsertId()];
        } catch (PDOException $e) {
            return ["error" => "Failed to add document: " . $e->getMessage()];
        }
    }
}