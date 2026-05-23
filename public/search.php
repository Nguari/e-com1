<?php
// public/search.php
require_once dirname(__DIR__) . '/config/config.php';

use App\Config\Database;

$q        = trim($_GET['q'] ?? '');
$produits = [];

if (!empty($q)) {
    try {
        $db = Database::getInstance()->getConnection();
        
        $searchTerm = '%' . mb_strtolower($q, 'UTF-8') . '%';

        $stmt = $db->prepare("
            SELECT p.*, c.nom AS categorie_nom
            FROM produits p
            LEFT JOIN categories c ON p.id_categorie = c.id_categorie
            WHERE (LOWER(p.nom) LIKE :q1 
               OR LOWER(p.description) LIKE :q2 
               OR LOWER(c.nom) LIKE :q3)
            AND p.actif = 1
            ORDER BY p.nom ASC
        ");
        
        $stmt->execute([
            ':q1' => $searchTerm,
            ':q2' => $searchTerm,
            ':q3' => $searchTerm
        ]);
        
        $produits = $stmt->fetchAll();

    } catch (\Exception $e) {
        die("Erreur SQL : " . $e->getMessage());
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
    
    /* ====== STYLES POUR L'AUTOCOMPLÉTION ====== */
    .search-container {
        position: relative;
        max-width: 600px;
    }
    
    .autocomplete-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        z-index: 9999;
        max-height: 400px;
        overflow-y: auto;
        display: none;
        margin-top: 5px;
    }
    
    .autocomplete-results.show {
        display: block;
    }
    
    .autocomplete-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 15px;
        cursor: pointer;
        transition: background 0.2s;
        border-bottom: 1px solid #f1f5f9;
    }
    
    .autocomplete-item:hover {
        background-color: #f0faf3;
    }
    
    .autocomplete-item-img {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        flex-shrink: 0;
    }
    
    .autocomplete-item-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .autocomplete-item-info {
        flex: 1;
    }
    
    .autocomplete-item-name {
        font-size: 0.85rem;
        font-weight: 600;
        color: #0d2818;
    }
    
    .autocomplete-item-category {
        font-size: 0.7rem;
        color: #64748b;
    }
    
    .autocomplete-item-price {
        font-size: 0.85rem;
        font-weight: 600;
        color: #16a34a;
        flex-shrink: 0;
    }
    
    .autocomplete-loading {
        padding: 15px;
        text-align: center;
        color: #64748b;
    }
    
    .autocomplete-see-all {
        padding: 12px 15px;
        text-align: center;
        background: #f8fafc;
        cursor: pointer;
        color: #16a34a;
        font-weight: 600;
        font-size: 0.85rem;
    }
    
    .autocomplete-see-all:hover {
        background: #f0faf3;
    }
</style>

<section class="py-4 bg-white border-bottom">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="<?= url('index.php') ?>" class="text-success text-decoration-none">Accueil</a></li>
                <li class="breadcrumb-item active">Recherche</li>
            </ol>
        </nav>
    </div>
</section>

<section class="py-5" style="background: #f0faf3;">
    <div class="container">
        <!-- Formulaire de recherche avec autocomplétion -->
        <div class="search-container">
            <form action="<?= url('search.php') ?>" method="GET" id="searchForm" autocomplete="off">
                <div class="input-group" style="max-width: 600px;">
                    <input type="text" name="q" id="searchInput" class="form-control form-control-lg bg-white border-0 shadow-sm ps-4" 
                           placeholder="Rechercher un produit..." value="<?= htmlspecialchars($q) ?>"
                           autocomplete="off">
                    <button class="btn btn-success px-4" type="submit"><i class="bi bi-search"></i></button>
                </div>
            </form>
            
            <!-- Résultats autocomplétion -->
            <div id="autocompleteResults" class="autocomplete-results"></div>
        </div>

        <?php if (empty($q)) : ?>
            <p class="text-muted mt-4">Entrez un terme de recherche pour trouver des produits.</p>
        <?php elseif (empty($produits)) : ?>
            <div class="text-center py-5 bg-white rounded-4 shadow-sm mt-4">
                <i class="bi bi-search" style="font-size: 4rem; color: #d1d5db;"></i>
                <h5 class="fw-bold mt-4 mb-2">Aucun résultat pour "<?= htmlspecialchars($q) ?>"</h5>
                <p class="text-muted mb-4">Conseil : Vérifiez que vos produits sont marqués comme "actifs" (actif = 1) en base de données.</p>
                <a href="<?= url('boutique.php') ?>" class="btn btn-success rounded-3 px-4">Voir tous les produits</a>
            </div>
        <?php else : ?>
            <p class="text-muted mb-4 mt-4"><strong><?= count($produits) ?></strong> résultat<?= count($produits) > 1 ? 's' : '' ?> pour "<strong><?= htmlspecialchars($q) ?></strong>"</p>
            <div class="row g-4">
                <?php foreach ($produits as $produit) : ?>
                <div class="col-6 col-lg-3">
                    <div class="card product-card h-100">
                        <div class="product-img">
                            <?php if (!empty($produit['image'])) : ?>
                                <img src="<?= url('assets/img/produits/' . $produit['image']) ?>" 
                                     alt="<?= htmlspecialchars($produit['nom']) ?>"
                                     class="img-fluid"
                                     style="max-height: 120px; object-fit: contain;"
                                     onerror="this.src='<?= url('assets/img/produits/default.jpg') ?>'">
                            <?php else : ?>
                                <i class="bi bi-image" style="font-size: 4rem; color: #a7c9b3;"></i>
                            <?php endif; ?>
                        </div>
                        <div class="card-body d-flex flex-column px-3 py-3">
                            <small class="text-muted mb-1" style="font-size: 0.75rem;"><?= htmlspecialchars($produit['categorie_nom'] ?? 'Sans catégorie') ?></small>
                            <h5 class="card-title h6 mb-1 fw-semibold"><?= htmlspecialchars($produit['nom']) ?></h5>
                            <div class="mt-auto">
                                <p class="text-success fw-bold mb-2"><?= formatFCFA((int)$produit['prix']) ?></p>
                                <form action="<?= url('cart_add.php') ?>" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                    <input type="hidden" name="id_produit" value="<?= $produit['id_produit'] ?>">
                                    <input type="hidden" name="quantite" value="1">
                                    <button type="submit" class="btn-cart"><i class="bi bi-cart-plus me-1"></i>Ajouter</button>
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

<script>
// ====== AUTOCOMPLÉTION ======
const searchInput = document.getElementById('searchInput');
const autocompleteResults = document.getElementById('autocompleteResults');
let debounceTimer;

// Fonction pour rechercher des suggestions
async function fetchSuggestions(query) {
    if (query.length < 2) {
        autocompleteResults.classList.remove('show');
        return;
    }
    
    // Afficher le chargement
    autocompleteResults.innerHTML = '<div class="autocomplete-loading"><i class="bi bi-search"></i> Recherche en cours...</div>';
    autocompleteResults.classList.add('show');
    
    try {
        const response = await fetch(`<?= url('search_ajax.php') ?>?q=${encodeURIComponent(query)}`);
        const data = await response.json();
        
        if (data.suggestions && data.suggestions.length > 0) {
            let html = '';
            data.suggestions.forEach(item => {
                // Mettre en surbrillance le terme recherché
                const highlightedName = item.name.replace(new RegExp(`(${query})`, 'gi'), '<strong class="text-success">$1</strong>');
                
                html += `
                    <div class="autocomplete-item" onclick="selectSuggestion('${item.id}')">
                        <div class="autocomplete-item-img">
                            ${item.image ? `<img src="${item.image}" alt="${item.name}" onerror="this.src='<?= url('assets/img/produits/default.jpg') ?>'">` : '<i class="bi bi-image text-muted"></i>'}
                        </div>
                        <div class="autocomplete-item-info">
                            <div class="autocomplete-item-name">${highlightedName}</div>
                            <div class="autocomplete-item-category">${item.category}</div>
                        </div>
                        <div class="autocomplete-item-price">${item.price}</div>
                    </div>
                `;
            });
            
            if (data.total > data.suggestions.length) {
                html += `
                    <div class="autocomplete-see-all" onclick="seeAllResults('${query}')">
                        <i class="bi bi-search me-1"></i> Voir tous les ${data.total} résultats
                    </div>
                `;
            }
            
            autocompleteResults.innerHTML = html;
        } else {
            autocompleteResults.innerHTML = '<div class="autocomplete-loading text-muted">Aucun produit trouvé</div>';
        }
    } catch (error) {
        console.error('Erreur:', error);
        autocompleteResults.innerHTML = '<div class="autocomplete-loading text-danger">Erreur lors de la recherche</div>';
    }
}

// Fonction pour sélectionner une suggestion
function selectSuggestion(productId) {
    window.location.href = `<?= url('produit.php') ?>?id=${productId}`;
}

// Fonction pour voir tous les résultats
function seeAllResults(query) {
    window.location.href = `<?= url('search.php') ?>?q=${encodeURIComponent(query)}`;
}

// Écouter les changements dans le champ de recherche
searchInput.addEventListener('input', (e) => {
    clearTimeout(debounceTimer);
    const query = e.target.value.trim();
    
    if (query.length >= 2) {
        debounceTimer = setTimeout(() => fetchSuggestions(query), 300);
    } else {
        autocompleteResults.classList.remove('show');
    }
});

// Fermer l'autocomplétion en cliquant ailleurs
document.addEventListener('click', (e) => {
    if (!searchInput.contains(e.target) && !autocompleteResults.contains(e.target)) {
        autocompleteResults.classList.remove('show');
    }
});

// Soumettre le formulaire normalement
searchInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
        autocompleteResults.classList.remove('show');
        document.getElementById('searchForm').submit();
    }
});
</script>

<?php include __DIR__ . '/../views/layouts/footer.php'; ?>