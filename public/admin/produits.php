<?php
// public/admin/produits.php
// IMPORTANT: charger bootstrap (autoload + helpers) avant toute instanciation App\...
$basePath = dirname(__DIR__, 2); // => project root

// Assurer le chargement bootstrap (autoload + helpers)
require_once $basePath . '/bootstrap.php';
require_once $basePath . '/config/config.php';

\App\Utils\AdminMiddleware::check();

use App\Config\Database;
use App\Controllers\Admin\ProduitController;

// Forcer le chargement du contrôleur (évite les soucis d’autoload sur l’hébergement)
$controllerFile = $basePath . '/src/Controllers/Admin/ProduitController.php';
if (!file_exists($controllerFile)) {
    die('Controller file missing: ' . $controllerFile);
}
require_once $controllerFile;

$db         = Database::getInstance()->getConnection();
$controller = new ProduitController($db);
$controller->index();


