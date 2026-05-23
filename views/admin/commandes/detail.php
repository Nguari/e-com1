<?php
/**
 * Détails d'une commande - Admin
 * 
 * @var array<string, mixed> $commande Données de la commande
 * @var string|null $flashSuccess Message de succès
 */

$pageTitle = 'Commande #' . ($commande['numero_commande'] ?? '') . ' - Admin';
$adminPage = 'commandes';
include view_path('admin/layouts/header.php');

$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_success']);
?>

<style>
    .detail-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        overflow: hidden;
    }
    .detail-card-header {
        background: #f8fafc;
        padding: 16px 20px;
        border-bottom: 1px solid #e2e8f0;
    }
    .detail-card-body {
        padding: 20px;
    }
    .status-timeline {
        display: flex;
        justify-content: space-between;
        margin: 20px 0;
    }
    .status-step {
        text-align: center;
        flex: 1;
        position: relative;
    }
    .status-step .step-icon {
        width: 40px;
        height: 40px;
        background: #f1f5f9;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 8px;
        color: #94a3b8;
        transition: all 0.3s;
    }
    .status-step.completed .step-icon {
        background: #16a34a;
        color: white;
    }
    .status-step.active .step-icon {
        background: #16a34a;
        color: white;
        box-shadow: 0 0 0 3px rgba(22,163,74,0.2);
    }
    .status-step .step-label {
        font-size: 0.7rem;
        color: #64748b;
    }
    .status-step.completed .step-label,
    .status-step.active .step-label {
        color: #16a34a;
        font-weight: 600;
    }
    .status-step:not(:last-child):before {
        content: '';
        position: absolute;
        top: 20px;
        left: 50%;
        width: 100%;
        height: 2px;
        background: #e2e8f0;
        z-index: -1;
    }
    .status-step.completed:not(:last-child):before {
        background: #16a34a;
    }
    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .info-row:last-child {
        border-bottom: none;
    }
    .info-label {
        font-weight: 500;
        color: #64748b;
    }
    .info-value {
        font-weight: 500;
        color: #0d2818;
    }
</style>

<?php if ($flashSuccess) : ?>
<div class="alert alert-success rounded-3 d-flex align-items-center gap-2 mb-4">
    <i class="bi bi-check-circle-fill"></i><?= htmlspecialchars($flashSuccess) ?>
</div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?= url('admin/commandes.php') ?>" class="text-muted text-decoration-none small">
            ← Retour aux commandes
        </a>
        <h5 class="fw-bold mb-0 mt-1">
            Commande <?= htmlspecialchars($commande['numero_commande'] ?? '') ?>
        </h5>
        <p class="text-muted small mb-0">
            Date : <?= date('d/m/Y à H:i', strtotime($commande['date_commande'] ?? 'now')) ?>
        </p>
    </div>
    <!-- CHANGER LE STATUT -->
    <form action="<?= url('admin/commande_statut.php') ?>" method="POST" class="d-flex gap-2">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
        <input type="hidden" name="id" value="<?= $commande['id_commande'] ?? '' ?>">
        <select name="statut" class="form-select form-select-sm rounded-3" style="width: auto;">
            <?php
            $statuts = ['en_attente', 'confirmee', 'en_preparation', 'expediee', 'livree', 'annulee'];
            $statutLabels = [
                'en_attente' => 'En attente',
                'confirmee' => 'Confirmée',
                'en_preparation' => 'En préparation',
                'expediee' => 'Expédiée',
                'livree' => 'Livrée',
                'annulee' => 'Annulée'
            ];
            foreach ($statuts as $s) :
            ?>
            <option value="<?= $s ?>" <?= ($commande['statut'] ?? '') === $s ? 'selected' : '' ?>>
                <?= $statutLabels[$s] ?>
            </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-success btn-sm rounded-3">Mettre à jour</button>
    </form>
</div>

<!-- Timeline des statuts -->
<div class="detail-card mb-4">
    <div class="detail-card-header">
        <h6 class="fw-bold mb-0"><i class="bi bi-clock-history me-2"></i>Suivi de commande</h6>
    </div>
    <div class="detail-card-body">
        <div class="status-timeline">
            <?php
            $statusOrder = ['en_attente', 'confirmee', 'en_preparation', 'expediee', 'livree'];
            $currentStatus = $commande['statut'] ?? 'en_attente';
            $currentIndex = array_search($currentStatus, $statusOrder);
            
            foreach ($statusOrder as $index => $status):
                $isCompleted = $index < $currentIndex;
                $isActive = $index === $currentIndex;
                $label = $statutLabels[$status];
            ?>
            <div class="status-step <?= $isCompleted ? 'completed' : '' ?> <?= $isActive ? 'active' : '' ?>">
                <div class="step-icon">
                    <i class="bi <?= $isCompleted || $isActive ? 'bi-check-lg' : 'bi-circle' ?>"></i>
                </div>
                <div class="step-label"><?= $label ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="row g-4">

    <!-- ARTICLES COMMANDÉS -->
    <div class="col-lg-8">
        <div class="detail-card">
            <div class="detail-card-header">
                <h6 class="fw-bold mb-0"><i class="bi bi-box-seam me-2"></i>Articles commandés</h6>
            </div>
            <div class="detail-card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th>Prix unitaire</th>
                                <th>Quantité</th>
                                <th>Sous-total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($commande['lignes']) && is_array($commande['lignes']) && count($commande['lignes']) > 0) : ?>
                                <?php foreach ($commande['lignes'] as $ligne) : ?>
                                <tr>
                                    <td class="small fw-semibold">
                                        <?= htmlspecialchars($ligne['nom_produit'] ?? $ligne['produit_nom'] ?? '—') ?>
                                    </td>
                                    <td class="small"><?= formatFCFA((int)($ligne['prix_unitaire'] ?? 0)) ?></td>
                                    <td class="small"><?= $ligne['quantite'] ?? 0 ?></td>
                                    <td class="fw-bold text-success small">
                                        <?= formatFCFA((int)($ligne['prix_unitaire'] * $ligne['quantite'] ?? 0)) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">Aucun article trouvé</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="border-top">
                            <tr>
                                <td colspan="3" class="fw-bold text-end">Total</td>
                                <td class="fw-bold text-success fs-5"><?= formatFCFA((int)($commande['montant_total'] ?? 0)) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Notes éventuelles -->
        <?php if (!empty($commande['notes'])): ?>
        <div class="detail-card mt-4">
            <div class="detail-card-header">
                <h6 class="fw-bold mb-0"><i class="bi bi-chat-text me-2"></i>Notes</h6>
            </div>
            <div class="detail-card-body">
                <p class="small text-muted mb-0"><?= nl2br(htmlspecialchars($commande['notes'])) ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- INFOS CLIENT & LIVRAISON -->
    <div class="col-lg-4">
        <div class="detail-card mb-4">
            <div class="detail-card-header">
                <h6 class="fw-bold mb-0"><i class="bi bi-person-fill text-success me-2"></i>Client</h6>
            </div>
            <div class="detail-card-body">
                <div class="info-row">
                    <span class="info-label">Nom complet</span>
                    <span class="info-value"><?= htmlspecialchars($commande['client'] ?? '—') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email</span>
                    <span class="info-value"><?= htmlspecialchars($commande['email_client'] ?? '—') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Téléphone</span>
                    <span class="info-value"><?= htmlspecialchars($commande['tel_client'] ?? $commande['telephone'] ?? '—') ?></span>
                </div>
            </div>
        </div>

        <div class="detail-card mb-4">
            <div class="detail-card-header">
                <h6 class="fw-bold mb-0"><i class="bi bi-geo-alt-fill text-success me-2"></i>Adresse de livraison</h6>
            </div>
            <div class="detail-card-body">
                <div class="info-row">
                    <span class="info-label">Adresse</span>
                    <span class="info-value"><?= htmlspecialchars($commande['rue'] ?? $commande['adresse'] ?? '—') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Ville</span>
                    <span class="info-value"><?= htmlspecialchars($commande['ville'] ?? '—') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Code postal</span>
                    <span class="info-value"><?= htmlspecialchars($commande['code_postal'] ?? '—') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Pays</span>
                    <span class="info-value"><?= htmlspecialchars($commande['pays'] ?? 'Sénégal') ?></span>
                </div>
            </div>
        </div>

        <div class="detail-card">
            <div class="detail-card-header">
                <h6 class="fw-bold mb-0"><i class="bi bi-credit-card-fill text-success me-2"></i>Paiement</h6>
            </div>
            <div class="detail-card-body">
                <div class="info-row">
                    <span class="info-label">Mode de paiement</span>
                    <span class="info-value"><?= htmlspecialchars($commande['mode_paiement'] ?? '—') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Statut</span>
                    <span class="info-value">
                        <span class="badge-statut badge-<?= $commande['statut_paiement'] ?? 'en_attente' ?>">
                            <?php
                            $paiementLabels = [
                                'en_attente' => 'En attente',
                                'paye' => 'Payé',
                                'echoue' => 'Échoué',
                                'rembourse' => 'Remboursé'
                            ];
                            echo $paiementLabels[$commande['statut_paiement'] ?? 'en_attente'];
                            ?>
                        </span>
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Récapitulatif -->
        <div class="detail-card mt-4">
            <div class="detail-card-header">
                <h6 class="fw-bold mb-0"><i class="bi bi-receipt me-2"></i>Récapitulatif</h6>
            </div>
            <div class="detail-card-body">
                <div class="info-row">
                    <span class="info-label">Sous-total</span>
                    <span class="info-value"><?= formatFCFA((int)($commande['montant_total'] ?? 0)) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Livraison</span>
                    <span class="info-value">Gratuite</span>
                </div>
                <div class="info-row border-top pt-2 mt-2">
                    <span class="fw-bold">Total</span>
                    <span class="fw-bold text-success fs-6"><?= formatFCFA((int)($commande['montant_total'] ?? 0)) ?></span>
                </div>
            </div>
        </div>
    </div>

</div>

<?php include view_path('admin/layouts/footer.php'); ?>