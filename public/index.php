<?php
/**
 * Page d'accueil - NGAARY SHOP
 * 
 * @var array<int, array> $produits Liste des produits
 * @var array<int, array> $categories Liste des catégories
 * @var int $tousLesProduits Nombre total de produits
 * @var array<int, array> $avis Liste des avis
 * @var string|null $flashSuccess Message de succès
 * @var string|null $flashError Message d'erreur
 * @var array<int, int> $favorisIds IDs des produits favoris
 */

require_once dirname(__DIR__) . '/config/config.php';

use App\Config\Database;
use App\Repositories\ProduitRepository;

// Chargement depuis la BDD
$db = Database::getInstance()->getConnection();
$produitRepo = new ProduitRepository($db);

$produits = $produitRepo->getMeilleuresVentes(8);
$categories = $produitRepo->getCategories();
$tousLesProduits = $produitRepo->getAllProduitsCount();

// Avis statiques
$avis = [
    ['nom' => 'Aminata D.', 'note' => 5, 'texte' => 'Qualité irréprochable, livraison rapide à Dakar. Je recommande !'],
    ['nom' => 'Moussa K.',  'note' => 5, 'texte' => 'Le panier tressé est magnifique, exactement comme sur la photo.'],
    ['nom' => 'Fatou B.',   'note' => 4, 'texte' => 'Très bons produits, le service client est réactif et sympa.'],
];

// Flash messages
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error']   ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// Charger les IDs des favoris
$favorisIds = [];
if (\App\Utils\Auth::check()) {
    try {
        $stmtFav = $db->prepare("SELECT id_produit FROM favoris WHERE id_utilisateur = :id");
        $stmtFav->execute([':id' => \App\Utils\Auth::id()]);
        $favorisIds = $stmtFav->fetchAll(\PDO::FETCH_COLUMN);
    } catch (\Exception $e) {}
}

// Générer token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Récupère la première image d'un produit (pour l'affichage)
 */
function getProductImage($produit) {
    // Vérifier d'abord si le champ 'images' existe et n'est pas vide
    if (!empty($produit['images'])) {
        $images = json_decode($produit['images'], true);
        if (is_array($images) && !empty($images)) {
            $firstImage = $images[0];
            return url('assets/img/produits/' . $firstImage);
        }
    }
    
    // Fallback sur l'ancien champ 'image'
    if (!empty($produit['image'])) {
        return url('assets/img/produits/' . $produit['image']);
    }
    
    // Image par défaut
    return url('assets/img/produits/default.jpg');
}

/**
 * Récupère toutes les images d'un produit (pour la galerie)
 */
function getProductImages($produit) {
    $images = [];
    
    // Vérifier le champ 'images' (JSON)
    if (!empty($produit['images'])) {
        $images = json_decode($produit['images'], true);
        if (is_array($images)) {
            $images = array_map(function($img) {
                return url('assets/img/produits/' . $img);
            }, $images);
        } else {
            $images = [];
        }
    }
    
    // Fallback sur l'ancien champ 'image'
    if (empty($images) && !empty($produit['image'])) {
        $images[] = url('assets/img/produits/' . $produit['image']);
    }
    
    // Image par défaut si aucune image
    if (empty($images)) {
        $images[] = url('assets/img/produits/default.jpg');
    }
    
    return $images;
}

// Badge promo
function getBadgeIndex(array $produit): ?array {
    if (!empty($produit['prix_promo']) && $produit['prix_promo'] < $produit['prix']) {
        $pct = round((1 - $produit['prix_promo'] / $produit['prix']) * 100);
        return ['label' => "-{$pct}%", 'color' => 'danger'];
    }
    if (!empty($produit['date_ajout'])) {
        $diff = (new DateTime())->diff(new DateTime($produit['date_ajout']))->days;
        if ($diff <= 7) return ['label' => 'Nouveau', 'color' => 'success'];
    }
    return null;
}

$pageTitle = 'Accueil - NGAARY SHOP';
$currentPage = 'index.php';

ob_start();
?>

<style>
    .font-serif { font-family: 'Playfair Display', serif; }

    @keyframes fadeInUp    { from { opacity:0; transform:translateY(30px); } to { opacity:1; transform:translateY(0); } }
    @keyframes fadeInLeft  { from { opacity:0; transform:translateX(-40px); } to { opacity:1; transform:translateX(0); } }
    @keyframes fadeInRight { from { opacity:0; transform:translateX(40px); } to { opacity:1; transform:translateX(0); } }
    @keyframes floating    { 0%,100% { transform:translateY(0); } 50% { transform:translateY(-15px); } }
    @keyframes pulse-green { 0%,100% { box-shadow:0 0 0 0 rgba(22,163,74,.4); } 50% { box-shadow:0 0 0 12px rgba(22,163,74,0); } }

    .reveal { opacity:0; transform:translateY(30px); transition:opacity .6s ease,transform .6s ease; }
    .reveal.visible { opacity:1; transform:translateY(0); }
    .reveal-delay-1 { transition-delay:.1s; }
    .reveal-delay-2 { transition-delay:.2s; }
    .reveal-delay-3 { transition-delay:.3s; }
    .reveal-delay-4 { transition-delay:.4s; }

    .hero-section  { background:linear-gradient(135deg,#0d2818 0%,#1a6b35 100%); padding:100px 0; }
    .hero-text     { animation:fadeInLeft .9s ease both; }
    .hero-image    { animation:fadeInRight .9s ease .3s both; }

    .btn-vivid { background-color:#16a34a; color:white; border:none; border-radius:50px; font-weight:600; letter-spacing:1px; transition:background-color .2s,transform .2s,box-shadow .2s; animation:pulse-green 2.5s infinite; }
    .btn-vivid:hover { background-color:#15803d; color:white; transform:translateY(-3px); box-shadow:0 8px 20px rgba(22,163,74,.4); animation:none; }

    .section-label   { color:#16a34a; font-size:.8rem; text-transform:uppercase; letter-spacing:3px; font-weight:600; display:block; margin-bottom:6px; }
    .section-divider { height:3px; width:50px; background:linear-gradient(90deg,#16a34a,#4ade80); margin-top:6px; border-radius:2px; }

    .avantage-item { background:white; border-radius:14px; padding:20px; text-align:center; box-shadow:0 2px 12px rgba(0,0,0,.05); transition:transform .3s,box-shadow .3s; }
    .avantage-item:hover { transform:translateY(-6px); box-shadow:0 12px 30px rgba(22,163,74,.15); }
    .avantage-icon { font-size:1.8rem; color:#16a34a; margin-bottom:8px; transition:transform .3s; }
    .avantage-item:hover .avantage-icon { transform:scale(1.2) rotate(-5deg); }

    .categorie-card { background:white; border-radius:16px; padding:28px 16px; box-shadow:0 2px 12px rgba(0,0,0,.06); transition:transform .3s,box-shadow .3s,background .3s; color:#0d2818; }
    .categorie-card:hover { transform:translateY(-6px); box-shadow:0 12px 30px rgba(22,163,74,.15); background:#f0faf3; }
    .categorie-icon { font-size:2.2rem; color:#16a34a; margin-bottom:12px; transition:transform .3s; }
    .categorie-card:hover .categorie-icon { transform:scale(1.2); }

    .product-card { border:none; border-radius:16px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,.07); transition:transform .3s,box-shadow .3s; }
    .product-card:hover { transform:translateY(-8px); box-shadow:0 16px 40px rgba(0,0,0,.14); }
    .product-img { background-color:#e8f5ee; position:relative; height:200px; overflow:hidden; display:flex; align-items:center; justify-content:center; }
    .product-img img { width:100%; height:100%; object-fit:cover; transition:transform .3s; }
    .product-card:hover .product-img img { transform:scale(1.05); }
    .btn-wishlist { background:white; border:none; border-radius:50%; width:32px; height:32px; display:flex; align-items:center; justify-content:center; box-shadow:0 2px 8px rgba(0,0,0,.1); color:#0d2818; transition:color .2s,transform .2s; cursor:pointer; }
    .btn-wishlist:hover { color:#e11d48; transform:scale(1.2); }
    .btn-cart { background-color:#16a34a; color:white; border:none; border-radius:8px; padding:8px 16px; font-size:.85rem; font-weight:600; width:100%; transition:background-color .2s; cursor:pointer; }
    .btn-cart:hover { background-color:#15803d; color:white; }

    .promo-banner { background:linear-gradient(135deg,#1a4731,#16a34a); border-radius:20px; color:white; padding:50px 40px; transition:transform .3s,box-shadow .3s; }
    .promo-banner:hover { transform:translateY(-4px); box-shadow:0 20px 50px rgba(22,163,74,.3); }

    .avis-card { background:white; border-radius:16px; padding:28px; box-shadow:0 4px 16px rgba(0,0,0,.06); height:100%; transition:transform .3s,box-shadow .3s; }
    .avis-card:hover { transform:translateY(-5px); box-shadow:0 12px 30px rgba(0,0,0,.1); }
    .avis-avatar { width:36px; height:36px; border-radius:50%; background-color:#16a34a; color:white; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:.9rem; }

    .toast-ngaary { position:fixed; bottom:30px; right:30px; z-index:9999; background:#0d2818; color:white; border-radius:14px; padding:16px 24px; display:flex; align-items:center; gap:12px; box-shadow:0 8px 30px rgba(0,0,0,.2); animation:slideInToast .4s ease; min-width:280px; }
    .toast-ngaary.error { background:#dc2626; }
    @keyframes slideInToast { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
    
    .multiple-images-badge {
        position: absolute;
        bottom: 8px;
        right: 8px;
        background: rgba(0,0,0,0.6);
        color: white;
        border-radius: 20px;
        padding: 2px 8px;
        font-size: 0.7rem;
        font-weight: 500;
        z-index: 5;
    }
</style>

<!-- ====== HERO ====== -->
<section class="hero-section text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 text-lg-start text-center hero-text">
                <span class="text-success text-uppercase small fw-bold" style="letter-spacing:3px;animation:fadeInUp .6s ease both;">✦ Nouvelle Collection 2025</span>
                <h1 class="display-3 font-serif my-4 lh-sm" style="animation:fadeInUp .7s ease .1s both;">
                    Naturellement<br><span style="color:#4ade80;">élégant</span>
                </h1>
                <p class="lead opacity-75 mb-4" style="animation:fadeInUp .7s ease .2s both;">
                    Des objets artisanaux durables, fabriqués avec soin pour embellir votre quotidien — à prix juste.
                </p>
                <div class="d-flex gap-3 flex-wrap justify-content-lg-start justify-content-center mb-5" style="animation:fadeInUp .7s ease .3s both;">
                    <a href="<?= url('boutique.php') ?>" class="btn btn-vivid px-4 py-2 shadow">
                        <i class="bi bi-bag me-2"></i>VOIR LA BOUTIQUE
                    </a>
                    <a href="<?= url('contact.php') ?>" class="btn btn-outline-light px-4 py-2 rounded-pill">
                        NOTRE HISTOIRE
                    </a>
                </div>
                <div class="d-flex gap-4 justify-content-lg-start justify-content-center" style="animation:fadeInUp .7s ease .4s both;">
                    <div>
                        <div class="h4 fw-bold mb-0" style="color:#4ade80;">+1 200</div>
                        <div class="small opacity-75">Clients satisfaits</div>
                    </div>
                    <div class="border-start border-secondary ps-4">
                        <div class="h4 fw-bold mb-0" style="color:#4ade80;"><?= (int)($tousLesProduits ?? count($produits)) ?>+</div>
                        <div class="small opacity-75">Produits disponibles</div>
                    </div>
                    <div class="border-start border-secondary ps-4">
                        <div class="h4 fw-bold mb-0" style="color:#4ade80;">4.9★</div>
                        <div class="small opacity-75">Note moyenne</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 d-none d-lg-flex justify-content-center align-items-center hero-image">
                <div style="width:100%; border-radius:24px; overflow:hidden; border:2px dashed rgba(255,255,255,.1); background:rgba(255,255,255,.05); animation:floating 3s ease-in-out infinite;">
                    <img src="<?= url('imgs/herologo.png') ?>" alt="NGAARY SHOP" style="width:100%; max-height:400px; object-fit:cover; display:block;" onerror="this.style.display='none'; document.getElementById('hero-placeholder').style.display='flex';">
                    <div id="hero-placeholder" style="display:none; width:100%; padding:60px; align-items:center; justify-content:center; text-align:center;">
                        <i class="bi bi-image" style="font-size:8rem; color:rgba(255,255,255,.15);"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FLASH MESSAGES -->
<?php if ($flashSuccess || $flashError) : ?>
<div class="container mt-4">
    <?php if ($flashSuccess) : ?>
    <div class="alert alert-success d-flex align-items-center gap-2 rounded-3 py-2">
        <i class="bi bi-check-circle-fill"></i><span><?= htmlspecialchars($flashSuccess) ?></span>
    </div>
    <?php endif; ?>
    <?php if ($flashError) : ?>
    <div class="alert alert-danger d-flex align-items-center gap-2 rounded-3 py-2">
        <i class="bi bi-exclamation-circle-fill"></i><span><?= htmlspecialchars($flashError) ?></span>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- AVANTAGES -->
<section class="py-4 bg-white border-bottom">
    <div class="container">
        <div class="row g-3">
            <div class="col-6 col-md-3 reveal reveal-delay-1">
                <div class="avantage-item">
                    <div class="avantage-icon"><i class="bi bi-truck"></i></div>
                    <p class="fw-semibold small mb-0">Livraison gratuite</p>
                    <p class="text-muted mb-0" style="font-size:.78rem;">Dès <?= formatFCFA(15000) ?></p>
                </div>
            </div>
            <div class="col-6 col-md-3 reveal reveal-delay-2">
                <div class="avantage-item">
                    <div class="avantage-icon"><i class="bi bi-arrow-return-left"></i></div>
                    <p class="fw-semibold small mb-0">Retour facile</p>
                    <p class="text-muted mb-0" style="font-size:.78rem;">Sous 7 jours</p>
                </div>
            </div>
            <div class="col-6 col-md-3 reveal reveal-delay-3">
                <div class="avantage-item">
                    <div class="avantage-icon"><i class="bi bi-shield-check"></i></div>
                    <p class="fw-semibold small mb-0">Paiement sécurisé</p>
                    <p class="text-muted mb-0" style="font-size:.78rem;">Wave, Orange Money</p>
                </div>
            </div>
            <div class="col-6 col-md-3 reveal reveal-delay-4">
                <div class="avantage-item">
                    <div class="avantage-icon"><i class="bi bi-headset"></i></div>
                    <p class="fw-semibold small mb-0">Support client</p>
                    <p class="text-muted mb-0" style="font-size:.78rem;">Lun – Sam, 8h–20h</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CATÉGORIES -->
<?php if (!empty($categories)) : ?>
<section class="py-5">
    <div class="container">
        <div class="text-center mb-4 reveal">
            <span class="section-label">Explorez</span>
            <h2 class="font-serif">Nos Catégories</h2>
            <div class="section-divider mx-auto"></div>
        </div>
        <div class="row g-3 justify-content-center">
            <?php
            $icones = ['bi-house-heart','bi-stars','bi-bag-heart','bi-basket','bi-lamp','bi-palette'];
            foreach (array_values($categories) as $i => $cat) :
            ?>
            <div class="col-6 col-md-3 reveal reveal-delay-<?= ($i % 4) + 1 ?>">
                <a href="<?= url('boutique.php?categorie=' . urlencode($cat['nom'])) ?>" class="text-decoration-none">
                    <div class="categorie-card text-center">
                        <div class="categorie-icon"><i class="bi <?= $icones[$i % count($icones)] ?>"></i></div>
                        <h6 class="fw-bold mb-1"><?= htmlspecialchars($cat['nom']) ?></h6>
                        <small class="text-muted"><?= $cat['nb_produits'] ?> articles</small>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- MEILLEURES VENTES -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4 reveal">
            <div>
                <span class="section-label">Tendances</span>
                <h2 class="font-serif mb-0">Nos Meilleures Ventes</h2>
                <div class="section-divider"></div>
            </div>
            <a href="<?= url('boutique.php') ?>" class="text-success text-decoration-none small fw-bold">VOIR TOUT →</a>
        </div>

        <?php if (empty($produits)) : ?>
        <div class="text-center py-5">
            <i class="bi bi-box-seam" style="font-size:3rem; color:#a7c9b3;"></i>
            <p class="text-muted mt-3">Aucun produit disponible pour le moment.</p>
        </div>
        <?php else : ?>
        <div class="row g-4">
            <?php foreach ($produits as $idx => $produit) :
                $badge = getBadgeIndex($produit);
                $prixAffiche = ($produit['prix_promo'] && $produit['prix_promo'] > 0) ? $produit['prix_promo'] : $produit['prix'];
                $imageUrl = getProductImage($produit);
                
                // Comptage des images
                $imageCount = 0;
                if (!empty($produit['images'])) {
                    $decoded = json_decode($produit['images'], true);
                    $imageCount = is_array($decoded) ? count($decoded) : 0;
                }
                if ($imageCount === 0 && !empty($produit['image'])) {
                    $imageCount = 1;
                }
            ?>
            <div class="col-6 col-lg-3 reveal reveal-delay-<?= ($idx % 4) + 1 ?>">
                <div class="card product-card h-100">
                    <div class="product-img">
                        <?php if ($badge) : ?>
                            <span class="badge bg-<?= $badge['color'] ?> position-absolute top-0 start-0 m-2 z-1"><?= $badge['label'] ?></span>
                        <?php endif; ?>
                        <button type="button" class="btn-wishlist position-absolute top-0 end-0 m-2 z-1 wishlist-btn" data-id="<?= $produit['id_produit'] ?>" onclick="event.preventDefault(); toggleWishlist(this)" title="Ajouter aux favoris">
                            <i class="bi bi-heart<?= in_array($produit['id_produit'], $favorisIds) ? '-fill text-danger' : '' ?>"></i>
                        </button>
                        <a href="<?= url('produit.php?id=' . $produit['id_produit']) ?>" class="d-block w-100 h-100">
                            <img src="<?= $imageUrl ?>" alt="<?= htmlspecialchars($produit['nom']) ?>" onerror="this.src='<?= url('assets/img/produits/default.jpg') ?>'">
                        </a>
                        <?php if ($imageCount > 1) : ?>
                            <span class="position-absolute bottom-0 end-0 m-2 bg-dark bg-opacity-75 text-white rounded-pill px-2 py-1 small" style="font-size: 0.7rem;">
                                <i class="bi bi-images"></i> <?= $imageCount ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body d-flex flex-column px-3 py-3">
                        <small class="text-muted mb-1" style="font-size:.75rem;"><?= htmlspecialchars($produit['categorie_nom'] ?? '') ?></small>
                        <h5 class="card-title h6 mb-1 fw-semibold">
                            <a href="<?= url('produit.php?id=' . $produit['id_produit']) ?>" class="text-dark text-decoration-none"><?= htmlspecialchars($produit['nom']) ?></a>
                        </h5>
                        <div class="mt-auto">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="text-success fw-bold"><?= formatFCFA((int)$prixAffiche) ?></span>
                                <?php if (!empty($produit['prix_promo']) && $produit['prix_promo'] < $produit['prix']) : ?>
                                <span class="text-muted text-decoration-line-through small"><?= formatFCFA((int)$produit['prix']) ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if ($produit['stock'] > 0) : ?>
                            <form action="<?= url('cart_add.php') ?>" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <input type="hidden" name="id_produit" value="<?= $produit['id_produit'] ?>">
                                <input type="hidden" name="quantite" value="1">
                                <input type="hidden" name="retour" value="index.php">
                                <button type="submit" class="btn-cart"><i class="bi bi-cart-plus me-1"></i>Ajouter</button>
                            </form>
                            <?php else : ?>
                            <button class="btn-cart" disabled style="opacity:.5; cursor:not-allowed;">Indisponible</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- BANNIÈRE PROMO -->
<section class="container py-5 reveal">
    <div class="promo-banner">
        <div class="row align-items-center">
            <div class="col-md-8 mb-3 mb-md-0">
                <span class="text-uppercase small fw-bold opacity-75" style="letter-spacing:2px;">⏳ Offre limitée</span>
                <h3 class="font-serif mt-1 mb-2 fw-bold">-15% sur votre première commande</h3>
                <p class="opacity-75 mb-0">Utilisez le code <strong class="text-white">NGAARY15</strong> à la caisse.<br>Livraison gratuite dès <?= formatFCFA(15000) ?> partout au Sénégal.</p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="<?= url('boutique.php') ?>" class="btn btn-light fw-bold px-4 py-3 rounded-pill text-success fs-6">J'en profite →</a>
            </div>
        </div>
    </div>
</section>

<!-- AVIS CLIENTS -->
<section class="pb-5">
    <div class="container">
        <div class="text-center mb-4 reveal">
            <span class="section-label">Ils nous font confiance</span>
            <h2 class="font-serif">Avis Clients</h2>
            <div class="section-divider mx-auto"></div>
        </div>
        <div class="row g-4">
            <?php foreach ($avis as $i => $a) : ?>
            <div class="col-md-4 reveal reveal-delay-<?= $i + 1 ?>">
                <div class="avis-card">
                    <div class="mb-2">
                        <?php for ($j = 1; $j <= 5; $j++) : ?>
                            <i class="bi bi-star<?= $j <= $a['note'] ? '-fill text-warning' : ' text-muted' ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <p class="mb-3 fst-italic">"<?= htmlspecialchars($a['texte']) ?>"</p>
                    <div class="d-flex align-items-center gap-2">
                        <div class="avis-avatar"><?= strtoupper(substr($a['nom'], 0, 1)) ?></div>
                        <strong class="small"><?= htmlspecialchars($a['nom']) ?></strong>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- TOAST -->
<?php if ($flashSuccess) : ?>
<div class="toast-ngaary" id="toast-success">
    <i class="bi bi-check-circle-fill text-success fs-5"></i>
    <div>
        <div class="fw-semibold small"><?= htmlspecialchars($flashSuccess) ?></div>
        <a href="<?= url('cart.php') ?>" class="text-success small text-decoration-none">Voir le panier →</a>
    </div>
    <button onclick="document.getElementById('toast-success').remove()" style="background:none; border:none; color:white; margin-left:auto; cursor:pointer;"><i class="bi bi-x-lg"></i></button>
</div>
<?php endif; ?>

<script>
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
    }, { threshold: 0.1 });
    document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
    setTimeout(() => document.getElementById('toast-success')?.remove(), 4000);

    function toggleWishlist(btn) {
        const idProduit = btn.dataset.id;
        const icon = btn.querySelector('i');
        fetch('<?= url('wishlist_toggle.php') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
            body: 'id_produit=' + idProduit + '&csrf_token=<?= $_SESSION['csrf_token'] ?? '' ?>'
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'unauthenticated') { window.location.href = '<?= url('login.php') ?>'; return; }
            if (data.status === 'success') {
                icon.className = data.action === 'added' ? 'bi bi-heart-fill text-danger' : 'bi bi-heart';
                const t = document.createElement('div');
                t.style.cssText = 'position:fixed;bottom:80px;right:30px;z-index:9999;background:#0d2818;color:white;border-radius:12px;padding:12px 20px;box-shadow:0 4px 20px rgba(0,0,0,.2);font-size:.9rem;';
                t.innerHTML = data.action === 'added' ? '❤️ Ajouté aux favoris !' : '🗑️ Retiré des favoris.';
                document.body.appendChild(t);
                setTimeout(() => t.remove(), 2500);
            }
        });
    }
</script>

<?php
$content = ob_get_clean();
require_once dirname(__DIR__) . '/views/layouts/main_layout.php';
?>