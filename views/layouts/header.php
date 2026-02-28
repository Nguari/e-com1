<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NGAARY SHOP</title>
    
    <!-- BOOTSTRAP 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    
    <!-- BOOTSTRAP ICONS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- FONT AWESOME -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- GOOGLE FONTS -->
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">

    <style>
        :root {
            --ngaary-green: #16a34a;
            --ngaary-bg: #f0faf3;
            --ngaary-dark: #0d2818;
        }
        body { font-family: 'DM Sans', sans-serif; background-color: var(--ngaary-bg); color: var(--ngaary-dark); }
        .font-serif { font-family: 'Playfair Display', serif; }
        .nav-link { letter-spacing: 1px; font-size: 0.8rem; text-transform: uppercase; font-weight: 500; color: var(--ngaary-dark) !important; }
        .nav-link.active { color: var(--ngaary-green) !important; border-bottom: 2px solid var(--ngaary-green); }
        .btn-success { background-color: var(--ngaary-green); border: none; }
    </style>
</head>
<body>


<!-- TOPBAR -->
<div class="bg-dark text-white py-2 small">
    <div class="container d-flex justify-content-between align-items-center">
        <span style="letter-spacing: 1px;">LIVRAISON GRATUITE DÈS 15 000 Fcfa</span>
        <a href="/login.php" class="text-success">           
            <i class='fas fa-user-circle fs-3'></i>
        </a>
    </div>
</div>

<!-- BRAND BAR -->
<header class="py-3 border-bottom bg-white shadow-sm">
    <div class="container d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            <div class="overflow-hidden d-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                <img src="imgs/onlineshop1.png" alt="Logo NGAARY SHOP" class="img-fluid object-fit-contain">
            </div>
            
            <div class="ms-3 d-none d-sm-block">
                <h1 class="h4 mb-0 font-serif fw-bold text-uppercase">NGAARY SHOP</h1>
                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.6rem; letter-spacing: 2px;">La qualité à petit prix</small>
            </div>
        </div>

        <!-- RECHERCHE -->
        <div class="flex-grow-1 mx-5 d-none d-md-block">
            <form action="search.php" method="GET" class="input-group">
                <input type="text" name="q" class="form-control bg-light border-0 rounded-pill-start ps-4 shadow-none" placeholder="Rechercher un produit...">
                <button class="btn btn-light border-0 rounded-pill-end px-3" type="submit">
                    <i class="bi bi-search text-success"></i>
                </button>
            </form>
        </div>

        <!-- PANIER -->
        <div class="d-flex align-items-center gap-3">
            <button class="btn border-0 position-relative p-0 text-dark">
                <i class="bi bi-bag-heart fs-4"></i>
            </button>
            <a href="cart.php" class="btn border-0 position-relative p-0 text-dark">
                <i class="bi bi-cart3 fs-4"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success" style="font-size: 0.6rem;">
                    <?= isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0 ?>
                </span>
            </a>
        </div>
    </div>
</header>

<!-- NAVIGATION -->
<nav class="navbar navbar-expand-lg bg-white sticky-top py-0 border-bottom">
    <div class="container text-center">
        <button class="navbar-toggler border-0 my-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item"><a class="nav-link px-4 py-3 active" href="index.php">Accueil</a></li>
                <li class="nav-item"><a class="nav-link px-4 py-3" href="boutique.php">Boutique</a></li>
                <li class="nav-item"><a class="nav-link px-4 py-3" href="promotions.php">Promotions</a></li>
                <li class="nav-item"><a class="nav-link px-4 py-3" href="contact.php">Contact</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- BOOTSTRAP JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>