<?php
$pageTitle = 'Commandes - Admin';
$adminPage = 'commandes';
include view_path('admin/layouts/header.php');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">Toutes les commandes (<?= count($commandes) ?>)</h5>
</div>

<div class="admin-table">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Numéro</th>
                    <th>Client</th>
                    <th>Email</th>
                    <th>Montant</th>
                    <th>Statut</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($commandes as $cmd) : ?>
                <tr>
                    <td class="fw-semibold small text-success">
                        <?= htmlspecialchars($cmd['numero_commande']) ?>
                    </td>
                    <td class="small"><?= htmlspecialchars($cmd['client']) ?></td>
                    <td class="text-muted small"><?= htmlspecialchars($cmd['email_client']) ?></td>
                    <td class="fw-semibold small"><?= formatFCFA((int)$cmd['montant_total']) ?></td>
                    <td>
                        <span class="badge-statut badge-<?= $cmd['statut'] ?>">
                            <?= ucfirst(str_replace('_', ' ', $cmd['statut'])) ?>
                        </span>
                    </td>
                    <td class="text-muted small">
                        <?= date('d/m/Y H:i', strtotime($cmd['date_commande'])) ?>
                    </td>
                    <td>
                        <a href="<?= url('admin/commande_detail.php?id=' . $cmd['id_commande']) ?>"
                           class="btn btn-sm btn-outline-success rounded-2">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($commandes)) : ?>
                <tr><td colspan="7" class="text-center text-muted py-5">Aucune commande</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include view_path('admin/layouts/footer.php'); ?>