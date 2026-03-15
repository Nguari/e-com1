<?php
/**
 * Point d'entrée - Déconnexion
 * 
 * Déconnecte l'utilisateur et redirige vers la page de connexion
 */
require_once dirname(__DIR__) . '/config/config.php';

use App\Controllers\AuthController;

$controller = new AuthController();
$controller->logout();