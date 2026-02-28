<?php

$pageTitle = 'Connexion - NGAARY SHOP';
$currentPage = 'login.php';

// Token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

ob_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
   <div class="row justify-content-center mt-5">
        <div class="col-md-8">
                <div class="card-show">
                    <div class="card-body">
                        <form action="" method="post">

                        <div class="mb-3">
                            <label class="form-label">Nom</label>
                            <input type="text" name="nom" class="form-control" placeholder="Exemple : NDIAYE">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Prénom</label>
                            <input type="text" name="prenom" class="form-control" placeholder="Exemple : Alioune">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Adresse electronique</label>
                            <input type="email" name="email" class="form-control" placeholder="Exemple : aaaaaaaa@gmail.com">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Numéro de téléphone</label>
                            <input type="tel" name="tel" class="form-control" placeholder="Exemple : (+221) 77 777 77 77">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mot de passe</label>
                            <input type="password" name="password" class="form-control" placeholder="Exemple : Abcd#123">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confiremer le mot de passe</label>
                            <input type="password" name="password2" class="form-control" placeholder="Exemple : Abcd#123">
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success">Inscription</button>
                        </div>
                        </form>
                    </div>
                </div>
        </div>
   </div> 
</body>
</html>
<?php
$content = ob_get_clean();
require_once dirname(__DIR__, 2) . '/views/layouts/main_layout.php';
?>