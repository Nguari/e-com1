<?php
require_once dirname(__DIR__) . '/config/config.php';

$pageTitle   = 'Boutique - NGAARY SHOP';
$currentPage = 'boutique.php';

include __DIR__ . '/../views/layouts/header.php';

// Produits statiques
$tousLesProduits = [
    ['nom' => 'Chaise en Chêne',       'prix' => 30000, 'prix_ancien' => 37500, 'badge' => '-20%',    'badge_color' => 'danger',  'categorie' => 'Mobilier',    'note' => 4],
    ['nom' => 'Table Basse Bambou',    'prix' => 45000, 'prix_ancien' => null,  'badge' => 'Nouveau', 'badge_color' => 'success', 'categorie' => 'Mobilier',    'note' => 5],
    ['nom' => 'Lampe en Rotin',        'prix' => 18500, 'prix_ancien' => 20500, 'badge' => '-10%',    'badge_color' => 'danger',  'categorie' => 'Décoration',  'note' => 4],
    ['nom' => 'Panier Tressé',         'prix' => 12000, 'prix_ancien' => null,  'badge' => null,      'badge_color' => null,      'categorie' => 'Accessoires', 'note' => 5],
    ['nom' => 'Miroir Bois Flotté',    'prix' => 28000, 'prix_ancien' => null,  'badge' => 'Nouveau', 'badge_color' => 'success', 'categorie' => 'Décoration',  'note' => 4],
    ['nom' => 'Coussin Wax Coloré',    'prix' => 8500,  'prix_ancien' => null,  'badge' => null,      'badge_color' => null,      'categorie' => 'Textile',     'note' => 5],
    ['nom' => 'Vase Terracotta',       'prix' => 15000, 'prix_ancien' => 18000, 'badge' => '-17%',    'badge_color' => 'danger',  'categorie' => 'Décoration',  'note' => 4],
    ['nom' => 'Plateau Osier Naturel', 'prix' => 9500,  'prix_ancien' => null,  'badge' => null,      'badge_color' => null,      'categorie' => 'Accessoires', 'note' => 3],
    ['nom' => 'Étagère Bambou',        'prix' => 35000, 'prix_ancien' => null,  'badge' => 'Nouveau', 'badge_color' => 'success', 'categorie' => 'Mobilier',    'note' => 5],
    ['nom' => 'Tapis Wax Géométrique', 'prix' => 22000, 'prix_ancien' => 25000, 'badge' => '-12%',    'badge_color' => 'danger',  'categorie' => 'Textile',     'note' => 4],
    ['nom' => 'Bougeoir Terracotta',   'prix' => 7500,  'prix_ancien' => null,  'badge' => null,      'badge_color' => null,      'categorie' => 'Décoration',  'note' => 4],
    ['nom' => 'Sac Raphia Naturel',    'prix' => 14000, 'prix_ancien' => null,  'badge' => 'Nouveau', 'badge_color' => 'success', 'categorie' => 'Accessoires', 'note' => 5],
];

$categories = ['Tous', 'Mobilier', 'Décoration', 'Textile', 'Accessoires'];

// Filtre catégorie
$categorieActive = $_GET['categorie'] ?? 'Tous';
$produits = $categorieActive === 'Tous'
    ? $tousLesProduits
    : array_filter($tousLesProduits, fn($p) => $p['categorie'] === $categorieActive);
?>

<style>
    .font-serif { font-family: 'Playfair Display', serif; }

    /* HERO */
    .boutique-hero { background: linear-gradient(135deg, #0d2818 0%, #1a6b35 100%); padding: 60px 0; }

    /* FILTRES */
    .filter-btn {
        border: 1.5px solid #e2e8f0;
        border-radius: 50px;
        padding: 8px 20px;
        background: white;
        color: #0d2818;
        font-size: 0.85rem;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.2s;
        display: inline-block;
    }
    .filter-btn:hover { border-color: #16a34a; color: #16a34a; background: #f0faf3; }
    .filter-btn.active { background: #16a34a; border-color: #16a34a; color: white; font-weight: 600; }

    /* PRODUITS */
    .product-card { border: none; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.07); transition: transform 0.3s, box-shadow 0.3s; }
    .product-card:hover { transform: translateY(-6px); box-shadow: 0 16px 40px rgba(0,0,0,0.12); }
    .product-img { background-color: #e8f5ee; padding: 30px; position: relative; min-height: 180px; display: flex; align-items: center; justify-content: center; }
    .product-img i { font-size: 4rem; color: #a7c9b3; transition: transform 0.3s; }
    .product-card:hover .product-img i { transform: scale(1.1); }
    .btn-wishlist { background: white; border: none; border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.1); color: #0d2818; transition: color 0.2s, transform 0.2s; cursor: pointer; }
    .btn-wishlist:hover { color: #e11d48; transform: scale(1.2); }
    .btn-cart { background-color: #16a34a; color: white; border: none; border-radius: 8px; padding: 8px 16px; font-size: 0.85rem; font-weight: 600; width: 100%; transition: background-color 0.2s, transform 0.2s; }
    .btn-cart:hover { background-color: #15803d; color: white; transform: translateY(-1px); }
    .section-divider { height: 3px; width: 50px; background-color: #16a34a; margin-top: 6px; }

    /* ANIMATIONS */
    .reveal { opacity: 0; transform: translateY(25px); transition: opacity 0.5s ease, transform 0.5s ease; }
    .reveal.visible { opacity: 1; transform: translateY(0); }
    .reveal-delay-1 { transition-delay: 0.05s; }
    .reveal-delay-2 { transition-delay: 0.1s; }
    .reveal-delay-3 { transition-delay: 0.15s; }
    .reveal-delay-4 { transition-delay: 0.2s; }
</style>

<!-- ====== HERO ====== -->
<section class="boutique-hero text-white">
    <div class="container text-center">
        <span class="text-success text-uppercase small fw-bold" style="letter-spacing: 3px;">✦ Notre sélection</span>
        <h1 class="display-5 font-serif fw-bold my-2">La Boutique</h1>
        <p class="text-white-50 mb-0">
            <?= count($produits) ?> produit<?= count($produits) > 1 ? 's' : '' ?>
            <?= $categorieActive !== 'Tous' ? 'dans <strong>' . htmlspecialchars($categorieActive) . '</strong>' : 'disponibles' ?>
        </p>
    </div>
</section>

<!-- ====== FILTRES CATÉGORIES ====== -->
<section class="py-4 bg-white border-bottom sticky-top" style="top: 56px; z-index: 100;">
    <div class="container">
        <div class="d-flex gap-2 flex-wrap justify-content-center">
            <?php foreach ($categories as $cat) :
                $nb  = $cat === 'Tous'
                    ? count($tousLesProduits)
                    : count(array_filter($tousLesProduits, fn($p) => $p['categorie'] === $cat));
                $url = $cat === 'Tous'
                    ? url('boutique.php')
                    : url('boutique.php') . '?categorie=' . urlencode($cat);
            ?>
                <a href="<?= $url ?>" class="filter-btn <?= $categorieActive === $cat ? 'active' : '' ?>">
                    <?= $cat ?> <span class="ms-1 opacity-75">(<?= $nb ?>)</span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ====== GRILLE PRODUITS ====== -->
<section class="py-5">
    <div class="container">

        <div class="d-flex justify-content-between align-items-end mb-4 reveal">
            <div>
                <span class="text-success small fw-bold text-uppercase" style="letter-spacing: 2px;">
                    <?= $categorieActive === 'Tous' ? 'Tous les produits' : htmlspecialchars($categorieActive) ?>
                </span>
                <h2 class="font-serif mb-0">
                    <?= $categorieActive === 'Tous' ? 'Nos Produits' : htmlspecialchars($categorieActive) ?>
                </h2>
                <div class="section-divider"></div>
            </div>
            <span class="text-muted small"><?= count($produits) ?> article<?= count($produits) > 1 ? 's' : '' ?></span>
        </div>

        <?php if (empty($produits)) : ?>
            <div class="text-center py-5">
                <i class="bi bi-box-seam" style="font-size: 3rem; color: #a7c9b3;"></i>
                <p class="text-muted mt-3">Aucun produit dans cette catégorie.</p>
                <a href="<?= url('boutique.php') ?>" class="btn btn-success rounded-3 px-4">Voir tous les produits</a>
            </div>
        <?php else : ?>
            <div class="row g-4">
                <?php foreach (array_values($produits) as $idx => $produit) : ?>
                <div class="col-6 col-lg-3 reveal reveal-delay-<?= ($idx % 4) + 1 ?>">
                    <div class="card product-card h-100">
                        <div class="product-img">
                            <?php if ($produit['badge']) : ?>
                                <span class="badge bg-<?= $produit['badge_color'] ?> position-absolute top-0 start-0 m-2">
                                    <?= htmlspecialchars($produit['badge']) ?>
                                </span>
                            <?php endif; ?>
                            <button class="btn-wishlist position-absolute top-0 end-0 m-2">
                                <i class="bi bi-heart"></i>
                            </button>
                            <i class="bi bi-image"></i>
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
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="text-success fw-bold"><?= number_format($produit['prix'], 0, ',', ' ') ?> FCFA</span>
                                    <?php if ($produit['prix_ancien']) : ?>
                                        <span class="text-muted text-decoration-line-through small"><?= number_format($produit['prix_ancien'], 0, ',', ' ') ?> FCFA</span>
                                    <?php endif; ?>
                                </div>
                                <button class="btn-cart">
                                    <i class="bi bi-cart-plus me-1"></i>Ajouter
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</section>

<!-- SCROLL REVEAL -->
<script>
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) entry.target.classList.add('visible');
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
</script>

<?php include __DIR__ . '/../views/layouts/footer.php'; ?>