<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\BaseEntity;

class UserRepository extends BaseRepository {
    protected string $tableName = 'Utilisateurs';

    protected function hydrate(array $data): BaseEntity {
        $user = new User();
        $user->setId((int)$data['id'])
             ->setNom($data['nom'])
             ->setPrenom($data['prenom'])
             ->setEmail($data['email'])
             ->setPasswordHash($data['password']) // Charger le hash depuis la DB
             ->setAvatar($data['tel'] ?? null)
             ->setRole($data['role'] ?? null)
             ->setIsActive(isset($data['is_active']) ? (bool)$data['is_active'] : null)
             ->setCreatedAt(new \DateTime($data['created_at']))
             ->setUpdatedAt(new \DateTime($data['updated_at']));
        return $user;
    }

    public function findByEmail(string $email): ?User {
        try {
            $sql = "SELECT * FROM {$this->tableName} WHERE email = :email";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':email', $email, \PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(); // Tableau associatif ou false
            /* var_dump($result);
            die('ici'); */
            if ($result) {
                return $this->hydrate($result);
            }
            return null;
        } catch (\PDOException $e) {
            $this->logError($e);
            return null;
        }
    }

    public function save(User $user): bool {
        if ($user->getId() === null) {
            // Insertion
            $sql = "INSERT INTO {$this->tableName} (nom, prenom, email, password, tel, role, is_active, created_at, updated_at)
                    VALUES (:nom, :prenom, :email, :password, :tel, :role, :is_active, NOW(), NOW())";
            $params = [
                ':nom' => $user->getNom(),
                ':prenom' => $user->getPrenom(),
                ':email' => $user->getEmail(),
                ':password' => $user->getPasswordHash(),
                ':tel' => $user->getTel(),
                ':role' => $user->getRole(),
                ':is_active' => $user->isActive() ? 1 : 0,
            ];
            $success = $this->executeCommand($sql, $params);
            if ($success) {
                $user->setId($this->lastInsertId());
            }
            return $success;
        } else {
            // Mise à jour
            $sql = "UPDATE {$this->tableName} SET
                    nom = :nom,
                    prenom = :prenom,
                    email = :email,
                    password = :password,
                    tel = :tel,
                    role = :role,
                    is_active = :is_active,
                    updated_at = NOW()
                    WHERE id = :id";
            $params = [
                ':nom' => $user->getNom(),
                ':prenom' => $user->getPrenom(),
                ':email' => $user->getEmail(),
                ':password' => $user->getPasswordHash(),
                ':tel' => $user->getTel(),
                ':role' => $user->getRole(),
                ':is_active' => $user->isActive() ? 1 : 0,
                ':id' => $user->getId(),
            ];
            return $this->executeCommand($sql, $params);
        }
    }
}