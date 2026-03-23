<?php
// public/admin/utilisateur_delete.php
require_once dirname(__DIR__, 2) . '/config/config.php';
\App\Utils\AdminMiddleware::check();

use App\Config\Database;
use App\Controllers\Admin\UtilisateurController;

$id         = (int)($_GET['id'] ?? 0);
$db         = Database::getInstance()->getConnection();
$controller = new UtilisateurController($db);
$controller->delete($id);