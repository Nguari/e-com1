<?php
require_once dirname(__DIR__, 2) . '/config/config.php';

use App\Utils\Auth;
use App\Utils\Session;
use App\Repositories\CartRepository;
use App\Config\Database;

// Vérifier si l'utilisateur est connecté
if (!Auth::check()) {
    Session::flash('error', 'Veuillez vous connecter pour accéder à votre panier.');
    header('Location: ' . url('login.php'));
    exit();
}

$db = Database::getInstance()->getConnection();
$cartRepo = new CartRepository($db);
$cart = $cartRepo->getCartByUser(Auth::id());

$pageTitle   = 'Mon Panier - NGAARY SHOP';
$currentPage = 'cart.php';

ob_start();

// Flash messages
$flashSuccess = Session::getFlash('success');
$flashError = Session::getFlash('error');
?>

<!-- BREADCRUMB -->
<section class="py-4 bg-white border-bottom">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item">
                    <a href="<?= url('index.php') ?>" class="text-success text-decoration-none">Accueil</a>
                </li>
                <li class="breadcrumb-item active">Mon Panier</li>
            </ol>
        </nav>
    </div>
</section>

<section class="py-5" style="background: var(--ngaary-bg, #f0faf3);">
    <div class="container">

        <h1 class="fw-bold mb-1" style="font-family: 'Playfair Display', serif; color: #0d2818;">
            Mon Panier
        </h1>
        <p class="text-muted small mb-4">
            <?= $cart->getNombreArticles() ?> article<?= $cart->getNombreArticles() > 1 ? 's' : '' ?>
        </p>

        <!-- FLASH MESSAGES -->
        <?php if ($flashSuccess) : ?>
            <div class="alert alert-success d-flex align-items-center gap-2 rounded-3 py-2 mb-4">
                <i class="bi bi-check-circle-fill"></i>
                <span><?= htmlspecialchars($flashSuccess) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($flashError) : ?>
            <div class="alert alert-danger d-flex align-items-center gap-2 rounded-3 py-2 mb-4">
                <i class="bi bi-exclamation-circle-fill"></i>
                <span><?= htmlspecialchars($flashError) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($cart->isEmpty()) : ?>

            <!-- PANIER VIDE -->
            <div class="text-center py-5 bg-white rounded-4 shadow-sm">
                <i class="bi bi-cart-x" style="font-size: 5rem; color: #d1d5db;"></i>
                <h4 class="fw-bold mt-4 mb-2">Votre panier est vide</h4>
                <p class="text-muted mb-4">Découvrez nos produits et commencez vos achats !</p>
                <a href="<?= url('boutique.php') ?>" class="btn btn-success rounded-3 px-4 py-2 fw-semibold">
                    <i class="bi bi-bag me-2"></i>Voir la boutique
                </a>
            </div>

        <?php else : ?>

            <div class="row g-4 align-items-start">

                <!-- ====== LISTE DES ARTICLES ====== -->
                <div class="col-lg-8">
                    <div class="card border-0 rounded-4 shadow-sm overflow-hidden">

                        <!-- EN-TÊTE -->
                        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">Articles</span>
                            <form action="<?= url('cart_clear.php') ?>" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <button type="submit"
                                        class="btn btn-link text-danger text-decoration-none small p-0"
                                        onclick="return confirm('Vider tout le panier ?')">
                                    <i class="bi bi-trash me-1"></i>Vider le panier
                                </button>
                            </form>
                        </div>

                        <!-- ARTICLES -->
                        <div class="card-body p-0">
                            <?php foreach ($cart->getItems() as $item) : ?>
                            <div class="d-flex align-items-center gap-3 p-4 border-bottom">

                                <!-- IMAGE -->
                                <div class="flex-shrink-0 bg-light rounded-3 d-flex align-items-center justify-content-center overflow-hidden"
                                     style="width: 90px; height: 90px;">
                                    <?php 
                                    $imagePath = $item->getImageProduit();
                                    if (!empty($imagePath)) : ?>
                                        <img src="<?= url('assets/img/produits/' . $imagePath) ?>"
                                             alt="<?= htmlspecialchars($item->getNomProduit()) ?>"
                                             class="img-fluid object-fit-cover w-100 h-100"
                                             onerror="this.src='<?= url('assets/img/produits/default.jpg') ?>'">
                                    <?php else : ?>
                                        <i class="bi bi-image text-muted" style="font-size: 2rem;"></i>
                                    <?php endif; ?>
                                </div>

                                <!-- INFOS PRODUIT -->
                                <div class="flex-grow-1">
                                    <h6 class="fw-semibold mb-1">
                                        <?= htmlspecialchars($item->getNomProduit()) ?>
                                    </h6>
                                    <span class="text-success fw-bold small">
                                        <?= formatFCFA((int)$item->getPrixUnitaire()) ?> / unité
                                    </span>
                                </div>

                                <!-- QUANTITÉ -->
                                <form action="<?= url('cart_update.php') ?>" method="POST"
                                      class="d-flex align-items-center gap-2">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <input type="hidden" name="id_produit" value="<?= $item->getIdProduit() ?>">

                                    <div class="input-group" style="width: 120px;">
                                        <button type="button"
                                                class="btn btn-outline-secondary btn-sm px-2"
                                                onclick="changeQty(this, -1)">
                                            <i class="bi bi-dash"></i>
                                        </button>
                                        <input type="number"
                                               name="quantite"
                                               value="<?= $item->getQuantite() ?>"
                                               min="1" max="99"
                                               class="form-control form-control-sm text-center border-secondary qty-input"
                                               onchange="this.form.submit()">
                                        <button type="button"
                                                class="btn btn-outline-secondary btn-sm px-2"
                                                onclick="changeQty(this, 1)">
                                            <i class="bi bi-plus"></i>
                                        </button>
                                    </div>
                                </form>

                                <!-- SOUS-TOTAL -->
                                <div class="text-end" style="min-width: 110px;">
                                    <span class="fw-bold text-dark">
                                        <?= formatFCFA((int)$item->getSousTotal()) ?>
                                    </span>
                                </div>

                                <!-- SUPPRIMER -->
                                <form action="<?= url('cart_remove.php') ?>" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <input type="hidden" name="id_produit" value="<?= $item->getIdProduit() ?>">
                                    <button type="submit" class="btn btn-link text-danger p-0" title="Supprimer">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </form>

                            </div>
                            <?php endforeach; ?>
                        </div>

                    </div>

                    <a href="<?= url('boutique.php') ?>" class="btn btn-outline-success rounded-3 mt-3">
                        <i class="bi bi-arrow-left me-2"></i>Continuer les achats
                    </a>
                </div>

                <!-- ====== RÉCAPITULATIF ====== -->
                <div class="col-lg-4">
                    <div class="card border-0 rounded-4 shadow-sm">
                        <div class="card-body p-4">

                            <h5 class="fw-bold mb-4" style="font-family: 'Playfair Display', serif;">
                                Récapitulatif
                            </h5>

                            <div class="d-flex justify-content-between mb-2 small">
                                <span class="text-muted">
                                    Sous-total (<?= $cart->getNombreArticles() ?> article<?= $cart->getNombreArticles() > 1 ? 's' : '' ?>)
                                </span>
                                <span class="fw-semibold"><?= formatFCFA((int)$cart->getTotal()) ?></span>
                            </div>

                            <div class="d-flex justify-content-between mb-3 small">
                                <span class="text-muted">Livraison</span>
                                <?php if ($cart->livraisonGratuite()) : ?>
                                    <span class="text-success fw-semibold">Gratuite 🎉</span>
                                <?php else : ?>
                                    <span class="fw-semibold"><?= formatFCFA(2500) ?></span>
                                <?php endif; ?>
                            </div>

                            <?php if (!$cart->livraisonGratuite()) :
                                $reste   = 15000 - $cart->getTotal();
                                $percent = min(100, ($cart->getTotal() / 15000) * 100);
                            ?>
                            <div class="mb-3">
                                <div class="progress rounded-pill mb-1" style="height: 6px;">
                                    <div class="progress-bar bg-success" style="width: <?= $percent ?>%"></div>
                                </div>
                                <small class="text-muted">
                                    Plus que <strong class="text-success"><?= formatFCFA((int)$reste) ?></strong>
                                    pour la livraison gratuite
                                </small>
                            </div>
                            <?php endif; ?>

                            <hr>

                            <div class="d-flex justify-content-between mb-4">
                                <span class="fw-bold fs-6">Total</span>
                                <span class="fw-bold fs-5 text-success">
                                    <?php
                                    $fraisLivraison = $cart->livraisonGratuite() ? 0 : 2500;
                                    echo formatFCFA((int)($cart->getTotal() + $fraisLivraison));
                                    ?>
                                </span>
                            </div>

                            <a href="<?= url('checkout.php') ?>"
                               class="btn btn-success w-100 py-3 fw-bold rounded-3 fs-6">
                                <i class="bi bi-lock-fill me-2"></i>Commander
                            </a>

                            <div class="text-center mt-3">
                                <small class="text-muted d-block mb-2">Paiements acceptés</small>
                                <div class="d-flex justify-content-center gap-2 flex-wrap">
                                    <span class="badge bg-light text-dark border small py-2 px-3">
                                        <i class="bi bi-phone me-1"></i>Wave
                                    </span>
                                    <span class="badge bg-light text-dark border small py-2 px-3">
                                        <i class="bi bi-phone me-1"></i>Orange Money
                                    </span>
                                    <span class="badge bg-light text-dark border small py-2 px-3">
                                        <i class="bi bi-cash me-1"></i>Espèces
                                    </span>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>

        <?php endif; ?>
    </div>
</section>

<script>
    function changeQty(btn, delta) {
        const input  = btn.closest('.input-group').querySelector('.qty-input');
        const newVal = Math.max(1, Math.min(99, parseInt(input.value) + delta));
        input.value  = newVal;
        input.form.submit();
    }
</script>

<?php
$content = ob_get_clean();
require_once dirname(__DIR__, 2) . '/views/layouts/main_layout.php';
?>