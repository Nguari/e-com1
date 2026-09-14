<?php
require 'C:/laragon/www/e-com/config/config.php';
$db = App\Config\Database::getInstance()->getConnection();
$tables = ['utilisateurs','produits','panier','commandes','paiements','settings'];
foreach ($tables as $table) {
    try {
        $db->query('SELECT 1 FROM ' . $table . ' LIMIT 1');
        echo 'TABLE_OK ' . $table . PHP_EOL;
    } catch (Throwable $e) {
        echo 'TABLE_FAIL ' . $table . ' => ' . $e->getMessage() . PHP_EOL;
    }
}
