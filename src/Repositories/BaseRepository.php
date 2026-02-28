<?php

namespace App\Repositories;

use App\Config\Database;
use App\Models\BaseEntity;
use PDO;
Use PDOException;

/**
 * Classe abstraite
 * 
 * Classe parent de tous les repositories (UserRepository, ProjectRepository, etc.)
 * Contient les méthodes communes à tous les repositories
 * 
 * Concepts enseignés :
 * - Pattern Repository  (Séparation de la logique d'accès aux données)
 * - Réquêtes préparées (prévention des injections SQL)
 * - Hydration d'objets (array en objet)
 * - Méthodes abstraites (doivent être implémentées dans les classes enfants)
 */
abstract class BaseRepository {
    protected PDO $pdo;

    protected string $tableName; // Nom de la table associée au repository

    public function __construct() {
        $db = Database::getInstance();
        $this->pdo = $db->getConnection();
    }

    /**
     * Méthode abstarite: Hydrater une objet depuis un tableau de données
     * 
     * Chaque repository doit implémenter cette méthode pour hydrater
     * Doit implémenter cette méthode pour créer son objet spécifique
     * 
     * @param array $data Données brutes depuis la base de données
     * @return BaseEntity Instance de l'entité (User, Project, etc.)
     */
    abstract protected function hydrate(array $data): BaseEntity;

    public function findAll(): array {
        try {
            $sql = "SELECT * FROM {$this->tableName} ORDER BY created_at DESC";
            $stmt = $this->pdo->prepare($sql);// Prepare la requête
            $stmt->execute();
            $results = $stmt->fetchAll(); // Tableau de tableaux associatifs
            return $this->hydrateMultiple($results);
        } catch (PDOException $e) {
             $this->logError($e);
            return [];
        }
    }

    public function findById(int $id): ?BaseEntity {
        try {
            $sql = "SELECT * FROM {$this->tableName} WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(); // Tableau associatif ou false
            if ($result) {
                return $this->hydrate($result);
            }
            return null;
        } catch (PDOException $e) {
            $this->logError($e);
            return null;
        }
    }

    public function delete(int $id): bool {
        try {
            $sql = "DELETE FROM {$this->tableName} WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
   
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            $this->logError($e);
            return false;
        }
    }

    protected function executeCommand(string $sql, array $params = []): bool {
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            $this->logError($e);
            return false;
        }
    }

    protected function lastInsertId(): int {
        return (int)$this->pdo->lastInsertId();
    }

    public function hydrateMultiple(array $results): array {
        $entities = [];
        foreach ($results as $row) {
            $entities[] = $this->hydrate($row);
        }
        return $entities;
    }

    protected function logError(PDOException $e): void {
        $logFile = ROOT_PATH . '/logs/repository.log';

        if(!is_dir(dirname($logFile))) {
            mkdir(dirname($logFile), 0755, true);
        }

        $message = date('Y-m-d H:i:s') . " - Erreur PDO: " . $e->getMessage() . PHP_EOL;
        file_put_contents($logFile, $message, FILE_APPEND);
    }
}