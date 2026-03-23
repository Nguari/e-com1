<?php

$pageTitle   = 'Commander - NGAARY SHOP';
$currentPage = 'checkout.php';

ob_start();

$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);

// Token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!-- BREADCRUMB -->
<section class="py-4 bg-white border-bottom">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item">
                    <a href="<?= url('index.php') ?>" class="text-success text-decoration-none">Accueil</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="<?= url('cart.php') ?>" class="text-success text-decoration-none">Panier</a>
                </li>
                <li class="breadcrumb-item active">Commander</li>
            </ol>
        </nav>
    </div>
</section>

<section class="py-5" style="background: var(--ngaary-bg, #f0faf3);">
    <div class="container">

        <h1 class="fw-bold mb-1" style="font-family: 'Playfair Display', serif; color: #0d2818;">
            Finaliser la commande
        </h1>
        <p class="text-muted small mb-4">Vérifiez votre commande et renseignez vos informations de livraison.</p>

        <?php if ($flashError) : ?>
            <div class="alert alert-danger d-flex align-items-center gap-2 rounded-3 py-2 mb-4">
                <i class="bi bi-exclamation-circle-fill"></i>
                <span><?= htmlspecialchars($flashError) ?></span>
            </div>
        <?php endif; ?>

        <form action="<?= url('checkout.php') ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <div class="row g-4 align-items-start">

                <!-- ====== COLONNE GAUCHE : FORMULAIRE ====== -->
                <div class="col-lg-7">

                    <!-- ADRESSE DE LIVRAISON -->
                    <div class="card border-0 rounded-4 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-4">
                                <i class="bi bi-geo-alt-fill text-success me-2"></i>
                                Adresse de livraison
                            </h5>

                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-medium small">
                                        Nom complet <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="nom_complet"
                                           class="form-control bg-light"
                                           placeholder="Fatou Diallo"
                                           >
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-medium small">
                                        Adresse / Rue <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="rue"
                                           class="form-control bg-light"
                                           placeholder="Rue de Thiong, Plateau"
                                           >
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium small">
                                        Ville <span class="text-danger">*</span>
                                    </label>
                                    <select name="ville" class="form-select bg-light" >
                                        <option value="" disabled selected>Choisir une ville</option>
                                        <option value="Dakar">Dakar</option>
                                        <option value="Pikine">Pikine</option>
                                        <option value="Guédiawaye">Guédiawaye</option>
                                        <option value="Thiès">Thiès</option>
                                        <option value="Saint-Louis">Saint-Louis</option>
                                        <option value="Ziguinchor">Ziguinchor</option>
                                        <option value="Kaolack">Kaolack</option>
                                        <option value="Autre">Autre</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium small">
                                        Téléphone <span class="text-danger">*</span>
                                    </label>
                                    <input type="tel" name="telephone"
                                           class="form-control bg-light"
                                           placeholder="+221 77 000 00 00"
                                           >
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-medium small">Notes (optionnel)</label>
                                    <textarea name="notes" class="form-control bg-light" rows="2"
                                              placeholder="Instructions particulières pour la livraison..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MODE DE PAIEMENT -->
                    <div class="card border-0 rounded-4 shadow-sm">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-4">
                                <i class="bi bi-credit-card-fill text-success me-2"></i>
                                Mode de paiement
                            </h5>

                            <div class="row g-3">

                                <div class="col-md-4">
                                    <input type="radio" class="btn-check" name="mode_paiement"
                                           id="wave" value="Wave" >
                                    <label class="btn btn-outline-secondary w-100 rounded-3 py-3" for="wave">
                                        <i class="bi bi-phone fs-4 d-block mb-1 text-primary"></i>
                                        <span class="fw-semibold small">Wave</span>
                                    </label>
                                </div>

                                <div class="col-md-4">
                                    <input type="radio" class="btn-check" name="mode_paiement"
                                           id="orange" value="Orange Money" >
                                    <label class="btn btn-outline-secondary w-100 rounded-3 py-3" for="orange">
                                        <i class="bi bi-phone fs-4 d-block mb-1 text-warning"></i>
                                        <span class="fw-semibold small">Orange Money</span>
                                    </label>
                                </div>

                                <div class="col-md-4">
                                    <input type="radio" class="btn-check" name="mode_paiement"
                                           id="especes" value="especes" >
                                    <label class="btn btn-outline-secondary w-100 rounded-3 py-3" for="especes">
                                        <i class="bi bi-cash fs-4 d-block mb-1 text-success"></i>
                                        <span class="fw-semibold small">Espèces</span>
                                    </label>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>

                <!-- ====== COLONNE DROITE : RÉCAPITULATIF ====== -->
                <div class="col-lg-5">
                    <div class="card border-0 rounded-4 shadow-sm sticky-top" style="top: 80px;">
                        <div class="card-body p-4">

                            <h5 class="fw-bold mb-4" style="font-family: 'Playfair Display', serif;">
                                Récapitulatif
                            </h5>

                            <!-- LISTE DES ARTICLES -->
                            <div class="mb-3">
                                <?php foreach ($cart->getItems() as $item) : ?>
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-light rounded-2 d-flex align-items-center justify-content-center"
                                             style="width: 45px; height: 45px; flex-shrink: 0;">
                                            <i class="bi bi-image text-muted"></i>
                                        </div>
                                        <div>
                                            <div class="small fw-semibold"><?= htmlspecialchars($item->getNomProduit()) ?></div>
                                            <div class="text-muted" style="font-size: 0.75rem;">
                                                Qté : <?= $item->getQuantite() ?>
                                            </div>
                                        </div>
                                    </div>
                                    <span class="fw-semibold small text-success">
                                        <?= formatFCFA((int)$item->getSousTotal()) ?>
                                    </span>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- TOTAUX -->
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

                            <!-- BOUTON COMMANDER -->
                            <button type="submit" class="btn btn-success w-100 py-3 fw-bold rounded-3 fs-6">
                                <i class="bi bi-lock-fill me-2"></i>Confirmer la commande
                            </button>

                            <a href="<?= url('cart.php') ?>" class="btn btn-outline-secondary w-100 mt-2 rounded-3 small">
                                <i class="bi bi-arrow-left me-1"></i>Retour au panier
                            </a>

                        </div>
                    </div>
                </div>

            </div>
        </form>

    </div>
</section>

<?php
$content = ob_get_clean();
require_once dirname(__DIR__, 2) . '/views/layouts/main_layout.php';
?>