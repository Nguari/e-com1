<?php
/**
 * Paramètres - Administration
 * 
 * @var array<string, mixed> $settings Paramètres actuels
 * @var string|null $success Message de succès
 * @var string|null $error Message d'erreur
 */

$pageTitle = 'Paramètres - Admin';
$adminPage = 'parametres';
include view_path('admin/layouts/header.php');

use App\Repositories\SettingRepository;
use App\Config\Database;
use App\Utils\Auth;

// Vérifier si l'utilisateur est admin
if (!Auth::isAdmin()) {
    header('Location: ' . url('admin/index.php'));
    exit();
}

$db = Database::getInstance()->getConnection();
$settingRepo = new SettingRepository($db);

$success = null;
$error = null;

// Traitement des formulaires
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier token CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "Erreur de sécurité. Veuillez réessayer.";
    } else {
        $action = $_POST['action'] ?? '';
        $data = [];

        switch ($action) {
            case 'update_general':
                $data['site_name'] = trim($_POST['site_name'] ?? 'NGAARY SHOP');
                $data['site_email'] = trim($_POST['site_email'] ?? 'contact@ngaary.sn');
                $data['site_phone'] = trim($_POST['site_phone'] ?? '+221 78 123 45 67');
                $data['site_address'] = trim($_POST['site_address'] ?? 'Dakar, Sénégal');
                $data['delivery_fee'] = (int)($_POST['delivery_fee'] ?? 2500);
                $data['free_delivery_min'] = (int)($_POST['free_delivery_min'] ?? 15000);
                break;
            case 'update_security':
                $data['min_password'] = (int)($_POST['min_password'] ?? 8);
                $data['max_login_attempts'] = (int)($_POST['max_login_attempts'] ?? 5);
                $data['session_lifetime'] = (int)($_POST['session_lifetime'] ?? 7200);
                break;
            case 'update_notifications':
                $data['order_email'] = isset($_POST['order_email']) ? 1 : 0;
                $data['newsletter_email'] = isset($_POST['newsletter_email']) ? 1 : 0;
                break;
            case 'update_payment':
                $data['enable_wave'] = isset($_POST['enable_wave']) ? 1 : 0;
                $data['enable_om'] = isset($_POST['enable_om']) ? 1 : 0;
                $data['enable_cash'] = isset($_POST['enable_cash']) ? 1 : 0;
                break;
            case 'update_appearance':
                $data['primary_color'] = trim($_POST['primary_color'] ?? '#16a34a');
                $data['header_bg'] = trim($_POST['header_bg'] ?? '#ffffff');
                break;
        }

        if (!empty($data)) {
            if ($settingRepo->setMultiple($data)) {
                $success = "Paramètres mis à jour avec succès !";
            } else {
                $error = "Erreur lors de la mise à jour des paramètres.";
            }
        } else {
            $error = "Aucune action valide.";
        }
    }
}

// Récupérer tous les paramètres depuis la BDD
$settings = $settingRepo->getAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">Paramètres</h5>
</div>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-circle-fill me-2"></i><?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- Paramètres généraux -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="fw-bold mb-0">
                    <i class="bi bi-gear-fill me-2 text-success"></i>Paramètres généraux
                </h6>
            </div>
            <div class="card-body p-4">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="action" value="update_general">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nom du site</label>
                        <input type="text" name="site_name" class="form-control" 
                               value="<?= htmlspecialchars($settings['site_name'] ?? 'NGAARY SHOP') ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Email du site</label>
                        <input type="email" name="site_email" class="form-control" 
                               value="<?= htmlspecialchars($settings['site_email'] ?? 'contact@ngaary.sn') ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Téléphone</label>
                        <input type="tel" name="site_phone" class="form-control" 
                               value="<?= htmlspecialchars($settings['site_phone'] ?? '+221 78 123 45 67') ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Adresse</label>
                        <textarea name="site_address" class="form-control" rows="2"><?= htmlspecialchars($settings['site_address'] ?? 'Dakar, Sénégal') ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-semibold">Frais de livraison (FCFA)</label>
                            <input type="number" name="delivery_fee" class="form-control" 
                                   value="<?= $settings['delivery_fee'] ?? 2500 ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-semibold">Livraison gratuite dès (FCFA)</label>
                            <input type="number" name="free_delivery_min" class="form-control" 
                                   value="<?= $settings['free_delivery_min'] ?? 15000 ?>">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-success rounded-3">
                        <i class="bi bi-save me-1"></i>Enregistrer
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Sécurité -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="fw-bold mb-0">
                    <i class="bi bi-shield-lock-fill me-2 text-success"></i>Sécurité
                </h6>
            </div>
            <div class="card-body p-4">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="action" value="update_security">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Longueur minimale du mot de passe</label>
                        <input type="number" name="min_password" class="form-control" 
                               value="<?= $settings['min_password'] ?? 8 ?>" min="6" max="20">
                        <small class="text-muted">Nombre de caractères minimum requis</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Tentatives de connexion max</label>
                        <input type="number" name="max_login_attempts" class="form-control" 
                               value="<?= $settings['max_login_attempts'] ?? 5 ?>" min="3" max="10">
                        <small class="text-muted">Nombre de tentatives avant blocage</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Durée de session (secondes)</label>
                        <input type="number" name="session_lifetime" class="form-control" 
                               value="<?= $settings['session_lifetime'] ?? 7200 ?>">
                        <small class="text-muted">7200 secondes = 2 heures</small>
                    </div>
                    
                    <button type="submit" class="btn btn-success rounded-3">
                        <i class="bi bi-save me-1"></i>Enregistrer
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Notifications -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="fw-bold mb-0">
                    <i class="bi bi-bell-fill me-2 text-success"></i>Notifications
                </h6>
            </div>
            <div class="card-body p-4">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="action" value="update_notifications">
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="order_email" id="order_email" 
                               <?= (($settings['order_email'] ?? 1) == 1) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="order_email">
                            <strong>Email de confirmation de commande</strong>
                            <br><small class="text-muted">Envoyer un email au client après chaque commande</small>
                        </label>
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="newsletter_email" id="newsletter_email" 
                               <?= (($settings['newsletter_email'] ?? 1) == 1) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="newsletter_email">
                            <strong>Newsletter</strong>
                            <br><small class="text-muted">Permettre aux clients de s'inscrire à la newsletter</small>
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-success rounded-3">
                        <i class="bi bi-save me-1"></i>Enregistrer
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modes de paiement -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="fw-bold mb-0">
                    <i class="bi bi-credit-card-fill me-2 text-success"></i>Modes de paiement
                </h6>
            </div>
            <div class="card-body p-4">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="action" value="update_payment">
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="enable_wave" id="enable_wave" 
                               <?= (($settings['enable_wave'] ?? 1) == 1) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="enable_wave">
                            <i class="bi bi-phone me-1"></i> <strong>Wave</strong>
                            <br><small class="text-muted">Paiement mobile via Wave</small>
                        </label>
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="enable_om" id="enable_om" 
                               <?= (($settings['enable_om'] ?? 1) == 1) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="enable_om">
                            <i class="bi bi-phone me-1"></i> <strong>Orange Money</strong>
                            <br><small class="text-muted">Paiement mobile via Orange Money</small>
                        </label>
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="enable_cash" id="enable_cash" 
                               <?= (($settings['enable_cash'] ?? 1) == 1) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="enable_cash">
                            <i class="bi bi-cash me-1"></i> <strong>Espèces</strong>
                            <br><small class="text-muted">Paiement à la livraison</small>
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-success rounded-3">
                        <i class="bi bi-save me-1"></i>Enregistrer
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Apparence -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="fw-bold mb-0">
                    <i class="bi bi-palette-fill me-2 text-success"></i>Apparence
                </h6>
            </div>
            <div class="card-body p-4">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="action" value="update_appearance">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Couleur principale</label>
                        <div class="d-flex align-items-center gap-3">
                            <input type="color" name="primary_color" class="form-control form-control-color w-25" 
                                   value="<?= $settings['primary_color'] ?? '#16a34a' ?>">
                            <input type="text" class="form-control" value="<?= $settings['primary_color'] ?? '#16a34a' ?>" 
                                   onchange="this.previousElementSibling.value = this.value">
                        </div>
                        <small class="text-muted">Couleur des boutons et éléments principaux</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Couleur d'arrière-plan</label>
                        <div class="d-flex align-items-center gap-3">
                            <input type="color" name="header_bg" class="form-control form-control-color w-25" 
                                   value="<?= $settings['header_bg'] ?? '#ffffff' ?>">
                            <input type="text" class="form-control" value="<?= $settings['header_bg'] ?? '#ffffff' ?>" 
                                   onchange="this.previousElementSibling.value = this.value">
                        </div>
                        <small class="text-muted">Couleur de fond principale</small>
                    </div>
                    
                    <button type="submit" class="btn btn-success rounded-3">
                        <i class="bi bi-save me-1"></i>Enregistrer
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Informations système -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="fw-bold mb-0">
                    <i class="bi bi-info-circle-fill me-2 text-success"></i>Informations système
                </h6>
            </div>
            <div class="card-body p-4">
                <table class="table table-sm">
                    <tr>
                        <td class="small fw-semibold">Version PHP</td>
                        <td class="small"><?= phpversion() ?></td>
                    </tr>
                    <tr>
                        <td class="small fw-semibold">Version MySQL</td>
                        <td class="small">
                            <?php
                            $stmt = $db->query("SELECT VERSION()");
                            echo $stmt->fetchColumn();
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="small fw-semibold">Environnement</td>
                        <td class="small">
                            <span class="badge bg-<?= APP_ENV === 'production' ? 'success' : 'warning' ?>">
                                <?= APP_ENV ?? 'development' ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="small fw-semibold">Dernière sauvegarde</td>
                        <td class="small">
                            <?= date('d/m/Y H:i:s') ?>
                            <button class="btn btn-sm btn-outline-success ms-2 rounded-2" onclick="alert('Fonctionnalité à venir')">
                                <i class="bi bi-download"></i>
                            </button>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include view_path('admin/layouts/footer.php'); ?>