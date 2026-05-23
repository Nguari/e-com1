<?php
/**
 * Page de finalisation de commande
 * 
 * @var \App\Models\Cart $cart Panier de l'utilisateur
 * @var bool $enableWave Wave activé
 * @var bool $enableOm Orange Money activé
 * @var bool $enableCash Espèces activé
 * @var string|null $flashError Message d'erreur flash
 */

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

        <form action="<?= url('checkout.php') ?>" method="POST" id="checkoutForm">
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
                                           required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-medium small">
                                        Adresse / Rue <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="rue"
                                           class="form-control bg-light"
                                           placeholder="Rue de Thiong, Plateau"
                                           required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium small">
                                        Ville <span class="text-danger">*</span>
                                    </label>
                                    <select name="ville" class="form-select bg-light" required>
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
                                    <input type="tel" name="telephone" id="telephone"
                                           class="form-control bg-light"
                                           placeholder="77 123 45 67"
                                           required>
                                    <small class="text-muted">9 chiffres (ex: 771234567)</small>
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
                                <?php if ($enableWave): ?>
                                <div class="col-md-4">
                                    <input type="radio" class="btn-check" name="mode_paiement"
                                           id="wave" value="wave" required>
                                    <label class="btn btn-outline-secondary w-100 rounded-3 py-3" for="wave">
                                        <i class="bi bi-phone fs-4 d-block mb-1 text-primary"></i>
                                        <span class="fw-semibold small">Wave</span>
                                    </label>
                                </div>
                                <?php endif; ?>

                                <?php if ($enableOm): ?>
                                <div class="col-md-4">
                                    <input type="radio" class="btn-check" name="mode_paiement"
                                           id="orange" value="orange_money" required>
                                    <label class="btn btn-outline-secondary w-100 rounded-3 py-3" for="orange">
                                        <i class="bi bi-phone fs-4 d-block mb-1 text-warning"></i>
                                        <span class="fw-semibold small">Orange Money</span>
                                    </label>
                                </div>
                                <?php endif; ?>

                                <?php if ($enableCash): ?>
                                <div class="col-md-4">
                                    <input type="radio" class="btn-check" name="mode_paiement"
                                           id="especes" value="especes" required>
                                    <label class="btn btn-outline-secondary w-100 rounded-3 py-3" for="especes">
                                        <i class="bi bi-cash fs-4 d-block mb-1 text-success"></i>
                                        <span class="fw-semibold small">Espèces</span>
                                    </label>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Message d'aide pour les modes de paiement mobile -->
                            <div class="alert alert-info mt-3 small" id="paymentHelp" style="display: none;">
                                <i class="bi bi-info-circle-fill me-1"></i>
                                Le paiement sera effectué via votre téléphone. Utilisez le numéro renseigné ci-dessus.
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

<script>
(function() {
    const checkoutForm = document.getElementById('checkoutForm');
    const phoneInput = document.getElementById('telephone');
    const paymentHelp = document.getElementById('paymentHelp');
    const radioButtons = document.querySelectorAll('input[name="mode_paiement"]');
    
    if (!checkoutForm || !phoneInput) return;
    
    function isValidPhone(phone) {
        const digits = phone.replace(/\D/g, '');
        if (digits.length !== 9) return false;
        const prefix = digits.substring(0, 2);
        const validPrefixes = ['70', '71', '75', '76', '77', '78'];
        return validPrefixes.includes(prefix);
    }
    
    function updatePhoneStyle() {
        const phone = phoneInput.value;
        if (isValidPhone(phone)) {
            phoneInput.classList.remove('is-invalid');
            phoneInput.classList.add('is-valid');
        } else {
            phoneInput.classList.remove('is-valid');
            if (phone.length > 0) phoneInput.classList.add('is-invalid');
            else phoneInput.classList.remove('is-invalid');
        }
    }
    
    function togglePaymentHelp() {
        const selected = document.querySelector('input[name="mode_paiement"]:checked');
        if (selected && (selected.value === 'wave' || selected.value === 'orange_money')) {
            paymentHelp.style.display = 'block';
            updatePhoneStyle();
        } else {
            paymentHelp.style.display = 'none';
            phoneInput.classList.remove('is-valid', 'is-invalid');
        }
    }
    
    radioButtons.forEach(radio => radio.addEventListener('change', togglePaymentHelp));
    
    phoneInput.addEventListener('input', function(e) {
        let raw = this.value.replace(/\D/g, '');
        if (raw.length > 9) raw = raw.slice(0, 9);
        this.value = raw;
        updatePhoneStyle();
    });
    
    checkoutForm.addEventListener('submit', function(e) {
        const selectedMode = document.querySelector('input[name="mode_paiement"]:checked');
        if (!selectedMode) {
            e.preventDefault();
            alert('Veuillez sélectionner un mode de paiement.');
            return;
        }
        if (selectedMode.value === 'wave' || selectedMode.value === 'orange_money') {
            const phone = phoneInput.value;
            if (!isValidPhone(phone)) {
                e.preventDefault();
                alert('Veuillez entrer un numéro valide à 9 chiffres (70,71,75,76,77,78).');
                phoneInput.focus();
                return;
            }
        }
    });
    
    togglePaymentHelp();
})();
</script>

<style>
.is-valid {
    border-color: #16a34a !important;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%2316a34a' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
    background-size: 1rem;
    padding-right: 2rem;
}
.is-invalid {
    border-color: #dc2626 !important;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc2626'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc2626' stroke='none'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
    background-size: 1rem;
    padding-right: 2rem;
}
</style>

<?php
$content = ob_get_clean();
require_once dirname(__DIR__, 2) . '/views/layouts/main_layout.php';
?>