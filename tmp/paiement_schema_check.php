<?php
require 'C:\laragon\www\e-com\config\config.php';
require 'C:\laragon\www\e-com\src\Config\Database.php';
$db = App\Config\Database::getInstance()->getConnection();
$sql = "CREATE TABLE IF NOT EXISTS paiements_backup AS SELECT * FROM paiements WHERE 1=0";
$db->exec($sql);
$check = $db->query("DESCRIBE paiements");
foreach ($check->fetchAll(PDO::FETCH_ASSOC) as $c) {
    echo $c['Field'] . ' => ' . $c['Type'] . PHP_EOL;
}
