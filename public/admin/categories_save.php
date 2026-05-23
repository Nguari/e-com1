<?php
require_once dirname(__DIR__, 2) . '/config/config.php';

use App\Config\Database;
use App\Utils\Auth;

if (!Auth::check() || !Auth::isAdmin()) {
    header('Location: ' . url('login.php'));
    exit();
}

$db = Database::getInstance()->getConnection();

$nom = trim($_POST['nom'] ?? '');

if (empty($nom)) {
    $_SESSION['flash_error'] = "Le nom est requis.";
    header('Location: ' . url('admin/categories.php'));
    exit();
}

// Générer un slug unique
function generateUniqueSlug($db, $nom, $id = null) {
    // Convertir en slug
    $slug = strtolower(trim($nom));
    $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    $slug = trim($slug, '-');
    
    // Si slug est vide, utiliser un fallback
    if (empty($slug)) {
        $slug = 'categorie-' . time();
    }
    
    // Vérifier l'unicité
    $originalSlug = $slug;
    $counter = 1;
    
    while (true) {
        if ($id) {
            $stmt = $db->prepare("SELECT id_categorie FROM categories WHERE slug = :slug AND id_categorie != :id");
            $stmt->execute([':slug' => $slug, ':id' => $id]);
        } else {
            $stmt = $db->prepare("SELECT id_categorie FROM categories WHERE slug = :slug");
            $stmt->execute([':slug' => $slug]);
        }
        
        if (!$stmt->fetch()) {
            break;
        }
        
        $slug = $originalSlug . '-' . $counter;
        $counter++;
    }
    
    return $slug;
}

$slug = generateUniqueSlug($db, $nom);

// Vérifier si la catégorie existe déjà
$check = $db->prepare("SELECT id_categorie FROM categories WHERE nom = :nom");
$check->execute([':nom' => $nom]);
if ($check->fetch()) {
    $_SESSION['flash_error'] = "Une catégorie avec ce nom existe déjà.";
    header('Location: ' . url('admin/categories.php'));
    exit();
}

$stmt = $db->prepare("INSERT INTO categories (nom, slug) VALUES (:nom, :slug)");
$stmt->execute([':nom' => $nom, ':slug' => $slug]);

$_SESSION['flash_success'] = "Catégorie ajoutée avec succès !";
header('Location: ' . url('admin/categories.php'));