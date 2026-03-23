<?php
$pageTitle = isset($produit) ? 'Modifier un produit - Admin' : 'Ajouter un produit - Admin';
$adminPage = 'produits';
include view_path('admin/layouts/header.php');

$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);

// Token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$isEdit = isset($produit);
$action = $isEdit
    ? url('admin/produit_edit.php?id=' . $produit['id_produit'])
    : url('admin/produit_add.php');
?>

<div class="mb-4">
    <a href="<?= url('admin/produits.php') ?>" class="text-muted text-decoration-none small">
        ← Retour aux produits
    </a>
    <h5 class="fw-bold mb-0 mt-1">
        <?= $isEdit ? 'Modifier : ' . htmlspecialchars($produit['nom']) : 'Ajouter un produit' ?>
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
        <form action="<?= $action ?>" method="POST">
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
                           value="<?= htmlspecialchars($_POST['reference'] ?? '') ?>"
                           required>
                </div>
                <?php endif; ?>

                <!-- NOM -->
                <div class="col-md-<?= $isEdit ? '6' : '8' ?>">
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

                <!-- PRIX -->
                <div class="col-md-4">
                    <label class="form-label fw-medium small">
                        Prix (FCFA) <span class="text-danger">*</span>
                    </label>
                    <input type="number" name="prix"
                           class="form-control bg-light"
                           placeholder="30000"
                           value="<?= htmlspecialchars($produit['prix'] ?? $_POST['prix'] ?? '') ?>"
                           min="0" required>
                </div>

                <!-- PRIX PROMO -->
                <div class="col-md-4">
                    <label class="form-label fw-medium small">Prix promo (FCFA)</label>
                    <input type="number" name="prix_promo"
                           class="form-control bg-light"
                           placeholder="25000"
                           value="<?= htmlspecialchars($produit['prix_promo'] ?? $_POST['prix_promo'] ?? '') ?>"
                           min="0">
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
                            $db3   = \App\Config\Database::getInstance()->getConnection();
                            $cats  = $db3->query("SELECT * FROM categories ORDER BY nom")->fetchAll();
                            foreach ($cats as $cat) :
                                $selected = ($produit['id_categorie'] ?? '') == $cat['id_categorie'] ? 'selected' : '';
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
                        <input class="form-check-input" type="checkbox" name="actif" id="actif"
                               <?= ($produit['actif'] ?? 1) ? 'checked' : '' ?>>
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

<?php include view_path('admin/layouts/footer.php'); ?>