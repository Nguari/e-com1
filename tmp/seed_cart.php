<?php
require 'C:\laragon\www\e-com\config\config.php';
require 'C:\laragon\www\e-com\src\Config\Database.php';
$db = App\Config\Database::getInstance()->getConnection();
// Nettoyer une éventuelle donnée de test précédente
$db->query("DELETE FROM panier WHERE id_utilisateur = 1");
$db->query("INSERT INTO panier (id_utilisateur, id_produit, quantite, date_ajout) VALUES (1, 1, 1, NOW())");
$db->query("INSERT INTO panier (id_utilisateur, id_produit, quantite, date_ajout) VALUES (1, 2, 2, NOW())");
$stmt = $db->query("SELECT p.id_panier, p.id_produit, pr.nom, p.quantite, pr.prix FROM panier p JOIN produits pr ON pr.id_produit = p.id_produit WHERE p.id_utilisateur = 1");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo $row['id_produit'] . ' | ' . $row['nom'] . ' | qty=' . $row['quantite'] . ' | prix=' . $row['prix'] . PHP_EOL;
}
