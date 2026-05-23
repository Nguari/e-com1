<?php
require_once dirname(__DIR__) . '/config/config.php';

use App\Config\Database;
use App\Utils\Auth;

header('Content-Type: application/json');

if (!Auth::check()) {
    echo json_encode(['items' => [], 'total' => '0 FCFA', 'total_items' => 0]);
    exit();
}

$db = Database::getInstance()->getConnection();
$userId = Auth::id();

/**
 * Récupère la première image d'un produit (images multiples ou unique)
 */
function getProductImage($produit) {
    // Vérifier les images multiples (JSON)
    if (!empty($produit['images'])) {
        $images = json_decode($produit['images'], true);
        if (is_array($images) && !empty($images)) {
            return '/assets/img/produits/' . $images[0];
        }
    }
    
    // Fallback sur l'image unique
    if (!empty($produit['image'])) {
        return '/assets/img/produits/' . $produit['image'];
    }
    
    return '/assets/img/produits/default.jpg';
}

$stmt = $db->prepare("
    SELECT p.id_produit, p.nom, p.prix, p.image, p.images, pc.quantite,
           (p.prix * pc.quantite) as subtotal
    FROM panier pc
    JOIN produits p ON pc.id_produit = p.id_produit
    WHERE pc.id_utilisateur = :id
");
$stmt->execute([':id' => $userId]);
$items = $stmt->fetchAll();

$total = 0;
$cartItems = [];

foreach ($items as $item) {
    $subtotal = $item['prix'] * $item['quantite'];
    $total += $subtotal;
    
    // Construire l'URL de l'image en utilisant la nouvelle fonction
    $imageUrl = !empty($item['images']) 
        ? getProductImage($item) 
        : (!empty($item['image']) 
            ? url('assets/img/produits/' . $item['image'])
            : url('assets/img/produits/default.jpg'));
    
    $cartItems[] = [
        'id' => $item['id_produit'],
        'name' => htmlspecialchars($item['nom']),
        'price' => number_format($item['prix'], 0, ',', ' ') . ' FCFA',
        'subtotal' => number_format($subtotal, 0, ',', ' ') . ' FCFA',
        'quantity' => (int)$item['quantite'],
        'image' => $imageUrl
    ];
}

echo json_encode([
    'items' => $cartItems,
    'total' => number_format($total, 0, ',', ' ') . ' FCFA',
    'total_items' => count($items)
]);