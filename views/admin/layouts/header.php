<?php
// Inclusion de la configuration (autoloader et fonctions globales)
require_once dirname(__DIR__, 3) . '/config/config.php';

use App\Utils\Auth;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Admin - NGAARY SHOP' ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">

    <style>
    :root {
        --admin-dark:  #0d2818;
        --admin-green: #16a34a;
        --admin-bg:    #f8fafc;
        --sidebar-w:   260px;
    }

    body { font-family: 'DM Sans', sans-serif; background: var(--admin-bg); overflow-x: hidden; }

    /* ====== SIDEBAR ====== */
    .admin-sidebar {
        width: var(--sidebar-w);
        height: 100vh;
        background: var(--admin-dark);
        position: fixed;
        top: 0; 
        left: 0;
        z-index: 1045;
        overflow-y: auto;
        overflow-x: hidden;
        transition: transform 0.3s ease;
        display: flex;
        flex-direction: column;
    }
    
    /* Personnalisation de la scrollbar pour le sidebar */
    .admin-sidebar::-webkit-scrollbar {
        width: 4px;
    }
    
    .admin-sidebar::-webkit-scrollbar-track {
        background: rgba(255,255,255,0.1);
    }
    
    .admin-sidebar::-webkit-scrollbar-thumb {
        background: var(--admin-green);
        border-radius: 4px;
    }
    
    .admin-sidebar::-webkit-scrollbar-thumb:hover {
        background: #4ade80;
    }

    /* Masqué sur mobile par défaut */
    @media (max-width: 991.98px) {
        .admin-sidebar {
            transform: translateX(-100%);
            height: 100vh;
        }
        .admin-sidebar.show {
            transform: translateX(0);
        }
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1040;
        }
        .sidebar-overlay.show {
            display: block;
        }
        .admin-main {
            margin-left: 0 !important;
        }
    }

    .admin-sidebar .brand {
        padding: 20px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        display: flex;
        align-items: center;
        gap: 12px;
        flex-shrink: 0;
    }
    
    .admin-sidebar .brand-logo {
    width: 45px;
    height: 45px;
    background: rgba(255,255,255,0.1);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    flex-shrink: 0;
}

.admin-sidebar .brand-logo img {
    width: 100%;
    height: 100%;
    object-fit: cover;  /* Couvre tout l'espace sans déformer */
    object-position: center;
}
    
    .admin-sidebar .brand-info {
        flex: 1;
    }
    
    .admin-sidebar .brand-name {
        font-family: 'Playfair Display', serif;
        font-size: 1rem;
        font-weight: 700;
        color: white;
        letter-spacing: 1px;
    }
    
    .admin-sidebar .brand-badge {
        font-size: 0.65rem;
        color: #4ade80;
        text-transform: uppercase;
        letter-spacing: 2px;
    }
    
    /* Conteneur de navigation qui défile */
    .sidebar-nav-wrapper {
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
        padding-bottom: 20px;
    }
    
    .sidebar-nav-wrapper::-webkit-scrollbar {
        width: 4px;
    }
    
    .sidebar-nav-wrapper::-webkit-scrollbar-track {
        background: rgba(255,255,255,0.1);
    }
    
    .sidebar-nav-wrapper::-webkit-scrollbar-thumb {
        background: var(--admin-green);
        border-radius: 4px;
    }

    .sidebar-nav .nav-link {
        color: rgba(255,255,255,0.7) !important;
        padding: 12px 20px;
        border-radius: 8px;
        margin: 2px 10px;
        font-size: 0.9rem;
        font-weight: 500;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .sidebar-nav .nav-link:hover,
    .sidebar-nav .nav-link.active {
        background: rgba(22,163,74,0.2);
        color: #4ade80 !important;
    }

    .sidebar-nav .nav-link i { font-size: 1.1rem; }

    .sidebar-section {
        padding: 16px 20px 4px;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 2px;
        color: rgba(255,255,255,0.3);
        font-weight: 600;
    }

    /* ====== MAIN CONTENT ====== */
    .admin-main {
        margin-left: var(--sidebar-w);
        min-height: 100vh;
        transition: margin-left 0.3s ease;
    }

    /* ====== TOPBAR ====== */
    .admin-topbar {
        background: white;
        border-bottom: 1px solid #e2e8f0;
        padding: 12px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: sticky;
        top: 0;
        z-index: 1000;
    }

    .btn-sidebar-toggle {
        background: none;
        border: none;
        font-size: 1.4rem;
        color: var(--admin-dark);
        cursor: pointer;
        padding: 0;
        line-height: 1;
        display: none;
    }

    @media (max-width: 991.98px) {
        .btn-sidebar-toggle { display: block; }
    }

    .admin-content { 
        padding: 20px 16px;
        max-width: 100%;
        overflow-x: auto;
    }

    @media (min-width: 768px) {
        .admin-content { padding: 28px 24px; }
    }

    /* ====== STAT CARDS ====== */
    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        transition: transform 0.2s;
        height: 100%;
    }
    .stat-card:hover { transform: translateY(-3px); }
    .stat-icon {
        width: 48px; height: 48px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.3rem;
        flex-shrink: 0;
    }

    /* ====== TABLES ====== */
    .admin-table {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    }
    .admin-table th {
        background: #f8fafc;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #64748b;
        border: none;
        padding: 12px 16px;
        white-space: nowrap;
    }
    .admin-table td {
        padding: 12px 16px;
        vertical-align: middle;
        border-color: #f1f5f9;
        font-size: 0.9rem;
    }

    /* ====== BADGES STATUT ====== */
    .badge-statut { padding: 5px 12px; border-radius: 20px; font-size: 0.72rem; font-weight: 600; white-space: nowrap; }
    .badge-en_attente     { background: #fef3c7; color: #d97706; }
    .badge-confirmee      { background: #dbeafe; color: #2563eb; }
    .badge-en_preparation { background: #ede9fe; color: #7c3aed; }
    .badge-expediee       { background: #d1fae5; color: #059669; }
    .badge-livree         { background: #dcfce7; color: #16a34a; }
    .badge-annulee        { background: #fee2e2; color: #dc2626; }

    /* ====== DROPDOWN UTILISATEUR ====== */
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
        cursor: pointer;
    }
    
    .user-avatar:hover {
        transform: scale(1.05);
    }
    
    .dropdown-menu {
        position: absolute !important;
        top: 100% !important;
        right: 0 !important;
        left: auto !important;
        transform: translateY(8px) !important;
        border: none;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        border-radius: 12px;
        min-width: 220px;
        z-index: 9999 !important;
        background: white;
        margin: 0;
        padding: 8px 0;
    }
    
    .dropdown-menu.show {
        display: block;
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
    
    /* Animation dropdown */
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
    
    .dropdown-menu {
        animation: fadeInDown 0.2s ease;
    }
    
    /* Logo dans topbar */
    .topbar-logo {
        display: none;
    }
    
    @media (max-width: 991.98px) {
        .topbar-logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .topbar-logo-img {
            width: 32px;
            height: 32px;
            border-radius: 8px;
        }
        .topbar-logo-text {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-size: 0.9rem;
            color: var(--admin-dark);
        }
    }
</style>
<script>
function toggleSidebar() {
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    
    if (sidebar) {
        sidebar.classList.toggle('show');
    }
    if (overlay) {
        overlay.classList.toggle('show');
    }
    
    // Empêcher le scroll du body quand le sidebar est ouvert
    if (sidebar && sidebar.classList.contains('show')) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
}

// Fermer le sidebar quand on clique sur l'overlay
const overlay = document.getElementById('sidebarOverlay');
if (overlay) {
    overlay.addEventListener('click', toggleSidebar);
}

// Réinitialiser quand la fenêtre est redimensionnée
window.addEventListener('resize', function() {
    if (window.innerWidth > 991.98) {
        const sidebar = document.getElementById('adminSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        if (sidebar && sidebar.classList.contains('show')) {
            sidebar.classList.remove('show');
        }
        if (overlay && overlay.classList.contains('show')) {
            overlay.classList.remove('show');
        }
        document.body.style.overflow = '';
    }
});
</script>
</head>
<body>

<!-- OVERLAY mobile -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<!-- SIDEBAR -->
<aside class="admin-sidebar" id="adminSidebar">
    <div class="brand">
        <div class="brand-logo">
            <img src="<?= url('imgs/onlineshop1.png') ?>" alt="Logo NGAARY SHOP">
        </div>
        <div class="brand-info">
            <div class="brand-name">NGAARY SHOP</div>
            <div class="brand-badge">Administration</div>
        </div>
        <!-- Bouton fermer sur mobile -->
        <button class="btn-sidebar-toggle text-white d-lg-none" onclick="toggleSidebar()" style="margin-left: auto;">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <nav class="sidebar-nav py-3">
        <div class="sidebar-section">Principal</div>
        <a href="<?= url('admin/index.php') ?>"
           class="nav-link <?= ($adminPage ?? '') === 'dashboard' ? 'active' : '' ?>">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <div class="sidebar-section mt-2">Catalogue</div>
        <a href="<?= url('admin/produits.php') ?>"
           class="nav-link <?= ($adminPage ?? '') === 'produits' ? 'active' : '' ?>">
            <i class="bi bi-box-seam"></i> Produits
        </a>
        <a href="<?= url('admin/categories.php') ?>"
           class="nav-link <?= ($adminPage ?? '') === 'categories' ? 'active' : '' ?>">
            <i class="bi bi-tags"></i> Catégories
        </a>

        <div class="sidebar-section mt-2">Ventes</div>
        <a href="<?= url('admin/commandes.php') ?>"
           class="nav-link <?= ($adminPage ?? '') === 'commandes' ? 'active' : '' ?>">
            <i class="bi bi-receipt"></i> Commandes
            <?php $cmdAttenteCount = (int)($commandes_attente_count ?? 0); ?>
            <?php if ($cmdAttenteCount > 0): ?>
                <span class="badge bg-danger ms-auto"><?= $cmdAttenteCount ?></span>
            <?php endif; ?>
        </a>

        <div class="sidebar-section mt-2">Utilisateurs</div>
        <a href="<?= url('admin/utilisateurs.php') ?>"
           class="nav-link <?= ($adminPage ?? '') === 'utilisateurs' ? 'active' : '' ?>">
            <i class="bi bi-people"></i> Clients
        </a>

        <div class="sidebar-section mt-2">Compte</div>
        <a href="<?= url('index.php') ?>" class="nav-link">
            <i class="bi bi-house"></i> Voir le site
        </a>
        <a href="<?= url('logout.php') ?>" class="nav-link" style="color: #f87171 !important;">
            <i class="bi bi-box-arrow-right"></i> Déconnexion
        </a>
    </nav>
</aside>

<!-- MAIN -->
<div class="admin-main" id="adminMain">

    <!-- TOPBAR -->
    <div class="admin-topbar">
        <div class="d-flex align-items-center gap-3">
            <!-- Bouton hamburger mobile -->
            <button class="btn-sidebar-toggle" onclick="toggleSidebar()">
                <i class="bi bi-list"></i>
            </button>
            <!-- Logo mobile (visible seulement sur petit écran) -->
            <div class="topbar-logo">
                <img src="<?= url('imgs/onlineshop1.png') ?>" alt="Logo" class="topbar-logo-img">
                <span class="topbar-logo-text">NGAARY SHOP</span>
            </div>
            <h6 class="mb-0 fw-semibold d-none d-sm-block"><?= $pageTitle ?? 'Dashboard' ?></h6>
        </div>
        
        <!-- DROPDOWN UTILISATEUR -->
        <div class="dropdown user-dropdown">
            <div class="d-flex align-items-center gap-2 dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
                <?php
                $adminUser = \App\Utils\Auth::user();
                $adminPrenom = $adminUser?->getPrenom() ?? 'A';
                $adminNom    = $adminUser?->getNom() ?? '';
                $adminInitial = strtoupper(substr($adminPrenom, 0, 1));
                ?>
                <div class="user-avatar">
                    <?= htmlspecialchars($adminInitial) ?>
                </div>
                <span class="text-muted small d-none d-md-inline">
                    <?= htmlspecialchars(trim($adminPrenom . ' ' . $adminNom)) ?>
                </span>
                <i class="bi bi-chevron-down text-muted fs-6"></i>
            </div>
            <ul class="dropdown-menu dropdown-menu-end">
                <li class="dropdown-header text-muted">Mon compte</li>
                <li>
                    <a class="dropdown-item" href="<?= url('profil.php') ?>">
                        <i class="bi bi-person"></i> Mon profil
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="<?= url('admin/parametres.php') ?>">
                        <i class="bi bi-gear"></i> Paramètres
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item text-danger" href="<?= url('logout.php') ?>">
                        <i class="bi bi-box-arrow-right"></i> Déconnexion
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- CONTENU -->
    <div class="admin-content">