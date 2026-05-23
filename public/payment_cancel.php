<?php
require_once dirname(__DIR__) . '/config/config.php';

$transactionId = $_GET['transaction_id'] ?? '';

$pageTitle = 'Paiement annulé - NGAARY SHOP';
include __DIR__ . '/../views/layouts/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 text-center">
                <div class="card-body p-5">
                    <div class="mb-4">
                        <i class="bi bi-x-circle-fill text-danger" style="font-size: 5rem;"></i>
                    </div>
                    <h3 class="fw-bold mb-3">Paiement annulé</h3>
                    <p class="text-muted mb-4">
                        Le paiement a été annulé ou une erreur est survenue.<br>
                        Transaction : <strong><?= htmlspecialchars($transactionId) ?></strong>
                    </p>
                    <a href="<?= url('checkout.php') ?>" class="btn btn-success px-4 py-2">
                        <i class="bi bi-arrow-repeat me-2"></i>Réessayer
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../views/layouts/footer.php'; ?>