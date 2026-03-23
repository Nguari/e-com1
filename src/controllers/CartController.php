<?php

namespace App\Controllers;

use PDO;
use App\Repositories\CartRepository;
use App\Utils\Auth;

/**
 * CartController
 * Gère toutes les actions du panier
 */
class CartController {

    private CartRepository $cartRepository;

    public function __construct(PDO $db) {
        $this->cartRepository = new CartRepository($db);
    }

    // =========================================
    // AFFICHER LE PANIER
    // =========================================
    public function index(): void {
        if (!Auth::check()) {
            $this->redirect(url('login.php'));
        }

        $cart = $this->cartRepository->getCartByUser((int)Auth::id());
        include view_path('cart/index.php');
    }

    // =========================================
    // AJOUTER UN ARTICLE
    // =========================================
    public function add(): void {
        if (!Auth::check()) {
            $_SESSION['flash_error'] = 'Connectez-vous pour ajouter au panier.';
            $this->redirect(url('login.php'));
        }

        $idProduit = (int)($_POST['id_produit'] ?? 0);
        $quantite  = max(1, (int)($_POST['quantite'] ?? 1));

        if ($idProduit <= 0) {
            $_SESSION['flash_error'] = 'Produit invalide.';
            $this->redirect(url('boutique.php')); // ✅ minuscule
            return;
        }

        $success = $this->cartRepository->addOrUpdate(
            (int)Auth::id(),
            $idProduit,
            $quantite
        );

        if ($success) {
            $_SESSION['flash_success'] = 'Produit ajouté au panier !';
        } else {
            $_SESSION['flash_error'] = 'Erreur lors de l\'ajout au panier.';
        }

        $this->redirect(url('cart.php')); // ✅ minuscule
    }

    // =========================================
    // MODIFIER LA QUANTITÉ
    // =========================================
    public function update(): void {
        if (!Auth::check()) {
            $this->redirect(url('login.php'));
        }

        $idProduit = (int)($_POST['id_produit'] ?? 0);
        $quantite  = (int)($_POST['quantite']   ?? 0);

        $this->cartRepository->updateQuantite(
            (int)Auth::id(),
            $idProduit,
            $quantite
        );

        $this->redirect(url('cart.php')); // ✅ minuscule
    }

    // =========================================
    // SUPPRIMER UN ARTICLE
    // =========================================
    public function remove(): void {
        if (!Auth::check()) {
            $this->redirect(url('login.php'));
        }

        $idProduit = (int)($_POST['id_produit'] ?? 0);

        $success = $this->cartRepository->removeItem(
            (int)Auth::id(),
            $idProduit
        );

        if ($success) {
            $_SESSION['flash_success'] = 'Article retiré du panier.';
        }

        $this->redirect(url('cart.php')); // ✅ minuscule
    }

    // =========================================
    // VIDER LE PANIER
    // =========================================
    public function clear(): void {
        if (!Auth::check()) {
            $this->redirect(url('login.php'));
        }

        $this->cartRepository->clearCart((int)Auth::id());
        $_SESSION['flash_success'] = 'Votre panier a été vidé.';
        $this->redirect(url('cart.php')); // ✅ minuscule
    }

    // =========================================
    // MÉTHODE PRIVÉE
    // =========================================
    private function redirect(string $url): void {
        header('Location: ' . $url);
        exit();
    }
}