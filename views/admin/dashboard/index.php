<?php

/**
 * Dashboard - Admin
 * 
 * @var array<string, int|string> $stats Statistiques clés
 * @var array<int, array> $dernieresCommandes Liste des dernières commandes
 * @var array<int, array> $plusVendus Liste des produits les plus vendus
 */
$pageTitle = 'Dashboard - Admin';
$adminPage = 'dashboard';
include view_path('admin/layouts/header.php');
?>

<!-- STATS -->
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="text-muted small mb-1">Commandes en attente</p>
                    <h3 class="fw-bold mb-0"><?= $stats['commandes_attente'] ?? 0 ?></h3>
                </div>
                <div class="stat-icon bg-warning bg-opacity-10">
                    <i class="bi bi-clock text-warning"></i>
                </div>
            </div>
            <a href="<?= url('admin/commandes.php') ?>" class="text-warning small text-decoration-none mt-2 d-block">
                Voir les commandes →
            </a>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="text-muted small mb-1">CA du mois</p>
                    <h3 class="fw-bold mb-0"><?= formatFCFA((int)($stats['ca_mois'] ?? 0)) ?></h3>
                </div>
                <div class="stat-icon bg-success bg-opacity-10">
                    <i class="bi bi-cash-stack text-success"></i>
                </div>
            </div>
            <p class="text-muted small mt-2 mb-0">
                Total : <?= formatFCFA((int)($stats['ca_total'] ?? 0)) ?>
            </p>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="text-muted small mb-1">Produits actifs</p>
                    <h3 class="fw-bold mb-0"><?= $stats['produits_actifs'] ?? 0 ?></h3>
                </div>
                <div class="stat-icon bg-primary bg-opacity-10">
                    <i class="bi bi-box-seam text-primary"></i>
                </div>
            </div>
            <a href="<?= url('admin/produits.php') ?>" class="text-primary small text-decoration-none mt-2 d-block">
                Gérer les produits →
            </a>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="text-muted small mb-1">Clients inscrits</p>
                    <h3 class="fw-bold mb-0"><?= $stats['clients_total'] ?? 0 ?></h3>
                </div>
                <div class="stat-icon bg-info bg-opacity-10">
                    <i class="bi bi-people text-info"></i>
                </div>
            </div>
            <a href="<?= url('admin/utilisateurs.php') ?>" class="text-info small text-decoration-none mt-2 d-block">
                Voir les clients →
            </a>
        </div>
    </div>
</div>

<div class="row g-4">

    <!-- DERNIÈRES COMMANDES -->
    <div class="col-lg-8">
        <div class="admin-table">
            <div class="d-flex justify-content-between align-items-center p-4 border-bottom">
                <h6 class="fw-bold mb-0">Dernières commandes</h6>
                <a href="<?= url('admin/commandes.php') ?>" class="btn btn-sm btn-outline-success rounded-3">
                    Voir tout
                </a>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead class="admin-table">
                        <tr>
                            <th>Numéro</th>
                            <th>Client</th>
                            <th>Montant</th>
                            <th>Statut</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dernieresCommandes as $cmd) : ?>
                        <tr>
                            <td>
                                <a href="<?= url('admin/commande_detail.php?id=' . $cmd['id_commande']) ?>"
                                   class="text-success fw-semibold text-decoration-none small">
                                    <?= htmlspecialchars($cmd['numero_commande']) ?>
                                </a>
                            </td>
                            <td class="small"><?= htmlspecialchars($cmd['client'] ?? '—') ?></td>
                            <td class="fw-semibold small"><?= formatFCFA((int)($cmd['montant_total'] ?? 0)) ?></td>
                            <td>
                                <?php
                                $statut = $cmd['statut'] ?? 'inconnu';
                                $badgeClass = match($statut) {
                                    'en_attente' => 'warning',
                                    'confirmee' => 'info', 
                                    'livree' => 'success',
                                    'annulee' => 'danger',
                                    default => 'secondary'
                                };
                                ?>
                                <span class="badge-statut badge-<?= $badgeClass ?>">
                                    <?= ucfirst(str_replace('_', ' ', $statut)) ?>
                                </span>
                            </td>
                            <td class="text-muted small">
                                <?= date('d/m/Y', strtotime($cmd['date_commande'])) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($dernieresCommandes)) : ?>
                        <td><td colspan="5" class="text-center text-muted py-4">Aucune commande</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- PRODUITS LES PLUS VENDUS -->
    <div class="col-lg-4">
        <div class="admin-table h-100">
            <div class="p-4 border-bottom">
                <h6 class="fw-bold mb-0">Produits les + vendus</h6>
            </div>
            <div class="p-3">
                <?php if (empty($plusVendus)) : ?>
                    <p class="text-muted small text-center py-3">Aucune vente pour l'instant</p>
                <?php else : ?>
                    <?php foreach ($plusVendus as $i => $p) : ?>
                    <div class="d-flex align-items-center gap-3 py-2 <?= $i < count($plusVendus) - 1 ? 'border-bottom' : '' ?>">
                        <div class="rounded-circle bg-success bg-opacity-10 text-success fw-bold d-flex align-items-center justify-content-center"
                             style="width: 32px; height: 32px; font-size: 0.8rem; flex-shrink: 0;">
                            <?= $i + 1 ?>
                        </div>
                        <div class="flex-grow-1">
                            <div class="small fw-semibold"><?= htmlspecialchars($p['nom']) ?></div>
                            <div class="text-muted" style="font-size: 0.75rem;"><?= $p['total_vendus'] ?? 0 ?> vendus</div>
                        </div>
                        <span class="text-success fw-bold small"><?= formatFCFA((int)($p['prix'] ?? 0)) ?></span>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<?php include view_path('admin/layouts/footer.php'); ?>