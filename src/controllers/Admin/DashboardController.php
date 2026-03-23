<?php

namespace App\Controllers\Admin;

use PDO;
use App\Repositories\CommandeRepository;
use App\Repositories\ProduitRepository;
use App\Repositories\UserRepository;

class DashboardController {

    private CommandeRepository $commandeRepo;
    private ProduitRepository  $produitRepo;
    private UserRepository     $userRepo;

    public function __construct(PDO $db) {
        $this->commandeRepo = new CommandeRepository($db);
        $this->produitRepo  = new ProduitRepository($db);
        $this->userRepo     = new UserRepository($db);
    }

    public function index(): void {
        $stats = [
            'commandes_attente'   => $this->commandeRepo->countByStatut('en_attente'),
            'commandes_total'     => $this->commandeRepo->count(),
            'ca_mois'             => $this->commandeRepo->getCAMois(),
            'ca_total'            => $this->commandeRepo->getTotalCA(),
            'produits_actifs'     => $this->produitRepo->countActifs(),
            'clients_total'       => $this->userRepo->count(),
        ];

        $dernieresCommandes = $this->commandeRepo->getRecentes(5);
        $plusVendus         = $this->produitRepo->getPlusVendus(5);

        include view_path('admin/dashboard/index.php');
    }
}