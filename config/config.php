<?php

use Dotenv\Dotenv;

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Fichier de configuration de l'application.
 * 
 * Charge les variables d'environnement depuis le fichier .env
 * et définit les constantes de l'application.
 * 
 * @author Babs
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Charger les variables d'environnement depuis le fichier .env
$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// ===== CONSTANTES DE L'APPLICATION =====
define('DS',          DIRECTORY_SEPARATOR);
define('APP_NAME',    $_ENV['APP_NAME'] ?? 'Ngaary SHOP');
define('APP_ENV',     $_ENV['APP_ENV']  ?? 'development');
define('APP_URL',     $_ENV['APP_URL']  ?? 'http://e-com.test/public');
define('ROOT_PATH',   dirname(__DIR__));
define('PUBLIC_PATH', ROOT_PATH . DS . 'public');
define('VIEW_PATH',   ROOT_PATH . DS . 'views');
define('SRC_PATH',    ROOT_PATH . DS . 'src');
define('URL_ROOT',    $_ENV['APP_URL']  ?? 'http://e-com.test/public');

// ===== CONSTANTES DE LA BASE DE DONNÉES =====
define('DB_HOST',     $_ENV['DB_HOST']     ?? '127.0.0.1');
define('DB_PORT',     $_ENV['DB_PORT']     ?? 3306);
define('DB_DATABASE', $_ENV['DB_DATABASE'] ?? 'ecommerce_db');
define('DB_USERNAME', $_ENV['DB_USERNAME'] ?? 'root');
define('DB_PASSWORD', $_ENV['DB_PASSWORD'] ?? 'Nguari2006');

// ===== CONFIGURATION DE SÉCURITÉ =====
define('SESSION_LIFETIME',    (int)($_ENV['SESSION_LIFETIME']    ?? 7200));
define('PASSWORD_MIN_LENGTH', (int)($_ENV['PASSWORD_MIN_LENGTH'] ?? 8));

// ===== FUSEAU HORAIRE =====
date_default_timezone_set('Africa/Dakar');

// ===== HELPERS =====

/**
 * Générer une URL web complète.
 * Toujours avec / (c'est une URL, pas un chemin fichier)
 */
function url(string $path = ''): string {
    return APP_URL . '/' . ltrim($path, '/');
}

/**
 * Obtenir le chemin fichier complet d'une vue.
 * Compatible Windows (\) et Linux (/)
 */
function view_path(string $view): string {
    $view = str_replace(['/', '\\'], DS, $view);
    return VIEW_PATH . DS . ltrim($view, DS);
}

/**
 * Obtenir le chemin fichier complet d'un fichier src/
 */
function src_path(string $path): string {
    $path = str_replace(['/', '\\'], DS, $path);
    return SRC_PATH . DS . ltrim($path, DS);
}

/**
 * Récupère un paramètre depuis la table settings
 * 
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function setting(string $key, $default = null) {
    static $settings = null;
    if ($settings === null) {
        try {
            $db = \App\Config\Database::getInstance()->getConnection();
            $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
            $settings = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        } catch (\Exception $e) {
            return $default;
        }
    }
    return $settings[$key] ?? $default;
}

/**
 * Formate un montant en FCFA
 * Exemple : formatFCFA(15000) → "15 000 FCFA"
 */
function formatFCFA(int $montant): string {
    return number_format($montant, 0, ',', ' ') . ' FCFA';
}

// ===== VALIDATION DES VARIABLES OBLIGATOIRES =====
$dotenv->required(['DB_HOST', 'DB_DATABASE', 'DB_USERNAME'])->notEmpty();
// Inclure la configuration des paiements
require_once __DIR__ . '/payment.php';