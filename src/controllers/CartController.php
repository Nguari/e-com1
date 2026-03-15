<?php

namespace App\Controllers;

use PDO;
use App\Repositories\CartRepository;

/**
 * CartController
 * 
 * Gère toutes les actions du panier :
 * - Affichage
 * - Ajout / Suppression / Modification
 * - Vidage
 */
class CartController {

    private CartRepository $cartRepository;
    private int $idUtilisateur;

    public function __construct(PDO $db) {
        $this->cartRepository = new CartRepository($db);
        $this->idUtilisateur  = (int)($_SESSION['user_id'] ?? 0);
    }

    // =========================================
    // AFFICHER LE PANIER
    // =========================================
    public function index(): void {
        if (!$this->isConnected()) {
            $this->redirect('/login');
        }

        $cart = $this->cartRepository->getCartByUser($this->idUtilisateur);
        include view_path('cart/index.php');
    }

    // =========================================
    // AJOUTER UN ARTICLE
    // =========================================
    public function add(): void {
        if (!$this->isConnected()) {
            $_SESSION['flash_error'] = 'Connectez-vous pour ajouter au panier.';
            $this->redirect('/login');
        }

        $idProduit = (int)($_POST['id_produit'] ?? 0);
        $quantite  = max(1, (int)($_POST['quantite'] ?? 1));

        if ($idProduit <= 0) {
            $_SESSION['flash_error'] = 'Produit invalide.';
            $this->redirect('/boutique');
            return;
        }

        $success = $this->cartRepository->addOrUpdate(
            $this->idUtilisateur,
            $idProduit,
            $quantite
        );

        if ($success) {
            $_SESSION['flash_success'] = 'Produit ajouté au panier !';
        } else {
            $_SESSION['flash_error'] = 'Erreur lors de l\'ajout au panier.';
        }

        $this->redirect('/cart');
    }

    // =========================================
    // MODIFIER LA QUANTITÉ
    // =========================================
    public function update(): void {
        if (!$this->isConnected()) {
            $this->redirect('/login');
        }

        $idProduit = (int)($_POST['id_produit'] ?? 0);
        $quantite  = (int)($_POST['quantite']   ?? 0);

        $this->cartRepository->updateQuantite(
            $this->idUtilisateur,
            $idProduit,
            $quantite
        );

        $this->redirect('/cart');
    }

    // =========================================
    // SUPPRIMER UN ARTICLE
    // =========================================
    public function remove(): void {
        if (!$this->isConnected()) {
            $this->redirect('/login');
        }

        $idProduit = (int)($_POST['id_produit'] ?? 0);

        $success = $this->cartRepository->removeItem(
            $this->idUtilisateur,
            $idProduit
        );

        if ($success) {
            $_SESSION['flash_success'] = 'Article retiré du panier.';
        }

        $this->redirect('/cart');
    }

    // =========================================
    // VIDER LE PANIER
    // =========================================
    public function clear(): void {
        if (!$this->isConnected()) {
            $this->redirect('/login');
        }

        $this->cartRepository->clearCart($this->idUtilisateur);
        $_SESSION['flash_success'] = 'Votre panier a été vidé.';
        $this->redirect('/cart');
    }

    // =========================================
    // MÉTHODES PRIVÉES
    // =========================================
    private function isConnected(): bool {
        return isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
    }

    private function redirect(string $path): void {
        header('Location: ' . url($path));
        exit();
    }
}