<?php
$pageTitle = 'Commande #' . ($commande['numero_commande'] ?? '') . ' - Admin';
$adminPage = 'commandes';
include view_path('admin/layouts/header.php');

$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_success']);
?>

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
            Commande <?= htmlspecialchars($commande['numero_commande']) ?>
        </h5>
    </div>
    <!-- CHANGER LE STATUT -->
    <form action="<?= url('admin/commande_statut.php') ?>" method="POST" class="d-flex gap-2">
        <input type="hidden" name="id" value="<?= $commande['id_commande'] ?>">
        <select name="statut" class="form-select form-select-sm rounded-3" style="width: auto;">
            <?php
            $statuts = ['en_attente', 'confirmee', 'en_preparation', 'expediee', 'livree', 'annulee'];
            foreach ($statuts as $s) :
            ?>
            <option value="<?= $s ?>" <?= $commande['statut'] === $s ? 'selected' : '' ?>>
                <?= ucfirst(str_replace('_', ' ', $s)) ?>
            </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-success btn-sm rounded-3">Mettre à jour</button>
    </form>
</div>

<div class="row g-4">

    <!-- ARTICLES COMMANDÉS -->
    <div class="col-lg-8">
        <div class="admin-table mb-4">
            <div class="p-4 border-bottom">
                <h6 class="fw-bold mb-0">Articles commandés</h6>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th>Prix unitaire</th>
                            <th>Qté</th>
                            <th>Sous-total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($commande['lignes'] as $ligne) : ?>
                        <tr>
                            <td class="small fw-semibold"><?= htmlspecialchars($ligne['nom_produit']) ?></td>
                            <td class="small"><?= formatFCFA((int)$ligne['prix_unitaire']) ?></td>
                            <td><span class="badge bg-light text-dark"><?= $ligne['quantite'] ?></span></td>
                            <td class="fw-bold text-success small"><?= formatFCFA((int)$ligne['sous_total']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="border-top">
                            <td colspan="3" class="fw-bold text-end">Total</td>
                            <td class="fw-bold text-success"><?= formatFCFA((int)$commande['montant_total']) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- INFOS CLIENT & LIVRAISON -->
    <div class="col-lg-4">
        <div class="card border-0 rounded-4 shadow-sm mb-4">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-person-fill text-success me-2"></i>Client</h6>
                <p class="mb-1 small fw-semibold"><?= htmlspecialchars($commande['client']) ?></p>
                <p class="mb-1 small text-muted"><?= htmlspecialchars($commande['email_client']) ?></p>
                <p class="mb-0 small text-muted"><?= htmlspecialchars($commande['tel_client'] ?? '—') ?></p>
            </div>
        </div>

        <div class="card border-0 rounded-4 shadow-sm mb-4">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-geo-alt-fill text-success me-2"></i>Livraison</h6>
                <p class="mb-1 small"><?= htmlspecialchars($commande['rue'] ?? '—') ?></p>
                <p class="mb-1 small"><?= htmlspecialchars($commande['ville'] ?? '—') ?></p>
                <p class="mb-0 small"><?= htmlspecialchars($commande['pays'] ?? 'Sénégal') ?></p>
            </div>
        </div>

        <div class="card border-0 rounded-4 shadow-sm">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-credit-card-fill text-success me-2"></i>Paiement</h6>
                <p class="mb-1 small fw-semibold"><?= htmlspecialchars($commande['mode_paiement'] ?? '—') ?></p>
                <span class="badge-statut badge-<?= $commande['statut_paiement'] ?? 'en_attente' ?>">
                    <?= ucfirst($commande['statut_paiement'] ?? 'en_attente') ?>
                </span>
            </div>
        </div>
    </div>

</div>

<?php include view_path('admin/layouts/footer.php'); ?>