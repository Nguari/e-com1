<?php

date_default_timezone_set('Africa/Dakar');

require_once __DIR__ . '/config/config.php';

if (APP_ENV === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

if (!str_contains($_SERVER['REQUEST_URI'] ?? '', '.css') &&
    !str_contains($_SERVER['REQUEST_URI'] ?? '', '.js') &&
    !str_contains($_SERVER['REQUEST_URI'] ?? '', '.jpg') &&
    !str_contains($_SERVER['REQUEST_URI'] ?? '', '.png')) {
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('X-XSS-Protection: 1; mode=block');
}

if (!defined('BASE_PATH')) {
    define('BASE_PATH', ROOT_PATH);
}
if (!defined('BASE_URL')) {
    define('BASE_URL', APP_URL);
}
if (!defined('VIEWS_PATH')) {
    define('VIEWS_PATH', VIEW_PATH);
}
if (!defined('ASSETS_PATH')) {
    define('ASSETS_PATH', PUBLIC_PATH . '/assets');
}

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

if (!function_exists('view_path')) {
    function view_path(string $path): string {
        return VIEW_PATH . '/' . ltrim($path, '/');
    }
}

if (!function_exists('formatFCFA')) {
    function formatFCFA(int $amount): string {
        return number_format($amount, 0, ',', ' ') . ' FCFA';
    }
}

if (!function_exists('old')) {
    function old(string $key, string $default = ''): string {
        return htmlspecialchars($_POST[$key] ?? $default);
    }
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}