<?php

namespace App\Repositories;

use PDO;

class ProduitRepository {
    
    private PDO $db;
    
    public function __construct(PDO $db) {
        $this->db = $db;
    }
    
    /**
     * Trouve tous les produits avec leur catégorie
     */
    public function findAllWithCategorie(): array {
        $sql = "SELECT p.*, c.nom as categorie_nom 
                FROM produits p 
                LEFT JOIN categories c ON p.id_categorie = c.id_categorie 
                ORDER BY p.id_produit DESC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Trouve un produit par son ID
     */
    public function findById(int $id): ?array {
        $sql = "SELECT p.*, c.nom as categorie_nom 
                FROM produits p 
                LEFT JOIN categories c ON p.id_categorie = c.id_categorie 
                WHERE p.id_produit = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ?: null;
    }
    
    /**
     * Crée un nouveau produit
     */
    public function create(array $data): bool {
        $sql = "INSERT INTO produits (reference, nom, description, images, prix, prix_promo, stock, id_categorie, actif) 
                VALUES (:reference, :nom, :description, :images, :prix, :prix_promo, :stock, :id_categorie, :actif)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }
    
    /**
     * Met à jour un produit
     */
    public function update(int $id, array $data): bool {
        $data[':id'] = $id;
        
        $sql = "UPDATE produits 
                SET nom = :nom, 
                    description = :description, 
                    images = :images, 
                    prix = :prix, 
                    prix_promo = :prix_promo, 
                    stock = :stock, 
                    id_categorie = :id_categorie, 
                    actif = :actif 
                WHERE id_produit = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }
    
    /**
     * Supprime un produit
     */
    public function delete(int $id): bool {
        $sql = "DELETE FROM produits WHERE id_produit = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
    
    /**
     * Active/Désactive un produit
     */
    public function toggleActif(int $id): bool {
        $sql = "UPDATE produits SET actif = NOT actif WHERE id_produit = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Récupère le nombre total de produits
     */
    public function getAllProduitsCount(): int {
        $sql = "SELECT COUNT(*) FROM produits WHERE actif = 1";
        $stmt = $this->db->query($sql);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Récupère les meilleures ventes (basé sur les commandes)
     */
    public function getMeilleuresVentes(int $limit = 8): array {
        $sql = "SELECT p.*, c.nom as categorie_nom, COALESCE(SUM(lc.quantite), 0) as total_vendus
                FROM produits p
                LEFT JOIN categories c ON p.id_categorie = c.id_categorie
                LEFT JOIN lignes_commande lc ON p.id_produit = lc.id_produit
                LEFT JOIN commandes cmd ON lc.id_commande = cmd.id_commande AND cmd.statut = 'livree'
                WHERE p.actif = 1
                GROUP BY p.id_produit
                ORDER BY total_vendus DESC, p.id_produit DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Si pas de ventes, afficher les produits récents
        if (empty($results) || $results[0]['total_vendus'] == 0) {
            return $this->getProduitsRecents($limit);
        }
        
        return $results;
    }

    /**
     * Récupère les produits récents (fallback pour meilleures ventes)
     */
    public function getProduitsRecents(int $limit = 8): array {
        $sql = "SELECT p.*, c.nom as categorie_nom 
                FROM produits p
                LEFT JOIN categories c ON p.id_categorie = c.id_categorie
                WHERE p.actif = 1
                ORDER BY p.id_produit DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère toutes les catégories avec nombre de produits
     */
    public function getCategories(): array {
        $sql = "SELECT c.*, COUNT(p.id_produit) as nb_produits 
                FROM categories c
                LEFT JOIN produits p ON c.id_categorie = p.id_categorie AND p.actif = 1
                GROUP BY c.id_categorie
                ORDER BY c.nom";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ========== MÉTHODES AJOUTÉES POUR LE DASHBOARD ==========

    /**
     * Compte les produits actifs
     */
    public function countActifs(): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM produits WHERE actif = 1");
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    /**
     * Récupère les produits les plus vendus (pour dashboard)
     */
    public function getPlusVendus(int $limit = 5): array {
        $sql = "SELECT 
                    p.id_produit, 
                    p.nom, 
                    p.prix, 
                    p.image,
                    p.images,
                    p.stock,
                    COALESCE(SUM(lc.quantite), 0) as total_vendus
                FROM produits p
                LEFT JOIN lignes_commande lc ON p.id_produit = lc.id_produit
                LEFT JOIN commandes c ON lc.id_commande = c.id_commande AND c.statut = 'livree'
                WHERE p.actif = 1
                GROUP BY p.id_produit
                ORDER BY total_vendus DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Compte tous les produits (actifs et inactifs)
     */
    public function count(): int {
        $stmt = $this->db->query("SELECT COUNT(*) FROM produits");
        return (int)$stmt->fetchColumn();
    }

    /**
     * Récupère les produits en stock faible
     */
    public function getStockFaible(int $seuil = 5): array {
        $sql = "SELECT * FROM produits WHERE stock <= :seuil AND actif = 1 ORDER BY stock ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':seuil' => $seuil]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Met à jour le stock d'un produit
     */
    public function updateStock(int $id, int $quantite): bool {
        $sql = "UPDATE produits SET stock = stock - :quantite WHERE id_produit = :id AND stock >= :quantite";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id, ':quantite' => $quantite]);
    }

    /**
     * Trouve les produits par nom de catégorie
     */
    public function findByCategorie(string $categorieNom): array {
        if ($categorieNom === 'Tous') {
            return $this->findAllWithCategorie();
        }
        
        $sql = "SELECT p.*, c.nom as categorie_nom 
                FROM produits p 
                LEFT JOIN categories c ON p.id_categorie = c.id_categorie 
                WHERE c.nom = :categorie_nom AND p.actif = 1
                ORDER BY p.id_produit DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':categorie_nom' => $categorieNom]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupère la première image d'un produit (méthode utilitaire)
     */
    public function getFirstImage(array $produit): string {
        // Vérifier les images multiples
        if (!empty($produit['images'])) {
            $images = json_decode($produit['images'], true);
            if (is_array($images) && !empty($images)) {
                return '/assets/img/produits/' . $images[0];
            }
        }
        
        // Fallback sur l'image unique
        if (!empty($produit['image'])) {
            return '/assets/img/produits/' . $produit['image'];
        }
        
        return '/assets/img/produits/default.jpg';
    }
}