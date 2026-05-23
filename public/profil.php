<?php
require_once dirname(__DIR__) . '/config/config.php';

use App\Utils\Auth;
use App\Utils\Session;

// Vérifier si l'utilisateur est connecté
if (!Auth::check()) {
    Session::flash('error', 'Veuillez vous connecter pour accéder à votre profil.');
    header('Location: ' . url('login.php'));
    exit();
}

$db = \App\Config\Database::getInstance()->getConnection();
$userId = Auth::id();

// Récupérer les infos utilisateur
$stmt = $db->prepare("SELECT id_utilisateur, nom, prenom, email, tel, date_inscription FROM utilisateurs WHERE id_utilisateur = :id");
$stmt->execute([':id' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    Auth::logout();
    header('Location: ' . url('login.php'));
    exit();
}

// Traitement du formulaire
$flashSuccess = null;
$flashError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier token CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $flashError = "Erreur de sécurité. Veuillez réessayer.";
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'update_profile') {
            $nom = trim($_POST['nom'] ?? '');
            $prenom = trim($_POST['prenom'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $tel = trim($_POST['tel'] ?? '');
            
            $errors = [];
            if (empty($nom)) $errors[] = "Le nom est requis";
            if (empty($prenom)) $errors[] = "Le prénom est requis";
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email invalide";
            
            if (empty($errors)) {
                $stmtCheck = $db->prepare("SELECT id_utilisateur FROM utilisateurs WHERE email = :email AND id_utilisateur != :id");
                $stmtCheck->execute([':email' => $email, ':id' => $userId]);
                if ($stmtCheck->fetch()) {
                    $flashError = "Cet email est déjà utilisé par un autre compte.";
                } else {
                    $stmtUpdate = $db->prepare("UPDATE utilisateurs SET nom = :nom, prenom = :prenom, email = :email, tel = :tel WHERE id_utilisateur = :id");
                    if ($stmtUpdate->execute([':nom' => $nom, ':prenom' => $prenom, ':email' => $email, ':tel' => $tel, ':id' => $userId])) {
                        $flashSuccess = "Profil mis à jour avec succès !";
                        $user['nom'] = $nom;
                        $user['prenom'] = $prenom;
                        $user['email'] = $email;
                        $user['tel'] = $tel;
                    } else {
                        $flashError = "Erreur lors de la mise à jour.";
                    }
                }
            } else {
                $flashError = implode(", ", $errors);
            }
        } elseif ($action === 'update_password') {
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            $stmtPass = $db->prepare("SELECT mot_de_passe FROM utilisateurs WHERE id_utilisateur = :id");
            $stmtPass->execute([':id' => $userId]);
            $currentHash = $stmtPass->fetchColumn();
            
            if (!password_verify($currentPassword, $currentHash)) {
                $flashError = "Mot de passe actuel incorrect.";
            } elseif (strlen($newPassword) < 6) {
                $flashError = "Le nouveau mot de passe doit contenir au moins 6 caractères.";
            } elseif ($newPassword !== $confirmPassword) {
                $flashError = "Les mots de passe ne correspondent pas.";
            } else {
                $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmtUpdate = $db->prepare("UPDATE utilisateurs SET mot_de_passe = :password WHERE id_utilisateur = :id");
                if ($stmtUpdate->execute([':password' => $newHash, ':id' => $userId])) {
                    $flashSuccess = "Mot de passe modifié avec succès !";
                } else {
                    $flashError = "Erreur lors de la modification du mot de passe.";
                }
            }
        }
    }
}

$pageTitle = 'Mon profil - NGAARY SHOP';
$currentPage = 'profil.php';
include __DIR__ . '/../views/layouts/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body text-center p-4">
                    <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 100px; height: 100px;">
                        <i class="bi bi-person fs-1 text-success"></i>
                    </div>
                    <h5 class="fw-bold mb-1"><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></h5>
                    <p class="text-muted small mb-3"><?= htmlspecialchars($user['email']) ?></p>
                    <p class="text-muted small mb-0">
                        <i class="bi bi-calendar3 me-1"></i>
                        Inscrit depuis <?= date('d/m/Y', strtotime($user['date_inscription'])) ?>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#info">Informations</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#password">Mot de passe</button>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-4">
                    <?php if ($flashSuccess): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($flashSuccess) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    <?php if ($flashError): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-circle-fill me-2"></i><?= htmlspecialchars($flashError) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <div class="tab-content">
                        <!-- Informations -->
                        <div class="tab-pane fade show active" id="info">
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <input type="hidden" name="action" value="update_profile">
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Nom</label>
                                        <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($user['nom']) ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Prénom</label>
                                        <input type="text" name="prenom" class="form-control" value="<?= htmlspecialchars($user['prenom']) ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Email</label>
                                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Téléphone</label>
                                        <input type="tel" name="tel" class="form-control" value="<?= htmlspecialchars($user['tel'] ?? '') ?>" placeholder="771234567">
                                        <small class="text-muted">Format: 771234567 ou +221771234567</small>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-success px-4">
                                            <i class="bi bi-save me-1"></i>Enregistrer
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Mot de passe -->
                        <div class="tab-pane fade" id="password">
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <input type="hidden" name="action" value="update_password">
                                
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label small fw-semibold">Mot de passe actuel</label>
                                        <input type="password" name="current_password" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Nouveau mot de passe</label>
                                        <input type="password" name="new_password" class="form-control" required>
                                        <small class="text-muted">Minimum 6 caractères</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Confirmer le mot de passe</label>
                                        <input type="password" name="confirm_password" class="form-control" required>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-success px-4">
                                            <i class="bi bi-key me-1"></i>Changer le mot de passe
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Lien de déconnexion rapide -->
            <div class="card border-0 shadow-sm rounded-4 mt-4">
                <div class="card-body p-4 text-center">
                    <p class="text-muted small mb-2">Vous voulez vous déconnecter ?</p>
                    <a href="<?= url('logout.php') ?>" class="btn btn-outline-danger rounded-3 px-4">
                        <i class="bi bi-box-arrow-right me-1"></i>Se déconnecter
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../views/layouts/footer.php'; ?>