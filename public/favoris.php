<?php
// public/mes_favoris.php
require_once dirname(__DIR__) . '/config/config.php';

use App\Config\Database;
use App\Utils\Auth;

if (!Auth::check()) {
    header('Location: ' . url('login.php'));
    exit();
}

/**
 * Récupère la première image d'un produit (images multiples ou unique)
 */
function getFavoriteProductImage($produit) {
    // Vérifier les images multiples (JSON)
    if (!empty($produit['images'])) {
        $images = json_decode($produit['images'], true);
        if (is_array($images) && !empty($images)) {
            return url('assets/img/produits/' . $images[0]);
        }
    }
    
    // Fallback sur l'image unique
    if (!empty($produit['image'])) {
        return url('assets/img/produits/' . $produit['image']);
    }
    
    return url('assets/img/produits/default.jpg');
}

try {
    $db   = Database::getInstance()->getConnection();
    $stmt = $db->prepare("
        SELECT p.*, c.nom AS categorie_nom, f.date_ajout AS date_favori
        FROM favoris f
        JOIN produits p ON f.id_produit = p.id_produit
        LEFT JOIN categories c ON p.id_categorie = c.id_categorie
        WHERE f.id_utilisateur = :id
        ORDER BY f.date_ajout DESC
    ");
    $stmt->execute([':id' => Auth::id()]);
    $favoris = $stmt->fetchAll();
} catch (\Exception $e) {
    $favoris = [];
}

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error']   ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$pageTitle   = 'Mes Favoris - NGAARY SHOP';
$currentPage = '';
include __DIR__ . '/../views/layouts/header.php';
?>

<style>
    .product-card { border:none; border-radius:16px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,.07); transition:transform .3s,box-shadow .3s; }
    .product-card:hover { transform:translateY(-6px); box-shadow:0 16px 40px rgba(0,0,0,.12); }
    .product-img { background-color:#e8f5ee; position:relative; height:200px; overflow:hidden; display:flex; align-items:center; justify-content:center; }
    .product-img img { width:100%; height:100%; object-fit:cover; }
    .btn-cart { background-color:#16a34a; color:white; border:none; border-radius:8px; padding:8px 16px; font-size:.85rem; font-weight:600; width:100%; cursor:pointer; transition:background-color .2s; }
    .btn-cart:hover { background-color:#15803d; color:white; }
    .btn-remove-fav { background:none; border:none; color:#e11d48; cursor:pointer; font-size:1.1rem; transition:transform .2s; padding:0; }
    .btn-remove-fav:hover { transform:scale(1.2); }
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

<!-- BREADCRUMB -->
<section class="py-4 bg-white border-bottom">
    <div class="container">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item">
                <a href="<?= url('index.php') ?>" class="text-success text-decoration-none">Accueil</a>
            </li>
            <li class="breadcrumb-item active">Mes Favoris</li>
        </ol>
    </div>
</section>

<section class="py-5" style="background:#f0faf3;">
    <div class="container">

        <h1 class="fw-bold mb-1" style="font-family:'Playfair Display',serif; color:#0d2818;">
            Mes Favoris
        </h1>
        <p class="text-muted small mb-4"><?= count($favoris) ?> produit<?= count($favoris) > 1 ? 's' : '' ?></p>

        <?php if ($flashSuccess) : ?>
        <div class="alert alert-success rounded-3 d-flex align-items-center gap-2 mb-4">
            <i class="bi bi-check-circle-fill"></i><?= htmlspecialchars($flashSuccess) ?>
        </div>
        <?php endif; ?>

        <?php if ($flashError) : ?>
        <div class="alert alert-danger rounded-3 d-flex align-items-center gap-2 mb-4">
            <i class="bi bi-exclamation-circle-fill"></i><?= htmlspecialchars($flashError) ?>
        </div>
        <?php endif; ?>

        <?php if (empty($favoris)) : ?>
            <div class="text-center py-5 bg-white rounded-4 shadow-sm">
                <i class="bi bi-heart" style="font-size:5rem; color:#d1d5db;"></i>
                <h5 class="fw-bold mt-4 mb-2">Aucun favori</h5>
                <p class="text-muted mb-4">Ajoutez des produits à vos favoris pour les retrouver facilement.</p>
                <a href="<?= url('boutique.php') ?>" class="btn btn-success rounded-3 px-4">
                    <i class="bi bi-bag me-2"></i>Voir la boutique
                </a>
            </div>

        <?php else : ?>
            <div class="row g-4">
                <?php foreach ($favoris as $produit) : 
                    $imageUrl = getFavoriteProductImage($produit);
                    
                    // Compter les images multiples
                    $imageCount = 0;
                    if (!empty($produit['images'])) {
                        $images = json_decode($produit['images'], true);
                        $imageCount = is_array($images) ? count($images) : 0;
                    }
                    if ($imageCount === 0 && !empty($produit['image'])) {
                        $imageCount = 1;
                    }
                ?>
                <div class="col-6 col-lg-3" data-product-id="<?= $produit['id_produit'] ?>">
                    <div class="card product-card h-100">

                        <!-- IMAGE -->
                        <div class="product-img">
                            <!-- Bouton retirer des favoris -->
                            <button class="btn-remove-fav position-absolute top-0 end-0 m-2 bg-white rounded-circle d-flex align-items-center justify-content-center"
                                    style="width:32px; height:32px; box-shadow:0 2px 8px rgba(0,0,0,.1); z-index: 10;"
                                    onclick="event.preventDefault(); removeFavori(<?= $produit['id_produit'] ?>, this)"
                                    title="Retirer des favoris">
                                <i class="bi bi-heart-fill text-danger"></i>
                            </button>
                            
                            <a href="<?= url('produit.php?id=' . $produit['id_produit']) ?>" class="d-block w-100 h-100 text-decoration-none">
                                <img src="<?= $imageUrl ?>"
                                     alt="<?= htmlspecialchars($produit['nom']) ?>"
                                     onerror="this.src='<?= url('assets/img/produits/default.jpg') ?>'">
                            </a>
                            
                            <?php if ($imageCount > 1) : ?>
                                <span class="multi-images-badge">
                                    <i class="bi bi-images"></i> <?= $imageCount ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- INFOS -->
                        <div class="card-body d-flex flex-column px-3 py-3">
                            <small class="text-muted mb-1" style="font-size:.75rem;">
                                <?= htmlspecialchars($produit['categorie_nom'] ?? '') ?>
                            </small>
                            <h5 class="card-title h6 mb-1 fw-semibold">
                                <a href="<?= url('produit.php?id=' . $produit['id_produit']) ?>"
                                   class="text-dark text-decoration-none">
                                    <?= htmlspecialchars($produit['nom']) ?>
                                </a>
                            </h5>
                            <div class="mt-auto">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="text-success fw-bold">
                                        <?= formatFCFA((int)($produit['prix_promo'] ?? $produit['prix'])) ?>
                                    </span>
                                    <?php if (!empty($produit['prix_promo']) && $produit['prix_promo'] < $produit['prix']) : ?>
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
                                    <input type="hidden" name="retour"     value="mes_favoris.php">
                                    <button type="submit" class="btn-cart">
                                        <i class="bi bi-cart-plus me-1"></i>Ajouter au panier
                                    </button>
                                </form>
                                <?php else : ?>
                                <button class="btn-cart" disabled style="opacity:.5;">Indisponible</button>
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

<script>
function removeFavori(idProduit, btn) {
    fetch('<?= url('wishlist_toggle.php') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'id_produit=' + idProduit + '&csrf_token=<?= $_SESSION['csrf_token'] ?? '' ?>'
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success') {
            // Supprimer la carte du DOM avec animation
            const card = btn.closest('.col-6');
            card.style.transition = 'opacity 0.3s';
            card.style.opacity = '0';
            setTimeout(() => {
                card.remove();
                
                // Mettre à jour le compteur
                const remainingCards = document.querySelectorAll('.col-6[data-product-id]').length;
                const counter = document.querySelector('.text-muted.small');
                if (counter) {
                    const text = remainingCards === 0 ? '0 produit' : remainingCards + ' produit' + (remainingCards > 1 ? 's' : '');
                    counter.textContent = text;
                }
                
                // Afficher le message si plus de favoris
                if (remainingCards === 0) {
                    location.reload();
                }
            }, 300);
        }
    });
}
</script>

<?php include __DIR__ . '/../views/layouts/footer.php'; ?>