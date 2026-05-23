<?php
// public/admin/avis.php
require_once dirname(__DIR__, 2) . '/config/config.php';
\App\Utils\AdminMiddleware::check();

use App\Config\Database;

$db     = Database::getInstance()->getConnection();
$action = $_GET['action'] ?? '';
$id     = (int)($_GET['id'] ?? 0);

// Actions
if ($action === 'valider' && $id > 0) {
    $db->prepare("UPDATE avis SET valide = 1 WHERE id_avis = :id")->execute([':id' => $id]);
    $_SESSION['flash_success'] = "Avis validé et publié.";
    header('Location: ' . url('admin/avis.php'));
    exit();
}

if ($action === 'rejeter' && $id > 0) {
    $db->prepare("DELETE FROM avis WHERE id_avis = :id")->execute([':id' => $id]);
    $_SESSION['flash_success'] = "Avis supprimé.";
    header('Location: ' . url('admin/avis.php'));
    exit();
}

// Charger les avis
$avisEnAttente = $db->query("
    SELECT a.*, p.nom AS produit_nom,
           CONCAT(u.prenom, ' ', u.nom) AS auteur
    FROM avis a
    JOIN produits p ON a.id_produit = p.id_produit
    JOIN utilisateurs u ON a.id_utilisateur = u.id_utilisateur
    WHERE a.valide = 0
    ORDER BY a.date_avis DESC
")->fetchAll();

$avisValides = $db->query("
    SELECT a.*, p.nom AS produit_nom,
           CONCAT(u.prenom, ' ', u.nom) AS auteur
    FROM avis a
    JOIN produits p ON a.id_produit = p.id_produit
    JOIN utilisateurs u ON a.id_utilisateur = u.id_utilisateur
    WHERE a.valide = 1
    ORDER BY a.date_avis DESC
    LIMIT 20
")->fetchAll();

$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_success']);

$pageTitle = 'Avis Clients - Admin';
$adminPage = 'avis';
include view_path('admin/layouts/header.php');
?>

<?php if ($flashSuccess) : ?>
<div class="alert alert-success rounded-3 d-flex align-items-center gap-2 mb-4">
    <i class="bi bi-check-circle-fill"></i><?= htmlspecialchars($flashSuccess) ?>
</div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">Gestion des avis</h5>
    <span class="badge bg-warning text-dark px-3 py-2">
        <?= count($avisEnAttente) ?> en attente de validation
    </span>
</div>

<!-- AVIS EN ATTENTE -->
<?php if (!empty($avisEnAttente)) : ?>
<div class="admin-table mb-4">
    <div class="p-4 border-bottom d-flex align-items-center gap-2">
        <i class="bi bi-clock-history text-warning fs-5"></i>
        <h6 class="fw-bold mb-0">En attente (<?= count($avisEnAttente) ?>)</h6>
    </div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Auteur</th>
                    <th>Produit</th>
                    <th>Note</th>
                    <th>Commentaire</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($avisEnAttente as $avis) : ?>
                <tr>
                    <td class="small fw-semibold"><?= htmlspecialchars($avis['auteur']) ?></td>
                    <td class="small text-muted"><?= htmlspecialchars($avis['produit_nom']) ?></td>
                    <td>
                        <?php for ($i = 1; $i <= 5; $i++) : ?>
                            <i class="bi bi-star<?= $i <= $avis['note'] ? '-fill text-warning' : ' text-muted' ?>"
                               style="font-size:.75rem;"></i>
                        <?php endfor; ?>
                    </td>
                    <td class="small" style="max-width:250px;">
                        <?= htmlspecialchars(mb_substr($avis['commentaire'], 0, 100)) ?>
                        <?= strlen($avis['commentaire']) > 100 ? '...' : '' ?>
                    </td>
                    <td class="text-muted small">
                        <?= date('d/m/Y', strtotime($avis['date_avis'])) ?>
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="<?= url('admin/avis.php?action=valider&id=' . $avis['id_avis']) ?>"
                               class="btn btn-sm btn-success rounded-2">
                                <i class="bi bi-check-lg me-1"></i>Valider
                            </a>
                            <a href="<?= url('admin/avis.php?action=rejeter&id=' . $avis['id_avis']) ?>"
                               class="btn btn-sm btn-outline-danger rounded-2"
                               onclick="return confirm('Supprimer cet avis ?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php else : ?>
<div class="alert alert-success rounded-3 mb-4">
    <i class="bi bi-check-circle-fill me-2"></i>Aucun avis en attente de validation.
</div>
<?php endif; ?>

<!-- AVIS VALIDÉS -->
<div class="admin-table">
    <div class="p-4 border-bottom d-flex align-items-center gap-2">
        <i class="bi bi-check-circle text-success fs-5"></i>
        <h6 class="fw-bold mb-0">Avis publiés (<?= count($avisValides) ?>)</h6>
    </div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Auteur</th>
                    <th>Produit</th>
                    <th>Note</th>
                    <th>Commentaire</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($avisValides as $avis) : ?>
                <tr>
                    <td class="small fw-semibold"><?= htmlspecialchars($avis['auteur']) ?></td>
                    <td class="small text-muted"><?= htmlspecialchars($avis['produit_nom']) ?></td>
                    <td>
                        <?php for ($i = 1; $i <= 5; $i++) : ?>
                            <i class="bi bi-star<?= $i <= $avis['note'] ? '-fill text-warning' : ' text-muted' ?>"
                               style="font-size:.75rem;"></i>
                        <?php endfor; ?>
                    </td>
                    <td class="small" style="max-width:250px;">
                        <?= htmlspecialchars(mb_substr($avis['commentaire'], 0, 100)) ?>
                        <?= strlen($avis['commentaire']) > 100 ? '...' : '' ?>
                    </td>
                    <td class="text-muted small">
                        <?= date('d/m/Y', strtotime($avis['date_avis'])) ?>
                    </td>
                    <td>
                        <a href="<?= url('admin/avis.php?action=rejeter&id=' . $avis['id_avis']) ?>"
                           class="btn btn-sm btn-outline-danger rounded-2"
                           onclick="return confirm('Supprimer cet avis ?')">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($avisValides)) : ?>
                <tr><td colspan="6" class="text-center text-muted py-4">Aucun avis publié</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include view_path('admin/layouts/footer.php'); ?>