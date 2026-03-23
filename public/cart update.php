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

$idProduit = (int)($_POST['id_produit'] ?? 0);
$quantite  = (int)($_POST['quantite']   ?? 0);

if ($idProduit <= 0) {
    $_SESSION['flash_error'] = "Produit invalide.";
    header('Location: ' . url('cart.php'));
    exit();
}

try {
    $db             = Database::getInstance()->getConnection();
    $cartRepository = new CartRepository($db);
    $cartRepository->updateQuantite((int)Auth::id(), $idProduit, $quantite);
} catch (\Exception $e) {
    error_log("[cart_update] " . $e->getMessage());
    $_SESSION['flash_error'] = "Erreur lors de la mise à jour.";
}

header('Location: ' . url('cart.php'));
exit();