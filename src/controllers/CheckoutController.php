<?php

namespace App\Controllers;

use PDO;
use App\Utils\Auth;
use App\Repositories\CartRepository;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Repositories\SettingRepository;

class CheckoutController {

    private PDO $db;
    private CartRepository $cartRepository;
    private OrderService   $orderService;
    private PaymentService $paymentService;
    private const MAX_ATTEMPTS   = 3;
    private const ATTEMPT_WINDOW = 300;

    public function __construct(PDO $db) {
        $this->db = $db;
        $this->cartRepository = new CartRepository();
        $this->orderService   = new OrderService($db);
        $this->paymentService = new PaymentService($db);

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // =========================================
    // AFFICHER LE FORMULAIRE DE COMMANDE
    // =========================================
    public function index(): void {
    if (!$this->ensureAuthenticated()) return;

    $cart = $this->cartRepository->getCartByUser((int)Auth::id());

    if ($cart->isEmpty()) {
        $this->setFlash('error', "Votre panier est vide.");
        $this->redirect(url('boutique.php'));
    }

    $this->generateCsrfToken();

    // Récupérer les paramètres de paiement
    $settingRepo = new SettingRepository($this->db);
    $enableWave = $settingRepo->getBool('enable_wave', true);
    $enableOm   = $settingRepo->getBool('enable_om', true);
    $enableCash = $settingRepo->getBool('enable_cash', true);

    // Les passer à la vue (variables globales)
    $GLOBALS['enableWave'] = $enableWave;
    $GLOBALS['enableOm']   = $enableOm;
    $GLOBALS['enableCash'] = $enableCash;

    include view_path('checkout/index.php');
}

    // =========================================
    // TRAITER LA COMMANDE
    // =========================================
    public function process(): void {
    error_log("=== CHECKOUT PROCESS START ===");
    
    if (!Auth::check()) {
        error_log("ERROR: Utilisateur non connecté");
        $this->redirect(url('login.php'));
    }

    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        error_log("ERROR: Token CSRF invalide");
        $_SESSION['flash_error'] = "Erreur de sécurité.";
        $this->redirect(url('checkout.php'));
    }

    $modePaiement = trim($_POST['mode_paiement'] ?? '');
    
    // --- VÉRIFICATION DES MODES ACTIFS ---
    $settingRepo = new SettingRepository($this->db);
    $enableWave = $settingRepo->getBool('enable_wave', true);
    $enableOm   = $settingRepo->getBool('enable_om', true);
    $enableCash = $settingRepo->getBool('enable_cash', true);

    if (($modePaiement === 'wave' && !$enableWave) ||
        ($modePaiement === 'orange_money' && !$enableOm) ||
        ($modePaiement === 'especes' && !$enableCash)) {
        $_SESSION['flash_error'] = "Ce mode de paiement n'est pas disponible.";
        $this->redirect(url('checkout.php'));
    }
    // --- FIN VÉRIFICATION ---
    
    $phone = trim($_POST['telephone'] ?? $_POST['phone'] ?? '');
    $phone = preg_replace('/\D/', '', $phone);
    
    error_log("Mode paiement: " . $modePaiement);
        error_log("Phone (nettoyé): " . $phone);
        
        if (in_array($modePaiement, ['wave', 'orange_money'])) {
            if (strlen($phone) !== 9) {
                $_SESSION['flash_error'] = "Numéro de téléphone invalide (doit contenir exactement 9 chiffres)";
                $this->redirect(url('checkout.php'));
            }
        }

        $adresse = [
            'nom_complet' => trim($_POST['nom_complet'] ?? ''),
            'rue' => trim($_POST['rue'] ?? ''),
            'ville' => trim($_POST['ville'] ?? ''),
            'code_postal' => trim($_POST['code_postal'] ?? '00000'),
            'telephone' => $phone,
            'pays' => 'Sénégal'
        ];

        try {
            $cart = $this->cartRepository->getCartByUser((int)Auth::id());
            
            error_log("Cart récupéré, isEmpty: " . ($cart->isEmpty() ? 'true' : 'false'));
            
            if ($cart->isEmpty()) {
                throw new \RuntimeException("Le panier est vide");
            }
            
            error_log("Tentative de création de commande...");
            $commande = $this->orderService->createFromCart(
                $cart,
                (int)Auth::id(),
                $adresse,
                $modePaiement,
                trim($_POST['notes'] ?? '')
            );
            
            error_log("Commande retournée: " . print_r($commande, true));
            
            if (!$commande || !isset($commande['id_commande'])) {
                throw new \RuntimeException("Erreur lors de la création de la commande");
            }
            
            if ($modePaiement === 'wave') {
                $result = $this->paymentService->initWavePayment(
                    $commande['montant_total'],
                    $phone,
                    $commande['id_commande']
                );
                
                if ($result['success']) {
                    $this->redirect($result['redirect_url']);
                    $_SESSION['last_wave_transaction'] = $result['transaction_id'];
                } else {
                    $_SESSION['flash_error'] = "Erreur de paiement Wave: " . $result['error'];
                    $this->redirect(url('checkout.php'));
                }
            } elseif ($modePaiement === 'orange_money') {
                $result = $this->paymentService->initOrangePayment(
                    $commande['montant_total'],
                    $phone,
                    $commande['id_commande']
                );
                
                if ($result['success']) {
                    $this->redirect($result['redirect_url']);
                    $_SESSION['last_orange_transaction'] = $result['transaction_id'];
                } else {
                    $_SESSION['flash_error'] = "Erreur de paiement Orange Money: " . $result['error'];
                    $this->redirect(url('checkout.php'));
                }
            }else{
    $this->enregistrerPaiement($commande['id_commande'], $commande['montant_total'], 'especes');
    $_SESSION['flash_success'] = "Commande confirmée ! Vous paierez à la livraison.";
    
    // Vérifier l'ID
    error_log("ID commande: " . ($commande['id_commande'] ?? 'VIDE'));
    error_log("Numéro commande: " . ($commande['numero_commande'] ?? 'VIDE'));
    
    // Construire l'URL manuellement
    $redirectUrl = rtrim(APP_URL, '/') . '/order_confirmation.php?id=' . $commande['id_commande'];
    error_log("Redirection vers: " . $redirectUrl);
    
    header('Location: ' . $redirectUrl);
    exit();
}
            
        } catch (\Exception $e) {
            error_log("EXCEPTION DETAILLEE: " . $e->getMessage());
            error_log("TRACE: " . $e->getTraceAsString());
            $_SESSION['flash_error'] = "Erreur lors de la commande: " . $e->getMessage();
            $this->redirect(url('checkout.php'));
        }
    }


    // =========================================
    // ENREGISTRER UN PAIEMENT
    // =========================================
    private function enregistrerPaiement($commandeId, $montant, $modePaiement): void {
        try {
            $db = \App\Config\Database::getInstance()->getConnection();
            $transactionId = strtoupper($modePaiement) . '_' . time() . '_' . $commandeId;
            $stmt = $db->prepare("
                INSERT INTO paiements (id_commande, montant, mode_paiement, statut, transaction_id, date_paiement)
                VALUES (:commande, :montant, :mode, 'paye', :transaction_id, NOW())
            ");
            $stmt->execute([
                ':commande' => $commandeId,
                ':montant' => $montant,
                ':mode' => $modePaiement,
                ':transaction_id' => $transactionId
            ]);
        } catch (\Exception $e) {
            error_log("Erreur enregistrement paiement: " . $e->getMessage());
        }
    }

    // =========================================
    // VALIDATION DES DONNÉES
    // =========================================
    
    private function validateOrderData(array $data): ?array {
        $nomComplet   = trim($data['nom_complet']   ?? '');
        $rue          = trim($data['rue']           ?? '');
        $ville        = trim($data['ville']         ?? '');
        $codePostal   = trim($data['code_postal']   ?? '');
        $telephone    = trim($data['telephone']     ?? '');
        $modePaiement = trim($data['mode_paiement'] ?? '');
        $notes        = trim($data['notes']         ?? '');

        $errors = [];

        if (empty($nomComplet) || strlen($nomComplet) < 3 || strlen($nomComplet) > 100) {
            $errors[] = "Le nom complet est requis (3-100 caractères)";
        }

        if (empty($rue) || strlen($rue) < 5 || strlen($rue) > 200) {
            $errors[] = "L'adresse est requise (5-200 caractères)";
        }

        if (empty($ville)) {
            $errors[] = "La ville est requise";
        }

        if (empty($telephone)) {
            $errors[] = "Le téléphone est requis";
        } elseif (!preg_match('/^[0-9]{9}$/', $telephone)) {
            $errors[] = "Le numéro de téléphone doit comporter 9 chiffres";
        }

        $validModes = ['Orange Money', 'Wave', 'Especes', 'wave', 'orange_money', 'especes'];
        if (empty($modePaiement) || !in_array($modePaiement, $validModes)) {
            $errors[] = "Mode de paiement invalide (reçu : '$modePaiement')";
        }

        if (!empty($notes) && strlen($notes) > 500) {
            $errors[] = "Les notes ne peuvent pas dépasser 500 caractères";
        }

        if (!empty($errors)) {
            $this->setFlash('error', implode('. ', $errors));
            return null;
        }

        return [
            'adresse' => [
                'nom_complet' => htmlspecialchars($nomComplet, ENT_QUOTES, 'UTF-8'),
                'rue'         => htmlspecialchars($rue, ENT_QUOTES, 'UTF-8'),
                'ville'       => htmlspecialchars($ville, ENT_QUOTES, 'UTF-8'),
                'code_postal' => $codePostal ?: '00000',
                'telephone'   => $telephone,
                'pays'        => 'Sénégal',
            ],
            'mode_paiement' => $modePaiement,
            'notes'         => htmlspecialchars(strip_tags($notes), ENT_QUOTES, 'UTF-8'),
        ];
    }
    
    // =========================================
    // UTILITAIRES
    // =========================================

    private function checkRateLimit(): bool {
        $key = "checkout_attempts_" . Auth::id();

        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['count' => 1, 'first_attempt' => time()];
            return true;
        }

        $attempts = $_SESSION[$key];

        if (time() - $attempts['first_attempt'] > self::ATTEMPT_WINDOW) {
            $_SESSION[$key] = ['count' => 1, 'first_attempt' => time()];
            return true;
        }

        if ($attempts['count'] >= self::MAX_ATTEMPTS) {
            return false;
        }

        $_SESSION[$key]['count']++;
        return true;
    }

    private function generateCsrfToken(): void {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    private function validateCsrfToken(): bool {
        if (!isset($_POST['csrf_token'], $_SESSION['csrf_token'])) {
            return false;
        }
        $isValid = hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
        if ($isValid) unset($_SESSION['csrf_token']);
        return $isValid;
    }

    private function ensureAuthenticated(): bool {
        if (!Auth::check()) {
            $this->setFlash('error', "Veuillez vous connecter pour passer commande.");
            $this->redirect(url('login.php'));
            return false;
        }
        return true;
    }

    private function setFlash(string $type, string $message): void {
        $_SESSION["flash_{$type}"] = $message;
    }

    private function redirect(string $url): void {
        header('Location: ' . $url);
        exit();
    }
}