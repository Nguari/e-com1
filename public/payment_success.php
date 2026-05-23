<?php
// public/payment_success.php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/payment.php';

use App\Services\PaymentService;

$db = \App\Config\Database::getInstance()->getConnection();
$transactionId = $_GET['transaction_id'] ?? '';

if (!$transactionId) {
    header('Location: ' . url('index.php'));
    exit();
}

$paymentService = new PaymentService($db);
$paiement = $paymentService->checkPaymentStatus($transactionId);

if ($paiement && $paiement['statut'] === 'paye') {
    error_log("payment_success: paiement non valide - " . print_r($paiement, true));
    header('Location: ' . url('boutique.php'));
    exit();
}

// Récupérer l'ID de la commande
$commandeId = $paiement['id_commande'] ?? 0;
if ($commandeId) {
    // Redirection vers la page de confirmation avec l'ID
    $url = url('order_confirmation.php?id=' . $commandeId);
    error_log("payment_success: redirection vers " . $url);
    header('Location: ' . $url);
} else {
    error_log("payment_success: id_commande manquant pour transaction " . $transactionId);
    header('Location: ' . url('boutique.php'));
}
exit();