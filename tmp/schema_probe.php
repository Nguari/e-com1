<?php
require 'C:\laragon\www\e-com\config\config.php';
require 'C:\laragon\www\e-com\src\Config\Database.php';
$db = App\Config\Database::getInstance()->getConnection();
$tables = ['utilisateurs','panier','produits','commandes','adresses','lignes_commande','paiements'];
foreach ($tables as $t) {
    try {
        $stmt = $db->query('DESCRIBE ' . $t);
        echo "TABLE $t\n";
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            echo ' - ' . $row['Field'] . ' : ' . $row['Type'] . PHP_EOL;
        }
    } catch (Throwable $e) {
        echo "TABLE $t MISSING: " . $e->getMessage() . PHP_EOL;
    }
    echo "---\n";
}
