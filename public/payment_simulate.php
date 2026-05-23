<?php
// public/payment_simulate.php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/payment.php';

use App\Services\PaymentService;

$db = \App\Config\Database::getInstance()->getConnection();
$transactionId = $_GET['transaction_id'] ?? '';
$success = isset($_GET['success']) ? (bool)$_GET['success'] : false;

if (!$transactionId) {
    header('Location: ' . url('index.php'));
    exit();
}

$paymentService = new PaymentService($db);

if ($success) {
    $paymentService->confirmPayment($transactionId);
    $_SESSION['flash_success'] = "Paiement effectué avec succès !";
    header('Location: ' . url('payment_success.php?transaction_id=' . $transactionId));
} else {
    header('Location: ' . url('payment_cancel.php?transaction_id=' . $transactionId));
}
exit();