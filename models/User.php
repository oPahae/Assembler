<?php

class User {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function register($firstName, $lastName, $email, $password, $img=NULL) {
        try {
            // nshofo p3da wash email existe déja
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                return ["status" => "error", "message" => "Email already used."];
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("INSERT INTO users (firstname, lastname, email, password, img) VALUES (?, ?, ?, ?, ?)");
            $success = $stmt->execute([$firstName, $lastName, $email, $hashedPassword, $img]);
            
            if ($success) {
                return ["status" => "success", "message" => "Registration successful!"];
            } else {
                return ["status" => "error", "message" => "Database insertion failed."];
            }
        } catch (PDOException $e) {
            return ["status" => "error", "message" => "Database error: " . $e->getMessage()];
        }
    }

    public function login($email, $password) {
        try {
            // had lcompte wash dial user ?
            $stmt = $this->db->prepare("SELECT id, firstname, lastname, email, password, img, role FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                // sinon wash dial professeur ?
                $stmt = $this->db->prepare("SELECT id, firstname, lastname, email, password, img, type FROM profs WHERE email = ?");
                $stmt->execute([$email]);
                $prof = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Authentification dial prof
                if (password_verify($password, $prof['password'])) {
                    session_start();
                    $_SESSION['id'] = $prof['id'];
                    $_SESSION['email'] = $prof['email'];
                    $_SESSION['firstname'] = $prof['firstname'];
                    $_SESSION['lastname'] = $prof['lastname'];
                    $_SESSION['type'] = $prof['type'];
                    
                    return [
                        "status" => "success",
                        "message" => "Login successful!",
                        "user" => [
                            "id" => $prof['id'],
                            "email" => $prof['email'],
                            "firstname" => $prof['firstname'],
                            "lastname" => $prof['lastname'],
                            "type" =>  $prof['type'],
                            "img" => $prof['img'],
                            "role" => "prof"
                        ]
                    ];
                } else {
                    return ["status" => "error", "message" => "Invalid email or password."];
                }
            }
            
            // Authentification dial user
            if (password_verify($password, $user['password'])) {
                ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 7);
                ini_set('session.cookie_lifetime', 60 * 60 * 24 * 7);
                session_start();
                $_SESSION['id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['firstname'] = $user['firstname'];
                $_SESSION['lastname'] = $user['lastname'];
                $_SESSION['img'] = $user['img'];
                $_SESSION['role'] = $user['role'];
                
                return [
                    "status" => "success",
                    "message" => "Login successful!",
                    "user" => [
                        "id" => $user['id'],
                        "email" => $user['email'],
                        "firstname" => $user['firstname'],
                        "lastname" => $user['lastname'],
                        "img" => $user['img'],
                        "role" => $user['role']
                    ]
                ];
            } else {
                return ["status" => "error", "message" => "Invalid email or password."];
            }
        } catch (PDOException $e) {
            return ["status" => "error", "message" => "Database error"];
        }
    }

    public function getByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM Users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getProjects($userID) {
        $stmt = $this->db->prepare(
            "SELECT p.* FROM Users u, UsPr up, Projects p
            WHERE up.userID = u.id
            AND up.projectID = p.id
            AND up.userID = ?"
        );
        $stmt->execute([$userID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateProfile($userID, $firstName, $lastName, $email, $profileImagePath = null) {
        $sql = "UPDATE users SET firstname = :firstname, lastname = :lastname, email = :email";
        if ($profileImagePath) {
            $sql .= ", img = :img"; // Update the `img` column if a new image is uploaded
        }
        $sql .= " WHERE id = :id";
    
        $stmt = $this->db->prepare($sql);
        $params = [
            ':firstname' => $firstName,
            ':lastname' => $lastName,
            ':email' => $email,
            ':id' => $userID
        ];
        if ($profileImagePath) {
            $params[':img'] = $profileImagePath; // Add the image path to the parameters
        }
    
        return $stmt->execute($params);
    }
    
    public function updatePassword($userID, $hashedPassword) {
        try {
            $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $userID]);
            return true; // Retourne true si la mise à jour réussit
        } catch (PDOException $e) {
            throw new Exception("Erreur de base de données : " . $e->getMessage());
        }
    }
       
    public function deleteAccount($userID) {
        try {
            // Delete the user's record from the database
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userID]);
    
            // Optionally, delete the user's uploaded files
            $this->deleteUserFiles($userID);
    
            return true;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }
    
    private function deleteUserFiles($userID) {
        // Example: Delete the user's uploaded profile image
        $stmt = $this->db->prepare("SELECT img FROM users WHERE id = ?");
        $stmt->execute([$userID]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($userData && !empty($userData['img'])) {
            $filePath = $userData['img'];
            if (file_exists($filePath)) {
                unlink($filePath); // Delete the file
            }
        }
    }

    public function getById($userID) {
        try {
            // Sélectionner le chemin de l'image directement
            $stmt = $this->db->prepare("SELECT id, firstname, lastname, email, img, password FROM users WHERE id = ?");
            $stmt->execute([$userID]);
            $profileData = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$profileData) {
                throw new Exception("Aucune donnée trouvée pour l'utilisateur avec l'ID : $userID");
            }
    
            return $profileData;
        } catch (PDOException $e) {
            throw new Exception("Erreur de base de données : " . $e->getMessage());
        }
    }
}

?>