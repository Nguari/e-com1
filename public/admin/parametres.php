<?php
require_once dirname(__DIR__, 2) . '/config/config.php';

use App\Utils\Auth;
use App\Config\Database;

// Vérifier si l'utilisateur est admin
if (!Auth::check() || !Auth::isAdmin()) {
    header('Location: ' . url('login.php'));
    exit();
}

$pageTitle = 'Paramètres - Admin';
$adminPage = 'parametres';

// Inclure la vue (le template)
include view_path('admin/parametres.php');