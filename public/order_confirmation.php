<?php
// public/order_confirmation.php
require_once dirname(__DIR__) . '/config/config.php';

use App\Utils\Auth;
use App\Config\Database;

if (!Auth::check()) {
    header('Location: ' . url('login.php'));
    exit();
}

$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_success']);

$numeroCommande = $_GET['numero'] ?? null;

// Charger les détails de la commande
$commande = null;
if ($numeroCommande) {
    try {
        $db   = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT c.*, 
                   CONCAT(u.prenom, ' ', u.nom) AS client,
                   a.rue, a.ville, a.pays, a.telephone AS tel_livraison,
                   p.mode_paiement
            FROM commandes c
            JOIN utilisateurs u ON c.id_utilisateur = u.id_utilisateur
            LEFT JOIN adresses a ON c.id_adresse_livraison = a.id_adresse
            LEFT JOIN paiements p ON c.id_commande = p.id_commande
            WHERE c.numero_commande = :numero
            AND c.id_utilisateur = :id_utilisateur
        ");
        $stmt->execute([
            ':numero'         => $numeroCommande,
            ':id_utilisateur' => Auth::id(),
        ]);
        $commande = $stmt->fetch();

        if ($commande) {
            $stmtLignes = $db->prepare("
                SELECT * FROM lignes_commande WHERE id_commande = :id
            ");
            $stmtLignes->execute([':id' => $commande['id_commande']]);
            $commande['lignes'] = $stmtLignes->fetchAll();
        }
    } catch (\Exception $e) {
        error_log($e->getMessage());
    }
}

$pageTitle   = 'Commande confirmée - NGAARY SHOP';
$currentPage = '';
include __DIR__ . '/../views/layouts/header.php';
?>

<style>
    .confirmation-hero {
        background: linear-gradient(135deg, #0d2818 0%, #1a6b35 100%);
        padding: 60px 0;
        text-align: center;
        color: white;
    }
    .check-circle {
        width: 80px; height: 80px;
        background: #16a34a;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        font-size: 2.5rem;
        animation: popIn 0.5s ease;
    }
    @keyframes popIn {
        from { transform: scale(0); opacity: 0; }
        to   { transform: scale(1); opacity: 1; }
    }
</style>

<!-- HERO -->
<section class="confirmation-hero">
    <div class="container">
        <div class="check-circle">
            <i class="bi bi-check-lg text-white"></i>
        </div>
        <h1 class="fw-bold mb-2" style="font-family: 'Playfair Display', serif;">
            Commande confirmée !
        </h1>
        <p class="opacity-75 mb-0">
            Merci pour votre achat. Votre commande a bien été enregistrée.
        </p>
    </div>
</section>

<section class="py-5" style="background: #f0faf3;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">

                <?php if ($commande) : ?>

                <!-- NUMÉRO DE COMMANDE -->
                <div class="card border-0 rounded-4 shadow-sm mb-4">
                    <div class="card-body p-4 text-center">
                        <p class="text-muted small mb-1">Numéro de commande</p>
                        <h4 class="fw-bold text-success mb-0">
                            <?= htmlspecialchars($commande['numero_commande']) ?>
                        </h4>
                        <p class="text-muted small mt-2 mb-0">
                            Passée le <?= date('d/m/Y à H:i', strtotime($commande['date_commande'])) ?>
                        </p>
                    </div>
                </div>

                <!-- ARTICLES -->
                <div class="card border-0 rounded-4 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3">
                            <i class="bi bi-bag-check text-success me-2"></i>Articles commandés
                        </h6>
                        <?php foreach ($commande['lignes'] as $ligne) : ?>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <div>
                                <span class="small fw-semibold"><?= htmlspecialchars($ligne['nom_produit']) ?></span>
                                <span class="text-muted small ms-2">x<?= $ligne['quantite'] ?></span>
                            </div>
                            <span class="fw-bold text-success small">
                                <?= formatFCFA((int)$ligne['sous_total']) ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                        <div class="d-flex justify-content-between mt-3">
                            <span class="fw-bold">Total payé</span>
                            <span class="fw-bold text-success fs-5">
                                <?= formatFCFA((int)$commande['montant_total']) ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- LIVRAISON & PAIEMENT -->
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="card border-0 rounded-4 shadow-sm h-100">
                            <div class="card-body p-4">
                                <h6 class="fw-bold mb-3">
                                    <i class="bi bi-geo-alt-fill text-success me-2"></i>Livraison
                                </h6>
                                <p class="small mb-1"><?= htmlspecialchars($commande['rue'] ?? '—') ?></p>
                                <p class="small mb-1"><?= htmlspecialchars($commande['ville'] ?? '—') ?></p>
                                <p class="small mb-0"><?= htmlspecialchars($commande['pays'] ?? 'Sénégal') ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 rounded-4 shadow-sm h-100">
                            <div class="card-body p-4">
                                <h6 class="fw-bold mb-3">
                                    <i class="bi bi-credit-card-fill text-success me-2"></i>Paiement
                                </h6>
                                <p class="small mb-1 fw-semibold">
                                    <?= htmlspecialchars($commande['mode_paiement'] ?? '—') ?>
                                </p>
                                <span class="badge bg-warning bg-opacity-10 text-warning small">
                                    En attente de confirmation
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <?php endif; ?>

                <!-- ACTIONS -->
                <div class="d-flex gap-3 justify-content-center flex-wrap">
                    <a href="<?= url('mes_commandes.php') ?>"
                       class="btn btn-success rounded-3 px-4 py-2 fw-semibold">
                        <i class="bi bi-receipt me-2"></i>Mes commandes
                    </a>
                    <a href="<?= url('boutique.php') ?>"
                       class="btn btn-outline-success rounded-3 px-4 py-2">
                        <i class="bi bi-bag me-2"></i>Continuer mes achats
                    </a>
                </div>

            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../views/layouts/footer.php'; ?>