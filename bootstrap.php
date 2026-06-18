<?php
// bootstrap.php - À placer à la racine du projet (dans e-com/)

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuration du fuseau horaire
date_default_timezone_set('Africa/Dakar');

// Définir l'environnement (développement par défaut)
define('APP_ENV', getenv('APP_ENV') ?: 'development');

// Gestion des erreurs
if (APP_ENV === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Headers de sécurité (sauf pour les assets)
if (!str_contains($_SERVER['REQUEST_URI'] ?? '', '.css') && 
    !str_contains($_SERVER['REQUEST_URI'] ?? '', '.js') &&
    !str_contains($_SERVER['REQUEST_URI'] ?? '', '.jpg') &&
    !str_contains($_SERVER['REQUEST_URI'] ?? '', '.png')) {
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('X-XSS-Protection: 1; mode=block');
}

// Chemins constants
define('BASE_PATH', __DIR__);
define('SRC_PATH', BASE_PATH . '/src');
define('VIEWS_PATH', BASE_PATH . '/views');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('ASSETS_PATH', BASE_PATH . '/public/assets');

// URL de base (à ajuster selon votre configuration)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$uri = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
define('BASE_URL', $protocol . '://' . $host . $uri);

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = SRC_PATH . '/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Fonctions globales
function url(string $path = ''): string {
    $base = rtrim(APP_URL, '/');
    // Si le chemin commence par 'admin/', on ne touche pas
    if (str_starts_with($path, 'admin/')) {
        return $base . '/' . ltrim($path, '/');
    }
    // Pour les autres chemins, on ajoute 'public/' sauf pour les assets
    if (!preg_match('#^(assets/|imgs/|css/|js/|uploads/|vendor/)#', $path) && !str_starts_with($path, 'http')) {
        $path = 'public/' . ltrim($path, '/');
    }
    return $base . '/' . ltrim($path, '/');
}

function view_path(string $path): string {
    return VIEWS_PATH . '/' . ltrim($path, '/');
}

function asset(string $path): string {
    return BASE_URL . '/assets/' . ltrim($path, '/');
}

function formatFCFA(int $amount): string {
    return number_format($amount, 0, ',', ' ') . ' FCFA';
}

function redirect(string $url): void {
    header('Location: ' . url($url));
    exit();
}

function old(string $key, string $default = ''): string {
    return htmlspecialchars($_POST[$key] ?? $default);
}

// Générer token CSRF si inexistant
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Connexion à la base de données (optionnelle, peut être appelée à la demande)
$db = null;
try {
    $config = require BASE_PATH . '/config/config.php';
    $db = new PDO(
        "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4",
        $config['db_user'],
        $config['db_pass'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    if (APP_ENV !== 'production') {
        die("Erreur de connexion à la base de données : " . $e->getMessage());
    }
    // En production, logguer l'erreur silencieusement
    error_log($e->getMessage());
}