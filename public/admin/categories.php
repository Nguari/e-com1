<?php
require_once dirname(__DIR__, 2) . '/config/config.php';

use App\Config\Database;
use App\Utils\Auth;

if (!Auth::check() || !Auth::isAdmin()) {
    header('Location: ' . url('login.php'));
    exit();
}

$db = Database::getInstance()->getConnection();

// Suppression
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Vérifier si des produits utilisent cette catégorie
    $check = $db->prepare("SELECT COUNT(*) FROM produits WHERE id_categorie = :id");
    $check->execute([':id' => $id]);
    $count = $check->fetchColumn();
    
    if ($count > 0) {
        $_SESSION['flash_error'] = "Impossible de supprimer cette catégorie. $count produit(s) y sont associés.";
    } else {
        $stmt = $db->prepare("DELETE FROM categories WHERE id_categorie = :id");
        $stmt->execute([':id' => $id]);
        $_SESSION['flash_success'] = "Catégorie supprimée avec succès !";
    }
    header('Location: ' . url('admin/categories.php'));
    exit();
}

// Récupérer les catégories
$stmt = $db->query("SELECT c.*, COUNT(p.id_produit) as nb_produits 
                    FROM categories c
                    LEFT JOIN produits p ON c.id_categorie = p.id_categorie
                    GROUP BY c.id_categorie
                    ORDER BY c.nom");
$categories = $stmt->fetchAll();

$pageTitle = 'Catégories - Admin';
$adminPage = 'categories';
include view_path('admin/layouts/header.php');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">Gestion des catégories</h5>
    <button type="button" class="btn btn-success rounded-3" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="bi bi-plus-lg me-1"></i>Ajouter une catégorie
    </button>
</div>

<?php if (isset($_SESSION['flash_success'])): ?>
    <div class="alert alert-success"><?= $_SESSION['flash_success'] ?></div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>
<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger"><?= $_SESSION['flash_error'] ?></div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<div class="admin-table">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Slug</th>
                    <th>Produits</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $cat): ?>
                <tr>
                    <td class="text-muted small">#<?= $cat['id_categorie'] ?></td>
                    <td class="fw-semibold small"><?= htmlspecialchars($cat['nom']) ?></td>
                    <td class="text-muted small"><?= htmlspecialchars($cat['slug'] ?? '—') ?></td>
                    <td class="small"><?= $cat['nb_produits'] ?> produit(s)</td>
                    <td>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-primary rounded-2 edit-btn" 
                                    data-id="<?= $cat['id_categorie'] ?>"
                                    data-nom="<?= htmlspecialchars($cat['nom']) ?>"
                                    data-slug="<?= htmlspecialchars($cat['slug'] ?? '') ?>">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <a href="?delete=<?= $cat['id_categorie'] ?>" 
                               class="btn btn-sm btn-outline-danger rounded-2"
                               onclick="return confirm('Supprimer cette catégorie ?')">
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

<!-- Modal Ajout -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="categories_save.php">
                <div class="modal-header">
                    <h6 class="fw-bold mb-0">Ajouter une catégorie</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nom</label>
                        <input type="text" name="nom" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Slug</label>
                        <input type="text" name="slug" class="form-control" placeholder="auto-généré">
                        <small class="text-muted">Laissez vide pour auto-génération</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success rounded-3">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Modification -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="categories_update.php">
                <div class="modal-header">
                    <h6 class="fw-bold mb-0">Modifier la catégorie</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nom</label>
                        <input type="text" name="nom" id="edit_nom" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Slug</label>
                        <input type="text" name="slug" id="edit_slug" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success rounded-3">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('edit_id').value = btn.dataset.id;
            document.getElementById('edit_nom').value = btn.dataset.nom;
            document.getElementById('edit_slug').value = btn.dataset.slug;
            new bootstrap.Modal(document.getElementById('editModal')).show();
        });
    });
</script>

<?php include view_path('admin/layouts/footer.php'); ?>