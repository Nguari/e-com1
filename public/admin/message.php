<?php
// public/admin/messages.php
require_once dirname(__DIR__, 2) . '/config/config.php';
\App\Utils\AdminMiddleware::check();

use App\Config\Database;

$db     = Database::getInstance()->getConnection();
$action = $_GET['action'] ?? '';
$id     = (int)($_GET['id'] ?? 0);

// =========================================
// ACTIONS
// =========================================

// Marquer comme lu
if ($action === 'lire' && $id > 0) {
    $db->prepare("UPDATE contacts SET lu = 1 WHERE id_contact = :id")
       ->execute([':id' => $id]);
    header('Location: ' . url('admin/messages.php'));
    exit();
}

// Supprimer
if ($action === 'supprimer' && $id > 0) {
    $db->prepare("DELETE FROM contacts WHERE id_contact = :id")
       ->execute([':id' => $id]);
    $_SESSION['flash_success'] = "Message supprimé.";
    header('Location: ' . url('admin/messages.php'));
    exit();
}

// Marquer tous comme lus
if ($action === 'tout_lire') {
    $db->exec("UPDATE contacts SET lu = 1");
    $_SESSION['flash_success'] = "Tous les messages marqués comme lus.";
    header('Location: ' . url('admin/messages.php'));
    exit();
}

// Voir un message en détail (marquer comme lu automatiquement)
$messageDetail = null;
if ($action === 'voir' && $id > 0) {
    $stmt = $db->prepare("SELECT * FROM contacts WHERE id_contact = :id");
    $stmt->execute([':id' => $id]);
    $messageDetail = $stmt->fetch();
    if ($messageDetail) {
        $db->prepare("UPDATE contacts SET lu = 1 WHERE id_contact = :id")
           ->execute([':id' => $id]);
    }
}

// =========================================
// CHARGEMENT DES MESSAGES
// =========================================
$filtre = $_GET['filtre'] ?? 'tous';

$sql = "SELECT * FROM contacts";
if ($filtre === 'non_lus') {
    $sql .= " WHERE lu = 0";
} elseif ($filtre === 'lus') {
    $sql .= " WHERE lu = 1";
}
$sql .= " ORDER BY created_at DESC";

$messages = $db->query($sql)->fetchAll();

// Compteurs
$nbTotal   = (int)$db->query("SELECT COUNT(*) FROM contacts")->fetchColumn();
$nbNonLus  = (int)$db->query("SELECT COUNT(*) FROM contacts WHERE lu = 0")->fetchColumn();

$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_success']);

$pageTitle = 'Messages - Admin';
$adminPage = 'messages';
include view_path('admin/layouts/header.php');
?>

<?php if ($flashSuccess) : ?>
<div class="alert alert-success rounded-3 d-flex align-items-center gap-2 mb-4">
    <i class="bi bi-check-circle-fill"></i><?= htmlspecialchars($flashSuccess) ?>
</div>
<?php endif; ?>

<!-- EN-TÊTE -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h5 class="fw-bold mb-0">Messages reçus</h5>
        <p class="text-muted small mb-0">
            <?= $nbTotal ?> message<?= $nbTotal > 1 ? 's' : '' ?> au total —
            <span class="text-danger fw-semibold"><?= $nbNonLus ?> non lu<?= $nbNonLus > 1 ? 's' : '' ?></span>
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <?php if ($nbNonLus > 0) : ?>
        <a href="<?= url('admin/message.php?action=tout_lire') ?>"
           class="btn btn-outline-success btn-sm rounded-3">
            <i class="bi bi-check-all me-1"></i>Tout marquer comme lu
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- FILTRES -->
<div class="d-flex gap-2 mb-4">
    <a href="<?= url('admin/message.php') ?>"
       class="btn btn-sm rounded-3 <?= $filtre === 'tous' ? 'btn-success' : 'btn-outline-secondary' ?>">
        Tous (<?= $nbTotal ?>)
    </a>
    <a href="<?= url('admin/message.php?filtre=non_lus') ?>"
       class="btn btn-sm rounded-3 <?= $filtre === 'non_lus' ? 'btn-danger' : 'btn-outline-danger' ?>">
        Non lus (<?= $nbNonLus ?>)
    </a>
    <a href="<?= url('admin/message.php?filtre=lus') ?>"
       class="btn btn-sm rounded-3 <?= $filtre === 'lus' ? 'btn-secondary' : 'btn-outline-secondary' ?>">
        Lus (<?= $nbTotal - $nbNonLus ?>)
    </a>
</div>

<div class="row g-4">

    <!-- LISTE DES MESSAGES -->
    <div class="col-lg-<?= $messageDetail ? '5' : '12' ?>">
        <div class="admin-table">
            <?php if (empty($messages)) : ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                    Aucun message
                </div>
            <?php else : ?>
            <div class="list-group list-group-flush">
                <?php foreach ($messages as $msg) : ?>
                <a href="<?= url('admin/message.php?action=voir&id=' . $msg['id_contact'] . ($filtre !== 'tous' ? '&filtre=' . $filtre : '')) ?>"
                   class="list-group-item list-group-item-action px-4 py-3
                          <?= !$msg['lu'] ? 'border-start border-4 border-danger' : '' ?>
                          <?= isset($messageDetail['id_contact']) && $messageDetail['id_contact'] == $msg['id_contact'] ? 'active' : '' ?>">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1 me-3" style="overflow:hidden;">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <?php if (!$msg['lu']) : ?>
                                    <span class="badge bg-danger" style="font-size:.6rem;">Nouveau</span>
                                <?php endif; ?>
                                <span class="fw-semibold small">
                                    <?= htmlspecialchars($msg['prenom'] . ' ' . $msg['nom']) ?>
                                </span>
                            </div>
                            <p class="text-muted small mb-1 text-truncate">
                                <strong><?= htmlspecialchars($msg['sujet'] ?? 'Sans sujet') ?></strong>
                            </p>
                            <p class="small mb-0 text-truncate opacity-75">
                                <?= htmlspecialchars(mb_substr($msg['message'], 0, 60)) ?>...
                            </p>
                        </div>
                        <div class="text-end flex-shrink-0">
                            <small class="text-muted d-block" style="font-size:.7rem;">
                                <?= date('d/m/Y', strtotime($msg['created_at'])) ?>
                            </small>
                            <small class="text-muted d-block" style="font-size:.7rem;">
                                <?= date('H:i', strtotime($msg['created_at'])) ?>
                            </small>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- DÉTAIL DU MESSAGE -->
    <?php if ($messageDetail) : ?>
    <div class="col-lg-7">
        <div class="card border-0 rounded-4 shadow-sm h-100">
            <div class="card-body p-4">

                <!-- EN-TÊTE MESSAGE -->
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <div>
                        <h5 class="fw-bold mb-1">
                            <?= htmlspecialchars($messageDetail['sujet'] ?? 'Sans sujet') ?>
                        </h5>
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center fw-bold"
                                 style="width:36px; height:36px; font-size:.85rem; flex-shrink:0;">
                                <?= strtoupper(substr($messageDetail['prenom'], 0, 1)) ?>
                            </div>
                            <div>
                                <div class="fw-semibold small">
                                    <?= htmlspecialchars($messageDetail['prenom'] . ' ' . $messageDetail['nom']) ?>
                                </div>
                                <div class="text-muted" style="font-size:.75rem;">
                                    <?= htmlspecialchars($messageDetail['email']) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <small class="text-muted">
                        <?= date('d/m/Y à H:i', strtotime($messageDetail['created_at'])) ?>
                    </small>
                </div>

                <!-- INFOS -->
                <div class="row g-2 mb-4">
                    <div class="col-md-6">
                        <div class="bg-light rounded-3 p-3">
                            <small class="text-muted d-block mb-1">Email</small>
                            <a href="mailto:<?= htmlspecialchars($messageDetail['email']) ?>"
                               class="text-success text-decoration-none small fw-semibold">
                                <?= htmlspecialchars($messageDetail['email']) ?>
                            </a>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="bg-light rounded-3 p-3">
                            <small class="text-muted d-block mb-1">Sujet</small>
                            <span class="small fw-semibold">
                                <?= htmlspecialchars($messageDetail['sujet'] ?? '—') ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- MESSAGE -->
                <div class="bg-light rounded-3 p-4 mb-4" style="min-height:150px;">
                    <p class="mb-0" style="white-space:pre-wrap; line-height:1.7;">
                        <?= htmlspecialchars($messageDetail['message']) ?>
                    </p>
                </div>

                <!-- ACTIONS -->
                <div class="d-flex gap-2 flex-wrap">
                    <!-- Répondre par email -->
                    <a href="mailto:<?= htmlspecialchars($messageDetail['email']) ?>?subject=Re: <?= urlencode($messageDetail['sujet'] ?? '') ?>"
                       class="btn btn-success rounded-3">
                        <i class="bi bi-reply me-2"></i>Répondre
                    </a>

                    <!-- Supprimer -->
                    <a href="<?= url('admin/message.php?action=supprimer&id=' . $messageDetail['id_contact']) ?>"
                       class="btn btn-outline-danger rounded-3"
                       onclick="return confirm('Supprimer ce message définitivement ?')">
                        <i class="bi bi-trash me-2"></i>Supprimer
                    </a>

                    <!-- Retour liste -->
                    <a href="<?= url('admin/message.php') ?>"
                       class="btn btn-outline-secondary rounded-3">
                        <i class="bi bi-arrow-left me-1"></i>Retour
                    </a>
                </div>

            </div>
        </div>
    </div>
    <?php endif; ?>

</div>

<?php include view_path('admin/layouts/footer.php'); ?>