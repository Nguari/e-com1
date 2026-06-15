<?php
// Inclusion de l'autoloader et des fonctions globales
require_once dirname(__DIR__, 2) . '/config/config.php';

use App\Utils\Auth;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'NGAARY SHOP' ?></title>
    
    <!-- BOOTSTRAP 5.3 CSS -->
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
        body { 
            font-family: 'DM Sans', sans-serif; 
            background-color: var(--ngaary-bg); 
            color: var(--ngaary-dark);
            overflow-x: hidden;
        }
        .font-serif { font-family: 'Playfair Display', serif; }
        .nav-link { letter-spacing: 1px; font-size: 0.8rem; text-transform: uppercase; font-weight: 500; color: var(--ngaary-dark) !important; }
        .nav-link.active { color: var(--ngaary-green) !important; border-bottom: 2px solid var(--ngaary-green); }
        .btn-success { background-color: var(--ngaary-green); border: none; }

        /* Styles pour le dropdown utilisateur */
        .user-dropdown {
            position: relative;
        }
        
        .user-dropdown .dropdown-toggle::after {
            display: none;
        }
        
        .user-avatar {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #0d2818, #16a34a);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
            transition: transform 0.2s;
        }
        
        .user-avatar:hover {
            transform: scale(1.05);
        }
        
        /* Dropdown au-dessus de tout */
        .dropdown-menu {
            position: absolute !important;
            top: 100% !important;
            right: 0 !important;
            left: auto !important;
            transform: translateY(8px) !important;
            border: none;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            border-radius: 12px;
            min-width: 250px;
            z-index: 9999 !important;
            animation: fadeInDown 0.2s ease;
            background: white;
        }
        
        /* Pour que le dropdown soit au-dessus de tout contenu */
        .navbar, .topbar, header {
            z-index: 1000;
            position: relative;
        }
        
        /* Le dropdown doit dépasser */
        .bg-dark {
            z-index: 1001;
            position: relative;
        }
        
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(8px);
            }
        }
        
        .dropdown-item {
            padding: 10px 20px;
            font-size: 0.85rem;
            transition: all 0.2s;
        }
        
        .dropdown-item:hover {
            background-color: #f0faf3;
            color: #16a34a;
            padding-left: 24px;
        }
        
        .dropdown-item i {
            width: 20px;
            margin-right: 8px;
            font-size: 1rem;
        }
        
        .dropdown-divider {
            margin: 6px 0;
        }
        
        .dropdown-header {
            padding: 8px 20px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            color: #6c757d;
        }
        
        .cart-badge {
            font-size: 0.6rem;
            padding: 2px 6px;
        }
        
        /* Pour éviter que le dropdown soit coupé */
        .container {
            overflow: visible !important;
        }
        
        .row, .col-*, .d-flex {
            overflow: visible !important;
        }
        /* ====== SIDE CART ====== */
        .side-cart {
            position: fixed;
            top: 0;
            right: -450px;
            width: 450px;
            height: 100vh;
            background: white;
            z-index: 99999;
            box-shadow: -5px 0 30px rgba(0,0,0,0.15);
            transition: right 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .side-cart.open {
            right: 0;
        }

        .side-cart-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 99998;
            display: none;
        }

        .side-cart-overlay.show {
            display: block;
        }

        .side-cart-header {
            padding: 20px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
        }

        .side-cart-header h5 {
            margin: 0;
            font-weight: 600;
        }

        .side-cart-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #64748b;
            transition: color 0.2s;
        }

        .side-cart-close:hover {
            color: #dc2626;
        }

        .side-cart-body {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
        }

        .side-cart-item {
            display: flex;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .side-cart-item-img {
            width: 70px;
            height: 70px;
            background: #f8fafc;
            border-radius: 12px;
            overflow: hidden;
            flex-shrink: 0;
        }

        .side-cart-item-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .side-cart-item-info {
            flex: 1;
        }

        .side-cart-item-title {
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .side-cart-item-price {
            font-size: 0.85rem;
            color: #16a34a;
            font-weight: 600;
        }

        .side-cart-item-quantity {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 8px;
        }

        .side-cart-item-quantity button {
            width: 24px;
            height: 24px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            background: white;
            cursor: pointer;
            transition: all 0.2s;
        }

        .side-cart-item-quantity button:hover {
            background: #16a34a;
            color: white;
            border-color: #16a34a;
        }

        .side-cart-item-remove {
            color: #dc2626;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 0.7rem;
            margin-top: 5px;
            padding: 0;
            transition: color 0.2s;
        }

        .side-cart-item-remove:hover {
            color: #991b1b;
            text-decoration: underline;
        }

        .side-cart-item-subtotal {
            font-weight: 700;
            color: #0d2818;
            min-width: 80px;
            text-align: right;
        }

        .side-cart-footer {
            padding: 20px;
            border-top: 1px solid #e2e8f0;
            background: white;
        }

        .side-cart-total {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .side-cart-btn {
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
            display: block;
        }

        .btn-cart-checkout {
            background: #16a34a;
            color: white;
        }

        .btn-cart-checkout:hover {
            background: #15803d;
        }

        .btn-cart-continue {
            background: #f1f5f9;
            color: #0d2818;
            margin-bottom: 10px;
        }

        .btn-cart-continue:hover {
            background: #e2e8f0;
        }

        .side-cart-empty {
            text-align: center;
            padding: 40px 20px;
            color: #64748b;
        }

        .side-cart-empty i {
            font-size: 4rem;
            margin-bottom: 15px;
            color: #cbd5e1;
        }

        /* Animation icône panier */
        .cart-icon-badge {
            cursor: pointer;
            transition: transform 0.2s;
        }

        .cart-icon-badge:hover {
            transform: scale(1.05);
        }

        @keyframes cartBump {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }

        .cart-bump {
            animation: cartBump 0.3s ease;
        }

        /* ====== QUICK VIEW ====== */
        .quick-view-btn {
            position: absolute;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
            background: white;
            border: none;
            border-radius: 20px;
            padding: 6px 12px;
            font-size: 0.7rem;
            font-weight: 500;
            color: #16a34a;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: all 0.2s;
            z-index: 10;
            opacity: 0;
        }

        .product-card:hover .quick-view-btn {
            opacity: 1;
        }

        .quick-view-btn:hover {
            background: #16a34a;
            color: white;
        }

        .modal-content {
            border: none;
        }

        /* Responsive */
        @media (max-width: 576px) {
            .side-cart {
                width: 100%;
                right: -100%;
            }
        }
    </style>
    
</head>
<body>

<!-- TOPBAR -->
<div class="bg-dark text-white py-2 small" style="position: relative; z-index: 1002;">
    <div class="container d-flex justify-content-between align-items-center" style="overflow: visible;">
        <span style="letter-spacing: 1px;">LIVRAISON GRATUITE DÈS 15 000 FCFA</span>

        <div class="d-flex align-items-center gap-3" style="overflow: visible;">
            <?php if (\App\Utils\Auth::check()) : ?>
                <!-- ✅ CONNECTÉ : Menu dropdown moderne -->
                <div class="dropdown user-dropdown" style="overflow: visible;">
                    <button class="btn p-0 dropdown-toggle d-flex align-items-center gap-2" 
                            type="button" 
                            data-bs-toggle="dropdown" 
                            data-bs-auto-close="outside"
                            aria-expanded="false">
                        <div class="user-avatar">
                            <?= strtoupper(substr(\App\Utils\Auth::user()->getPrenom() ?? 'U', 0, 1)) ?>
                        </div>
                        <span class="text-white-50 d-none d-md-inline">
                            <?= htmlspecialchars(\App\Utils\Auth::user()->getPrenom()) ?>
                        </span>
                        <i class="bi bi-chevron-down text-white-50 fs-6"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" style="position: absolute; top: 100%; right: 0; left: auto; z-index: 9999;">
                        <li class="dropdown-header text-muted">Mon compte</li>
                        <li>
                            <a class="dropdown-item" href="<?= url('profil.php') ?>">
                                <i class="bi bi-person"></i> Mon profil
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?= url('mes_commandes.php') ?>">
                                <i class="bi bi-receipt"></i> Mes commandes
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?= url('favoris.php') ?>">
                                <i class="bi bi-heart text-danger"></i> Mes favoris
                            </a>
                        </li>
                        <?php if (\App\Utils\Auth::isAdmin()) : ?>
                            <li><hr class="dropdown-divider"></li>
                            <li class="dropdown-header text-muted">Administration</li>
                            <li>
                                <a class="dropdown-item" href="<?= url('admin/index.php') ?>">
                                    <i class="bi bi-speedometer2"></i> Tableau de bord
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?= url('admin/produits.php') ?>">
                                    <i class="bi bi-box-seam"></i> Gestion produits
                                </a>
                            </li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="<?= url('logout.php') ?>">
                                <i class="bi bi-box-arrow-right"></i> Déconnexion
                            </a>
                        </li>
                    </ul>
                </div>

            <?php else : ?>
                <!-- 👤 NON CONNECTÉ : icône + lien connexion et inscription -->
                <a href="<?= url('login.php') ?>" class="text-success text-decoration-none d-flex align-items-center gap-2" title="Se connecter">
                    <i class="fas fa-user-circle fs-4"></i>
                    <span class="small fw-semibold d-none d-md-inline">Connexion</span>
                </a>
                <a href="<?= url('register.php') ?>" class="text-white-50 text-decoration-none small d-none d-md-inline">
                    Inscription
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- BRAND BAR -->
<!-- BRAND BAR -->
<header class="py-3 border-bottom bg-white shadow-sm" style="position: relative; z-index: 1001;">
    <div class="container d-flex align-items-center justify-content-between" style="overflow: visible;">
        
        <!-- Logo (gauche) -->
        <div class="d-flex align-items-center">
            <div class="overflow-hidden d-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                <img src="<?= url('imgs/onlineshop1.png') ?>" alt="Logo NGAARY SHOP" class="img-fluid object-fit-contain">
            </div>
            <div class="ms-3 d-none d-sm-block">
                <h1 class="h4 mb-0 font-serif fw-bold text-uppercase">NGAARY SHOP</h1>
                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.6rem; letter-spacing: 2px;">La qualité à petit prix</small>
            </div>
        </div>

        <!-- RECHERCHE (centrée) -->
        <div class="flex-grow-1 mx-3 mx-md-5">
            <form action="<?= url('search.php') ?>" method="GET" class="input-group">
                <input type="text" name="q" class="form-control bg-light border-0 rounded-pill-start ps-3 ps-md-4 shadow-none" 
                       placeholder="Rechercher un produit..." 
                       style="font-size: 0.85rem;">
                <button class="btn btn-light border-0 rounded-pill-end px-2 px-md-3" type="submit">
                    <i class="bi bi-search text-success"></i>
                </button>
            </form>
        </div>

        <!-- PANIER et Connexion (droite) -->
        <div class="d-flex align-items-center gap-3">
            <!-- PANIER -->
            <a href="javascript:void(0)" onclick="openSideCart()" class="btn border-0 position-relative p-0 text-dark cart-icon-badge">
                <i class="bi bi-cart3 fs-4"></i>
                <?php
                $cartCount = 0;
                if (\App\Utils\Auth::check()) {
                    try {
                        $cartDb   = \App\Config\Database::getInstance()->getConnection();
                        $cartStmt = $cartDb->prepare(
                            "SELECT COALESCE(SUM(quantite), 0) FROM panier WHERE id_utilisateur = :id"
                        );
                        $cartStmt->execute([':id' => \App\Utils\Auth::id()]);
                        $cartCount = (int)$cartStmt->fetchColumn();
                    } catch (\Exception $e) {
                        $cartCount = 0;
                    }
                }
                ?>
                <span id="cartCount" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success cart-badge" <?= $cartCount == 0 ? 'style="display: none;"' : '' ?>>
                    <?= $cartCount ?>
                </span>
            </a>

            
        </div>
    </div>
</header>

<!-- NAVIGATION -->
<nav class="navbar navbar-expand-lg bg-white sticky-top py-0 border-bottom" style="z-index: 1000;">
    <div class="container text-center">
        <button class="navbar-toggler border-0 my-2" 
                type="button" 
                data-bs-toggle="collapse" 
                data-bs-target="#navbarNav" 
                aria-controls="navbarNav" 
                aria-expanded="false" 
                aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse " id="navbarNav" >
            <ul class="navbar-nav mx-auto">
                <li class="nav-item">
                    <a class="nav-link px-4 py-3 <?= ($currentPage ?? '') === 'index.php' ? 'active' : '' ?>"
                       href="<?= url('index.php') ?>">Accueil</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-4 py-3 <?= ($currentPage ?? '') === 'Boutique.php' ? 'active' : '' ?>"
                       href="<?= url('Boutique.php') ?>">Boutique</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-4 py-3 <?= ($currentPage ?? '') === 'Promotions.php' ? 'active' : '' ?>"
                       href="<?= url('Promotions.php') ?>">Promotions</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-4 py-3 <?= ($currentPage ?? '') === 'contact.php' ? 'active' : '' ?>"
                       href="<?= url('contact.php') ?>">Contact</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Récupérer les éléments
        const navbarToggler = document.querySelector('.navbar-toggler');
        const navbarCollapse = document.getElementById('navbarNav');
        
        // Fonction pour fermer le menu
        function closeNavbar() {
            if (navbarCollapse && navbarCollapse.classList.contains('show')) {
                const bsCollapse = bootstrap.Collapse.getInstance(navbarCollapse);
                if (bsCollapse) {
                    bsCollapse.hide();
                }
                navbarToggler.setAttribute('aria-expanded', 'false');
            }
        }
        
        // S'assurer que le menu hamburger fonctionne correctement
        if (navbarToggler && navbarCollapse) {
            // Bootstrap gère déjà le toggle, mais on ajoute un comportement supplémentaire
            navbarToggler.addEventListener('click', function() {
                // Bootstrap gère automatiquement l'ouverture/fermeture
                setTimeout(function() {
                    if (navbarCollapse.classList.contains('show')) {
                        navbarToggler.setAttribute('aria-expanded', 'true');
                    } else {
                        navbarToggler.setAttribute('aria-expanded', 'false');
                    }
                }, 10);
            });
        }
        
        // Fermer le menu quand on clique sur un lien (un choix est fait)
        const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
        navLinks.forEach(function(link) {
            link.addEventListener('click', function() {
                closeNavbar();
            });
        });
        
        // Fermer le dropdown utilisateur quand on clique ailleurs
        var dropdowns = document.querySelectorAll('.dropdown-toggle');
        dropdowns.forEach(function(dropdown) {
            dropdown.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        });
        
        document.addEventListener('click', function() {
            var openDropdowns = document.querySelectorAll('.dropdown-menu.show');
            openDropdowns.forEach(function(menu) {
                var instance = bootstrap.Dropdown.getInstance(menu.previousElementSibling);
                if (instance) {
                    instance.hide();
                }
            });
        });
    });
</script>