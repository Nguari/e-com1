<?php

namespace App\Models;

use PDO;

class PaymentModel {
    
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function create($data) {
        $sql = "INSERT INTO transactions (reference, id_commande, id_utilisateur, montant, mode_paiement, phone, statut) 
                VALUES (:reference, :id_commande, :id_utilisateur, :montant, :mode_paiement, :phone, :statut)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }
    
    public function updateStatus($reference, $statut, $transactionId = null) {
        $sql = "UPDATE transactions SET statut = :statut, numero_transaction = :transaction_id, date_paiement = NOW() 
                WHERE reference = :reference";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':statut' => $statut,
            ':transaction_id' => $transactionId,
            ':reference' => $reference
        ]);
    }
    
    public function getByReference($reference) {
        $stmt = $this->db->prepare("SELECT * FROM transactions WHERE reference = :reference");
        $stmt->execute([':reference' => $reference]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getByCommande($commandeId) {
        $stmt = $this->db->prepare("SELECT * FROM transactions WHERE id_commande = :commande");
        $stmt->execute([':commande' => $commandeId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}