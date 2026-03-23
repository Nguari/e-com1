<?php
// public/search.php
require_once dirname(__DIR__) . '/config/config.php';

use App\Config\Database;

$q        = trim($_GET['q'] ?? '');
$produits = [];

if (!empty($q)) {
    try {
        $db   = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT p.*, c.nom AS categorie_nom
            FROM produits p
            LEFT JOIN categories c ON p.id_categorie = c.id_categorie
            WHERE p.actif = 1
            AND (p.nom LIKE :q OR p.description LIKE :q OR c.nom LIKE :q)
            ORDER BY p.nom ASC
        ");
        $stmt->execute([':q' => '%' . $q . '%']);
        $produits = $stmt->fetchAll();
    } catch (\Exception $e) {
        error_log($e->getMessage());
    }
}

$pageTitle   = 'Recherche : ' . htmlspecialchars($q) . ' - NGAARY SHOP';
$currentPage = '';
include __DIR__ . '/../views/layouts/header.php';
?>

<style>
    .product-card { border: none; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.07); transition: transform 0.3s, box-shadow 0.3s; }
    .product-card:hover { transform: translateY(-6px); box-shadow: 0 16px 40px rgba(0,0,0,0.12); }
    .product-img { background-color: #e8f5ee; padding: 30px; position: relative; min-height: 180px; display: flex; align-items: center; justify-content: center; }
    .btn-cart { background-color: #16a34a; color: white; border: none; border-radius: 8px; padding: 8px 16px; font-size: 0.85rem; font-weight: 600; width: 100%; cursor: pointer; transition: background-color 0.2s; }
    .btn-cart:hover { background-color: #15803d; color: white; }
</style>

<!-- BREADCRUMB -->
<section class="py-4 bg-white border-bottom">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item">
                    <a href="<?= url('index.php') ?>" class="text-success text-decoration-none">Accueil</a>
                </li>
                <li class="breadcrumb-item active">Recherche</li>
            </ol>
        </nav>
    </div>
</section>

<section class="py-5" style="background: #f0faf3;">
    <div class="container">

        <!-- BARRE DE RECHERCHE -->
        <form action="<?= url('search.php') ?>" method="GET" class="mb-5">
            <div class="input-group" style="max-width: 600px;">
                <input type="text" name="q"
                       class="form-control form-control-lg bg-white border-0 shadow-sm ps-4"
                       placeholder="Rechercher un produit..."
                       value="<?= htmlspecialchars($q) ?>">
                <button class="btn btn-success px-4" type="submit">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </form>

        <?php if (empty($q)) : ?>
            <p class="text-muted">Entrez un terme de recherche pour trouver des produits.</p>

        <?php elseif (empty($produits)) : ?>
            <div class="text-center py-5 bg-white rounded-4 shadow-sm">
                <i class="bi bi-search" style="font-size: 4rem; color: #d1d5db;"></i>
                <h5 class="fw-bold mt-4 mb-2">Aucun résultat pour "<?= htmlspecialchars($q) ?>"</h5>
                <p class="text-muted mb-4">Essayez avec d'autres mots-clés.</p>
                <a href="<?= url('boutique.php') ?>" class="btn btn-success rounded-3 px-4">
                    Voir tous les produits
                </a>
            </div>

        <?php else : ?>
            <p class="text-muted mb-4">
                <strong><?= count($produits) ?></strong> résultat<?= count($produits) > 1 ? 's' : '' ?>
                pour "<strong><?= htmlspecialchars($q) ?></strong>"
            </p>

            <div class="row g-4">
                <?php foreach ($produits as $produit) : ?>
                <div class="col-6 col-lg-3">
                    <div class="card product-card h-100">
                        <div class="product-img">
                            <i class="bi bi-image" style="font-size: 4rem; color: #a7c9b3;"></i>
                        </div>
                        <div class="card-body d-flex flex-column px-3 py-3">
                            <small class="text-muted mb-1" style="font-size: 0.75rem;">
                                <?= htmlspecialchars($produit['categorie_nom'] ?? '') ?>
                            </small>
                            <h5 class="card-title h6 mb-1 fw-semibold">
                                <?= htmlspecialchars($produit['nom']) ?>
                            </h5>
                            <div class="mt-auto">
                                <p class="text-success fw-bold mb-2">
                                    <?= formatFCFA((int)$produit['prix']) ?>
                                </p>
                                <form action="<?= url('cart_add.php') ?>" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                    <input type="hidden" name="id_produit" value="<?= $produit['id_produit'] ?>">
                                    <input type="hidden" name="quantite"   value="1">
                                    <input type="hidden" name="retour"     value="search.php?q=<?= urlencode($q) ?>">
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
        <?php endif; ?>

    </div>
</section>

<?php include __DIR__ . '/../views/layouts/footer.php'; ?>