<?php

namespace App\Controllers\Admin;

use PDO;
use App\Repositories\UserRepository;

class UtilisateurController {

    private UserRepository $userRepo;
    private PDO            $db;

    public function __construct(PDO $db) {
        $this->db       = $db;
        $this->userRepo = new UserRepository($db);
    }

    // =========================================
    // LISTE DES UTILISATEURS
    // =========================================
    public function index(): void {
        $utilisateurs = $this->getAll();
        include view_path('admin/utilisateurs/index.php');
    }

    // =========================================
    // CHANGER LE RÔLE
    // =========================================
    public function toggleRole(int $id): void {
        $user = $this->userRepo->findById($id);
        if (!$user) {
            header('Location: ' . url('admin/utilisateurs.php'));
            exit();
        }

        $nouveauRole = $user->toArray()['role'] === 'admin' ? 'client' : 'admin';

        $stmt = $this->db->prepare(
            "UPDATE utilisateurs SET role = :role WHERE id_utilisateur = :id"
        );
        $stmt->execute([':role' => $nouveauRole, ':id' => $id]);

        $_SESSION['flash_success'] = "Rôle mis à jour → " . ucfirst($nouveauRole);
        header('Location: ' . url('admin/utilisateurs.php'));
        exit();
    }

    // =========================================
    // ACTIVER / DÉSACTIVER
    // =========================================
    public function toggleActif(int $id): void {
        $stmt = $this->db->prepare(
            "UPDATE utilisateurs SET actif = NOT actif WHERE id_utilisateur = :id"
        );
        $stmt->execute([':id' => $id]);

        $_SESSION['flash_success'] = "Statut utilisateur mis à jour.";
        header('Location: ' . url('admin/utilisateurs.php'));
        exit();
    }

    // =========================================
    // SUPPRIMER
    // =========================================
    public function delete(int $id): void {
        $this->userRepo->delete($id);
        $_SESSION['flash_success'] = "Utilisateur supprimé.";
        header('Location: ' . url('admin/utilisateurs.php'));
        exit();
    }

    // =========================================
    // MÉTHODE PRIVÉE
    // =========================================
    private function getAll(): array {
        $stmt = $this->db->query("
            SELECT u.*,
                   COUNT(DISTINCT c.id_commande) AS nb_commandes,
                   COALESCE(SUM(c.montant_total), 0) AS total_achats
            FROM utilisateurs u
            LEFT JOIN commandes c ON u.id_utilisateur = c.id_utilisateur
            GROUP BY u.id_utilisateur
            ORDER BY u.date_inscription DESC
        ");
        return $stmt->fetchAll();
    }
}