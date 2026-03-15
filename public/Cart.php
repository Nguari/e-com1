<?php
// public/cart.php
session_start();
require_once dirname(__DIR__) . '/config/config.php';

use App\Config\Database;
use App\Controllers\CartController;

$db         = Database::getInstance()->getConnection();
$controller = new CartController($db);
$controller->index();