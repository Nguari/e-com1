<?php
/**
 * Point d'entrée - Connexion
 * 
 * GET  → affiche le formulaire de connexion
 * POST → traite le formulaire de connexion
 */
include __DIR__ . '/../views/layouts/header.php';
require_once dirname(__DIR__) . '/config/config.php';

use App\Controllers\AuthController;

$controller = new AuthController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->login();
} else {
    $controller->showLoginForm();
}
include __DIR__ . '/../views/layouts/footer.php';
