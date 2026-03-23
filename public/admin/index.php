<?php
// public/admin/index.php
require_once dirname(__DIR__, 2) . '/config/config.php';
\App\Utils\AdminMiddleware::check();

use App\Config\Database;
use App\Controllers\Admin\DashboardController;

$db         = Database::getInstance()->getConnection();
$controller = new DashboardController($db);
$controller->index();