<?php
require_once "../lib/connect.php"; 
class Project {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::connect();
    }

    public function deleteById($projectID) {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("DELETE FROM UsPr WHERE projectID = :projectID");//supprimer mn l table uspr 
            $stmt->execute([':projectID' => $projectID]);

            $stmt = $this->pdo->prepare("DELETE FROM Tasks WHERE projectID = :projectID");//supprimer les taches dyawla 
            $stmt->execute([':projectID' => $projectID]);

            $stmt = $this->pdo->prepare("DELETE FROM projects WHERE id = :id"); //supprimer mn table dyalha 
            $stmt->execute([':id' => $projectID]);

            $this->pdo->commit();//validation dyl dkchi li derna 

            return [
                "status" => "success",
                "message" => "Le projet a été supprimé avec succès."
            ];
        } catch (PDOException $e) {
            //n annuliw transacttion ila kan err
            $this->pdo->rollBack();
            error_log("Erreur lors de la suppression du projet : " . $e->getMessage());
            return [
                "status" => "error",
                "message" => "Erreur lors de la suppression du projet : " . $e->getMessage()
            ];
        }
    }
}

if (isset($_POST['projectID'])) {
    $projectID = $_POST['projectID'];

    $project = new Project();
    $response = $project->deleteById($projectID);

    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "L'ID du projet est manquant."
    ]);
}
?>
