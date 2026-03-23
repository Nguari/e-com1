<?php

namespace App\Repositories;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\BaseEntity;

/**
 * CartRepository
 * 
 * Gère toutes les interactions avec la table `panier` en BDD
 */
class CartRepository extends BaseRepository {

    protected string $tableName = 'panier';

    // =========================================
    // HYDRATATION
    // =========================================
    protected function hydrate(array $data): BaseEntity {
        $item = new CartItem(
            (int)$data['id_utilisateur'],
            (int)$data['id_produit'],
            (int)$data['quantite'],
            $data['nom']   ?? '',
            (float)($data['prix_promo'] ?? $data['prix'] ?? 0)
        );

        $item->setId((int)$data['id_panier']);

        if (!empty($data['image_principale'])) {
            $item->setImageProduit($data['image_principale']);
        }

        if (!empty($data['date_ajout'])) {
            $item->setCreatedAt(new \DateTime($data['date_ajout']));
        }

        return $item;
    }

    // =========================================
    // MÉTHODES PRINCIPALES
    // =========================================

    /**
     * Charger le panier complet d'un utilisateur (avec les infos produits)
     */
    public function getCartByUser(int $idUtilisateur): Cart {
        $cart = new Cart($idUtilisateur);

        try {
            $sql = "SELECT 
                        p.id_panier,
                        p.id_utilisateur,
                        p.id_produit,
                        p.quantite,
                        p.date_ajout,
                        pr.nom,
                        pr.prix,
                        pr.prix_promo,
                        (SELECT url FROM images_produits 
                         WHERE id_produit = pr.id_produit 
                         AND principale = TRUE LIMIT 1) AS image_principale
                    FROM {$this->tableName} p
                    JOIN produits pr ON p.id_produit = pr.id_produit
                    WHERE p.id_utilisateur = :id_utilisateur
                    ORDER BY p.date_ajout DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id_utilisateur' => $idUtilisateur]);
            $rows = $stmt->fetchAll();

            foreach ($rows as $row) {
                $cart->addItem($this->hydrate($row));
            }

        } catch (\PDOException $e) {
            $this->logError($e);
        }

        return $cart;
    }

    /**
     * Ajouter ou mettre à jour un article dans le panier
     * ✅ Deux paramètres distincts pour éviter Invalid parameter number
     */
    public function addOrUpdate(int $idUtilisateur, int $idProduit, int $quantite = 1): bool {
        try {
            $sql = "INSERT INTO {$this->tableName} (id_utilisateur, id_produit, quantite)
                    VALUES (:id_utilisateur, :id_produit, :quantite)
                    ON DUPLICATE KEY UPDATE quantite = quantite + :quantite_update";

            return $this->executeCommand($sql, [
                ':id_utilisateur'  => $idUtilisateur,
                ':id_produit'      => $idProduit,
                ':quantite'        => $quantite,
                ':quantite_update' => $quantite,
            ]);

        } catch (\PDOException $e) {
            $this->logError($e);
            return false;
        }
    }

    /**
     * Modifier la quantité d'un article
     */
    public function updateQuantite(int $idUtilisateur, int $idProduit, int $quantite): bool {
        if ($quantite <= 0) {
            return $this->removeItem($idUtilisateur, $idProduit);
        }

        $sql = "UPDATE {$this->tableName}
                SET quantite = :quantite
                WHERE id_utilisateur = :id_utilisateur 
                AND id_produit = :id_produit";

        return $this->executeCommand($sql, [
            ':quantite'       => $quantite,
            ':id_utilisateur' => $idUtilisateur,
            ':id_produit'     => $idProduit,
        ]);
    }

    /**
     * Supprimer un article du panier
     */
    public function removeItem(int $idUtilisateur, int $idProduit): bool {
        $sql = "DELETE FROM {$this->tableName}
                WHERE id_utilisateur = :id_utilisateur 
                AND id_produit = :id_produit";

        return $this->executeCommand($sql, [
            ':id_utilisateur' => $idUtilisateur,
            ':id_produit'     => $idProduit,
        ]);
    }

    /**
     * Vider le panier d'un utilisateur
     */
    public function clearCart(int $idUtilisateur): bool {
        $sql = "DELETE FROM {$this->tableName} 
                WHERE id_utilisateur = :id_utilisateur";

        return $this->executeCommand($sql, [
            ':id_utilisateur' => $idUtilisateur,
        ]);
    }

    /**
     * Compter le nombre d'articles dans le panier
     */
    public function countItems(int $idUtilisateur): int {
        $sql  = "SELECT COALESCE(SUM(quantite), 0) 
                 FROM {$this->tableName} 
                 WHERE id_utilisateur = :id_utilisateur";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id_utilisateur' => $idUtilisateur]);
        return (int)$stmt->fetchColumn();
    }
}