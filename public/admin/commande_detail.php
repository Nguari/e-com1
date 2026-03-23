<?php
// public/admin/commande_detail.php
require_once dirname(__DIR__, 2) . '/config/config.php';
\App\Utils\AdminMiddleware::check();

use App\Config\Database;
use App\Controllers\Admin\CommandeController;

$id         = (int)($_GET['id'] ?? 0);
$db         = Database::getInstance()->getConnection();
$controller = new CommandeController($db);
$controller->detail($id);