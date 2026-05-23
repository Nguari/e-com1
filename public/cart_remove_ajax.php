<?php
require_once dirname(__DIR__) . '/config/config.php';

use App\Config\Database;
use App\Utils\Auth;

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

if ($productId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Données invalides']);
    exit();
}

$db = Database::getInstance()->getConnection();
$userId = Auth::id();

$stmt = $db->prepare("DELETE FROM panier WHERE id_utilisateur = :id AND id_produit = :produit");
$result = $stmt->execute([
    ':id' => $userId,
    ':produit' => $productId
]);

echo json_encode(['success' => $result]);