<?php

namespace App\Repositories;

use App\Models\BaseEntity;

class ProduitRepository extends BaseRepository {

    protected string $tableName  = 'produits';
    protected string $primaryKey = 'id_produit';
    protected string $orderBy    = 'date_ajout';

    protected function hydrate(array $data): BaseEntity {
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
     * Tous les produits actifs avec catégorie et image principale
     */
    public function findAllWithCategorie(): array {
        $sql = "SELECT p.*,
                       c.nom AS categorie_nom,
                       (SELECT url FROM images_produits
                        WHERE id_produit = p.id_produit
                        AND principale = 1 LIMIT 1) AS image
                FROM produits p
                LEFT JOIN categories c ON p.id_categorie = c.id_categorie
                WHERE p.actif = 1
                ORDER BY p.date_ajout DESC";
        return $this->pdo->query($sql)->fetchAll();
    }

    /**
     * Produits filtrés par catégorie
     */
    public function findByCategorie(string $categorie): array {
        $sql = "SELECT p.*,
                       c.nom AS categorie_nom,
                       (SELECT url FROM images_produits
                        WHERE id_produit = p.id_produit
                        AND principale = 1 LIMIT 1) AS image
                FROM produits p
                LEFT JOIN categories c ON p.id_categorie = c.id_categorie
                WHERE p.actif = 1 AND c.nom = :categorie
                ORDER BY p.date_ajout DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':categorie' => $categorie]);
        return $stmt->fetchAll();
    }

    /**
     * Meilleures ventes pour la page d'accueil
     */
    public function getMeilleuresVentes(int $limit = 8): array {
        $stmt = $this->pdo->prepare("
            SELECT p.*,
                   c.nom AS categorie_nom,
                   (SELECT url FROM images_produits
                    WHERE id_produit = p.id_produit
                    AND principale = 1 LIMIT 1) AS image,
                   COALESCE(SUM(lc.quantite), 0) AS total_vendu
            FROM produits p
            LEFT JOIN categories c ON p.id_categorie = c.id_categorie
            LEFT JOIN lignes_commande lc ON p.id_produit = lc.id_produit
            WHERE p.actif = 1
            GROUP BY p.id_produit
            ORDER BY total_vendu DESC, p.date_ajout DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Produits en promotion
     */
    public function getEnPromotion(): array {
        $sql = "SELECT p.*,
                       c.nom AS categorie_nom,
                       (SELECT url FROM images_produits
                        WHERE id_produit = p.id_produit
                        AND principale = 1 LIMIT 1) AS image
                FROM produits p
                LEFT JOIN categories c ON p.id_categorie = c.id_categorie
                WHERE p.actif = 1 AND p.prix_promo IS NOT NULL
                ORDER BY p.date_ajout DESC";
        return $this->pdo->query($sql)->fetchAll();
    }

    /**
     * Toutes les catégories avec le nombre de produits
     */
    public function getCategories(): array {
        $sql = "SELECT c.nom, c.id_categorie,
                       COUNT(p.id_produit) AS nb_produits
                FROM categories c
                LEFT JOIN produits p ON c.id_categorie = p.id_categorie AND p.actif = 1
                GROUP BY c.id_categorie
                ORDER BY c.nom";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function create(array $data): bool {
        $sql = "INSERT INTO produits (reference, nom, description, prix, prix_promo, stock, id_categorie, actif)
                VALUES (:reference, :nom, :description, :prix, :prix_promo, :stock, :id_categorie, :actif)";
        return $this->executeCommand($sql, $data);
    }

    public function update(int $id, array $data): bool {
        $sql = "UPDATE produits SET
                    nom          = :nom,
                    description  = :description,
                    prix         = :prix,
                    prix_promo   = :prix_promo,
                    stock        = :stock,
                    id_categorie = :id_categorie,
                    actif        = :actif
                WHERE id_produit = :id";
        $data[':id'] = $id;
        return $this->executeCommand($sql, $data);
    }

    public function toggleActif(int $id): bool {
        return $this->executeCommand(
            "UPDATE produits SET actif = NOT actif WHERE id_produit = :id",
            [':id' => $id]
        );
    }

    public function countActifs(): int {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM produits WHERE actif = 1");
        return (int)$stmt->fetchColumn();
    }

    public function getPlusVendus(int $limit = 5): array {
        $stmt = $this->pdo->prepare("
            SELECT p.id_produit, p.nom, p.prix,
                   COALESCE(SUM(lc.quantite), 0) AS total_vendu
            FROM produits p
            LEFT JOIN lignes_commande lc ON p.id_produit = lc.id_produit
            WHERE p.actif = 1
            GROUP BY p.id_produit
            ORDER BY total_vendu DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}