<?php
// public/promotions.php
require_once dirname(__DIR__) . '/config/config.php';

$produits = [
    ['id_produit' => 1,  'nom' => 'Chaise en Chêne',      'prix' => 30000, 'prix_ancien' => 37500, 'badge' => '-20%', 'categorie' => 'Mobilier',   'note' => 4],
    ['id_produit' => 3,  'nom' => 'Lampe en Rotin',        'prix' => 18500, 'prix_ancien' => 20500, 'badge' => '-10%', 'categorie' => 'Décoration', 'note' => 4],
    ['id_produit' => 7,  'nom' => 'Vase Terracotta',       'prix' => 15000, 'prix_ancien' => 18000, 'badge' => '-17%', 'categorie' => 'Décoration', 'note' => 4],
    ['id_produit' => 10, 'nom' => 'Tapis Wax Géométrique', 'prix' => 22000, 'prix_ancien' => 25000, 'badge' => '-12%', 'categorie' => 'Textile',    'note' => 4],
];

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error']   ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$pageTitle   = 'Promotions - NGAARY SHOP';
$currentPage = 'promotions.php';
include __DIR__ . '/../views/layouts/header.php';
?>

<style>
    .promo-hero { background: linear-gradient(135deg, #7f1d1d 0%, #dc2626 100%); padding: 60px 0; }
    .product-card { border: none; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.07); transition: transform 0.3s, box-shadow 0.3s; }
    .product-card:hover { transform: translateY(-6px); box-shadow: 0 16px 40px rgba(0,0,0,0.12); }
    .product-img { background-color: #fef2f2; padding: 30px; position: relative; min-height: 180px; display: flex; align-items: center; justify-content: center; }
    .btn-cart { background-color: #16a34a; color: white; border: none; border-radius: 8px; padding: 8px 16px; font-size: 0.85rem; font-weight: 600; width: 100%; cursor: pointer; }
    .btn-cart:hover { background-color: #15803d; color: white; }
    .countdown { background: rgba(255,255,255,0.1); border-radius: 12px; padding: 20px; display: inline-flex; gap: 20px; }
    .countdown-item { text-align: center; }
    .countdown-num { font-size: 2rem; font-weight: 700; line-height: 1; }
    .countdown-label { font-size: 0.7rem; opacity: 0.75; text-transform: uppercase; letter-spacing: 1px; }
</style>

<!-- HERO -->
<section class="promo-hero text-white text-center">
    <div class="container">
        <span class="badge bg-white text-danger fw-bold px-3 py-2 mb-3">🔥 OFFRES LIMITÉES</span>
        <h1 class="fw-bold mb-2" style="font-family: 'Playfair Display', serif; font-size: 2.5rem;">
            Nos Promotions
        </h1>
        <p class="opacity-75 mb-4">Des réductions exceptionnelles sur une sélection de produits.</p>
        <div class="countdown">
            <div class="countdown-item">
                <div class="countdown-num" id="hours">00</div>
                <div class="countdown-label">Heures</div>
            </div>
            <div class="countdown-item">
                <div class="countdown-num" id="minutes">00</div>
                <div class="countdown-label">Minutes</div>
            </div>
            <div class="countdown-item">
                <div class="countdown-num" id="seconds">00</div>
                <div class="countdown-label">Secondes</div>
            </div>
        </div>
    </div>
</section>

<!-- CODE PROMO -->
<section class="py-3 bg-warning">
    <div class="container text-center">
        <p class="mb-0 fw-semibold">
            🎁 Code promo : <strong>NGAARY15</strong> — 15% de réduction supplémentaire !
        </p>
    </div>
</section>

<!-- PRODUITS -->
<section class="py-5" style="background: #f0faf3;">
    <div class="container">

        <div class="text-center mb-5">
            <h2 class="fw-bold" style="font-family: 'Playfair Display', serif;">Produits en Promotion</h2>
            <p class="text-muted"><?= count($produits) ?> offres disponibles</p>
        </div>

        <?php if ($flashSuccess) : ?>
        <div class="alert alert-success rounded-3 d-flex align-items-center gap-2 mb-4">
            <i class="bi bi-check-circle-fill"></i><?= htmlspecialchars($flashSuccess) ?>
        </div>
        <?php endif; ?>

        <div class="row g-4">
            <?php foreach ($produits as $produit) : ?>
            <div class="col-6 col-lg-3">
                <div class="card product-card h-100">
                    <div class="product-img">
                        <span class="badge bg-danger position-absolute top-0 start-0 m-2 fs-6">
                            <?= $produit['badge'] ?>
                        </span>
                        <i class="bi bi-image" style="font-size: 4rem; color: #fca5a5;"></i>
                    </div>
                    <div class="card-body d-flex flex-column px-3 py-3">
                        <small class="text-muted mb-1" style="font-size: 0.75rem;"><?= $produit['categorie'] ?></small>
                        <h5 class="card-title h6 mb-1 fw-semibold"><?= htmlspecialchars($produit['nom']) ?></h5>
                        <div class="mb-2">
                            <?php for ($j = 1; $j <= 5; $j++) : ?>
                                <i class="bi bi-star<?= $j <= $produit['note'] ? '-fill text-warning' : ' text-muted' ?>" style="font-size: 0.7rem;"></i>
                            <?php endfor; ?>
                        </div>
                        <div class="mt-auto">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="text-success fw-bold"><?= formatFCFA($produit['prix']) ?></span>
                                <span class="text-muted text-decoration-line-through small">
                                    <?= formatFCFA($produit['prix_ancien']) ?>
                                </span>
                            </div>
                            <p class="text-danger small mb-2 fw-semibold">
                                Économisez <?= formatFCFA($produit['prix_ancien'] - $produit['prix']) ?>
                            </p>
                            <form action="<?= url('cart_add.php') ?>" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                <input type="hidden" name="id_produit" value="<?= $produit['id_produit'] ?>">
                                <input type="hidden" name="quantite"   value="1">
                                <input type="hidden" name="retour"     value="promotions.php">
                                <button type="submit" class="btn-cart">
                                    <i class="bi bi-cart-plus me-1"></i>Ajouter
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<script>
let endTime = new Date();
endTime.setHours(endTime.getHours() + 24);
function updateCountdown() {
    const diff    = endTime - new Date();
    const hours   = Math.floor(diff / 3600000);
    const minutes = Math.floor((diff % 3600000) / 60000);
    const seconds = Math.floor((diff % 60000) / 1000);
    document.getElementById('hours').textContent   = String(hours).padStart(2, '0');
    document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
    document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');
}
updateCountdown();
setInterval(updateCountdown, 1000);
</script>

<?php include __DIR__ . '/../views/layouts/footer.php'; ?>