<?php
namespace App\Config;

use PDO;
use PDOException;

/**
 * Classe Database - Pattern Singleton pour la connexion à la base de données
 * 
 * Gére la connexion unique à la base de données en utilisant PDO.
 * 
 * Concepts:
 * - Pattern Singleton (Une seule instance de connexion)
 * - PDO pour l'abstraction de la base de données
 * - Gestion des erreurs de connexion TRY/CATCH
 * - Utilisation des variables d'environnement pour les paramètres de connexion
 * - Méthodes et attributs statiques
 */
class Database {

    /**
     * Instance unique de Database
     * @var Database|null
     */
    private static ?Database $instance = null;

    /**
     * Connexion PDO à la base de données
     * @var PDO|null
     */
    private ?PDO $connection = null;

    private string $host;
    private int    $port;
    private string $dbName;
    private string $username;
    private string $password;

    /**
     * Constructeur privé : empêche l'instanciation directe (pattern Singleton)
     * Charge les constantes définies dans config/config.php
     */
    private function __construct() {
        $this->host     = DB_HOST;
        $this->port     = (int) DB_PORT;
        $this->dbName   = DB_DATABASE;
        $this->username = DB_USERNAME;
        $this->password = DB_PASSWORD;
    }

    /**
     * Empêche le clonage de l'instance (pattern Singleton)
     */
    private function __clone() {}

    /**
     * Obtenir l'instance unique de Database.
     * Si elle n'existe pas, la créer. Sinon, retourner l'existante.
     * 
     * @return Database
     */
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    /**
     * Retourne la connexion PDO.
     * La connexion est créée uniquement lors du premier appel (lazy loading).
     * 
     * @return PDO
     * @throws \RuntimeException si la connexion échoue
     */
    public function getConnection(): PDO {
        if ($this->connection === null) {
            try {
                $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->dbName};charset=utf8mb4";

                $options = [
                    // Lancer des exceptions en cas d'erreur SQL
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,

                    // Retourner des tableaux associatifs par défaut
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

                    // Désactiver l'émulation pour une meilleure sécurité
                    PDO::ATTR_EMULATE_PREPARES   => false,

                    // Pas de connexions persistantes
                    PDO::ATTR_PERSISTENT         => false,
                ];

                $this->connection = new PDO($dsn, $this->username, $this->password, $options);

            } catch (PDOException $e) {
                error_log("[Database] Erreur de connexion : " . $e->getMessage());
                throw new \RuntimeException("Impossible de se connecter à la base de données.");
            }
        }

        return $this->connection;
    }

    /**
     * Ferme la connexion PDO
     */
    public function closeConnection(): void {
        $this->connection = null;
    }
}