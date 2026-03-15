<?php

$pageTitle   = 'Inscription - NGAARY SHOP';
$currentPage = 'register.php';

// Token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Récupérer les anciens champs en cas d'erreur
$old = [
    'nom'    => htmlspecialchars($_SESSION['old']['nom']    ?? ''),
    'prenom' => htmlspecialchars($_SESSION['old']['prenom'] ?? ''),
    'email'  => htmlspecialchars($_SESSION['old']['email']  ?? ''),
    'tel'    => htmlspecialchars($_SESSION['old']['tel']    ?? ''),
];
unset($_SESSION['old']);

ob_start();
?>

<!-- MESSAGES FLASH -->
<?php if (\App\Utils\Session::hasFlash('error')) : ?>
    <div class="alert alert-danger d-flex align-items-center gap-2 rounded-3 mx-auto mt-4" style="max-width: 700px;">
        <i class="bi bi-exclamation-circle-fill"></i>
        <span><?= \App\Utils\Session::getFlash('error') ?></span>
    </div>
<?php endif; ?>

<?php if (\App\Utils\Session::hasFlash('success')) : ?>
    <div class="alert alert-success d-flex align-items-center gap-2 rounded-3 mx-auto mt-4" style="max-width: 700px;">
        <i class="bi bi-check-circle-fill"></i>
        <span><?= \App\Utils\Session::getFlash('success') ?></span>
    </div>
<?php endif; ?>

<!-- FORMULAIRE INSCRIPTION -->
<style>
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(30px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadeInDown {
        from { opacity: 0; transform: translateY(-20px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes float {
        0%, 100% { transform: translateY(0) rotate(0deg); }
        50%       { transform: translateY(-8px) rotate(5deg); }
    }
    @keyframes pulse-green {
        0%, 100% { box-shadow: 0 0 0 0 rgba(22,163,74,0.4); }
        50%       { box-shadow: 0 0 0 10px rgba(22,163,74,0); }
    }

    .register-card {
        animation: fadeInUp 0.7s ease both;
    }
    .register-icon {
        animation: float 3s ease-in-out infinite;
    }
    .register-title {
        animation: fadeInDown 0.6s ease 0.2s both;
        opacity: 0;
    }

    .register-field {
        opacity: 0;
        transform: translateY(15px);
        animation: fadeInUp 0.4s ease forwards;
    }
    .register-field:nth-child(1) { animation-delay: 0.3s; }
    .register-field:nth-child(2) { animation-delay: 0.4s; }
    .register-field:nth-child(3) { animation-delay: 0.5s; }
    .register-field:nth-child(4) { animation-delay: 0.6s; }
    .register-field:nth-child(5) { animation-delay: 0.7s; }
    .register-field:nth-child(6) { animation-delay: 0.8s; }
    .register-field:nth-child(7) { animation-delay: 0.9s; }

    .btn-register-submit {
        animation: pulse-green 2.5s infinite;
        transition: transform 0.2s;
    }
    .btn-register-submit:hover {
        animation: none;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(22,163,74,0.4) !important;
    }

    .form-control:focus {
        transform: translateY(-1px);
        transition: all 0.2s;
    }
</style>

<section class="min-vh-100 d-flex align-items-center py-5"
         style="background: linear-gradient(160deg, #f0faf3 60%, #d1fae5 100%);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-7 col-lg-8 col-md-10">

                <div class="card border-0 rounded-4 shadow-lg register-card">
                    <div class="card-body p-5">

                        <!-- EN-TÊTE -->
                        <div class="text-center mb-4 register-title">
                            <div class="register-icon d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10 rounded-3 mb-3"
                                 style="width: 56px; height: 56px; font-size: 1.6rem;">
                                <i class="bi bi-person-plus-fill text-success"></i>
                            </div>
                            <h1 class="fw-bold mb-1" style="font-family: 'Playfair Display', serif; font-size: 1.8rem; color: #0d2818;">
                                Créer un compte
                            </h1>
                            <p class="text-muted small mb-0">
                                Déjà inscrit ?
                                <a href="<?= url('login.php') ?>" class="text-success fw-semibold text-decoration-none">
                                    Se connecter
                                </a>
                            </p>
                        </div>

                        <!-- FORMULAIRE -->
                        <form action="<?= url('register.php') ?>" method="POST" novalidate>
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                            <!-- NOM + PRÉNOM -->
                            <div class="row g-3 mb-3 register-field">
                                <div class="col-md-6">
                                    <label class="form-label fw-medium small">Nom <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="bi bi-person text-secondary"></i>
                                        </span>
                                        <input
                                            type="text"
                                            name="nom"
                                            class="form-control border-start-0 bg-light"
                                            placeholder="NDIAYE"
                                            value="<?= $old['nom'] ?>"
                                            required
                                        >
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium small">Prénom <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="bi bi-person text-secondary"></i>
                                        </span>
                                        <input
                                            type="text"
                                            name="prenom"
                                            class="form-control border-start-0 bg-light"
                                            placeholder="Alioune"
                                            value="<?= $old['prenom'] ?>"
                                            required
                                        >
                                    </div>
                                </div>
                            </div>

                            <!-- EMAIL -->
                            <div class="mb-3 register-field">
                                <label class="form-label fw-medium small">Adresse email <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-envelope text-secondary"></i>
                                    </span>
                                    <input
                                        type="email"
                                        name="email"
                                        class="form-control border-start-0 bg-light"
                                        placeholder="alioune@gmail.com"
                                        value="<?= $old['email'] ?>"
                                        required
                                        autocomplete="email"
                                    >
                                </div>
                            </div>

                            <!-- TÉLÉPHONE -->
                            <div class="mb-3 register-field">
                                <label class="form-label fw-medium small">Numéro de téléphone</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-telephone text-secondary"></i>
                                    </span>
                                    <input
                                        type="tel"
                                        name="tel"
                                        class="form-control border-start-0 bg-light"
                                        placeholder="(+221) 77 777 77 77"
                                        value="<?= $old['tel'] ?>"
                                    >
                                </div>
                            </div>

                            <!-- MOT DE PASSE -->
                            <div class="mb-3 register-field">
                                <label class="form-label fw-medium small">Mot de passe <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-lock text-secondary"></i>
                                    </span>
                                    <input
                                        type="password"
                                        name="password"
                                        id="password"
                                        class="form-control border-start-0 border-end-0 bg-light"
                                        placeholder="Min. <?= PASSWORD_MIN_LENGTH ?> caractères"
                                        required
                                        autocomplete="new-password"
                                    >
                                    <button type="button"
                                            class="input-group-text bg-light border-start-0"
                                            onclick="togglePassword('password', 'eyeIcon1')">
                                        <i class="bi bi-eye text-secondary" id="eyeIcon1"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- CONFIRMATION MOT DE PASSE -->
                            <div class="mb-4 register-field">
                                <label class="form-label fw-medium small">Confirmer le mot de passe <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-lock-fill text-secondary"></i>
                                    </span>
                                    <input
                                        type="password"
                                        name="password2"
                                        id="password2"
                                        class="form-control border-start-0 border-end-0 bg-light"
                                        placeholder="Répétez le mot de passe"
                                        required
                                        autocomplete="new-password"
                                    >
                                    <button type="button"
                                            class="input-group-text bg-light border-start-0"
                                            onclick="togglePassword('password2', 'eyeIcon2')">
                                        <i class="bi bi-eye text-secondary" id="eyeIcon2"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- BOUTON -->
                            <div class="d-grid mb-3 register-field">
                                <button type="submit" class="btn btn-success py-2 fw-semibold rounded-3 btn-register-submit">
                                    <i class="bi bi-person-check me-2"></i>Créer mon compte
                                </button>
                            </div>

                            <!-- LIEN CONNEXION -->
                            <p class="text-center text-muted small mb-0 register-field">
                                Déjà un compte ?
                                <a href="<?= url('login.php') ?>" class="text-success fw-semibold text-decoration-none">
                                    Se connecter
                                </a>
                            </p>

                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<script>
    function togglePassword(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon  = document.getElementById(iconId);
        input.type     = input.type === 'password' ? 'text' : 'password';
        icon.className = input.type === 'password'
            ? 'bi bi-eye text-secondary'
            : 'bi bi-eye-slash text-success';
    }
</script>

<?php
$content = ob_get_clean();
require_once dirname(__DIR__, 2) . '/views/layouts/main_layout.php';
?>