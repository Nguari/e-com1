<?php
require_once dirname(__DIR__, 2) . '/config/config.php';

use App\Utils\Auth;
use App\Utils\Session;

// Si déjà connecté, rediriger vers l'accueil
if (Auth::check()) {
    header('Location: ' . url('index.php'));
    exit();
}

$pageTitle   = 'Connexion - NGAARY SHOP';
$currentPage = 'login.php';

// Token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le token CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        Session::flash('error', 'Erreur de sécurité. Veuillez réessayer.');
        header('Location: ' . url('login.php'));
        exit();
    }
    
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    // Sauvegarder l'email pour le réafficher
    $_SESSION['old']['email'] = $email;
    
    // Validation des champs
    if (empty($email) || empty($password)) {
        Session::flash('error', 'Veuillez remplir tous les champs.');
        header('Location: ' . url('login.php'));
        exit();
    }
    
    // Vérifier les tentatives de connexion
    if (!Auth::checkLoginAttempts($email)) {
        $remaining = Auth::getLockoutTimeRemaining($email);
        Session::flash('error', "Trop de tentatives. Veuillez réessayer dans {$remaining} minute(s).");
        header('Location: ' . url('login.php'));
        exit();
    }
    
    // Tenter la connexion
    if (Auth::attempt($email, $password)) {
        // Gérer "Se souvenir de moi"
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $_SESSION['remember_token'] = $token;
            setcookie('remember_token', $token, time() + 86400 * 30, '/', '', false, true);
        }
        
        Session::flash('success', 'Bienvenue ' . Auth::user()->getPrenom() . ' !');
        
        // Redirection vers la page demandée ou l'accueil
        $redirect = $_GET['redirect'] ?? 'index.php';
        header('Location: ' . url($redirect));
        exit();
    } else {
        // La tentative a échoué, le compteur est incrémenté automatiquement dans Auth::attempt()
        Session::flash('error', 'Email ou mot de passe incorrect.');
        header('Location: ' . url('login.php'));
        exit();
    }
}

// Récupérer l'ancien email en cas d'erreur
$oldEmail = htmlspecialchars($_SESSION['old']['email'] ?? '');
unset($_SESSION['old']);

ob_start();
?>

<!-- MESSAGES FLASH -->
<?php if (Session::hasFlash('error')) : ?>
    <div class="alert alert-danger d-flex align-items-center gap-2 rounded-3 mx-auto mt-4" style="max-width: 900px;">
        <i class="bi bi-exclamation-circle-fill"></i>
        <span><?= Session::getFlash('error') ?></span>
    </div>
<?php endif; ?>

<?php if (Session::hasFlash('success')) : ?>
    <div class="alert alert-success d-flex align-items-center gap-2 rounded-3 mx-auto mt-4" style="max-width: 900px;">
        <i class="bi bi-check-circle-fill"></i>
        <span><?= Session::getFlash('success') ?></span>
    </div>
<?php endif; ?>

<style>
    @keyframes fadeInLeft {
        from { opacity: 0; transform: translateX(-40px); }
        to   { opacity: 1; transform: translateX(0); }
    }
    @keyframes fadeInRight {
        from { opacity: 0; transform: translateX(40px); }
        to   { opacity: 1; transform: translateX(0); }
    }
    @keyframes fadeInDown {
        from { opacity: 0; transform: translateY(-20px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50%       { transform: translateY(-8px); }
    }
    @keyframes pulse-green {
        0%, 100% { box-shadow: 0 0 0 0 rgba(22,163,74,0.4); }
        50%       { box-shadow: 0 0 0 10px rgba(22,163,74,0); }
    }

    .login-left  { animation: fadeInLeft  0.8s ease both; }
    .login-right { animation: fadeInRight 0.8s ease 0.2s both; }
    .login-alert { animation: fadeInDown  0.5s ease both; }

    .login-icon {
        animation: float 3s ease-in-out infinite;
    }

    .login-avantage {
        opacity: 0;
        transform: translateX(-20px);
        animation: fadeInLeft 0.5s ease forwards;
    }
    .login-avantage:nth-child(1) { animation-delay: 0.5s; }
    .login-avantage:nth-child(2) { animation-delay: 0.65s; }
    .login-avantage:nth-child(3) { animation-delay: 0.8s; }
    .login-avantage:nth-child(4) { animation-delay: 0.95s; }
    .login-avantage:nth-child(5) { animation-delay: 1.1s; }

    .login-field {
        opacity: 0;
        transform: translateY(15px);
        animation: fadeInRight 0.4s ease forwards;
    }
    .login-field:nth-child(1) { animation-delay: 0.4s; }
    .login-field:nth-child(2) { animation-delay: 0.5s; }
    .login-field:nth-child(3) { animation-delay: 0.6s; }
    .login-field:nth-child(4) { animation-delay: 0.7s; }
    .login-field:nth-child(5) { animation-delay: 0.8s; }
    .login-field:nth-child(6) { animation-delay: 0.9s; }

    .btn-login-submit {
        animation: pulse-green 2.5s infinite;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .btn-login-submit:hover {
        animation: none;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(22,163,74,0.4) !important;
    }

    .form-control, .form-select {
        transition: border-color 0.2s, box-shadow 0.2s, transform 0.2s;
    }
    .form-control:focus {
        transform: translateY(-1px);
    }
    
    .btn-outline-secondary:hover {
        background-color: #f8f9fa;
        border-color: #16a34a;
    }
    .btn-outline-secondary:hover span {
        color: #16a34a;
    }
</style>

<!-- FORMULAIRE CONNEXION -->
<section class="min-vh-100 d-flex align-items-center py-5"
         style="background: linear-gradient(160deg, #f0faf3 60%, #d1fae5 100%);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-9 col-lg-10">

                <div class="card border-0 rounded-4 overflow-hidden shadow-lg">
                    <div class="row g-0">

                        <!-- ====== PANNEAU GAUCHE ====== -->
                        <div class="col-lg-5 d-none d-lg-block text-white login-left"
                             style="background: linear-gradient(160deg, #0d2818 0%, #1a6b35 100%);">
                            <div class="p-5 h-100 d-flex flex-column justify-content-center">

                                <div class="login-icon bg-white bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center mb-4"
                                     style="width: 64px; height: 64px; font-size: 1.8rem;">
                                    <i class="bi bi-bag-heart-fill text-white"></i>
                                </div>

                                <h2 class="fw-bold mb-2" style="font-family: 'Playfair Display', serif;">
                                    Bienvenue sur<br>NGAARY SHOP
                                </h2>
                                <p class="opacity-75 small mb-4">
                                    Connectez-vous pour accéder à votre espace personnel.
                                </p>

                                <ul class="list-unstyled mb-0">
                                    <li class="login-avantage d-flex align-items-center gap-3 py-2 border-bottom border-white border-opacity-10 small opacity-90">
                                        <i class="bi bi-truck-front-fill text-success fs-5"></i>
                                        <span>Livraison gratuite dès 15 000 FCFA</span>
                                    </li>
                                    <li class="login-avantage d-flex align-items-center gap-3 py-2 border-bottom border-white border-opacity-10 small opacity-90">
                                        <i class="bi bi-clock-history text-success fs-5"></i>
                                        <span>Suivi de vos commandes en temps réel</span>
                                    </li>
                                    <li class="login-avantage d-flex align-items-center gap-3 py-2 border-bottom border-white border-opacity-10 small opacity-90">
                                        <i class="bi bi-heart-fill text-success fs-5"></i>
                                        <span>Sauvegardez vos produits favoris</span>
                                    </li>
                                    <li class="login-avantage d-flex align-items-center gap-3 py-2 border-bottom border-white border-opacity-10 small opacity-90">
                                        <i class="bi bi-gift-fill text-success fs-5"></i>
                                        <span>Offres exclusives pour les membres</span>
                                    </li>
                                    <li class="login-avantage d-flex align-items-center gap-3 py-2 small opacity-90">
                                        <i class="bi bi-arrow-return-left text-success fs-5"></i>
                                        <span>Retour simplifié sous 7 jours</span>
                                    </li>
                                </ul>

                            </div>
                        </div>

                        <!-- ====== PANNEAU DROIT - FORMULAIRE ====== -->
                        <div class="col-lg-7 bg-white login-right">
                            <div class="p-5">

                                <h1 class="fw-bold mb-1"
                                    style="font-family: 'Playfair Display', serif; font-size: 1.9rem; color: #0d2818;">
                                    Connexion
                                </h1>
                                <p class="text-muted small mb-4">
                                    Pas encore de compte ?
                                    <a href="<?= url('register.php') ?>"
                                       class="text-success fw-semibold text-decoration-none">
                                        Créer un compte
                                    </a>
                                </p>

                                <!-- FORMULAIRE -->
                                <form action="<?= url('login.php') ?>" method="POST" novalidate>
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                                    <!-- EMAIL -->
                                    <div class="mb-3 login-field">
                                        <label class="form-label fw-medium small">
                                            Adresse email <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">
                                                <i class="bi bi-envelope text-secondary"></i>
                                            </span>
                                            <input
                                                type="email"
                                                name="email"
                                                class="form-control border-start-0 bg-light"
                                                placeholder="fatou@email.com"
                                                value="<?= $oldEmail ?>"
                                                required
                                                autocomplete="email"
                                            >
                                        </div>
                                    </div>

                                    <!-- MOT DE PASSE -->
                                    <div class="mb-2 login-field">
                                        <label class="form-label fw-medium small">
                                            Mot de passe <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">
                                                <i class="bi bi-lock text-secondary"></i>
                                            </span>
                                            <input
                                                type="password"
                                                name="password"
                                                id="password"
                                                class="form-control border-start-0 border-end-0 bg-light"
                                                placeholder="••••••••"
                                                required
                                                autocomplete="current-password"
                                            >
                                            <button type="button"
                                                    class="input-group-text bg-light border-start-0"
                                                    onclick="togglePassword()"
                                                    title="Afficher/masquer"
                                                    style="cursor: pointer;">
                                                <i class="bi bi-eye text-secondary" id="eyeIcon"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- SE SOUVENIR + MOT DE PASSE OUBLIÉ -->
                                    <div class="login-field d-flex justify-content-between align-items-center mb-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                   name="remember" id="remember">
                                            <label class="form-check-label small text-muted" for="remember">
                                                Se souvenir de moi
                                            </label>
                                        </div>
                                        <a href="<?= url('forgot_password.php') ?>"
                                           class="text-success small fw-semibold text-decoration-none">
                                            Mot de passe oublié ?
                                        </a>
                                    </div>

                                    <!-- BOUTON CONNEXION -->
                                    <div class="login-field">
                                        <button type="submit"
                                                class="btn btn-success w-100 py-2 fw-semibold rounded-3 mb-4 btn-login-submit">
                                            <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
                                        </button>
                                    </div>

                                    <!-- SÉPARATEUR -->
                                    <div class="login-field d-flex align-items-center gap-3 text-muted small mb-4">
                                        <hr class="flex-grow-1 m-0">
                                        <span>ou continuer avec</span>
                                        <hr class="flex-grow-1 m-0">
                                    </div>

                                    <!-- CONNEXION SOCIALE -->
                                    <div class="login-field">
                                        <div class="col-12">
                                            <button type="button"
                                                    class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center gap-2 rounded-3 py-2">
                                                <img src="https://www.google.com/favicon.ico" width="18" alt="Google">
                                                <span class="small fw-medium">Google</span>
                                            </button>
                                        </div>
                                    </div>

                                </form>

                                <p class="text-center text-muted small mt-4 mb-0">
                                    Nouveau client ?
                                    <a href="<?= url('register.php') ?>"
                                       class="text-success fw-semibold text-decoration-none">
                                        Créer un compte gratuitement
                                    </a>
                                </p>

                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<script>
    function togglePassword() {
        const input = document.getElementById('password');
        const icon  = document.getElementById('eyeIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'bi bi-eye-slash text-success';
        } else {
            input.type = 'password';
            icon.className = 'bi bi-eye text-secondary';
        }
    }
    
    // Masquer les messages flash après 5 secondes
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        });
    }, 5000);
</script>

<?php
$content = ob_get_clean();
require_once dirname(__DIR__, 2) . '/views/layouts/main_layout.php';
?>