<?php
// public/checkout.php
require_once dirname(__DIR__) . '/config/config.php';

use App\Config\Database;
use App\Controllers\CheckoutController;

$db         = Database::getInstance()->getConnection();
$controller = new CheckoutController($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->process();
} else {
    $controller->index();
}