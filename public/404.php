<?php
http_response_code(404);
require_once dirname(__DIR__) . '/config/config.php';

$pageTitle = 'Page non trouvée - NGAARY SHOP';
include __DIR__ . '/../views/layouts/header.php';
?>

<div class="container text-center py-5" style="min-height: 60vh;">
    <i class="bi bi-binoculars" style="font-size: 5rem; color: #cbd5e1;"></i>
    <h1 class="display-1 fw-bold text-muted mt-4">404</h1>
    <h2 class="h4 mb-3">Page non trouvée</h2>
    <p class="text-muted mb-4">Désolé, la page que vous cherchez n'existe pas ou a été déplacée.</p>
    <a href="<?= url('index.php') ?>" class="btn btn-success rounded-3 px-4">
        <i class="bi bi-house me-2"></i>Retour à l'accueil
    </a>
</div>

<?php include __DIR__ . '/../views/layouts/footer.php'; ?>