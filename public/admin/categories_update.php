<?php
require_once dirname(__DIR__, 2) . '/config/config.php';

use App\Config\Database;
use App\Utils\Auth;

if (!Auth::check() || !Auth::isAdmin()) {
    header('Location: ' . url('login.php'));
    exit();
}

$db = Database::getInstance()->getConnection();

$id = (int)($_POST['id'] ?? 0);
$nom = trim($_POST['nom'] ?? '');
$slug = trim($_POST['slug'] ?? '');

if ($id <= 0 || empty($nom)) {
    $_SESSION['flash_error'] = "Données invalides.";
    header('Location: ' . url('admin/categories.php'));
    exit();
}

if (empty($slug)) {
    $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9-]+/', '-', $nom), '-'));
}

// Vérifier si le slug existe déjà pour une autre catégorie
$check = $db->prepare("SELECT id_categorie FROM categories WHERE slug = :slug AND id_categorie != :id");
$check->execute([':slug' => $slug, ':id' => $id]);
if ($check->fetch()) {
    $_SESSION['flash_error'] = "Une catégorie avec ce slug existe déjà.";
    header('Location: ' . url('admin/categories.php'));
    exit();
}

$stmt = $db->prepare("UPDATE categories SET nom = :nom, slug = :slug WHERE id_categorie = :id");
$stmt->execute([':nom' => $nom, ':slug' => $slug, ':id' => $id]);

$_SESSION['flash_success'] = "Catégorie modifiée avec succès !";
header('Location: ' . url('admin/categories.php'));