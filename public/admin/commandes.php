<?php
// public/admin/commandes.php
require_once dirname(__DIR__, 2) . '/config/config.php';
\App\Utils\AdminMiddleware::check();

use App\Config\Database;
use App\Controllers\Admin\CommandeController;

$db         = Database::getInstance()->getConnection();
$controller = new CommandeController($db);
$controller->index();