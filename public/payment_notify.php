<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/payment.php';

use App\Services\PaymentService;

// Connexion à la base de données
$db = \App\Config\Database::getInstance()->getConnection();

// Récupérer les données du webhook
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if ($data) {
    $paymentService = new PaymentService($db);
    
    $transactionId = $data['transaction_id'] ?? $data['reference'] ?? '';
    $status = $data['status'] ?? '';
    
    if ($transactionId && $status === 'completed') {
        $paymentService->confirmPayment($transactionId);
    }
}

http_response_code(200);
echo json_encode(['status' => 'ok']);
?>