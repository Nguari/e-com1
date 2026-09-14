<?php

use Dotenv\Dotenv;

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';

$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $dotenv = Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->safeLoad();

    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || strpos($line, '=') === false) {
            continue;
        }

        [$key, $value] = array_map('trim', explode('=', $line, 2));
        $value = trim($value, " \t\n\r\0\x0B\"'");

        if (!array_key_exists($key, $_ENV)) {
            $_ENV[$key] = $value;
        }
        if (!array_key_exists($key, $_SERVER)) {
            $_SERVER[$key] = $value;
        }
        putenv($key . '=' . $value);
    }
}

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
$scriptDir = rtrim(dirname($scriptName), '/\\');

if (basename($scriptDir) === 'public') {
    $basePath = dirname($scriptDir);
} else {
    $basePath = $scriptDir;
}

if ($basePath === '/' || $basePath === '\\' || $basePath === '.' || $basePath === '') {
    $basePath = '';
}

$envValue = static fn (string $key, $default = null) => $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?: $default;

define('APP_BASE_PATH', $basePath);
define('APP_URL', $envValue('APP_URL', rtrim($protocol . '://' . $host . $basePath, '/')));
define('APP_NAME', $envValue('APP_NAME', 'Ngaary SHOP'));
define('APP_ENV', $envValue('APP_ENV', 'development'));

define('ROOT_PATH', dirname(__DIR__));
define('PUBLIC_PATH', ROOT_PATH . DS . 'public');
define('VIEW_PATH', ROOT_PATH . DS . 'views');
define('SRC_PATH', ROOT_PATH . DS . 'src');

define('DB_HOST', $envValue('DB_HOST', '127.0.0.1'));
define('DB_PORT', (int)($envValue('DB_PORT', 3306)));
define('DB_DATABASE', $envValue('DB_DATABASE', 'ecommerce_db'));
define('DB_USERNAME', $envValue('DB_USERNAME', 'root'));
define('DB_PASSWORD', $envValue('DB_PASSWORD', ''));

define('SESSION_LIFETIME', (int)($envValue('SESSION_LIFETIME', 7200)));
define('PASSWORD_MIN_LENGTH', (int)($envValue('PASSWORD_MIN_LENGTH', 8)));

date_default_timezone_set('Africa/Dakar');

if (!function_exists('src_path')) {
    function src_path(string $path): string {
        $path = str_replace(['/', '\\'], DS, $path);
        return SRC_PATH . DS . ltrim($path, DS);
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string {
        $base = rtrim(APP_URL, '/');

        if ($path === '') {
            return $base;
        }

        if (preg_match('#^(https?:)?//#i', $path) || str_starts_with($path, '/')) {
            return $path;
        }

        if (str_starts_with($path, 'admin/')) {
            return $base . '/' . ltrim($path, '/');
        }

        if (preg_match('#^(assets/|imgs/|css/|js/|uploads/|vendor/|public/)#', $path)) {
            return $base . '/' . ltrim($path, '/');
        }

        return $base . '/public/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string {
        return rtrim(APP_URL, '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url): void {
        $target = $url;

        if (!preg_match('#^(https?:)?//#i', $url) && !str_starts_with($url, '/')) {
            $target = url($url);
        }

        if (!headers_sent()) {
            header('Location: ' . $target);
        }

        exit;
    }
}

if (!function_exists('setting')) {
    function setting(string $key, $default = null) {
        static $settings = null;

        if (!defined('DB_HOST') || !DB_HOST) {
            return $default;
        }

        if ($settings === null) {
            try {
                if (class_exists('App\\Config\\Database')) {
                    $db = \App\Config\Database::getInstance()->getConnection();
                    $stmt = $db->query('SELECT setting_key, setting_value FROM settings');
                    $settings = [];
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $settings[$row['setting_key']] = $row['setting_value'];
                    }
                }
            } catch (\Throwable $e) {
                return $default;
            }
        }

        return $settings[$key] ?? $default;
    }
}

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

if (empty($_SESSION['csrf_token'] ?? null)) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (file_exists(__DIR__ . '/payment.php')) {
    require_once __DIR__ . '/payment.php';
}
