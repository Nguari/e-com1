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
        include view_path('admin/commandes/detail.php');
    }

    public function updateStatut(int $id): void {
        $statut = $_POST['statut'] ?? '';
        $statuts = ['en_attente', 'confirmee', 'en_preparation', 'expediee', 'livree', 'annulee'];

        if (in_array($statut, $statuts)) {
            $this->commandeRepo->updateStatut($id, $statut);
            $_SESSION['flash_success'] = "Statut mis à jour !";
        }

        header('Location: ' . url('admin/commande_detail.php?id=' . $id));
        exit();
    }
}