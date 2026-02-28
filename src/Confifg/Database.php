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
 * - Pattern Singleton(Une seule instance de connexion)
 * - PDO pour l'abstraction de la base de données
 * - Gestion des erreurs de connexion TRY/CATCH
 * - Utilisation des variables d'environnement pour les paramètres de connexion
 * - Methodes et attributs statiques
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
    private int $port;
    private string $dbName;
    private string $username;
    private string $password;

    private function __construct() {
        // Chargement des variables d'environnement
        $this->host = DB_HOST;
        $this->port = DB_PORT;
        $this->dbName = DB_DATABASE;
        $this->username = DB_USERNAME;
        $this->password = DB_PASSWORD;
    }

    /**
     * Obtenir l'instance unique de Database
     * 
     * Si l'instance n'existe pas, la créer.
     * sinon, retourner l'instance existante.
     * 
     * @return Database
     */
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection(): PDO {
        if ($this->connection === null) {
            try {
                // DSN (Data Source Name): chaîne de connexion
                $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->dbName};charset=utf8mb4";
                
                // Options PDO
                $options = [
                    // Mode d'erreur : Exceptions (pour attrapper les erreurs avec try/catch)
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    
                    // Mode de récupération par défaut : tableau associatif
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

                    // Désactiver l'émulation des requêtes préparées pour une meilleure sécurité
                    PDO::ATTR_EMULATE_PREPARES => false,

                    // Ne pas utiliser de connexions persistantes(Réutilisation des connexions existantes)
                    PDO::ATTR_PERSISTENT => false,
                ];

                $this->connection = new PDO($dsn, $this->username, $this->password, $options);
            } catch (PDOException $e) {
                // Gérer les erreurs de connexion
                error_log($e->getMessage()); // écrit dans logs/errors.log
                throw new \RuntimeException("Erreur de connexion à la base de données.");
            }
        }
        return $this->connection;
    }
}