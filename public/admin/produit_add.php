<?php
// public/admin/produit_add.php
require_once dirname(__DIR__, 2) . '/config/config.php';
\App\Utils\AdminMiddleware::check();

use App\Config\Database;
use App\Controllers\Admin\ProduitController;

$db         = Database::getInstance()->getConnection();
$controller = new ProduitController($db);
$controller->create();