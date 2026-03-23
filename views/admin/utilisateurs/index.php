<?php
$pageTitle = 'Utilisateurs - Admin';
$adminPage = 'utilisateurs';
include view_path('admin/layouts/header.php');

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error']   ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>

<?php if ($flashSuccess) : ?>
<div class="alert alert-success rounded-3 d-flex align-items-center gap-2 mb-4">
    <i class="bi bi-check-circle-fill"></i><?= htmlspecialchars($flashSuccess) ?>
</div>
<?php endif; ?>

<?php if ($flashError) : ?>
<div class="alert alert-danger rounded-3 d-flex align-items-center gap-2 mb-4">
    <i class="bi bi-exclamation-circle-fill"></i><?= htmlspecialchars($flashError) ?>
</div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">Tous les utilisateurs (<?= count($utilisateurs) ?>)</h5>
</div>

<div class="admin-table">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom complet</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Commandes</th>
                    <th>Total achats</th>
                    <th>Rôle</th>
                    <th>Statut</th>
                    <th>Inscription</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($utilisateurs as $u) : ?>
                <tr>
                    <td class="text-muted small">#<?= $u['id_utilisateur'] ?></td>
                    <td class="fw-semibold small">
                        <?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?>
                    </td>
                    <td class="text-muted small"><?= htmlspecialchars($u['email']) ?></td>
                    <td class="text-muted small"><?= htmlspecialchars($u['telephone'] ?? '—') ?></td>
                    <td class="text-center">
                        <span class="badge bg-light text-dark"><?= $u['nb_commandes'] ?></span>
                    </td>
                    <td class="fw-semibold small text-success">
                        <?= formatFCFA((int)$u['total_achats']) ?>
                    </td>
                    <td>
                        <span class="badge <?= $u['role'] === 'admin' ? 'bg-danger' : 'bg-primary' ?> bg-opacity-10 text-<?= $u['role'] === 'admin' ? 'danger' : 'primary' ?>">
                            <?= ucfirst($u['role']) ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge <?= $u['actif'] ? 'bg-success' : 'bg-secondary' ?> bg-opacity-10 text-<?= $u['actif'] ? 'success' : 'secondary' ?>">
                            <?= $u['actif'] ? 'Actif' : 'Inactif' ?>
                        </span>
                    </td>
                    <td class="text-muted small">
                        <?= date('d/m/Y', strtotime($u['date_inscription'])) ?>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <!-- CHANGER RÔLE -->
                            <a href="<?= url('admin/utilisateur_role.php?id=' . $u['id_utilisateur']) ?>"
                               class="btn btn-sm btn-outline-warning rounded-2"
                               title="<?= $u['role'] === 'admin' ? 'Rétrograder en client' : 'Promouvoir admin' ?>"
                               onclick="return confirm('Changer le rôle de cet utilisateur ?')">
                                <i class="bi bi-shield-<?= $u['role'] === 'admin' ? 'minus' : 'plus' ?>"></i>
                            </a>

                            <!-- ACTIVER/DÉSACTIVER -->
                            <a href="<?= url('admin/utilisateur_toggle.php?id=' . $u['id_utilisateur']) ?>"
                               class="btn btn-sm btn-outline-secondary rounded-2"
                               title="Activer/Désactiver">
                                <i class="bi bi-toggle-<?= $u['actif'] ? 'on' : 'off' ?>"></i>
                            </a>

                            <!-- SUPPRIMER -->
                            <a href="<?= url('admin/utilisateur_delete.php?id=' . $u['id_utilisateur']) ?>"
                               class="btn btn-sm btn-outline-danger rounded-2"
                               title="Supprimer"
                               onclick="return confirm('Supprimer cet utilisateur ?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($utilisateurs)) : ?>
                <tr>
                    <td colspan="10" class="text-center text-muted py-5">Aucun utilisateur</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include view_path('admin/layouts/footer.php'); ?>