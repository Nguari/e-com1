<?php
// public/payment_wave.php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/payment.php';

use App\Services\PaymentService;

$db = \App\Config\Database::getInstance()->getConnection();
$commandeId = $_POST['commande_id'] ?? 0;
$montant = $_POST['montant'] ?? 0;
$phone = $_POST['phone'] ?? '';

if (!$commandeId || !$montant || !$phone) {
    $_SESSION['flash_error'] = "Données invalides.";
    header('Location: ' . url('checkout.php'));
    exit();
}

$paymentService = new PaymentService($db);
$result = $paymentService->initWavePayment($montant, $phone, $commandeId);

if ($result['success']) {
    header('Location: ' . $result['redirect_url']);
} else {
    $_SESSION['flash_error'] = "Erreur Wave : " . $result['error'];
    header('Location: ' . url('checkout.php'));
}
exit();