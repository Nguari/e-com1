<?php
require_once dirname(__DIR__) . '/config/config.php';

use App\Config\Database;
use App\Repositories\ProduitRepository;

$pageTitle   = 'Boutique - NGAARY SHOP';
$currentPage = 'boutique.php';

include __DIR__ . '/../views/layouts/header.php';

// ✅ Chargement depuis la BDD
$db          = Database::getInstance()->getConnection();
$produitRepo = new ProduitRepository($db);

$categorieActive = $_GET['categorie'] ?? 'Tous';
$tousLesProduits = $produitRepo->findAllWithCategorie();
$categories      = $produitRepo->getCategories();

$produits = $categorieActive === 'Tous'
    ? $tousLesProduits
    : $produitRepo->findByCategorie($categorieActive);

// Flash messages
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error']   ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// ✅ Charger les favoris de l'utilisateur connecté
$favorisIds = [];
if (\App\Utils\Auth::check()) {
    try {
        $stmtFav = $db->prepare("SELECT id_produit FROM favoris WHERE id_utilisateur = :id");
        $stmtFav->execute([':id' => \App\Utils\Auth::id()]);
        $favorisIds = $stmtFav->fetchAll(\PDO::FETCH_COLUMN);
    } catch (\Exception $e) {}
}

/**
 * Récupère la première image d'un produit (images multiples ou unique)
 */
function getProductImage($produit) {
    // Vérifier les images multiples (JSON)
    if (!empty($produit['images'])) {
        $images = json_decode($produit['images'], true);
        if (is_array($images) && !empty($images)) {
            return '/assets/img/produits/' . $images[0];
        }
    }
    
    // Fallback sur l'image unique
    if (!empty($produit['image'])) {
        return '/assets/img/produits/' . $produit['image'];
    }
    
    return '/assets/img/produits/default.jpg';
}

/**
 * Compte le nombre d'images d'un produit
 */
function getImageCount($produit) {
    if (!empty($produit['images'])) {
        $images = json_decode($produit['images'], true);
        if (is_array($images)) {
            return count($images);
        }
    }
    return !empty($produit['image']) ? 1 : 0;
}

// ✅ Calcul du badge dynamique
function getBadge(array $produit): ?array {
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
?>

<style>
    .font-serif { font-family: 'Playfair Display', serif; }
    .boutique-hero { background: linear-gradient(135deg, #0d2818 0%, #1a6b35 100%); padding: 60px 0; }
    .filter-btn { border: 1.5px solid #e2e8f0; border-radius: 50px; padding: 8px 20px; background: white; color: #0d2818; font-size: 0.85rem; font-weight: 500; cursor: pointer; text-decoration: none; transition: all 0.2s; display: inline-block; }
    .filter-btn:hover { border-color: #16a34a; color: #16a34a; background: #f0faf3; }
    .filter-btn.active { background: #16a34a; border-color: #16a34a; color: white; font-weight: 600; }
    .product-card { border: none; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.07); transition: transform 0.3s, box-shadow 0.3s; }
    .product-card:hover { transform: translateY(-6px); box-shadow: 0 16px 40px rgba(0,0,0,0.12); }

    .product-img {
    background-color: #e8f5ee;
    position: relative;
    height: 200px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}
    .product-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s;
    }
    .product-card:hover .product-img img { transform: scale(1.05); }
    .product-img .placeholder { font-size: 4rem; color: #a7c9b3; }

.btn-wishlist {
    background: white;
    border: none;
    border-radius: 50%;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    color: #0d2818;
    transition: all 0.2s;
    cursor: pointer;
    opacity: 1; /* Toujours visible */
    z-index: 10;
}

.btn-wishlist:hover {
    color: #e11d48;
    transform: scale(1.1);
    background: white;
}
    .btn-cart { background-color: #16a34a; color: white; border: none; border-radius: 8px; padding: 8px 16px; font-size: 0.85rem; font-weight: 600; width: 100%; transition: background-color 0.2s; cursor: pointer; }
    .btn-cart:hover { background-color: #15803d; color: white; }
    .section-divider { height: 3px; width: 50px; background-color: #16a34a; margin-top: 6px; }
    .reveal { opacity: 0; transform: translateY(25px); transition: opacity 0.5s ease, transform 0.5s ease; }
    .reveal.visible { opacity: 1; transform: translateY(0); }
    .reveal-delay-1 { transition-delay: 0.05s; }
    .reveal-delay-2 { transition-delay: 0.1s; }
    .reveal-delay-3 { transition-delay: 0.15s; }
    .reveal-delay-4 { transition-delay: 0.2s; }
    .toast-ngaary { position: fixed; bottom: 30px; right: 30px; z-index: 9999; background: #0d2818; color: white; border-radius: 14px; padding: 16px 24px; display: flex; align-items: center; gap: 12px; box-shadow: 0 8px 30px rgba(0,0,0,0.2); animation: slideInToast 0.4s ease; min-width: 280px; }
    .toast-ngaary.error { background: #dc2626; }
    @keyframes slideInToast { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    
    .multi-images-badge {
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

<!-- HERO -->
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

<!-- FILTRES -->
<section class="py-4 bg-white border-bottom" style="top: 56px; z-index: 100;">
    <div class="container">
        <div class="d-flex gap-2 flex-wrap justify-content-center">
            <a href="<?= url('boutique.php') ?>"
               class="filter-btn <?= $categorieActive === 'Tous' ? 'active' : '' ?>">
                Tous <span class="ms-1 opacity-75">(<?= count($tousLesProduits) ?>)</span>
            </a>
          <?php foreach ($categories as $cat) : ?>
            <a href="<?= url('boutique.php') . '?categorie=' . urlencode($cat['nom']) ?>"
               class="filter-btn <?= $categorieActive === $cat['nom'] ? 'active' : '' ?>">
                <?= htmlspecialchars($cat['nom']) ?>
                <span class="ms-1 opacity-75">(<?= $cat['nb_produits'] ?>)</span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- GRILLE PRODUITS -->
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
                <?php foreach (array_values($produits) as $idx => $produit) :
                    $badge       = getBadge($produit);
                    $prixAffiche = $produit['prix_promo'] ?? $produit['prix'];
                    $imageUrl    = getProductImage($produit);
                    $imageCount  = getImageCount($produit);
                ?>
                <div class="col-6 col-lg-3 reveal reveal-delay-<?= ($idx % 4) + 1 ?>">
                    <div class="card product-card h-100">

                        <!-- IMAGE -->
                        <div class="product-img">
                            <?php if ($badge) : ?>
                                <span class="badge bg-<?= $badge['color'] ?> position-absolute top-0 start-0 m-2">
                                    <?= $badge['label'] ?>
                                </span>
                            <?php endif; ?>

                            <button type="button"
                                    class="btn-wishlist position-absolute top-0 end-0 m-2 wishlist-btn"
                                    data-id="<?= $produit['id_produit'] ?>"
                                    onclick="event.preventDefault(); toggleWishlist(this)"
                                    title="Ajouter aux favoris">
                                <i class="bi bi-heart<?= in_array($produit['id_produit'], $favorisIds) ? '-fill text-danger' : '' ?>"></i>
                            </button>

                            <a href="<?= url('produit.php?id=' . $produit['id_produit']) ?>" class="d-block w-100 h-100">
                                <img src="<?= $imageUrl ?>"
                                     alt="<?= htmlspecialchars($produit['nom']) ?>"
                                     onerror="this.src='/assets/img/produits/default.jpg'">
                            </a>
                            
                            <?php if ($imageCount > 1) : ?>
                                <span class="multi-images-badge">
                                    <i class="bi bi-images"></i> <?= $imageCount ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- INFOS -->
                        <div class="card-body d-flex flex-column px-3 py-3">
                            <small class="text-muted mb-1" style="font-size: 0.75rem;">
                                <?= htmlspecialchars($produit['categorie_nom'] ?? '') ?>
                            </small>
                            <h5 class="card-title h6 mb-1 fw-semibold">
                                <a href="<?= url('produit.php?id=' . $produit['id_produit']) ?>"
                                   class="text-dark text-decoration-none">
                                    <?= htmlspecialchars($produit['nom']) ?>
                                </a>
                            </h5>

                            <?php if ($produit['stock'] <= 5 && $produit['stock'] > 0) : ?>
                            <small class="text-warning mb-1">
                                <i class="bi bi-exclamation-circle me-1"></i>Plus que <?= $produit['stock'] ?> en stock
                            </small>
                            <?php elseif ($produit['stock'] == 0) : ?>
                            <small class="text-danger mb-1">
                                <i class="bi bi-x-circle me-1"></i>Rupture de stock
                            </small>
                            <?php endif; ?>

                            <div class="mt-auto">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="text-success fw-bold"><?= formatFCFA((int)$prixAffiche) ?></span>
                                    <?php if (!empty($produit['prix_promo'])) : ?>
                                        <span class="text-muted text-decoration-line-through small">
                                            <?= formatFCFA((int)$produit['prix']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <?php if ($produit['stock'] > 0) : ?>
                                <form action="<?= url('cart_add.php') ?>" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                    <input type="hidden" name="id_produit" value="<?= $produit['id_produit'] ?>">
                                    <input type="hidden" name="quantite"   value="1">
                                    <input type="hidden" name="retour"     value="boutique.php">
                                    <button type="submit" class="btn-cart">
                                        <i class="bi bi-cart-plus me-1"></i>Ajouter
                                    </button>
                                </form>
                                <?php else : ?>
                                <button class="btn-cart" disabled style="opacity: 0.5; cursor: not-allowed;">
                                    Indisponible
                                </button>
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

<!-- TOAST -->
<?php if ($flashSuccess) : ?>
<div class="toast-ngaary" id="toast-success">
    <i class="bi bi-check-circle-fill text-success fs-5"></i>
    <div>
        <div class="fw-semibold small"><?= htmlspecialchars($flashSuccess) ?></div>
        <a href="<?= url('cart.php') ?>" class="text-success small text-decoration-none">Voir le panier →</a>
    </div>
    <button onclick="document.getElementById('toast-success').remove()"
            style="background:none; border:none; color:white; margin-left:auto; cursor:pointer;">
        <i class="bi bi-x-lg"></i>
    </button>
</div>
<?php endif; ?>

<?php if ($flashError) : ?>
<div class="toast-ngaary error" id="toast-error">
    <i class="bi bi-exclamation-circle-fill fs-5"></i>
    <div class="fw-semibold small"><?= htmlspecialchars($flashError) ?></div>
    <button onclick="document.getElementById('toast-error').remove()"
            style="background:none; border:none; color:white; margin-left:auto; cursor:pointer;">
        <i class="bi bi-x-lg"></i>
    </button>
</div>
<?php endif; ?>

<script>
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) entry.target.classList.add('visible');
        });
    }, { threshold: 0.1 });
    document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

    setTimeout(() => {
        document.getElementById('toast-success')?.remove();
        document.getElementById('toast-error')?.remove();
    }, 4000);

    function toggleWishlist(btn) {
        const idProduit = btn.dataset.id;
        const icon      = btn.querySelector('i');
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
                t.innerHTML = data.action === 'added' ? '❤️ Ajouté aux favoris !' : 'Retiré des favoris.';
                document.body.appendChild(t);
                setTimeout(() => t.remove(), 2500);
            }
        });
    }
</script>

<?php include __DIR__ . '/../views/layouts/footer.php'; ?>