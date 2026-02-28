<?php 
include __DIR__ . '/../views/layouts/header.php';

define('URL_ROOT', '/e-com/public'); 

?>

<!-- HERO SECTION -->
<section class="py-5 text-white" style="background: linear-gradient(135deg, #0d2818 0%, #1a6b35 100%);">
    <div class="container py-5 text-center">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <span class="text-success text-uppercase small fw-bold" style="letter-spacing: 3px;">Nouvelle Collection</span>
                <h2 class="display-3 font-serif my-4">Naturellement élégant</h2>
                <p class="lead opacity-75 mb-5">Découvrez notre sélection d'objets durables, un meilleur choix à portée de main.</p>
                <div class="d-flex gap-3 justify-content-center">
                    <button class="btn btn-vivid px-4 py-2 shadow">VOIR LA BOUTIQUE</button>
                    <button class="btn btn-outline-light px-4 py-2 rounded-pill">NOTRE HISTOIRE</button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CATALOGUE -->
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h3 class="font-serif">Nos Meilleures Ventes</h3>
            <div class="bg-success" style="height: 3px; width: 50px;"></div>
        </div>
        <a href="#" class="text-success text-decoration-none small fw-bold">VOIR TOUT →</a>
    </div>

    <div class="row g-4">
        <!-- Carte Produit Type -->
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 overflow-hidden">
                <div class="bg-light p-4 text-center position-relative">
                    <span class="badge bg-danger position-absolute top-0 start-0 m-2">-20%</span>
                    <i class="bi bi-image text-muted display-1"></i>
                </div>
                <div class="card-body text-center">
                    <h5 class="card-title h6 mb-1">Chaise en Chêne</h5>
                    <p class="text-success fw-bold mb-0">30 000 Fcfa</p>
                </div>
            </div>
        </div>
        <!-- Fin Carte -->
    </div>
</div>

<?php include __DIR__ . '/../views/layouts/footer.php'; ?>
