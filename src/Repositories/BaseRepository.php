<?php

namespace App\Repositories;

use App\Config\Database;
use App\Models\BaseEntity;
use PDO;
use PDOException;

/**
 * Classe abstraite BaseRepository
 * 
 * Classe parent de tous les repositories (UserRepository, ProductRepository, etc.)
 * Contient les méthodes communes à tous les repositories
 * 
 * Concepts enseignés :
 * - Pattern Repository (Séparation de la logique d'accès aux données)
 * - Requêtes préparées (prévention des injections SQL)
 * - Hydration d'objets (array → objet)
 * - Méthodes abstraites (doivent être implémentées dans les classes enfants)
 */
abstract class BaseRepository {

    protected PDO $pdo;

    /** Nom de la table associée au repository */
    protected string $tableName;

    /** Clé primaire de la table (à surcharger dans les enfants si différente de 'id') */
    protected string $primaryKey = 'id';

    /** Colonne de tri par défaut (à surcharger dans les enfants) */
    protected string $orderBy = 'id';

    // =========================================
    // CONSTRUCTEUR
    // =========================================

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    // =========================================
    // MÉTHODE ABSTRAITE
    // =========================================

    /**
     * Hydrater un objet depuis un tableau de données BDD
     * Chaque repository implémente cette méthode pour son entité spécifique
     *
     * @param array $data Données brutes depuis la base de données
     * @return BaseEntity Instance de l'entité (User, Product, etc.)
     */
    abstract protected function hydrate(array $data): BaseEntity;

    // =========================================
    // MÉTHODES CRUD GÉNÉRIQUES
    // =========================================

    /**
     * Récupérer toutes les entrées de la table
     *
     * @return array Tableau d'objets hydratés
     */
    public function findAll(): array {
        try {
            $sql  = "SELECT * FROM {$this->tableName} ORDER BY {$this->orderBy} DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll();
            return $this->hydrateMultiple($results);
        } catch (PDOException $e) {
            $this->logError($e);
            return [];
        }
    }

    /**
     * Récupérer une entrée par sa clé primaire
     *
     * @param int $id
     * @return BaseEntity|null
     */
    public function findById(int $id): ?BaseEntity {
        try {
            $sql  = "SELECT * FROM {$this->tableName} WHERE {$this->primaryKey} = :id LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result ? $this->hydrate($result) : null;
        } catch (PDOException $e) {
            $this->logError($e);
            return null;
        }
    }

    /**
     * Supprimer une entrée par sa clé primaire
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool {
        try {
            $sql  = "DELETE FROM {$this->tableName} WHERE {$this->primaryKey} = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            $this->logError($e);
            return false;
        }
    }

    /**
     * Compter le nombre total d'entrées dans la table
     *
     * @return int
     */
    public function count(): int {
        try {
            $sql  = "SELECT COUNT(*) FROM {$this->tableName}";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            $this->logError($e);
            return 0;
        }
    }

    /**
     * Vérifier si une entrée existe par sa clé primaire
     *
     * @param int $id
     * @return bool
     */
    public function exists(int $id): bool {
        try {
            $sql  = "SELECT COUNT(*) FROM {$this->tableName} WHERE {$this->primaryKey} = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            return (int)$stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            $this->logError($e);
            return false;
        }
    }

    // =========================================
    // MÉTHODES PROTÉGÉES UTILITAIRES
    // =========================================

    /**
     * Exécuter une requête SQL avec des paramètres (INSERT, UPDATE, DELETE)
     *
     * @param string $sql
     * @param array  $params
     * @return bool
     */
    protected function executeCommand(string $sql, array $params = []): bool {
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            $this->logError($e);
            return false;
        }
    }

    /**
     * Retourner l'ID du dernier enregistrement inséré
     *
     * @return int
     */
    protected function lastInsertId(): int {
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Hydrater plusieurs résultats en tableau d'objets
     *
     * @param array $results
     * @return array
     */
    public function hydrateMultiple(array $results): array {
        $entities = [];
        foreach ($results as $row) {
            $entities[] = $this->hydrate($row);
        }
        return $entities;
    }

    /**
     * Logger une erreur PDO dans logs/repository.log
     *
     * @param PDOException $e
     */
    protected function logError(PDOException $e): void {
        $logFile = ROOT_PATH . '/logs/repository.log';

        if (!is_dir(dirname($logFile))) {
            mkdir(dirname($logFile), 0755, true);
        }

        $message = sprintf(
            "[%s] Erreur PDO dans %s : %s%s",
            date('Y-m-d H:i:s'),
            static::class,
            $e->getMessage(),
            PHP_EOL
        );

        file_put_contents($logFile, $message, FILE_APPEND);
    }
}