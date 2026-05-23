<?php
// public/wishlist_toggle.php
require_once dirname(__DIR__) . '/config/config.php';

use App\Config\Database;
use App\Utils\Auth;

if (!Auth::check()) {
    // Si requête AJAX
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'unauthenticated']);
        exit();
    }
    header('Location: ' . url('login.php'));
    exit();
}

$idProduit = (int)($_POST['id_produit'] ?? $_GET['id_produit'] ?? 0);

if ($idProduit <= 0) {
    header('Location: ' . url('boutique.php'));
    exit();
}

try {
    $db   = Database::getInstance()->getConnection();

    // Vérifier si déjà en favoris
    $stmt = $db->prepare("
        SELECT id_favori FROM favoris
        WHERE id_utilisateur = :id_user AND id_produit = :id_produit
    ");
    $stmt->execute([':id_user' => Auth::id(), ':id_produit' => $idProduit]);
    $favori = $stmt->fetch();

    if ($favori) {
        // Supprimer
        $db->prepare("DELETE FROM favoris WHERE id_favori = :id")
           ->execute([':id' => $favori['id_favori']]);
        $action  = 'removed';
        $message = 'Produit retiré des favoris.';
    } else {
        // Ajouter
        $db->prepare("
            INSERT INTO favoris (id_utilisateur, id_produit)
            VALUES (:id_user, :id_produit)
        ")->execute([':id_user' => Auth::id(), ':id_produit' => $idProduit]);
        $action  = 'added';
        $message = 'Produit ajouté aux favoris !';
    }

    // Compter les favoris
    $stmtCount = $db->prepare("SELECT COUNT(*) FROM favoris WHERE id_utilisateur = :id");
    $stmtCount->execute([':id' => Auth::id()]);
    $count = (int)$stmtCount->fetchColumn();

    // Réponse AJAX
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        header('Content-Type: application/json');
        echo json_encode([
            'status'  => 'success',
            'action'  => $action,
            'message' => $message,
            'count'   => $count,
        ]);
        exit();
    }

    $_SESSION['flash_success'] = $message;

} catch (\Exception $e) {
    error_log($e->getMessage());
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error']);
        exit();
    }
}

// Retour à la page précédente
$retour = $_POST['retour'] ?? $_GET['retour'] ?? 'boutique.php';
header('Location: ' . url($retour));
exit();