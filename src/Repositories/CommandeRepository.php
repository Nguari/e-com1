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

    /**
     * Récupère toutes les commandes avec les détails client
     */
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

    /**
     * Récupère une commande avec tous ses détails
     */
    public function findByIdWithDetails(int $id): ?array {
        $sql = "SELECT 
                    c.*,
                    CONCAT(u.prenom, ' ', u.nom) AS client,
                    u.email AS email_client,
                    COALESCE(u.tel, '—') AS tel_client,
                    p.mode_paiement, 
                    p.statut AS statut_paiement
                FROM commandes c
                JOIN utilisateurs u ON c.id_utilisateur = u.id_utilisateur
                LEFT JOIN paiements p ON c.id_commande = p.id_commande
                WHERE c.id_commande = :id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $commande = $stmt->fetch();
        
        if (!$commande) return null;

        // Récupération des lignes de commande
        $stmtLignes = $this->pdo->prepare("
            SELECT lc.*, p.nom as produit_nom 
            FROM lignes_commande lc
            LEFT JOIN produits p ON lc.id_produit = p.id_produit
            WHERE lc.id_commande = :id
        ");
        $stmtLignes->execute([':id' => $id]);
        $commande['lignes'] = $stmtLignes->fetchAll();

        return $commande;
    }

    /**
     * Met à jour le statut d'une commande
     */
    public function updateStatut(int $id, string $statut): bool {
        return $this->executeCommand(
            "UPDATE commandes SET statut = :statut WHERE id_commande = :id",
            [':statut' => $statut, ':id' => $id]
        );
    }

    /**
     * Compte les commandes par statut
     */
    public function countByStatut(string $statut): int {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM commandes WHERE statut = :statut"
        );
        $stmt->execute([':statut' => $statut]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Compte toutes les commandes
     */
    public function count(): int {
        $stmt = $this->pdo->query(
            "SELECT COUNT(*) FROM commandes"
        );
        return (int)$stmt->fetchColumn();
    }

    /**
     * Récupère le chiffre d'affaires total (commandes livrées uniquement)
     */
    public function getTotalCA(): float {
        $stmt = $this->pdo->query(
            "SELECT COALESCE(SUM(montant_total), 0) FROM commandes WHERE statut = 'livree'"
        );
        return (float)$stmt->fetchColumn();
    }

    /**
     * Récupère le chiffre d'affaires du mois en cours
     */
    public function getCAMois(): float {
        $stmt = $this->pdo->prepare("
            SELECT COALESCE(SUM(montant_total), 0) 
            FROM commandes 
            WHERE statut = 'livree' 
            AND MONTH(date_commande) = MONTH(CURRENT_DATE())
            AND YEAR(date_commande) = YEAR(CURRENT_DATE())
        ");
        $stmt->execute();
        return (float)$stmt->fetchColumn();
    }

    /**
     * Récupère les dernières commandes
     */
    public function getRecentes(int $limit = 5): array {
        $stmt = $this->pdo->prepare("
            SELECT c.id_commande, 
                   c.numero_commande, 
                   c.date_commande,
                   c.montant_total, 
                   c.statut,
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

    public function findByNumero(string $numero): ?array {
    $stmt = $this->pdo->prepare("SELECT * FROM commandes WHERE numero_commande = :numero");
    $stmt->execute([':numero' => $numero]);
    $commande = $stmt->fetch(\PDO::FETCH_ASSOC);
    return $commande ?: null;
}

    /**
     * Récupère le nombre de commandes par mois (pour les graphiques)
     */
    public function getCommandesParMois(int $annee = null): array {
        $annee = $annee ?? date('Y');
        $stmt = $this->pdo->prepare("
            SELECT 
                MONTH(date_commande) as mois,
                COUNT(*) as total
            FROM commandes
            WHERE YEAR(date_commande) = :annee
            GROUP BY MONTH(date_commande)
            ORDER BY mois
        ");
        $stmt->execute([':annee' => $annee]);
        $results = $stmt->fetchAll();
        
        // Remplir les mois manquants avec 0
        $commandesParMois = array_fill(1, 12, 0);
        foreach ($results as $row) {
            $commandesParMois[(int)$row['mois']] = (int)$row['total'];
        }
        
        return $commandesParMois;
    }

    /**
     * Récupère le chiffre d'affaires par mois (pour les graphiques)
     */
    public function getCAParMois(int $annee = null): array {
        $annee = $annee ?? date('Y');
        $stmt = $this->pdo->prepare("
            SELECT 
                MONTH(date_commande) as mois,
                COALESCE(SUM(montant_total), 0) as ca
            FROM commandes
            WHERE YEAR(date_commande) = :annee AND statut = 'livree'
            GROUP BY MONTH(date_commande)
            ORDER BY mois
        ");
        $stmt->execute([':annee' => $annee]);
        $results = $stmt->fetchAll();
        
        // Remplir les mois manquants avec 0
        $caParMois = array_fill(1, 12, 0);
        foreach ($results as $row) {
            $caParMois[(int)$row['mois']] = (float)$row['ca'];
        }
        
        return $caParMois;
    }

    /**
     * Récupère le chiffre d'affaires total avec filtre
     */
    public function getCA(array $filtres = []): float {
        $sql = "SELECT COALESCE(SUM(montant_total), 0) FROM commandes WHERE statut = 'livree'";
        $params = [];
        
        if (!empty($filtres['date_debut'])) {
            $sql .= " AND date_commande >= :date_debut";
            $params[':date_debut'] = $filtres['date_debut'];
        }
        
        if (!empty($filtres['date_fin'])) {
            $sql .= " AND date_commande <= :date_fin";
            $params[':date_fin'] = $filtres['date_fin'];
        }
        
        if (!empty($filtres['statut'])) {
            $sql .= " AND statut = :statut";
            $params[':statut'] = $filtres['statut'];
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (float)$stmt->fetchColumn();
    }
}