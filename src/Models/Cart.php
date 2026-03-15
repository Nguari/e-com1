<?php

namespace App\Models;

/**
 * Classe Cart
 * 
 * Représente le panier d'un utilisateur
 * Contient une collection de CartItem
 */
class Cart {

    private int   $idUtilisateur;
    private array $items = []; // CartItem[]

    // =========================================
    // CONSTRUCTEUR
    // =========================================
    public function __construct(int $idUtilisateur) {
        $this->idUtilisateur = $idUtilisateur;
    }

    // =========================================
    // GETTERS
    // =========================================
    public function getIdUtilisateur(): int { return $this->idUtilisateur; }

    /**
     * Retourne tous les articles du panier
     * @return CartItem[]
     */
    public function getItems(): array { return $this->items; }

    // =========================================
    // MÉTHODES MÉTIER
    // =========================================

    /**
     * Ajouter un article au panier
     * Si le produit existe déjà, incrémente la quantité
     */
    public function addItem(CartItem $item): self {
        $idProduit = $item->getIdProduit();

        if (isset($this->items[$idProduit])) {
            $this->items[$idProduit]->incrementer($item->getQuantite());
        } else {
            $this->items[$idProduit] = $item;
        }
        return $this;
    }

    /**
     * Supprimer un article du panier
     */
    public function removeItem(int $idProduit): self {
        unset($this->items[$idProduit]);
        return $this;
    }

    /**
     * Modifier la quantité d'un article
     */
    public function updateQuantite(int $idProduit, int $quantite): self {
        if (isset($this->items[$idProduit])) {
            if ($quantite <= 0) {
                $this->removeItem($idProduit);
            } else {
                $this->items[$idProduit]->setQuantite($quantite);
            }
        }
        return $this;
    }

    /**
     * Vider le panier
     */
    public function clear(): self {
        $this->items = [];
        return $this;
    }

    /**
     * Vérifier si le panier est vide
     */
    public function isEmpty(): bool {
        return empty($this->items);
    }

    /**
     * Nombre total d'articles (somme des quantités)
     */
    public function getNombreArticles(): int {
        return array_sum(array_map(
            fn(CartItem $item) => $item->getQuantite(),
            $this->items
        ));
    }

    /**
     * Montant total du panier
     */
    public function getTotal(): float {
        return array_sum(array_map(
            fn(CartItem $item) => $item->getSousTotal(),
            $this->items
        ));
    }

    /**
     * Vérifier si la livraison est gratuite
     */
    public function livraisonGratuite(float $seuil = 15000): bool {
        return $this->getTotal() >= $seuil;
    }

    /**
     * Vérifier si un produit est dans le panier
     */
    public function contientProduit(int $idProduit): bool {
        return isset($this->items[$idProduit]);
    }
}