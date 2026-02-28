<?php
/**
 * Fichier de configuration de l'application.
 * 
 * Charger les variables d'environnement depuis le fichier .env
 * et définir les constantes de l'application.
 * 
 * Ce fichier doit inclus au début de l'application
 * (dans public/index.php ou les scripts de test).
 * 
 * @author Babs
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Charger les variables d'environnement depuis le fichier .env
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// ===== CONSTANTES DE L'APPLICATION =====
define('APP_NAME', $_ENV['APP_NAME'] ?? 'Ngaary SHOP');
define('APP_ENV', $_ENV['APP_ENV'] ?? 'development');
define('APP_URL', $_ENV['APP_URL'] ?? 'http://e-com.test/public');
define('ROOT_PATH', dirname(__DIR__));
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('VIEW_PATH', ROOT_PATH . '/views');

// ===== CONSTANTES DE LA BASE DE DONNÉES =====
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_PORT', $_ENV['DB_PORT'] ?? 3306);
define('DB_DATABASE', $_ENV['DB_DATABASE'] ?? 'ecommerce_db');
define('DB_USERNAME', $_ENV['DB_USERNAME'] ?? 'root');
define('DB_PASSWORD', $_ENV['DB_PASSWORD'] ?? 'Nguari2006');

// ===== CONFIGURATION DE SECURITE =====
/**
 * Durée de vie de la session en secondes
 * Par défaut : 7200 secondes (2 heures)
 */
define('SESSION_LIFETIME', (int)($_ENV['SESSION_LIFETIME'] ?? 7200));

/**
 * Longueur minimale du mot de passe
 * Par défaut : 8 caractères
 */
define('PASSWORD_MIN_LENGTH', $_ENV['PASSWORD_MIN_LENGTH'] ?? 8);

date_default_timezone_set('Africa/Dakar'); // Définir le fuseau horaire par défaut

// ===== HELPERS =====

/**
 * Générer une URL complète pour une route donnée.
 * 
 * @param string $path Le chemin relatif de la route (ex: "/login").
 * @return string L'URL complète.
 */
function url(string $path = ''): string {
    return APP_URL . '/' . ltrim($path, '/');
}

/**
 * Obtenir le chemin complet d'une vue.
 * 
 * @param string $view Le chemin relatif de la vue (ex: "home/index.php").
 * @return string Le chemin complet de la vue.
 */
function view_path(string $view): string {
    return VIEW_PATH . '/' . ltrim($view, '/');
}