<?php

namespace App\Controllers\Admin;

use PDO;
use App\Repositories\CommandeRepository;

class CommandeController {

    private CommandeRepository $commandeRepo;

    public function __construct(PDO $db) {
        $this->commandeRepo = new CommandeRepository($db);
    }

    public function index(): void {
        $commandes = $this->commandeRepo->findAllWithDetails();
        include view_path('admin/commandes/index.php');
    }

    public function detail(int $id): void {
        $commande = $this->commandeRepo->findByIdWithDetails($id);
        if (!$commande) {
            header('Location: ' . url('admin/commandes.php'));
            exit();
        }
        
        // Définir les variables pour la vue
        $pageTitle = 'Commande #' . ($commande['numero_commande'] ?? '') . ' - Admin';
        $adminPage = 'commandes';
        
        // Inclure le header admin
        include view_path('admin/layouts/header.php');
        
        // Inclure la vue du détail
        include view_path('admin/commandes/detail.php');
        
        // Inclure le footer admin (contient la fonction toggleSidebar)
        include view_path('admin/layouts/footer.php');
    }

    public function updateStatut(int $id): void {
        // Vérifier le token CSRF
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $_SESSION['flash_error'] = "Erreur de sécurité. Veuillez réessayer.";
            header('Location: ' . url('admin/commande_detail.php?id=' . $id));
            exit();
        }
        
        $statut = $_POST['statut'] ?? '';
        $statuts = ['en_attente', 'confirmee', 'en_preparation', 'expediee', 'livree', 'annulee'];

        if (in_array($statut, $statuts)) {
            $this->commandeRepo->updateStatut($id, $statut);
            $_SESSION['flash_success'] = "Statut mis à jour !";
        } else {
            $_SESSION['flash_error'] = "Statut invalide.";
        }

        header('Location: ' . url('admin/commande_detail.php?id=' . $id));
        exit();
    }
}