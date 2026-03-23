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

        body { font-family: 'DM Sans', sans-serif; background: var(--admin-bg); }

        /* ====== SIDEBAR ====== */
        .admin-sidebar {
            width: var(--sidebar-w);
            min-height: 100vh;
            background: var(--admin-dark);
            position: fixed;
            top: 0; left: 0;
            z-index: 1045;
            overflow-y: auto;
            transition: transform 0.3s ease;
        }

        /* Masqué sur mobile par défaut */
        @media (max-width: 991.98px) {
            .admin-sidebar {
                transform: translateX(-100%);
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
            padding: 24px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
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
            padding: 14px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
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

        .admin-content { padding: 20px 16px; }

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
    </style>
</head>
<body>

<!-- OVERLAY mobile -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<!-- SIDEBAR -->
<aside class="admin-sidebar" id="adminSidebar">
    <div class="brand d-flex align-items-center justify-content-between">
        <div>
            <div class="text-white fw-bold" style="font-family: 'Playfair Display', serif; font-size: 1.1rem;">
                NGAARY SHOP
            </div>
            <div class="text-success small">Panel Admin</div>
        </div>
        <!-- Bouton fermer sur mobile -->
        <button class="btn-sidebar-toggle text-white d-lg-none" onclick="toggleSidebar()">
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

        <div class="sidebar-section mt-2">Ventes</div>
        <a href="<?= url('admin/commandes.php') ?>"
           class="nav-link <?= ($adminPage ?? '') === 'commandes' ? 'active' : '' ?>">
            <i class="bi bi-receipt"></i> Commandes
            <?php
            try {
                $db2   = \App\Config\Database::getInstance()->getConnection();
                $stmt2 = $db2->query("SELECT COUNT(*) FROM commandes WHERE statut = 'en_attente'");
                $nb    = (int)$stmt2->fetchColumn();
                if ($nb > 0) echo "<span class='badge bg-danger ms-auto'>$nb</span>";
            } catch (\Exception $e) {}
            ?>
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
            <h6 class="mb-0 fw-semibold d-none d-sm-block"><?= $pageTitle ?? 'Dashboard' ?></h6>
        </div>
        <div class="d-flex align-items-center gap-2 gap-md-3">
            <span class="text-muted small d-none d-md-inline">
                Bonjour, <strong class="text-success">
                    <?= htmlspecialchars(\App\Utils\Auth::user()->getFullName()) ?>
                </strong>
            </span>
            <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                 style="width: 36px; height: 36px; font-size: 0.9rem;">
                <?= strtoupper(substr(\App\Utils\Auth::user()->getPrenom(), 0, 1)) ?>
            </div>
        </div>
    </div>

    <!-- CONTENU -->
    <div class="admin-content">

<script>
function toggleSidebar() {
    document.getElementById('adminSidebar').classList.toggle('show');
    document.getElementById('sidebarOverlay').classList.toggle('show');
}
</script>