<?php
require_once dirname(__DIR__) . '/config/config.php';

use App\Config\Database;
use App\Repositories\CartRepository;
use App\Utils\Auth;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('cart.php'));
    exit();
}

if (!isset($_POST['csrf_token'], $_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $_SESSION['flash_error'] = "Erreur de sécurité.";
    header('Location: ' . url('cart.php'));
    exit();
}

if (!Auth::check()) {
    header('Location: ' . url('login.php'));
    exit();
}

try {
    $db             = Database::getInstance()->getConnection();
    $cartRepository = new CartRepository($db);
    $cartRepository->clearCart((int)Auth::id());
    $_SESSION['flash_success'] = "Votre panier a été vidé.";
} catch (\Exception $e) {
    error_log("[cart_clear] " . $e->getMessage());
    $_SESSION['flash_error'] = "Erreur lors de la suppression.";
}

header('Location: ' . url('cart.php'));
exit();