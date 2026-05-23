<?php
require_once dirname(__DIR__, 2) . '/config/config.php';

use App\Config\Database;
use App\Utils\Auth;
use App\Utils\Session;

// Vérifier si l'utilisateur est admin
if (!Auth::check() || !Auth::isAdmin()) {
    header('Location: ' . url('login.php'));
    exit();
}

// Vérifier le token CSRF
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $_SESSION['flash_error'] = "Erreur de sécurité. Veuillez réessayer.";
    header('Location: ' . url('admin/commandes.php'));
    exit();
}

$commandeId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$nouveauStatut = isset($_POST['statut']) ? trim($_POST['statut']) : '';

// Liste des statuts valides
$statutsValides = ['en_attente', 'confirmee', 'en_preparation', 'expediee', 'livree', 'annulee'];

if ($commandeId <= 0 || !in_array($nouveauStatut, $statutsValides)) {
    $_SESSION['flash_error'] = "Données invalides.";
    header('Location: ' . url('admin/commandes.php'));
    exit();
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Récupérer l'ancien statut
    $stmt = $db->prepare("SELECT statut FROM commandes WHERE id_commande = :id");
    $stmt->execute([':id' => $commandeId]);
    $commande = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$commande) {
        $_SESSION['flash_error'] = "Commande introuvable.";
        header('Location: ' . url('admin/commandes.php'));
        exit();
    }
    
    $ancienStatut = $commande['statut'];
    
    // Récupérer l'email du client
    $stmtUser = $db->prepare("
        SELECT u.email 
        FROM commandes c
        JOIN utilisateurs u ON c.id_utilisateur = u.id_utilisateur
        WHERE c.id_commande = :id
    ");
    $stmtUser->execute([':id' => $commandeId]);
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);
    $emailClient = $user['email'] ?? '';
    
    // Mettre à jour le statut
    $stmt = $db->prepare("UPDATE commandes SET statut = :statut WHERE id_commande = :id");
    $stmt->execute([':statut' => $nouveauStatut, ':id' => $commandeId]);
    
    // Si la commande est livrée, on peut aussi mettre à jour le statut du paiement
    if ($nouveauStatut === 'livree') {
        // Vérifier si la table paiements existe
        try {
            $stmt = $db->prepare("UPDATE paiements SET statut = 'paye' WHERE id_commande = :id");
            $stmt->execute([':id' => $commandeId]);
        } catch (Exception $e) {
            // Table paiements n'existe pas, ignorer
        }
    }
    
    // Si la commande est annulée
    if ($nouveauStatut === 'annulee') {
        // Remettre les produits en stock
        $stmt = $db->prepare("
            SELECT p.id_produit, lc.quantite 
            FROM lignes_commande lc
            JOIN produits p ON lc.id_produit = p.id_produit
            WHERE lc.id_commande = :id
        ");
        $stmt->execute([':id' => $commandeId]);
        $lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($lignes as $ligne) {
            $updateStock = $db->prepare("UPDATE produits SET stock = stock + :quantite WHERE id_produit = :id");
            $updateStock->execute([':quantite' => $ligne['quantite'], ':id' => $ligne['id_produit']]);
        }
    }
    
    // Envoyer un email au client (optionnel)
    if ($ancienStatut !== $nouveauStatut && !empty($emailClient)) {
        envoyerEmailStatut($emailClient, $commandeId, $ancienStatut, $nouveauStatut);
    }
    
    $_SESSION['flash_success'] = "Statut de la commande mis à jour : " . ucfirst(str_replace('_', ' ', $nouveauStatut));
    
} catch (Exception $e) {
    error_log("Erreur mise à jour statut: " . $e->getMessage());
    $_SESSION['flash_error'] = "Erreur lors de la mise à jour du statut.";
}

// Redirection vers la page de détail
header('Location: ' . url('admin/commande_detail.php?id=' . $commandeId));
exit();

/**
 * Envoie un email de notification au client
 */
function envoyerEmailStatut($email, $commandeId, $ancienStatut, $nouveauStatut) {
    // Labels des statuts
    $statutLabels = [
        'en_attente' => 'En attente',
        'confirmee' => 'Confirmée',
        'en_preparation' => 'En préparation',
        'expediee' => 'Expédiée',
        'livree' => 'Livrée',
        'annulee' => 'Annulée'
    ];
    
    $sujet = "Mise à jour de votre commande #" . $commandeId;
    
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #16a34a; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9fafb; }
            .footer { text-align: center; padding: 10px; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>NGAARY SHOP</h2>
            </div>
            <div class='content'>
                <p>Bonjour,</p>
                <p>Le statut de votre commande <strong>#" . $commandeId . "</strong> a été mis à jour :</p>
                <p>
                    <strong>Ancien statut :</strong> " . $statutLabels[$ancienStatut] . "<br>
                    <strong>Nouveau statut :</strong> " . $statutLabels[$nouveauStatut] . "
                </p>
                <p>Vous pouvez suivre votre commande en vous connectant à votre espace client.</p>
                <p>Cordialement,<br>L'équipe NGAARY SHOP</p>
            </div>
            <div class='footer'>
                &copy; " . date('Y') . " NGAARY SHOP - Tous droits réservés
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: NGAARY SHOP <noreply@ngaary.sn>" . "\r\n";
    
    // Envoyer l'email en production uniquement
    if (defined('APP_ENV') && APP_ENV === 'production') {
        @mail($email, $sujet, $message, $headers);
    }
}