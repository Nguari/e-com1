<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\BaseEntity;

/**
 * Classe UserRepository
 * 
 * Gère toutes les interactions avec la table 'utilisateurs'
 * Hérite de BaseRepository pour les méthodes CRUD génériques
 */
class UserRepository extends BaseRepository {

    protected string $tableName  = 'utilisateurs';
    protected string $primaryKey = 'id_utilisateur';
    protected string $orderBy    = 'date_inscription';

    // =========================================
    // HYDRATION
    // =========================================

    /**
     * Convertit un tableau associatif (résultat BDD) en objet User
     */
    protected function hydrate(array $data): BaseEntity {
        $user = new User();

        $user->setId((int)$data['id_utilisateur'])
             ->setNom($data['nom'])
             ->setPrenom($data['prenom'])
             ->setEmail($data['email'])
             ->setPasswordHash($data['mot_de_passe'])
             ->setTel($data['tel'] ?? null)
             ->setRole($data['role'] ?? 'client')
             ->setIsActive((bool)($data['actif'] ?? true));

        if (!empty($data['date_inscription'])) {
            $user->setCreatedAt(new \DateTime($data['date_inscription']));
        }

        if (!empty($data['derniere_connexion'])) {
            $user->setUpdatedAt(new \DateTime($data['derniere_connexion']));
        }

        return $user;
    }

    // =========================================
    // REQUÊTES SPÉCIFIQUES
    // =========================================

    /**
     * Trouver un utilisateur par son email
     */
    public function findByEmail(string $email): ?User {
        try {
            $sql  = "SELECT * FROM {$this->tableName} WHERE email = :email LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':email', $email, \PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result ? $this->hydrate($result) : null;
        } catch (\PDOException $e) {
            $this->logError($e);
            return null;
        }
    }

    /**
     * Vérifier si un email est déjà utilisé
     */
    public function emailExiste(string $email): bool {
        try {
            $sql  = "SELECT COUNT(*) FROM {$this->tableName} WHERE email = :email";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':email' => $email]);
            return (int)$stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            $this->logError($e);
            return false;
        }
    }

    /**
     * Mettre à jour la dernière connexion d'un utilisateur
     */
    public function updateDerniereConnexion(int $id): bool {
        $sql = "UPDATE {$this->tableName} 
                SET derniere_connexion = NOW() 
                WHERE {$this->primaryKey} = :id";
        return $this->executeCommand($sql, [':id' => $id]);
    }

    // =========================================
    // SAUVEGARDE (INSERT / UPDATE)
    // =========================================

    /**
     * Sauvegarder un utilisateur (insertion si nouveau, mise à jour sinon)
     */
    public function save(User $user): bool {

        if ($user->getId() === null) {
            // =====================
            // INSERTION
            // =====================
            $sql = "INSERT INTO {$this->tableName} 
                        (nom, prenom, email, mot_de_passe, tel, role, actif)
                    VALUES 
                        (:nom, :prenom, :email, :mot_de_passe, :tel, :role, :actif)";

            $params = [
                ':nom'          => $user->getNom(),
                ':prenom'       => $user->getPrenom(),
                ':email'        => $user->getEmail(),
                ':mot_de_passe' => $user->getPasswordHash(),
                ':tel'          => $user->getTel(),
                ':role'         => $user->getRole(),
                ':actif'        => $user->isActive() ? 1 : 0,
            ];

            $success = $this->executeCommand($sql, $params);

            if ($success) {
                $user->setId($this->lastInsertId());
            }

            return $success;

        } else {
            // =====================
            // MISE À JOUR
            // =====================
            $sql = "UPDATE {$this->tableName} SET
                        nom               = :nom,
                        prenom            = :prenom,
                        email             = :email,
                        mot_de_passe      = :mot_de_passe,
                        tel               = :tel,
                        role              = :role,
                        actif             = :actif,
                        derniere_connexion = NOW()
                    WHERE {$this->primaryKey} = :id";

            $params = [
                ':nom'          => $user->getNom(),
                ':prenom'       => $user->getPrenom(),
                ':email'        => $user->getEmail(),
                ':mot_de_passe' => $user->getPasswordHash(),
                ':tel'          => $user->getTel(),
                ':role'         => $user->getRole(),
                ':actif'        => $user->isActive() ? 1 : 0,
                ':id'           => $user->getId(),
            ];

            return $this->executeCommand($sql, $params);
        }
    }
}