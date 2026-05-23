<?php
require_once dirname(__DIR__) . '/config/config.php';

use App\Config\Database;
use App\Utils\Auth;
use App\Utils\Session;

header('Content-Type: application/json');

if (!Auth::check()) {
    echo json_encode(['success' => false, 'error' => 'Non authentifié']);
    exit();
}

if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'error' => 'Token CSRF invalide']);
    exit();
}

$productId = (int)($_POST['id_produit'] ?? 0);
$quantity = (int)($_POST['quantite'] ?? 1);

if ($productId <= 0 || $quantity < 1) {
    echo json_encode(['success' => false, 'error' => 'Données invalides']);
    exit();
}

$db = Database::getInstance()->getConnection();
$userId = Auth::id();

$stmt = $db->prepare("UPDATE panier SET quantite = :quantite WHERE id_utilisateur = :id AND id_produit = :produit");
$result = $stmt->execute([
    ':quantite' => $quantity,
    ':id' => $userId,
    ':produit' => $productId
]);

echo json_encode(['success' => $result]);