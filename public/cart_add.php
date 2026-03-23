<?php
require_once dirname(__DIR__) . '/config/config.php';

use App\Config\Database;
use App\Repositories\CartRepository;
use App\Utils\Auth;

// 1. VÉRIFICATION MÉTHODE POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('boutique.php'));
    exit();
}

// 2. VÉRIFICATION CSRF
if (
    !isset($_POST['csrf_token'], $_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    $_SESSION['flash_error'] = "Erreur de sécurité, veuillez réessayer.";
    header('Location: ' . url('boutique.php'));
    exit();
}

// 3. VÉRIFICATION CONNEXION
if (!Auth::check()) {
    $_SESSION['flash_error'] = "Connectez-vous pour ajouter au panier.";
    header('Location: ' . url('login.php'));
    exit();
}

// 4. RÉCUPÉRATION DES DONNÉES
$idProduit     = (int)($_POST['id_produit'] ?? 0);
$idUtilisateur = (int)Auth::id();
$quantite      = max(1, (int)($_POST['quantite'] ?? 1));

if ($idProduit <= 0) {
    $_SESSION['flash_error'] = "Produit invalide.";
    header('Location: ' . url('boutique.php'));
    exit();
}

// 5. AJOUT EN BASE DE DONNÉES
try {
    $db             = Database::getInstance()->getConnection();
    $cartRepository = new CartRepository($db);

    $success = $cartRepository->addOrUpdate($idUtilisateur, $idProduit, $quantite);

    if ($success) {
        $_SESSION['flash_success'] = "Produit ajouté au panier !";
    } else {
        $_SESSION['flash_error'] = "Erreur lors de l'ajout au panier.";
    }

} catch (\Exception $e) {
    error_log("[cart_add] Erreur : " . $e->getMessage());
    $_SESSION['flash_error'] = "Erreur lors de l'ajout au panier.";
}

// 6. REDIRECTION VERS LA PAGE PRÉCÉDENTE
$retour = $_POST['retour'] ?? 'boutique.php';
header('Location: ' . url($retour));
exit();