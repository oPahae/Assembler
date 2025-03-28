<?php
require_once "../lib/connect.php";

class Project {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::connect();
    }

    public function getID($projectID) {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT p.*,
                    (SELECT COUNT(*) FROM UsPr u WHERE u.projectID = p.id) AS totalUsers,
                    (SELECT COUNT(*) FROM Tasks t WHERE t.projectID = p.id) AS totalTasks,
                    (SELECT COUNT(*) FROM Tasks t WHERE t.projectID = p.id AND t.status = 'done') AS completedTasks,
                    (SELECT COUNT(*) FROM Tasks t WHERE t.projectID = p.id AND t.status = 'ongoing') AS ongoingTasks
                FROM Projects p
                WHERE p.id = ?"
            );
            $stmt->execute([$projectID]);
            $project = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                "status" => "success",
                "data" => $project
            ];
        } catch (PDOException $e) {
            error_log('Error fetching project: ' . $e->getMessage());
            return [
                "status" => "error",
                "message" => "Database error: " . $e->getMessage()
            ];
        }
    }
    
    public function getUsers($projectID) {
        try {
            $query = "
                SELECT DISTINCT u.id, u.firstname, u.lastname, u.email 
                FROM Users u
                JOIN UsPr up ON u.id = up.userID
                WHERE up.projectID = ?
            ";
            $params = [$projectID];

            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            
            return [
                "status" => "success", 
                "users" => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];
        } catch (PDOException $e) {
            error_log('Error fetching project users: ' . $e->getMessage());
            return [
                "status" => "error", 
                "message" => "Database error: " . $e->getMessage()
            ];
        }
    }

    public function create($userID, $name, $deadline, $maxMembers, $slogan, $type) {
        // Démarrer la SESSION
        
        
        // ndiro validation deyal data
        if (empty($userID) || empty($name) || empty($deadline) || empty($maxMembers) || empty($type)) {
            throw new Exception("Tous les champs obligatoires doivent être remplis.");
        }
    
        // nverifiw maxMembers entier positif
        if (!is_numeric($maxMembers) || $maxMembers <= 0 || !is_int($maxMembers + 0)) {
            throw new Exception("Le nombre maximum de membres doit être un entier positif.");
        }
    
        // bach nchofo wach date valide
        if (!strtotime($deadline)) {
            throw new Exception("La date limite est invalide.");
        }
    
        // bach ngiriw code ykon unique
        $code = $this->generateUniqueCode();
    
        // bach ndimariw wahd transaction
        $this->pdo->beginTransaction();
    
        try {
            // Rq bach ndiro insertion f table projects
            $sql = "INSERT INTO projects (userID, name, deadline, maxMembers, slogan, type, code)
                    VALUES (:userID, :name, :deadline, :maxMembers, :slogan, :type, :code)";
            
            $stmt = $this->pdo->prepare($sql);
            
            // hadi bach ndiro debbogagggge
            error_log("Tentative d'insertion : userID=$userID, name=$name, deadline=$deadline, maxMembers=$maxMembers, slogan=$slogan, type=$type, code=$code");
            
            // henna radi ndiro l'execution 
            $stmt->execute([
                ':userID' => $userID,
                ':name' => $name,
                ':deadline' => $deadline,
                ':maxMembers' => $maxMembers,
                ':slogan' => $slogan,
                ':type' => $type,
                ':code' => $code
            ]);
            
            
            // hena bach ndiro wahd recupiration l projectID li criyinna
            $projectID = $this->pdo->lastInsertId();
            error_log("Insertion réussie, ID du projet: $projectID, Code: $code");
    
            //  RHadi wahd req bach ndiro association user l projet
            $sql2 = "INSERT INTO UsPr (userID, projectID) VALUES (:userID, :projectID)";
            $stmt2 = $this->pdo->prepare($sql2);
            $stmt2->execute([
                ':userID' => $userID,
                ':projectID' => $projectID
            ]);
            
        // hena radi ndiro wahd search f users 3ala prof li3ndo nfss speciality likyn f type d project
        $sql3 = "SELECT id FROM users WHERE role = 'prof' AND speciality = :type LIMIT 1";
        $stmt3 = $this->pdo->prepare($sql3);
        $stmt3->execute([':type' => $type]);
        $professor = $stmt3->fetch(PDO::FETCH_ASSOC);

        if ($professor) {
            
            // henna radi associw prof l projet ka supervisor
            $sql4 = "INSERT INTO UsPr (userID, projectID, role) VALUES (:professorID, :projectID, 'prof')";
            $stmt4 = $this->pdo->prepare($sql4);
            $stmt4->execute([
                ':professorID' => $professor['id'],
                ':projectID' => $projectID
            ]);
            error_log("Professeur assigné au projet: " . $professor['id']);
        } else {
            error_log("Aucun professeur trouvé avec la spécialité: $type");
            throw new Exception("Aucun professeur disponible pour superviser ce projet.");
        }
            // bach nvalidiw transaction
            $this->pdo->commit();
    
            return $projectID; // bach retourniw projet id
        } catch (PDOException $e) {
            // nblokiw transaction in case 3dna chi mochkil
            $this->pdo->rollBack();
            error_log("Erreur PDO: " . $e->getMessage());
            throw new Exception("Erreur lors de la création du projet : " . $e->getMessage());
        }
    }

    private function generateProjectCode($length = 8) {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $code;
    }

    private function generateUniqueCode($maxAttempts = 10) {
        $attempts = 0;
        do {
            $code = $this->generateProjectCode();
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM projects WHERE code = :code");
            $stmt->execute([':code' => $code]);
            $count = $stmt->fetchColumn();
            $attempts++;
        } while ($count > 0 && $attempts < $maxAttempts);

        if ($attempts >= $maxAttempts) {
            throw new Exception("Impossible de générer un code unique après $maxAttempts tentatives.");
        }

        return $code;
    }

    public function getUsersByProject($projectID) {
        $query = "SELECT users.*
                  FROM users 
                  INNER JOIN uspr ON users.id = uspr.userID
                  INNER JOIN projects ON uspr.projectID = projects.id
                  WHERE uspr.projectID = :projectID";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['projectID' => $projectID]); // Correction ici
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function join($userID,$code){

        
        if(empty($code)){
            throw new Exception("le Code est obligatoire.");
        }
        try{
            
            $this->pdo->beginTransaction();
            $sql = "SELECT id FROM projects WHERE code = :code";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':code' => $code]);

            
            // radi nchofo wach project Kaynn
            if($stmt->rowCount() > 0){

                $line = $stmt->fetch(PDO::FETCH_ASSOC);
                $projectID = $line["id"];
                // F had etape Radi ntistiw Wach user kayn deja f projet
                $sql_check = "SELECT * FROM uspr WHERE userID = :userID AND projectID = :projectID";
                $stmt_check = $this->pdo->prepare($sql_check);
                $stmt_check->execute([
                    ':userID' => $userID,
                    ':projectID' => $projectID
                ]);

                if($stmt_check->rowCount() == 0){
                        // radi nhsbo nbr deyl users lid5lin l Project Y3ni Chehal homa f Team
                        $sql_count_members = "SELECT COUNT(*) FROM uspr WHERE projectID = :projectID";
                        $stmt_count_members = $this->pdo->prepare($sql_count_members);
                        $stmt_count_members->execute([':projectID' => $projectID]);
                        $currentMembers = $stmt_count_members->fetchColumn();

                        // radi njbdo maxMembers mn projects
                        $sql_max = "SELECT maxMembers FROM projects WHERE id = :projectID";
                        $stmt_max = $this->pdo->prepare($sql_max);
                        $stmt_max->execute([
                            ':projectID' => $projectID
                        ]);
                        $max_memb = $stmt_max->fetchColumn();
                        
                        // Radi ntistiw Wach Team Mazal fih places
                        if($max_memb > $currentMembers){
                            // daba n9dro nzido user l projet bela sd3 Rass
                            $sql2 = "INSERT INTO UsPr (userID, projectID) VALUES (:userID, :projectID)";
                            $stmt2 = $this->pdo->prepare($sql2);
                            $stmt2->execute([
                                ':userID' => $userID,
                                ':projectID' => $projectID
                            ]);
                                $this->pdo->commit();
                                return [
                                    'status' => 'success',
                                    'message' => 'Projet rejoint avec succès.',
                                    'projectID' => $this->pdo->lastInsertId()
                                ];
                        }else {
                            // Team d project 3ammer
                            throw new Exception("This Project is Completed !!!");
                        }

                    }else{
                        // user deja kayn f project m3dna manzido
                        throw new Exception("You Already join This Project  !!!");
                    }
                
            }else{
                // project mazal makynch
                error_log("Le projet n'exsiste pas !!!");
                throw new Exception("Le projet n'existe pas.");
            }

            }catch(Exception $e){
                $this->pdo->rollBack();
                error_log("Erreur lors de l'ajout de l'utilisateur au projet : " . $e->getMessage());
                throw $e;
            }
        
    
    }
    
    public function getByProjectId($projectID) {
        $sql = "SELECT * FROM projects WHERE id = :id";
        
        $stmt = $this->pdo->prepare($sql); 
        $stmt->bindParam(':id', $projectID);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC); // bach nakhdo un tableau associatif 
    }
    
    public function updateProject($projectID, $name, $maxMembers, $slogan) {
        $stmt = $this->pdo->prepare("UPDATE projects SET name = ?, maxMembers = ?, slogan = ? WHERE id = ?");
        return $stmt->execute([$name, $maxMembers, $slogan, $projectID]);
    }
    
    public function getProjectDetails($projectID) {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT name, maxMembers, slogan 
                FROM projects 
                WHERE id = :projectID"
            );
            $stmt->execute(['projectID' => $projectID]);
            $project = $stmt->fetch(PDO::FETCH_ASSOC);
    
            return [
                "status" => "success",
                "project" => $project 
            ];
        } catch (PDOException $e) {
            error_log('Error fetching project details: ' . $e->getMessage());
            return [
                "status" => "error",
                "message" => "Database error: " . $e->getMessage()
            ];
        }
    }

}
?>