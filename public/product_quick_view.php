<?php
require_once dirname(__DIR__) . '/config/config.php';

use App\Config\Database;
use App\Repositories\ProduitRepository;

header('Content-Type: application/json');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    echo json_encode(['success' => false]);
    exit();
}

$db = Database::getInstance()->getConnection();
$produitRepo = new ProduitRepository($db);
$produit = $produitRepo->findById($id);

if (!$produit) {
    echo json_encode(['success' => false]);
    exit();
}

/**
 * Récupère la première image du produit (images multiples ou unique)
 */
function getQuickViewImage($produit) {
    // Vérifier les images multiples (JSON)
    if (!empty($produit['images'])) {
        $images = json_decode($produit['images'], true);
        if (is_array($images) && !empty($images)) {
            return url('assets/img/produits/' . $images[0]);
        }
    }
    
    // Fallback sur l'image unique
    if (!empty($produit['image'])) {
        return url('assets/img/produits/' . $produit['image']);
    }
    
    return url('assets/img/produits/default.jpg');
}

/**
 * Compte le nombre total d'images
 */
function getQuickViewImageCount($produit) {
    if (!empty($produit['images'])) {
        $images = json_decode($produit['images'], true);
        if (is_array($images)) {
            return count($images);
        }
    }
    return !empty($produit['image']) ? 1 : 0;
}

$imagePath = getQuickViewImage($produit);
$imageCount = getQuickViewImageCount($produit);

$description = htmlspecialchars(substr($produit['description'] ?? '', 0, 200));
if (strlen($produit['description'] ?? '') > 200) {
    $description .= '...';
}

// Générer les miniatures pour la galerie
$thumbnails = [];
if (!empty($produit['images'])) {
    $images = json_decode($produit['images'], true);
    if (is_array($images)) {
        foreach ($images as $img) {
            $thumbnails[] = url('assets/img/produits/' . $img);
        }
    }
} elseif (!empty($produit['image'])) {
    $thumbnails[] = url('assets/img/produits/' . $produit['image']);
}

echo json_encode([
    'success' => true,
    'id' => $produit['id_produit'],
    'name' => htmlspecialchars($produit['nom']),
    'description' => $description,
    'price' => number_format($produit['prix'], 0, ',', ' ') . ' FCFA',
    'old_price' => (!empty($produit['prix_promo']) && $produit['prix_promo'] > 0 && $produit['prix_promo'] < $produit['prix']) 
        ? number_format($produit['prix'], 0, ',', ' ') . ' FCFA' 
        : null,
    'image' => $imagePath,
    'images' => $thumbnails,
    'image_count' => $imageCount,
    'stock' => (int)$produit['stock'],
    'reference' => $produit['reference'] ?? '',
    'categorie' => $produit['categorie_nom'] ?? ''
]);