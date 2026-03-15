<?php

namespace App\Models;

/**
 * Classe CartItem
 * 
 * Représente une ligne du panier (un produit + quantité)
 * Hérite de BaseEntity (id, createdAt, updatedAt)
 */
class CartItem extends BaseEntity {

    private int    $idUtilisateur;
    private int    $idProduit;
    private int    $quantite;
    private string $nomProduit;
    private float  $prixUnitaire;
    private ?string $imageProduit = null;

    // =========================================
    // CONSTRUCTEUR
    // =========================================
    public function __construct(
        int    $idUtilisateur = 0,
        int    $idProduit     = 0,
        int    $quantite      = 1,
        string $nomProduit    = '',
        float  $prixUnitaire  = 0.0
    ) {
        parent::__construct();
        $this->idUtilisateur = $idUtilisateur;
        $this->idProduit     = $idProduit;
        $this->quantite      = $quantite;
        $this->nomProduit    = $nomProduit;
        $this->prixUnitaire  = $prixUnitaire;
    }

    // =========================================
    // GETTERS
    // =========================================
    public function getIdUtilisateur(): int   { return $this->idUtilisateur; }
    public function getIdProduit(): int        { return $this->idProduit; }
    public function getQuantite(): int         { return $this->quantite; }
    public function getNomProduit(): string    { return $this->nomProduit; }
    public function getPrixUnitaire(): float   { return $this->prixUnitaire; }
    public function getImageProduit(): ?string { return $this->imageProduit; }

    // =========================================
    // SETTERS
    // =========================================
    public function setIdUtilisateur(int $id): self    { $this->idUtilisateur = $id; return $this; }
    public function setIdProduit(int $id): self         { $this->idProduit = $id; return $this; }
    public function setQuantite(int $quantite): self    { $this->quantite = max(1, $quantite); return $this; }
    public function setNomProduit(string $nom): self    { $this->nomProduit = $nom; return $this; }
    public function setPrixUnitaire(float $prix): self  { $this->prixUnitaire = $prix; return $this; }
    public function setImageProduit(?string $img): self { $this->imageProduit = $img; return $this; }

    // =========================================
    // MÉTHODES MÉTIER
    // =========================================

    /**
     * Calcule le sous-total de cette ligne
     */
    public function getSousTotal(): float {
        return $this->prixUnitaire * $this->quantite;
    }

    /**
     * Incrémente la quantité
     */
    public function incrementer(int $qty = 1): self {
        $this->quantite += $qty;
        return $this;
    }

    /**
     * Décrémente la quantité (minimum 1)
     */
    public function decrementer(int $qty = 1): self {
        $this->quantite = max(1, $this->quantite - $qty);
        return $this;
    }

    // =========================================
    // MÉTHODE ABSTRAITE IMPLÉMENTÉE
    // =========================================
    public function toArray(): array {
        return [
            'id'             => $this->getId(),
            'id_utilisateur' => $this->idUtilisateur,
            'id_produit'     => $this->idProduit,
            'quantite'       => $this->quantite,
            'nom_produit'    => $this->nomProduit,
            'prix_unitaire'  => $this->prixUnitaire,
            'sous_total'     => $this->getSousTotal(),
            'image_produit'  => $this->imageProduit,
            'created_at'     => $this->getCreatedAt()?->format('Y-m-d H:i:s'),
        ];
    }
}