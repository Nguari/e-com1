<?php

namespace App\Controllers\Admin;

use PDO;
use App\Repositories\ProduitRepository;

class ProduitController {

    private ProduitRepository $produitRepo;
    private string $uploadDir;

    public function __construct(PDO $db) {
        $this->produitRepo = new ProduitRepository($db);
        
        // Définir le dossier d'upload
        $this->uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/produits/';
        
        // Créer le dossier s'il n'existe pas
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    public function index(): void {
        $produits = $this->produitRepo->findAllWithCategorie();
        include view_path('admin/produits/index.php');
    }

    public function create(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->store();
            return;
        }
        $produit = null;
        include view_path('admin/produits/form.php');
    }

    public function store(): void {
        // Vérifier le token CSRF
        if (!$this->validateCsrfToken()) {
            $_SESSION['flash_error'] = "Erreur de sécurité. Veuillez réessayer.";
            header('Location: ' . url('admin/produit_add.php'));
            exit();
        }
        
        // Upload des images multiples
        $uploadedImages = $this->uploadMultipleImages($_FILES['images'] ?? null);
        
        if (empty($uploadedImages)) {
            $_SESSION['flash_error'] = "Au moins une image est obligatoire.";
            header('Location: ' . url('admin/produit_add.php'));
            exit();
        }
        
        // Convertir le tableau d'images en JSON
        $imagesJson = json_encode($uploadedImages);
        
        $data = [
            'reference'   => trim($_POST['reference']   ?? ''),
            'nom'         => trim($_POST['nom']         ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'images'      => $imagesJson,
            'prix'        => (float)($_POST['prix']     ?? 0),
            'prix_promo'  => !empty($_POST['prix_promo']) ? (float)$_POST['prix_promo'] : null,
            'stock'       => (int)($_POST['stock']      ?? 0),
            'id_categorie'=> !empty($_POST['id_categorie']) ? (int)$_POST['id_categorie'] : null,
            'actif'       => isset($_POST['actif']) ? 1 : 0,
        ];

        if ($this->produitRepo->create($data)) {
            $_SESSION['flash_success'] = "Produit créé avec succès !";
        } else {
            $_SESSION['flash_error'] = "Erreur lors de la création.";
        }

        header('Location: ' . url('admin/produits.php'));
        exit();
    }

    public function edit(int $id): void {
        $produit = $this->produitRepo->findById($id);
        if (!$produit) {
            header('Location: ' . url('admin/produits.php'));
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->update($id);
            return;
        }

        include view_path('admin/produits/form.php');
    }

    public function update(int $id): void {
        // Vérifier le token CSRF
        if (!$this->validateCsrfToken()) {
            $_SESSION['flash_error'] = "Erreur de sécurité. Veuillez réessayer.";
            header('Location: ' . url('admin/produit_edit.php?id=' . $id));
            exit();
        }
        
        // Récupérer le produit existant
        $existingProduit = $this->produitRepo->findById($id);
        $oldImages = [];
        if ($existingProduit && !empty($existingProduit['images'])) {
            $oldImages = json_decode($existingProduit['images'], true) ?: [];
        }
        
        // Images à conserver (celles qui n'ont pas été supprimées)
        $keepImages = [];
        if (isset($_POST['existing_images']) && !empty($_POST['existing_images'])) {
            $keepImages = json_decode($_POST['existing_images'], true) ?: [];
        }
        
        // Supprimer les fichiers des images non conservées
        $imagesToDelete = array_diff($oldImages, $keepImages);
        foreach ($imagesToDelete as $img) {
            $filePath = $this->uploadDir . $img;
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        
        // Upload des nouvelles images
        $newImages = $this->uploadMultipleImages($_FILES['images'] ?? null);
        
        // Fusionner les images conservées et les nouvelles
        $allImages = array_merge($keepImages, $newImages);
        $imagesJson = json_encode($allImages);
        
        $data = [
            'nom'         => trim($_POST['nom']         ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'images'      => $imagesJson,
            'prix'        => (float)($_POST['prix']     ?? 0),
            'prix_promo'  => !empty($_POST['prix_promo']) ? (float)$_POST['prix_promo'] : null,
            'stock'       => (int)($_POST['stock']      ?? 0),
            'id_categorie'=> !empty($_POST['id_categorie']) ? (int)$_POST['id_categorie'] : null,
            'actif'       => isset($_POST['actif']) ? 1 : 0,
        ];

        if ($this->produitRepo->update($id, $data)) {
            $_SESSION['flash_success'] = "Produit mis à jour !";
        } else {
            $_SESSION['flash_error'] = "Erreur lors de la mise à jour.";
        }

        header('Location: ' . url('admin/produits.php'));
        exit();
    }

    public function delete(int $id): void {
        // Récupérer les images avant suppression
        $produit = $this->produitRepo->findById($id);
        if ($produit && !empty($produit['images'])) {
            $images = json_decode($produit['images'], true) ?: [];
            foreach ($images as $image) {
                $filePath = $this->uploadDir . $image;
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
        }
        
        $this->produitRepo->delete($id);
        $_SESSION['flash_success'] = "Produit supprimé.";
        header('Location: ' . url('admin/produits.php'));
        exit();
    }

    public function toggleActif(int $id): void {
        $this->produitRepo->toggleActif($id);
        $_SESSION['flash_success'] = "Statut du produit modifié.";
        header('Location: ' . url('admin/produits.php'));
        exit();
    }
    
    /**
     * Upload de multiples images
     * 
     * @param array|null $files Le tableau $_FILES['images']
     * @return array Liste des noms de fichiers uploadés
     */
    private function uploadMultipleImages(?array $files): array {
        // Vérifier si des fichiers ont été uploadés
        if (!$files || !isset($files['tmp_name']) || empty($files['tmp_name'][0])) {
            return [];
        }
        
        $uploadedImages = [];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB par image
        
        foreach ($files['tmp_name'] as $key => $tmpName) {
            // Vérifier s'il n'y a pas d'erreur
            if ($files['error'][$key] !== UPLOAD_ERR_OK) {
                continue;
            }
            
            // Vérifier l'extension
            $extension = strtolower(pathinfo($files['name'][$key], PATHINFO_EXTENSION));
            if (!in_array($extension, $allowedExtensions)) {
                $_SESSION['flash_error'] = "Extension non autorisée pour " . $files['name'][$key];
                continue;
            }
            
            // Vérifier le MIME type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $tmpName);
            finfo_close($finfo);
            
            if (!in_array($mimeType, $allowedTypes)) {
                $_SESSION['flash_error'] = "Format d'image non autorisé pour " . $files['name'][$key];
                continue;
            }
            
            // Vérifier la taille
            if ($files['size'][$key] > $maxSize) {
                $_SESSION['flash_error'] = "L'image " . $files['name'][$key] . " dépasse 5MB";
                continue;
            }
            
            // Générer un nom unique
            $imageName = uniqid('produit_') . '.' . $extension;
            $destination = $this->uploadDir . $imageName;
            
            // Déplacer le fichier
            if (move_uploaded_file($tmpName, $destination)) {
                $uploadedImages[] = $imageName;
            } else {
                $_SESSION['flash_error'] = "Erreur lors de l'upload de " . $files['name'][$key];
            }
        }
        
        return $uploadedImages;
    }
    
    /**
     * Valide le token CSRF
     */
    private function validateCsrfToken(): bool {
        if (!isset($_POST['csrf_token'], $_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
    }
}