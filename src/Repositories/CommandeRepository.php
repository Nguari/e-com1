<?php

namespace App\Repositories;

use App\Models\BaseEntity;

class CommandeRepository extends BaseRepository {

    protected string $tableName  = 'commandes';
    protected string $primaryKey = 'id_commande';
    protected string $orderBy    = 'date_commande';

    protected function hydrate(array $data): BaseEntity {
        // Retourne un objet générique pour simplifier
        return new class($data) extends BaseEntity {
            private array $data;
            public function __construct(array $data) {
                parent::__construct();
                $this->data = $data;
            }
            public function toArray(): array { return $this->data; }
            public function get(string $key): mixed { return $this->data[$key] ?? null; }
        };
    }

    public function findAllWithDetails(): array {
        $sql = "SELECT 
                    c.id_commande,
                    c.numero_commande,
                    c.date_commande,
                    c.montant_total,
                    c.statut,
                    CONCAT(u.prenom, ' ', u.nom) AS client,
                    u.email AS email_client
                FROM commandes c
                JOIN utilisateurs u ON c.id_utilisateur = u.id_utilisateur
                ORDER BY c.date_commande DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    public function findByIdWithDetails(int $id): ?array {
        $sql = "SELECT 
                    c.*,
                    CONCAT(u.prenom, ' ', u.nom) AS client,
                    u.email AS email_client,
                    u.telephone AS tel_client,
                    a.rue, a.ville, a.pays, a.telephone AS tel_livraison,
                    p.mode_paiement, p.statut AS statut_paiement
                FROM commandes c
                JOIN utilisateurs u ON c.id_utilisateur = u.id_utilisateur
                LEFT JOIN adresses a ON c.id_adresse_livraison = a.id_adresse
                LEFT JOIN paiements p ON c.id_commande = p.id_commande
                WHERE c.id_commande = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $commande = $stmt->fetch();
        if (!$commande) return null;

        // Lignes de commande
        $stmtLignes = $this->pdo->prepare("
            SELECT * FROM lignes_commande WHERE id_commande = :id
        ");
        $stmtLignes->execute([':id' => $id]);
        $commande['lignes'] = $stmtLignes->fetchAll();

        return $commande;
    }

    public function updateStatut(int $id, string $statut): bool {
        return $this->executeCommand(
            "UPDATE commandes SET statut = :statut WHERE id_commande = :id",
            [':statut' => $statut, ':id' => $id]
        );
    }

    public function countByStatut(string $statut): int {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM commandes WHERE statut = :statut"
        );
        $stmt->execute([':statut' => $statut]);
        return (int)$stmt->fetchColumn();
    }

    public function getTotalCA(): float {
        $stmt = $this->pdo->query(
            "SELECT COALESCE(SUM(montant_total), 0) FROM commandes WHERE statut != 'annulee'"
        );
        return (float)$stmt->fetchColumn();
    }

    public function getCAMois(): float {
        $stmt = $this->pdo->query("
            SELECT COALESCE(SUM(montant_total), 0) 
            FROM commandes 
            WHERE statut != 'annulee' 
            AND MONTH(date_commande) = MONTH(NOW())
            AND YEAR(date_commande) = YEAR(NOW())
        ");
        return (float)$stmt->fetchColumn();
    }

    public function getRecentes(int $limit = 5): array {
        $stmt = $this->pdo->prepare("
            SELECT c.id_commande, c.numero_commande, c.date_commande,
                   c.montant_total, c.statut,
                   CONCAT(u.prenom, ' ', u.nom) AS client
            FROM commandes c
            JOIN utilisateurs u ON c.id_utilisateur = u.id_utilisateur
            ORDER BY c.date_commande DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}