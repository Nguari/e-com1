<?php

namespace App\Services;

use PDO;

class OrderService {
    
    private PDO $db;
    
    public function __construct(PDO $db) {
        $this->db = $db;
    }
    
    public function createFromCart($cart, $userId, $adresse, $modePaiement, $notes) {
        try {
            $numeroCommande = 'CMD-' . date('Ymd') . '-' . uniqid();
            $montantTotal = $cart->getTotal();
            
            // Insérer l'adresse
            $sqlAdresse = "INSERT INTO adresses (
                                id_utilisateur, 
                                nom_complet, 
                                rue, 
                                ville, 
                                code_postal, 
                                tel, 
                                pays
                            ) VALUES (
                                :id_utilisateur, 
                                :nom_complet, 
                                :rue, 
                                :ville, 
                                :code_postal, 
                                :tel, 
                                :pays
                            )";
            $stmtAdresse = $this->db->prepare($sqlAdresse);
            $stmtAdresse->execute([
                ':id_utilisateur' => $userId,
                ':nom_complet'    => $adresse['nom_complet'],
                ':rue'            => $adresse['rue'],
                ':ville'          => $adresse['ville'],
                ':code_postal'    => $adresse['code_postal'],
                ':tel'            => $adresse['telephone'],
                ':pays'           => $adresse['pays']
            ]);
            $adresseId = $this->db->lastInsertId();
            
            // Insérer la commande
            $sql = "INSERT INTO commandes (
                        numero_commande, 
                        id_utilisateur, 
                        id_adresse,
                        montant_total, 
                        statut, 
                        notes, 
                        date_commande
                    ) VALUES (
                        :numero_commande, 
                        :id_utilisateur, 
                        :id_adresse,
                        :montant_total, 
                        :statut, 
                        :notes, 
                        NOW()
                    )";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':numero_commande' => $numeroCommande,
                ':id_utilisateur'  => $userId,
                ':id_adresse'      => $adresseId,
                ':montant_total'   => $montantTotal,
                ':statut'          => 'en_attente',
                ':notes'           => $notes ?? ''
            ]);
            $commandeId = $this->db->lastInsertId();
            
            // Lignes de commande
            foreach ($cart->getItems() as $item) {
                $sousTotal = $item->getPrixUnitaire() * $item->getQuantite();
                $sqlLigne = "INSERT INTO lignes_commande (
                                id_commande, 
                                id_produit, 
                                nom_produit, 
                                quantite, 
                                prix_unitaire, 
                                sous_total
                            ) VALUES (
                                :id_commande, 
                                :id_produit, 
                                :nom_produit, 
                                :quantite, 
                                :prix_unitaire, 
                                :sous_total
                            )";
                $stmtLigne = $this->db->prepare($sqlLigne);
                $stmtLigne->execute([
                    ':id_commande'   => $commandeId,
                    ':id_produit'    => $item->getIdProduit(),
                    ':nom_produit'   => $item->getNomProduit(),
                    ':quantite'      => $item->getQuantite(),
                    ':prix_unitaire' => $item->getPrixUnitaire(),
                    ':sous_total'    => $sousTotal
                ]);
            }
            
            // Vider le panier
            $stmt = $this->db->prepare("DELETE FROM panier WHERE id_utilisateur = :id");
            $stmt->execute([':id' => $userId]);
            
            return [
                'id_commande'      => $commandeId,
                'numero_commande'  => $numeroCommande,
                'montant_total'    => $montantTotal,
                'statut'           => 'en_attente'
            ];
            
        } catch (\Exception $e) {
            error_log("OrderService Error: " . $e->getMessage());
            throw $e;
        }
    }
}