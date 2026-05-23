<?php
/**
 * Formulaire d'ajout/modification de produit
 * 
 * @var array|null $produit Données du produit (null pour l'ajout, tableau pour la modification)
 * @var string|null $flashError Message d'erreur flash
 */

$pageTitle = isset($produit) ? 'Modifier un produit - Admin' : 'Ajouter un produit - Admin';
$adminPage = 'produits';
include view_path('admin/layouts/header.php');

$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);

// Token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$isEdit = isset($produit) && is_array($produit);
$action = $isEdit
    ? url('admin/produit_edit.php?id=' . ($produit['id_produit'] ?? 0))
    : url('admin/produit_add.php');
?>

<div class="mb-4">
    <a href="<?= url('admin/produits.php') ?>" class="text-muted text-decoration-none small">
        ← Retour aux produits
    </a>
    <h5 class="fw-bold mb-0 mt-1">
        <?= $isEdit ? 'Modifier : ' . htmlspecialchars($produit['nom'] ?? '') : 'Ajouter un produit' ?>
    </h5>
</div>

<?php if ($flashError) : ?>
<div class="alert alert-danger rounded-3 d-flex align-items-center gap-2 mb-4">
    <i class="bi bi-exclamation-circle-fill"></i>
    <?= htmlspecialchars($flashError) ?>
</div>
<?php endif; ?>

<div class="card border-0 rounded-4 shadow-sm">
    <div class="card-body p-4">
        <form action="<?= $action ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <div class="row g-3">

                <!-- RÉFÉRENCE -->
                <?php if (!$isEdit) : ?>
                <div class="col-md-4">
                    <label class="form-label fw-medium small">
                        Référence <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="reference"
                           class="form-control bg-light"
                           placeholder="PROD-013"
                           value="<?= htmlspecialchars($_POST['reference'] ?? $produit['reference'] ?? '') ?>"
                           required>
                    <small class="text-muted">La référence doit être unique</small>
                </div>
                <?php else: ?>
                <div class="col-md-4">
                    <label class="form-label fw-medium small">
                        Référence
                    </label>
                    <input type="text" class="form-control bg-light" 
                           value="<?= htmlspecialchars($produit['reference'] ?? '') ?>" disabled>
                    <small class="text-muted">La référence ne peut pas être modifiée</small>
                </div>
                <?php endif; ?>

                <!-- NOM -->
                <div class="col-md-<?= $isEdit ? '8' : '8' ?>">
                    <label class="form-label fw-medium small">
                        Nom du produit <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="nom"
                           class="form-control bg-light"
                           placeholder="Chaise en Chêne"
                           value="<?= htmlspecialchars($produit['nom'] ?? $_POST['nom'] ?? '') ?>"
                           required>
                </div>

                <!-- DESCRIPTION -->
                <div class="col-12">
                    <label class="form-label fw-medium small">Description</label>
                    <textarea name="description" class="form-control bg-light" rows="3"
                              placeholder="Description du produit..."><?= htmlspecialchars($produit['description'] ?? $_POST['description'] ?? '') ?></textarea>
                </div>

                <!-- IMAGES MULTIPLES -->
<div class="col-12">
    <label class="form-label fw-medium small">
        Images du produit <?= !$isEdit ? '<span class="text-danger">*</span>' : '' ?>
    </label>
    
    <!-- Input pour plusieurs fichiers -->
    <input type="file" name="images[]" 
           class="form-control bg-light" 
           accept="image/jpeg,image/png,image/webp,image/gif"
           multiple
           <?= !$isEdit ? 'required' : '' ?>>
    <small class="text-muted">Formats acceptés : JPG, PNG, WEBP, GIF (max 2MB par image). Vous pouvez sélectionner plusieurs images (Ctrl+clic).</small>
    
    <!-- Zone d'aperçu -->
    <div id="imagePreview" class="row g-2 mt-3"></div>
    
    <!-- Images existantes (en modification) -->
    <?php if ($isEdit && !empty($produit['images'])): ?>
        <div class="mt-3">
            <label class="form-label fw-medium small">Images actuelles :</label>
            <div class="row g-2" id="existingImages">
                <?php 
                $images = is_string($produit['images']) ? json_decode($produit['images'], true) : $produit['images'];
                if (is_array($images)):
                    foreach ($images as $index => $img): 
                ?>
                <div class="col-auto position-relative existing-image" data-image="<?= htmlspecialchars($img) ?>">
                    <img src="<?= url('assets/img/produits/' . $img) ?>" 
                         alt="Image produit" 
                         style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;" 
                         class="border">
                    <button type="button" 
                            class="btn btn-sm btn-danger rounded-circle position-absolute top-0 end-0 p-0" 
                            style="width: 20px; height: 20px; font-size: 10px;"
                            onclick="removeExistingImage(this, '<?= htmlspecialchars($img) ?>')">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <?php 
                    endforeach;
                endif; 
                ?>
            </div>
            <input type="hidden" name="existing_images" id="existingImagesInput" value="<?= htmlspecialchars($produit['images'] ?? '') ?>">
        </div>
    <?php endif; ?>
</div>

                <!-- PRIX -->
                <div class="col-md-4">
                    <label class="form-label fw-medium small">
                        Prix (FCFA) <span class="text-danger">*</span>
                    </label>
                    <input type="number" name="prix"
                           class="form-control bg-light"
                           placeholder="30000"
                           value="<?= htmlspecialchars($produit['prix'] ?? $_POST['prix'] ?? '') ?>"
                           min="0" step="1" required>
                </div>

                <!-- PRIX PROMO -->
                <div class="col-md-4">
                    <label class="form-label fw-medium small">Prix promo (FCFA)</label>
                    <input type="number" name="prix_promo"
                           class="form-control bg-light"
                           placeholder="25000"
                           value="<?= htmlspecialchars($produit['prix_promo'] ?? $_POST['prix_promo'] ?? '') ?>"
                           min="0" step="1">
                    <small class="text-muted">Laissez vide si pas de promo</small>
                </div>

                <!-- STOCK -->
                <div class="col-md-4">
                    <label class="form-label fw-medium small">
                        Stock <span class="text-danger">*</span>
                    </label>
                    <input type="number" name="stock"
                           class="form-control bg-light"
                           placeholder="100"
                           value="<?= htmlspecialchars($produit['stock'] ?? $_POST['stock'] ?? '0') ?>"
                           min="0" required>
                </div>

                <!-- CATÉGORIE -->
                <div class="col-md-6">
                    <label class="form-label fw-medium small">Catégorie</label>
                    <select name="id_categorie" class="form-select bg-light">
                        <option value="">— Aucune catégorie —</option>
                        <?php
                        try {
                            $db3 = \App\Config\Database::getInstance()->getConnection();
                            $cats = $db3->query("SELECT * FROM categories ORDER BY nom")->fetchAll();
                            foreach ($cats as $cat) :
                                $selected = (($produit['id_categorie'] ?? '') == $cat['id_categorie']) ? 'selected' : '';
                        ?>
                            <option value="<?= $cat['id_categorie'] ?>" <?= $selected ?>>
                                <?= htmlspecialchars($cat['nom']) ?>
                            </option>
                        <?php endforeach;
                        } catch (\Exception $e) {}
                        ?>
                    </select>
                </div>

                <!-- ACTIF -->
                <div class="col-md-6 d-flex align-items-end">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="actif" id="actif" value="1"
                               <?= (($produit['actif'] ?? 1) == 1) ? 'checked' : '' ?>>
                        <label class="form-check-label fw-medium small" for="actif">
                            Produit actif (visible sur le site)
                        </label>
                    </div>
                </div>

                <!-- BOUTONS -->
                <div class="col-12 d-flex gap-3 pt-2">
                    <button type="submit" class="btn btn-success rounded-3 px-4">
                        <i class="bi bi-check-lg me-2"></i>
                        <?= $isEdit ? 'Enregistrer les modifications' : 'Ajouter le produit' ?>
                    </button>
                    <a href="<?= url('admin/produits.php') ?>"
                       class="btn btn-outline-secondary rounded-3 px-4">
                        Annuler
                    </a>
                </div>

            </div>
        </form>
    </div>
</div>
<script>
// Aperçu des images avant upload
document.querySelector('input[name="images[]"]').addEventListener('change', function(e) {
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';
    
    if (this.files) {
        Array.from(this.files).forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(event) {
                const col = document.createElement('div');
                col.className = 'col-auto';
                col.innerHTML = `
                    <div class="position-relative">
                        <img src="${event.target.result}" 
                             style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;" 
                             class="border">
                        <button type="button" 
                                class="btn btn-sm btn-danger rounded-circle position-absolute top-0 end-0 p-0" 
                                style="width: 20px; height: 20px; font-size: 10px;"
                                onclick="this.parentElement.parentElement.remove()">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                `;
                preview.appendChild(col);
            }
            reader.readAsDataURL(file);
        });
    }
});

// Supprimer une image existante
function removeExistingImage(btn, imageName) {
    if (confirm('Supprimer cette image ?')) {
        const container = btn.closest('.existing-image');
        container.remove();
        
        // Mettre à jour le champ hidden avec les images restantes
        const remainingImages = [];
        document.querySelectorAll('#existingImages .existing-image').forEach(img => {
            remainingImages.push(img.dataset.image);
        });
        document.getElementById('existingImagesInput').value = JSON.stringify(remainingImages);
    }
}
</script>
<?php include view_path('admin/layouts/footer.php'); ?>
