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
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Charger les variables d'environnement si le fichier .env existe
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $dotenv = Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->load();
}

// ===== DÉTECTION AUTOMATIQUE DE L'URL (pour éviter la 404) =====
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'darkgoldenrod-crab-568952.hostingersite.com';

// Chemin de base : répertoire du script (ex: / ou /sous-dossier)
$scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
// Si le script est dans /public, on remonte à la racine du site
if (basename($scriptDir) === 'public') {
    $basePath = dirname($scriptDir);
} else {
    $basePath = $scriptDir;
}
// Si $basePath est vide ou '/', on le laisse vide
if ($basePath === '/' || $basePath === '\\') {
    $basePath = '';
}
define('APP_BASE_PATH', $basePath);

// Construction de l'URL de base
$appUrl = rtrim($protocol . '://' . $host . $basePath, '/');
define('APP_URL', getenv('APP_URL') ?: $appUrl);

// ===== CONSTANTES DE L'APPLICATION =====
define('DS', DIRECTORY_SEPARATOR);
define('APP_NAME',    getenv('APP_NAME') ?: 'Ngaary SHOP');
define('APP_ENV',     getenv('APP_ENV') ?: 'production');

// Chemins absolus (inchangés, mais vérifiez qu'ils sont corrects sur Hostinger)
define('ROOT_PATH',   dirname(__DIR__));          // ex: /home/u493370766/domains/votredomaine/public_html
define('PUBLIC_PATH', ROOT_PATH . DS . 'public'); // ex: .../public_html/public
define('VIEW_PATH',   ROOT_PATH . DS . 'views');
define('SRC_PATH',    ROOT_PATH . DS . 'src');

// ===== CONSTANTES DE LA BASE DE DONNÉES (inchangées, mais vérifiez les valeurs) =====
define('DB_HOST',     getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT',     getenv('DB_PORT') ?: 3306);
define('DB_DATABASE', getenv('DB_DATABASE') ?: 'u493370766_e_com');
define('DB_USERNAME', getenv('DB_USERNAME') ?: 'u493370766_ngaary');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: 'Passer@2026'); // À adapter si nécessaire

// ===== CONFIGURATION DE SÉCURITÉ =====
define('SESSION_LIFETIME',    (int)(getenv('SESSION_LIFETIME') ?: 7200));
define('PASSWORD_MIN_LENGTH', (int)(getenv('PASSWORD_MIN_LENGTH') ?: 8));

// ===== FUSEAU HORAIRE =====
date_default_timezone_set('Africa/Dakar');





/**
 * Chemin absolu vers un fichier source
 */
function src_path(string $path): string {
    $path = str_replace(['/', '\\'], DS, $path);
    return SRC_PATH . DS . ltrim($path, DS);
}

/**
 * Récupère un paramètre depuis la table settings (sans boucle infinie)
 */
function setting(string $key, $default = null) {
    static $settings = null;
    if (!defined('DB_HOST') || !DB_HOST) return $default;
    
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


// ===== CHARGEMENT DES PARAMÈTRES DE PAIEMENT =====
if (file_exists(__DIR__ . '/payment.php')) {
    require_once __DIR__ . '/payment.php';
}