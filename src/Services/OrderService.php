<?php

namespace App\Services;

use PDO;
use App\Models\Cart;
use App\Repositories\CartRepository;

class OrderService {

    private PDO            $db;
    private CartRepository $cartRepository;

    public function __construct(PDO $db) {
        $this->db             = $db;
        $this->cartRepository = new CartRepository($db);
    }

    /**
     * Créer une commande depuis le panier
     *
     * @return array Données de la commande créée
     */
    public function createFromCart(
        Cart   $cart,
        int    $idUtilisateur,
        array  $adresse,
        string $modePaiement,
        string $notes = ''
    ): array {

        // Générer un numéro de commande unique
        $numeroCommande = 'CMD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

        // Calculer le total
        $sousTotal      = $cart->getTotal();
        $fraisLivraison = $cart->livraisonGratuite() ? 0 : 2500;
        $montantTotal   = $sousTotal + $fraisLivraison;

        $this->db->beginTransaction();

        try {
            // 1 — Insérer l'adresse de livraison
            $stmtAdresse = $this->db->prepare("
                INSERT INTO adresses (id_utilisateur, nom_complet, rue, ville, code_postal, pays, telephone, par_defaut)
                VALUES (:id_utilisateur, :nom_complet, :rue, :ville, :code_postal, :pays, :telephone, 0)
            ");
            $stmtAdresse->execute([
                ':id_utilisateur' => $idUtilisateur,
                ':nom_complet'    => $adresse['nom_complet'],
                ':rue'            => $adresse['rue'],
                ':ville'          => $adresse['ville'],
                ':code_postal'    => $adresse['code_postal'] ?? '00000',
                ':pays'           => $adresse['pays'] ?? 'Sénégal',
                ':telephone'      => $adresse['telephone'],
            ]);
            $idAdresse = (int)$this->db->lastInsertId();

            // 2 — Créer la commande
            $stmtCommande = $this->db->prepare("
                INSERT INTO commandes (numero_commande, id_utilisateur, montant_total, statut, id_adresse_livraison, notes)
                VALUES (:numero, :id_utilisateur, :montant_total, 'en_attente', :id_adresse, :notes)
            ");
            $stmtCommande->execute([
                ':numero'         => $numeroCommande,
                ':id_utilisateur' => $idUtilisateur,
                ':montant_total'  => $montantTotal,
                ':id_adresse'     => $idAdresse,
                ':notes'          => $notes,
            ]);
            $idCommande = (int)$this->db->lastInsertId();

            // 3 — Créer les lignes de commande
            $stmtLigne = $this->db->prepare("
                INSERT INTO lignes_commande (id_commande, id_produit, nom_produit, quantite, prix_unitaire, sous_total)
                VALUES (:id_commande, :id_produit, :nom_produit, :quantite, :prix_unitaire, :sous_total)
            ");
            foreach ($cart->getItems() as $item) {
                $stmtLigne->execute([
                    ':id_commande'   => $idCommande,
                    ':id_produit'    => $item->getIdProduit(),
                    ':nom_produit'   => $item->getNomProduit(),
                    ':quantite'      => $item->getQuantite(),
                    ':prix_unitaire' => $item->getPrixUnitaire(),
                    ':sous_total'    => $item->getSousTotal(),
                ]);
            }

            // 4 — Créer le paiement
            $stmtPaiement = $this->db->prepare("
                INSERT INTO paiements (id_commande, montant, mode_paiement, statut)
                VALUES (:id_commande, :montant, :mode_paiement, 'en_attente')
            ");
            $stmtPaiement->execute([
                ':id_commande'   => $idCommande,
                ':montant'       => $montantTotal,
                ':mode_paiement' => $modePaiement,
            ]);

            // 5 — Vider le panier
            $this->cartRepository->clearCart($idUtilisateur);

            $this->db->commit();

            return [
                'id'     => $idCommande,
                'numero' => $numeroCommande,
                'total'  => $montantTotal,
            ];

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}