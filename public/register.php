<?php
/**
 * Point d'entrée - Inscription
 * 
 * GET  → affiche le formulaire d'inscription
 * POST → traite le formulaire d'inscription
 */
require_once dirname(__DIR__) . '/config/config.php';

use App\Controllers\AuthController;

$controller = new AuthController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->register();
} else {
    $controller->showRegisterForm();
}