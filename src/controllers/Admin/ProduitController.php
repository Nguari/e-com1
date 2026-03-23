<?php

namespace App\Controllers\Admin;

use PDO;
use App\Repositories\ProduitRepository;

class ProduitController {

    private ProduitRepository $produitRepo;

    public function __construct(PDO $db) {
        $this->produitRepo = new ProduitRepository($db);
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
        include view_path('admin/produits/form.php');
    }

    public function store(): void {
        $data = [
            ':reference'   => trim($_POST['reference']   ?? ''),
            ':nom'         => trim($_POST['nom']         ?? ''),
            ':description' => trim($_POST['description'] ?? ''),
            ':prix'        => (float)($_POST['prix']     ?? 0),
            ':prix_promo'  => !empty($_POST['prix_promo']) ? (float)$_POST['prix_promo'] : null,
            ':stock'       => (int)($_POST['stock']      ?? 0),
            ':id_categorie'=> !empty($_POST['id_categorie']) ? (int)$_POST['id_categorie'] : null,
            ':actif'       => isset($_POST['actif']) ? 1 : 0,
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
        $produit = $produit->toArray();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->update($id);
            return;
        }

        include view_path('admin/produits/form.php');
    }

    public function update(int $id): void {
        $data = [
            ':nom'         => trim($_POST['nom']         ?? ''),
            ':description' => trim($_POST['description'] ?? ''),
            ':prix'        => (float)($_POST['prix']     ?? 0),
            ':prix_promo'  => !empty($_POST['prix_promo']) ? (float)$_POST['prix_promo'] : null,
            ':stock'       => (int)($_POST['stock']      ?? 0),
            ':id_categorie'=> !empty($_POST['id_categorie']) ? (int)$_POST['id_categorie'] : null,
            ':actif'       => isset($_POST['actif']) ? 1 : 0,
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
        $this->produitRepo->delete($id);
        $_SESSION['flash_success'] = "Produit supprimé.";
        header('Location: ' . url('admin/produits.php'));
        exit();
    }

    public function toggleActif(int $id): void {
        $this->produitRepo->toggleActif($id);
        header('Location: ' . url('admin/produits.php'));
        exit();
    }
}