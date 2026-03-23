<?php
// public/forgot_password.php
require_once dirname(__DIR__) . '/config/config.php';

use App\Config\Database;

$pageTitle   = 'Mot de passe oublié - NGAARY SHOP';
$currentPage = '';

// Token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error']   ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['flash_error'] = "Adresse email invalide.";
        header('Location: ' . url('forgot_password.php'));
        exit();
    }

    try {
        $db   = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id_utilisateur FROM utilisateurs WHERE email = :email AND actif = 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        // On affiche toujours le même message pour ne pas révéler si l'email existe
        $_SESSION['flash_success'] = "Si cet email existe, un lien de réinitialisation vous a été envoyé.";

        if ($user) {
            // Générer un token de réinitialisation
            $token   = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Stocker le token en session (à remplacer par une table BDD en production)
            $_SESSION['reset_token']   = $token;
            $_SESSION['reset_email']   = $email;
            $_SESSION['reset_expires'] = $expires;

            // En production : envoyer l'email avec PHPMailer
            // $lien = url('reset_password.php?token=' . $token);
            // MailService::sendResetPassword($email, $lien);
        }

        header('Location: ' . url('forgot_password.php'));
        exit();

    } catch (\Exception $e) {
        error_log($e->getMessage());
        $_SESSION['flash_error'] = "Une erreur est survenue.";
        header('Location: ' . url('forgot_password.php'));
        exit();
    }
}

include __DIR__ . '/../views/layouts/header.php';
?>

<section class="min-vh-100 d-flex align-items-center py-5"
         style="background: linear-gradient(160deg, #f0faf3 60%, #d1fae5 100%);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card border-0 rounded-4 shadow-lg">
                    <div class="card-body p-5">

                        <div class="text-center mb-4">
                            <div class="bg-success bg-opacity-10 rounded-3 d-inline-flex align-items-center justify-content-center mb-3"
                                 style="width: 64px; height: 64px; font-size: 1.8rem;">
                                <i class="bi bi-key-fill text-success"></i>
                            </div>
                            <h4 class="fw-bold" style="font-family: 'Playfair Display', serif;">
                                Mot de passe oublié
                            </h4>
                            <p class="text-muted small">
                                Entrez votre email et nous vous enverrons un lien de réinitialisation.
                            </p>
                        </div>

                        <?php if ($flashSuccess) : ?>
                        <div class="alert alert-success rounded-3 d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill"></i>
                            <?= htmlspecialchars($flashSuccess) ?>
                        </div>
                        <?php endif; ?>

                        <?php if ($flashError) : ?>
                        <div class="alert alert-danger rounded-3 d-flex align-items-center gap-2">
                            <i class="bi bi-exclamation-circle-fill"></i>
                            <?= htmlspecialchars($flashError) ?>
                        </div>
                        <?php endif; ?>

                        <form action="<?= url('forgot_password.php') ?>" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                            <div class="mb-4">
                                <label class="form-label fw-medium small">Adresse email</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-envelope text-secondary"></i>
                                    </span>
                                    <input type="email" name="email"
                                           class="form-control border-start-0 bg-light"
                                           placeholder="fatou@email.com"
                                           required>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success w-100 py-2 fw-semibold rounded-3">
                                <i class="bi bi-send me-2"></i>Envoyer le lien
                            </button>
                        </form>

                        <p class="text-center text-muted small mt-4 mb-0">
                            <a href="<?= url('login.php') ?>"
                               class="text-success fw-semibold text-decoration-none">
                                ← Retour à la connexion
                            </a>
                        </p>

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../views/layouts/footer.php'; ?>