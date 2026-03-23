<?php
// public/mes_commandes.php
require_once dirname(__DIR__) . '/config/config.php';

use App\Utils\Auth;
use App\Config\Database;

if (!Auth::check()) {
    header('Location: ' . url('login.php'));
    exit();
}

try {
    $db   = Database::getInstance()->getConnection();
    $stmt = $db->prepare("
        SELECT c.id_commande, c.numero_commande, c.date_commande,
               c.montant_total, c.statut,
               COUNT(lc.id_ligne) AS nb_articles,
               p.mode_paiement
        FROM commandes c
        LEFT JOIN lignes_commande lc ON c.id_commande = lc.id_commande
        LEFT JOIN paiements p ON c.id_commande = p.id_commande
        WHERE c.id_utilisateur = :id
        GROUP BY c.id_commande
        ORDER BY c.date_commande DESC
    ");
    $stmt->execute([':id' => Auth::id()]);
    $commandes = $stmt->fetchAll();
} catch (\Exception $e) {
    $commandes = [];
}

$pageTitle   = 'Mes Commandes - NGAARY SHOP';
$currentPage = '';
include __DIR__ . '/../views/layouts/header.php';
?>

<style>
    .statut-badge { padding: 5px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
    .statut-en_attente    { background: #fef3c7; color: #d97706; }
    .statut-confirmee     { background: #dbeafe; color: #2563eb; }
    .statut-en_preparation{ background: #ede9fe; color: #7c3aed; }
    .statut-expediee      { background: #d1fae5; color: #059669; }
    .statut-livree        { background: #dcfce7; color: #16a34a; }
    .statut-annulee       { background: #fee2e2; color: #dc2626; }
</style>

<!-- HERO -->
<section class="py-4 bg-white border-bottom">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item">
                    <a href="<?= url('index.php') ?>" class="text-success text-decoration-none">Accueil</a>
                </li>
                <li class="breadcrumb-item active">Mes commandes</li>
            </ol>
        </nav>
    </div>
</section>

<section class="py-5" style="background: #f0faf3;">
    <div class="container">

        <h1 class="fw-bold mb-1" style="font-family: 'Playfair Display', serif; color: #0d2818;">
            Mes Commandes
        </h1>
        <p class="text-muted small mb-4"><?= count($commandes) ?> commande<?= count($commandes) > 1 ? 's' : '' ?></p>

        <?php if (empty($commandes)) : ?>
            <div class="text-center py-5 bg-white rounded-4 shadow-sm">
                <i class="bi bi-receipt" style="font-size: 4rem; color: #d1d5db;"></i>
                <h5 class="fw-bold mt-4 mb-2">Aucune commande</h5>
                <p class="text-muted mb-4">Vous n'avez pas encore passé de commande.</p>
                <a href="<?= url('boutique.php') ?>" class="btn btn-success rounded-3 px-4">
                    <i class="bi bi-bag me-2"></i>Voir la boutique
                </a>
            </div>
        <?php else : ?>
            <div class="d-flex flex-column gap-3">
                <?php foreach ($commandes as $cmd) : ?>
                <div class="card border-0 rounded-4 shadow-sm">
                    <div class="card-body p-4">
                        <div class="row align-items-center">

                            <!-- NUMÉRO & DATE -->
                            <div class="col-md-3">
                                <p class="text-muted small mb-1">Numéro</p>
                                <p class="fw-bold text-success mb-0 small">
                                    <?= htmlspecialchars($cmd['numero_commande']) ?>
                                </p>
                                <p class="text-muted mb-0" style="font-size: 0.75rem;">
                                    <?= date('d/m/Y', strtotime($cmd['date_commande'])) ?>
                                </p>
                            </div>

                            <!-- ARTICLES & TOTAL -->
                            <div class="col-md-3">
                                <p class="text-muted small mb-1">Articles</p>
                                <p class="fw-semibold mb-0 small">
                                    <?= $cmd['nb_articles'] ?> article<?= $cmd['nb_articles'] > 1 ? 's' : '' ?>
                                </p>
                                <p class="fw-bold text-success mb-0 small">
                                    <?= formatFCFA((int)$cmd['montant_total']) ?>
                                </p>
                            </div>

                            <!-- PAIEMENT -->
                            <div class="col-md-2">
                                <p class="text-muted small mb-1">Paiement</p>
                                <p class="small mb-0"><?= htmlspecialchars($cmd['mode_paiement'] ?? '—') ?></p>
                            </div>

                            <!-- STATUT -->
                            <div class="col-md-2">
                                <span class="statut-badge statut-<?= $cmd['statut'] ?>">
                                    <?= ucfirst(str_replace('_', ' ', $cmd['statut'])) ?>
                                </span>
                            </div>

                            <!-- ACTION -->
                            <div class="col-md-2 text-md-end mt-3 mt-md-0">
                                <a href="<?= url('order_confirmation.php?numero=' . $cmd['numero_commande']) ?>"
                                   class="btn btn-outline-success btn-sm rounded-3">
                                    <i class="bi bi-eye me-1"></i>Détails
                                </a>
                            </div>

                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</section>

<?php include __DIR__ . '/../views/layouts/footer.php'; ?>