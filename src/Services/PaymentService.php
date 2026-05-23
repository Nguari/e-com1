<?php

namespace App\Services;

use GuzzleHttp\Client;
use PDO;

class PaymentService {
    
    private $client;
    private $db;
    
    public function __construct($db) {
        $this->client = new Client();
        $this->db = $db;
    }
    
    /**
     * Initier un paiement Wave
     */
    public function initWavePayment($amount, $phone, $commandeId) {
        try {
            $transactionId = 'WAVE_' . time() . '_' . $commandeId;
            
            // Enregistrer dans la table paiements
            $stmt = $this->db->prepare("
                INSERT INTO paiements (id_commande, montant, mode_paiement, statut, transaction_id, date_paiement)
                VALUES (:commande, :montant, 'wave', 'en_attente', :transaction_id, NOW())
            ");
            $stmt->execute([
                ':commande' => $commandeId,
                ':montant' => $amount,
                ':transaction_id' => $transactionId
            ]);
            
            // Mode test
            if (PAYMENT_TEST_MODE) {
                $paymentUrl = url('payment_simulate.php?transaction_id=' . $transactionId . '&success=1');
                return ['success' => true, 'redirect_url' => $paymentUrl, 'transaction_id' => $transactionId];
            }
            
            // Mode réel - Appel API Wave
            $data = [
                'amount' => $amount,
                'currency' => 'XOF',
                'phone' => $this->formatPhone($phone),
                'reference' => $transactionId,
                'webhook' => url('payment_notify.php'),
                'callback_url' => url('payment_success.php')
            ];
            
            $response = $this->client->post(WAVE_API_URL . 'checkout', [
                'headers' => [
                    'Authorization' => 'Bearer ' . WAVE_API_KEY,
                    'Content-Type' => 'application/json'
                ],
                'json' => $data
            ]);
            
            $result = json_decode($response->getBody(), true);
            
            if (isset($result['checkout_url'])) {
                return ['success' => true, 'redirect_url' => $result['checkout_url'], 'transaction_id' => $transactionId];
            }
            
            return ['success' => false, 'error' => 'Erreur Wave'];
            
        } catch (\Exception $e) {
            error_log("Wave Error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Initier un paiement Orange Money
     */
    public function initOrangePayment($amount, $phone, $commandeId) {
        try {
            $transactionId = 'OM_' . time() . '_' . $commandeId;
            
            // Enregistrer dans la table paiements
            $stmt = $this->db->prepare("
                INSERT INTO paiements (id_commande, montant, mode_paiement, statut, transaction_id, date_paiement)
                VALUES (:commande, :montant, 'orange_money', 'en_attente', :transaction_id, NOW())
            ");
            $stmt->execute([
                ':commande' => $commandeId,
                ':montant' => $amount,
                ':transaction_id' => $transactionId
            ]);
            
            // Mode test
            if (PAYMENT_TEST_MODE) {
                $paymentUrl = url('payment_simulate.php?transaction_id=' . $transactionId . '&success=1');
                return ['success' => true, 'redirect_url' => $paymentUrl, 'transaction_id' => $transactionId];
            }
            
            // Mode réel - Appel Orange Money
            $token = $this->getOrangeToken();
            if (!$token) {
                return ['success' => false, 'error' => 'Impossible d\'obtenir le token Orange'];
            }
            
            $formattedPhone = $this->formatPhone($phone);
            
            $data = [
                'merchant_key' => ORANGE_MERCHANT_ID,
                'currency' => 'XOF',
                'order_id' => $transactionId,
                'amount' => $amount,
                'return_url' => ORANGE_RETURN_URL,
                'cancel_url' => ORANGE_CANCEL_URL,
                'notif_url' => ORANGE_NOTIFY_URL,
                'lang' => 'fr',
                'reference' => $transactionId,
                'payer' => $formattedPhone
            ];
            
            $response = $this->client->post(ORANGE_API_URL, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json'
                ],
                'json' => $data
            ]);
            
            $result = json_decode($response->getBody(), true);
            
            if (isset($result['payment_url'])) {
                return ['success' => true, 'redirect_url' => $result['payment_url'], 'transaction_id' => $transactionId];
            }
            
            return ['success' => false, 'error' => 'Erreur Orange Money'];
            
        } catch (\Exception $e) {
            error_log("Orange Money Error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Confirmer un paiement
     */
    public function confirmPayment($transactionId) {
        try {
            // Mettre à jour le statut dans paiements
            $stmt = $this->db->prepare("
                UPDATE paiements 
                SET statut = 'paye', date_paiement = NOW()
                WHERE transaction_id = :transaction_id
            ");
            $stmt->execute([':transaction_id' => $transactionId]);
            
            // Récupérer l'ID commande
            $stmt = $this->db->prepare("SELECT id_commande FROM paiements WHERE transaction_id = :transaction_id");
            $stmt->execute([':transaction_id' => $transactionId]);
            $paiement = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($paiement) {
                // Mettre à jour le statut de la commande
                $stmt = $this->db->prepare("UPDATE commandes SET statut = 'confirmee' WHERE id_commande = :id");
                $stmt->execute([':id' => $paiement['id_commande']]);
            }
            
            return true;
            
        } catch (\Exception $e) {
            error_log("Confirm Payment Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Vérifier le statut d'un paiement
     */
    public function checkPaymentStatus($transactionId) {
    $stmt = $this->db->prepare("
        SELECT p.*, c.numero_commande 
        FROM paiements p
        JOIN commandes c ON p.id_commande = c.id_commande
        WHERE p.transaction_id = :transaction_id
    ");
    $stmt->execute([':transaction_id' => $transactionId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
    
    /**
     * Obtenir le token Orange Money
     */
    private function getOrangeToken() {
        try {
            $response = $this->client->post(ORANGE_TOKEN_URL, [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode(ORANGE_CLIENT_ID . ':' . ORANGE_CLIENT_SECRET),
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ],
                'form_params' => [
                    'grant_type' => 'client_credentials'
                ]
            ]);
            
            $result = json_decode($response->getBody(), true);
            return $result['access_token'] ?? null;
            
        } catch (\Exception $e) {
            error_log("Orange Token Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Formater le numéro de téléphone
     */
    private function formatPhone($phone) {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) === 9) {
            $phone = '221' . $phone;
        }
        return $phone;
    }
}