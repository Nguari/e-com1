<?php
require 'C:\laragon\www\e-com\config\config.php';
require 'C:\laragon\www\e-com\src\Config\Database.php';
$db = App\Config\Database::getInstance()->getConnection();
$stmt = $db->query("SELECT id_produit, nom, prix, stock FROM produits LIMIT 5");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $p) {
    echo $p['id_produit'] . ' | ' . $p['nom'] . ' | ' . $p['prix'] . ' | stock=' . $p['stock'] . PHP_EOL;
}
