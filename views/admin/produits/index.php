<?php
/**
 * Liste des produits - Admin
 * 
 * @var array<int, array> $produits Liste des produits
 * @var string|null $flashSuccess Message de succès
 * @var string|null $flashError Message d'erreur
 */

$pageTitle = 'Produits - Admin';
$adminPage = 'produits';
include view_path('admin/layouts/header.php');

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error']   ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

/**
 * Récupère la première image d'un produit
 */
function getProductImage($produit) {
    // Vérifier les images multiples (JSON)
    if (!empty($produit['images'])) {
        $images = json_decode($produit['images'], true);
        if (is_array($images) && !empty($images)) {
            return '/assets/img/produits/' . $images[0];
        }
    }
    
    // Fallback sur l'image unique
    if (!empty($produit['image'])) {
        return '/assets/img/produits/' . $produit['image'];
    }
    
    return '/assets/img/produits/default.jpg';
}

/**
 * Compte le nombre d'images
 */
function getImageCount($produit) {
    if (!empty($produit['images'])) {
        $images = json_decode($produit['images'], true);
        if (is_array($images)) {
            return count($images);
        }
    }
    return !empty($produit['image']) ? 1 : 0;
}
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
    <h5 class="fw-bold mb-0">Tous les produits (<?= count($produits) ?>)</h5>
    <a href="<?= url('admin/produit_add.php') ?>" class="btn btn-success rounded-3">
        <i class="bi bi-plus-lg me-1"></i>Ajouter un produit
    </a>
</div>

<div class="admin-table">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th style="width: 80px">Image(s)</th>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Catégorie</th>
                    <th>Prix</th>
                    <th>Stock</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produits as $produit) : 
                    $imageUrl = getProductImage($produit);
                    $imageCount = getImageCount($produit);
                ?>
                <tr>
                    <td style="width: 80px">
                        <div style="position: relative; display: inline-block;">
                            <img src="<?= $imageUrl ?>" 
                                 alt="<?= htmlspecialchars($produit['nom']) ?>"
                                 style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px; background: #f8f9fa; display: block;"
                                 loading="lazy"
                                 onerror="this.src='/assets/img/produits/default.jpg'">
                            <?php if ($imageCount > 1) : ?>
                                <span class="position-absolute bottom-0 end-0 bg-dark bg-opacity-75 text-white rounded-pill px-1" style="font-size: 9px;">
                                    +<?= $imageCount - 1 ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="text-muted small">#<?= $produit['id_produit'] ?></td>
                    <td class="fw-semibold small"><?= htmlspecialchars($produit['nom']) ?></td>
                    <td class="text-muted small"><?= htmlspecialchars($produit['categorie_nom'] ?? '—') ?></td>
                    <td class="fw-semibold small text-success"><?= formatFCFA((int)$produit['prix']) ?></td>
                    <td>
                        <span class="badge <?= $produit['stock'] <= 5 ? 'bg-danger' : 'bg-success' ?> bg-opacity-10 text-<?= $produit['stock'] <= 5 ? 'danger' : 'success' ?>">
                            <?= $produit['stock'] ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge <?= $produit['actif'] ? 'bg-success' : 'bg-secondary' ?> bg-opacity-10 text-<?= $produit['actif'] ? 'success' : 'secondary' ?>">
                            <?= $produit['actif'] ? 'Actif' : 'Inactif' ?>
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="<?= url('admin/produit_edit.php?id=' . $produit['id_produit']) ?>"
                               class="btn btn-sm btn-outline-primary rounded-2" title="Modifier">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="<?= url('admin/produit_toggle.php?id=' . $produit['id_produit']) ?>"
                               class="btn btn-sm btn-outline-warning rounded-2" title="Activer/Désactiver">
                                <i class="bi bi-toggle-on"></i>
                            </a>
                            <a href="<?= url('admin/produit_delete.php?id=' . $produit['id_produit']) ?>"
                               class="btn btn-sm btn-outline-danger rounded-2" title="Supprimer"
                               onclick="return confirm('Supprimer ce produit ?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($produits)) : ?>
                <tr>
                    <td colspan="8" class="text-center text-muted py-5">Aucun produit</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include view_path('admin/layouts/footer.php'); ?>