<?php

namespace App\Controllers;

use PDO;
use App\Utils\Auth;
use App\Repositories\CartRepository;
use App\Services\OrderService;

class CheckoutController {

    private CartRepository $cartRepository;
    private OrderService   $orderService;

    public function __construct(PDO $db) {
        $this->cartRepository = new CartRepository($db);
        $this->orderService   = new OrderService($db);
    }

    // =========================================
    // AFFICHER LE FORMULAIRE DE COMMANDE
    // =========================================
    public function index(): void {
        if (!Auth::check()) {
            $this->redirect(url('login.php'));
        }

        $cart = $this->cartRepository->getCartByUser((int)Auth::id());

        // Panier vide → retour boutique
        if ($cart->isEmpty()) {
            $_SESSION['flash_error'] = "Votre panier est vide.";
            $this->redirect(url('boutique.php'));
        }

        include view_path('checkout/index.php');
    }

    // =========================================
    // TRAITER LA COMMANDE
    // =========================================
    public function process(): void {
        if (!Auth::check()) {
            $this->redirect(url('login.php'));
        }

        // Vérification CSRF
        if (
            !isset($_POST['csrf_token'], $_SESSION['csrf_token']) ||
            !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
        ) {
            $_SESSION['flash_error'] = "Erreur de sécurité.";
            $this->redirect(url('checkout.php'));
        }

        // Récupération des données du formulaire
        $nomComplet    = trim($_POST['nom_complet']    ?? '');
        $rue           = trim($_POST['rue']            ?? '');
        $ville         = trim($_POST['ville']          ?? '');
        $codePostal    = trim($_POST['code_postal']    ?? '00000');
        $telephone     = trim($_POST['telephone']      ?? '');
        $modePaiement  = trim($_POST['mode_paiement']  ?? '');
        $notes         = trim($_POST['notes']          ?? '');

        // Validation
        if (empty($nomComplet) || empty($rue) || empty($ville) || empty($telephone) || empty($modePaiement)) {
            $_SESSION['flash_error'] = "Veuillez remplir tous les champs obligatoires.";
            $this->redirect(url('checkout.php'));
        }

        $adresse = [
            'nom_complet' => $nomComplet,
            'rue'         => $rue,
            'ville'       => $ville,
            'code_postal' => $codePostal,
            'telephone'   => $telephone,
            'pays'        => 'Sénégal',
        ];

        try {
            $cart    = $this->cartRepository->getCartByUser((int)Auth::id());
            $commande = $this->orderService->createFromCart(
                $cart,
                (int)Auth::id(),
                $adresse,
                $modePaiement,
                $notes
            );

            $_SESSION['flash_success'] = "Commande confirmée ! Merci pour votre achat 🎉";
            $this->redirect(url('order_confirmation.php?numero=' . $commande['numero']));

        } catch (\Exception $e) {
            error_log("[checkout] Erreur : " . $e->getMessage());
            $_SESSION['flash_error'] = "Erreur lors de la commande. Veuillez réessayer.";
            $this->redirect(url('checkout.php'));
        }
    }

    private function redirect(string $url): void {
        header('Location: ' . $url);
        exit();
    }
}