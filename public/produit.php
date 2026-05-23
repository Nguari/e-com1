<?php
require_once dirname(__DIR__) . '/config/config.php';

use App\Config\Database;
use App\Repositories\ProduitRepository;

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: ' . url('boutique.php'));
    exit();
}

$db = Database::getInstance()->getConnection();
$produitRepo = new ProduitRepository($db);
$produit = $produitRepo->findById($id);

if (!$produit) {
    header('Location: ' . url('boutique.php'));
    exit();
}

// Flash messages
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error']   ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// Récupérer les produits similaires (même catégorie)
$produitsSimilaires = [];
if (!empty($produit['id_categorie'])) {
    $stmt = $db->prepare("
        SELECT p.*, c.nom as categorie_nom 
        FROM produits p
        LEFT JOIN categories c ON p.id_categorie = c.id_categorie
        WHERE p.id_categorie = :categorie AND p.id_produit != :id AND p.actif = 1
        LIMIT 4
    ");
    $stmt->execute([':categorie' => $produit['id_categorie'], ':id' => $id]);
    $produitsSimilaires = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Récupérer toutes les images du produit
$productImages = [];
if (!empty($produit['images'])) {
    $productImages = json_decode($produit['images'], true);
    if (!is_array($productImages)) {
        $productImages = [];
    }
}
// Fallback sur l'image unique
if (empty($productImages) && !empty($produit['image'])) {
    $productImages = [$produit['image']];
}
// Image par défaut
if (empty($productImages)) {
    $productImages = ['default.jpg'];
}

// Charger les favoris
$isFavorite = false;
if (\App\Utils\Auth::check()) {
    try {
        $stmtFav = $db->prepare("SELECT id_produit FROM favoris WHERE id_utilisateur = :user AND id_produit = :produit");
        $stmtFav->execute([':user' => \App\Utils\Auth::id(), ':produit' => $id]);
        $isFavorite = $stmtFav->fetch() !== false;
    } catch (\Exception $e) {}
}

$pageTitle = htmlspecialchars($produit['nom']) . ' - NGAARY SHOP';
$currentPage = 'produit.php';

ob_start();
?>

<style>
    .product-gallery {
        position: sticky;
        top: 100px;
    }
    .main-image {
        background-color: #f8fafc;
        border-radius: 20px;
        overflow: hidden;
        height: 400px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: zoom-in;
    }
    .main-image img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        transition: transform 0.3s;
    }
    .main-image img:hover {
        transform: scale(1.05);
    }
    .thumbnail-list {
        display: flex;
        gap: 10px;
        margin-top: 15px;
        overflow-x: auto;
        padding-bottom: 5px;
    }
    .thumbnail {
        width: 80px;
        height: 80px;
        border-radius: 12px;
        overflow: hidden;
        cursor: pointer;
        border: 2px solid transparent;
        transition: all 0.2s;
        flex-shrink: 0;
    }
    .thumbnail.active {
        border-color: #16a34a;
        box-shadow: 0 0 0 2px rgba(22,163,74,0.2);
    }
    .thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .thumbnail:hover:not(.active) {
        border-color: #cbd5e1;
        transform: scale(1.02);
    }
    .quantity-selector {
        display: flex;
        align-items: center;
        gap: 15px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 5px;
        width: fit-content;
    }
    .quantity-btn {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        border: none;
        background: #f1f5f9;
        font-size: 1.2rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }
    .quantity-btn:hover {
        background: #16a34a;
        color: white;
    }
    .quantity-input {
        width: 60px;
        text-align: center;
        border: none;
        font-weight: 600;
        font-size: 1.1rem;
        background: transparent;
    }
    .quantity-input:focus {
        outline: none;
    }
    .stock-info {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 12px;
        border-radius: 50px;
        font-size: 0.8rem;
    }
    .stock-instock {
        background: #d1fae5;
        color: #059669;
    }
    .stock-lowstock {
        background: #fed7aa;
        color: #c2410c;
    }
    .stock-outstock {
        background: #fee2e2;
        color: #dc2626;
    }
    .similar-product-card {
        transition: transform 0.2s;
        text-decoration: none;
    }
    .similar-product-card:hover {
        transform: translateY(-5px);
    }
    .similar-product-card img {
        width: 100%;
        height: 150px;
        object-fit: cover;
    }
    
    @media (max-width: 768px) {
        .main-image {
            height: 300px;
        }
        .thumbnail {
            width: 60px;
            height: 60px;
        }
    }
</style>

<section class="py-5">
    <div class="container">
        
        <!-- Fil d'Ariane -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= url('index.php') ?>" class="text-success text-decoration-none">Accueil</a></li>
                <li class="breadcrumb-item"><a href="<?= url('boutique.php') ?>" class="text-success text-decoration-none">Boutique</a></li>
                <?php if (!empty($produit['categorie_nom'])): ?>
                <li class="breadcrumb-item">
                    <a href="<?= url('boutique.php?categorie=' . urlencode($produit['categorie_nom'])) ?>" class="text-success text-decoration-none">
                        <?= htmlspecialchars($produit['categorie_nom']) ?>
                    </a>
                </li>
                <?php endif; ?>
                <li class="breadcrumb-item active"><?= htmlspecialchars($produit['nom']) ?></li>
            </ol>
        </nav>

        <!-- FLASH MESSAGES -->
        <?php if ($flashSuccess || $flashError) : ?>
        <div class="mb-4">
            <?php if ($flashSuccess) : ?>
            <div class="alert alert-success d-flex align-items-center gap-2 rounded-3 py-2 alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill"></i>
                <span><?= htmlspecialchars($flashSuccess) ?></span>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <?php if ($flashError) : ?>
            <div class="alert alert-danger d-flex align-items-center gap-2 rounded-3 py-2 alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle-fill"></i>
                <span><?= htmlspecialchars($flashError) ?></span>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="row g-5">
            
            <!-- GALERIE D'IMAGES -->
            <div class="col-lg-6">
                <div class="product-gallery">
                    <div class="main-image" id="mainImageContainer">
                        <img src="<?= url('assets/img/produits/' . $productImages[0]) ?>" 
                             alt="<?= htmlspecialchars($produit['nom']) ?>"
                             id="mainImage">
                    </div>
                    <div class="thumbnail-list" id="thumbnailList">
                        <?php foreach ($productImages as $index => $img): ?>
                        <div class="thumbnail <?= $index === 0 ? 'active' : '' ?>" 
                             data-image="<?= url('assets/img/produits/' . $img) ?>">
                            <img src="<?= url('assets/img/produits/' . $img) ?>" 
                                 alt="Image <?= $index + 1 ?>">
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- INFORMATIONS PRODUIT -->
            <div class="col-lg-6">
                <!-- Badges -->
                <div class="d-flex gap-2 mb-3">
                    <?php if (!empty($produit['prix_promo']) && $produit['prix_promo'] < $produit['prix']): ?>
                        <span class="badge bg-danger px-3 py-2">-<?= round((1 - $produit['prix_promo'] / $produit['prix']) * 100) ?>%</span>
                    <?php endif; ?>
                    <?php 
                    $dateAjout = strtotime($produit['date_ajout'] ?? 'now');
                    $diff = time() - $dateAjout;
                    if ($diff < 7 * 24 * 3600): ?>
                        <span class="badge bg-success px-3 py-2">Nouveau</span>
                    <?php endif; ?>
                    <?php if ($produit['stock'] <= 5 && $produit['stock'] > 0): ?>
                        <span class="badge bg-warning text-dark px-3 py-2">Stock limité</span>
                    <?php endif; ?>
                </div>

                <!-- Nom -->
                <h1 class="fw-bold mb-3" style="font-family: 'Playfair Display', serif;"><?= htmlspecialchars($produit['nom']) ?></h1>

                <!-- Référence -->
                <p class="text-muted small mb-3">
                    <i class="bi bi-upc-scan me-1"></i>Référence : <?= htmlspecialchars($produit['reference'] ?? '—') ?>
                </p>

                <!-- Prix -->
                <div class="mb-4">
                    <?php if (!empty($produit['prix_promo']) && $produit['prix_promo'] < $produit['prix']): ?>
                        <span class="text-muted text-decoration-line-through me-2"><?= formatFCFA((int)$produit['prix']) ?></span>
                        <span class="text-success fw-bold fs-1"><?= formatFCFA((int)$produit['prix_promo']) ?></span>
                    <?php else: ?>
                        <span class="text-success fw-bold fs-1"><?= formatFCFA((int)$produit['prix']) ?></span>
                    <?php endif; ?>
                </div>

                <!-- Stock -->
                <div class="mb-4">
                    <?php if ($produit['stock'] > 10): ?>
                        <span class="stock-info stock-instock">
                            <i class="bi bi-check-circle-fill"></i> En stock (<?= $produit['stock'] ?> disponibles)
                        </span>
                    <?php elseif ($produit['stock'] > 0): ?>
                        <span class="stock-info stock-lowstock">
                            <i class="bi bi-exclamation-triangle-fill"></i> Plus que <?= $produit['stock'] ?> en stock
                        </span>
                    <?php else: ?>
                        <span class="stock-info stock-outstock">
                            <i class="bi bi-x-circle-fill"></i> Rupture de stock
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Description -->
                <div class="mb-4">
                    <h6 class="fw-bold mb-2">Description</h6>
                    <p class="text-muted"><?= nl2br(htmlspecialchars($produit['description'] ?? 'Aucune description disponible.')) ?></p>
                </div>

                <!-- Formulaire d'ajout au panier -->
                <?php if ($produit['stock'] > 0): ?>
                <form action="<?= url('cart_add.php') ?>" method="POST" class="mb-4" id="addToCartForm">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="id_produit" value="<?= $produit['id_produit'] ?>">
                    <input type="hidden" name="retour" value="produit.php?id=<?= $produit['id_produit'] ?>">
                    
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <div class="quantity-selector">
                            <button type="button" class="quantity-btn" onclick="changeQuantity(-1)">-</button>
                            <input type="number" name="quantite" id="quantity" value="1" min="1" max="<?= $produit['stock'] ?>" class="quantity-input">
                            <button type="button" class="quantity-btn" onclick="changeQuantity(1)">+</button>
                        </div>
                        <button type="submit" class="btn btn-success px-5 py-3 rounded-3 fw-semibold flex-grow-1">
                            <i class="bi bi-cart-plus me-2"></i>Ajouter au panier
                        </button>
                    </div>
                </form>
                <?php else: ?>
                    <button class="btn btn-secondary px-5 py-3 rounded-3 fw-semibold w-100 mb-4" disabled>
                        <i class="bi bi-x-circle me-2"></i>Indisponible
                    </button>
                <?php endif; ?>

                <!-- Wishlist corrigée -->
                <div class="mb-4">
                    <button type="button" 
                            class="btn btn-outline-danger rounded-3 px-4" 
                            data-id="<?= $produit['id_produit'] ?>"
                            onclick="toggleWishlist(this)" 
                            id="wishlistBtn">
                        <i class="bi bi-heart<?= $isFavorite ? '-fill' : '' ?> me-2"></i>
                        <?= $isFavorite ? 'Retirer des favoris' : 'Ajouter aux favoris' ?>
                    </button>
                </div>

                <!-- Livraison -->
                <div class="border-top pt-4 mt-2">
                    <div class="d-flex gap-3 small text-muted">
                        <div>
                            <i class="bi bi-truck text-success me-1"></i> Livraison gratuite dès 15 000 FCFA
                        </div>
                        <div>
                            <i class="bi bi-arrow-return-left text-success me-1"></i> Retour sous 7 jours
                        </div>
                        <div>
                            <i class="bi bi-shield-check text-success me-1"></i> Paiement sécurisé
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PRODUITS SIMILAIRES -->
        <?php if (!empty($produitsSimilaires)): ?>
        <div class="mt-5 pt-4">
            <h3 class="fw-bold mb-4" style="font-family: 'Playfair Display', serif;">Produits similaires</h3>
            <div class="row g-4">
                <?php foreach ($produitsSimilaires as $similaire): 
                    $simImages = [];
                    if (!empty($similaire['images'])) {
                        $simImages = json_decode($similaire['images'], true);
                    }
                    if (empty($simImages) && !empty($similaire['image'])) {
                        $simImages = [$similaire['image']];
                    }
                    $simImage = !empty($simImages) ? url('assets/img/produits/' . $simImages[0]) : url('assets/img/produits/default.jpg');
                    $simPrix = !empty($similaire['prix_promo']) ? $similaire['prix_promo'] : $similaire['prix'];
                ?>
                <div class="col-6 col-md-3">
                    <a href="<?= url('produit.php?id=' . $similaire['id_produit']) ?>" class="similar-product-card text-decoration-none">
                        <div class="card border-0 rounded-4 overflow-hidden shadow-sm h-100">
                            <img src="<?= $simImage ?>" alt="<?= htmlspecialchars($similaire['nom']) ?>" class="card-img-top" style="height: 180px; object-fit: cover;">
                            <div class="card-body p-3">
                                <small class="text-muted"><?= htmlspecialchars($similaire['categorie_nom'] ?? '') ?></small>
                                <h6 class="fw-semibold mb-1 text-dark"><?= htmlspecialchars($similaire['nom']) ?></h6>
                                <span class="text-success fw-bold"><?= formatFCFA((int)$simPrix) ?></span>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>
</section>

<script>
    // Changement de quantité
    function changeQuantity(delta) {
        const input = document.getElementById('quantity');
        let value = parseInt(input.value) + delta;
        const max = parseInt(input.max);
        if (value < 1) value = 1;
        if (value > max) value = max;
        input.value = value;
    }
    
    // Limiter la saisie manuelle
    document.getElementById('quantity')?.addEventListener('change', function() {
        let value = parseInt(this.value);
        const max = parseInt(this.max);
        if (isNaN(value) || value < 1) this.value = 1;
        if (value > max) this.value = max;
    });
    
    // Galerie d'images
    document.querySelectorAll('.thumbnail').forEach(thumb => {
        thumb.addEventListener('click', function() {
            const imageUrl = this.dataset.image;
            document.getElementById('mainImage').src = imageUrl;
            
            document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
        });
    });
    
    // Zoom sur l'image principale
    const mainImage = document.getElementById('mainImage');
    if (mainImage) {
        mainImage.addEventListener('click', function() {
            window.open(this.src, '_blank');
        });
    }
    
    // Wishlist corrigée
    function toggleWishlist(btn) {
        const productId = btn.getAttribute('data-id');
        
        // Désactiver temporairement le bouton
        btn.style.pointerEvents = 'none';
        btn.style.opacity = '0.7';
        
        fetch('<?= url('wishlist_toggle.php') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'id_produit=' + productId + '&csrf_token=<?= $_SESSION['csrf_token'] ?? '' ?>'
        })
        .then(response => response.json())
        .then(data => {
            // Réactiver le bouton
            btn.style.pointerEvents = 'auto';
            btn.style.opacity = '1';
            
            if (data.status === 'unauthenticated') {
                window.location.href = '<?= url('login.php') ?>?redirect=produit.php?id=<?= $id ?>';
                return;
            }
            
            if (data.status === 'success') {
                if (data.action === 'added') {
                    btn.innerHTML = '<i class="bi bi-heart-fill me-2"></i>Retirer des favoris';
                    showToast('❤️ Ajouté aux favoris !');
                } else {
                    btn.innerHTML = '<i class="bi bi-heart me-2"></i>Ajouter aux favoris';
                    showToast('🗑️ Retiré des favoris.');
                }
            }
        })
        .catch(error => {
            btn.style.pointerEvents = 'auto';
            btn.style.opacity = '1';
            console.error('Erreur:', error);
            showToast('❌ Une erreur est survenue', true);
        });
    }
    
    function showToast(message, isError = false) {
        const toast = document.createElement('div');
        toast.style.cssText = 'position:fixed;bottom:80px;right:30px;z-index:9999;background:' + (isError ? '#dc2626' : '#0d2818') + ';color:white;border-radius:12px;padding:12px 20px;box-shadow:0 4px 20px rgba(0,0,0,.2);font-size:.9rem;z-index:99999;';
        toast.innerHTML = message;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.transition = 'opacity 0.3s';
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 2500);
    }
    
    // Auto-fermeture des alertes après 5 secondes
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        });
    }, 5000);
</script>

<?php
$content = ob_get_clean();
require_once dirname(__DIR__) . '/views/layouts/main_layout.php';
?>