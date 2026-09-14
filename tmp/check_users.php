<?php
require 'C:\laragon\www\e-com\config\config.php';
require 'C:\laragon\www\e-com\src\Config\Database.php';
$db = App\Config\Database::getInstance()->getConnection();
$stmt = $db->query("SELECT id_utilisateur, email, role, actif, mot_de_passe FROM utilisateurs ORDER BY id_utilisateur LIMIT 10");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo $r['email'] . ' | ' . $r['role'] . ' | ' . ($r['actif'] ? 'actif' : 'inactif') . ' | ' . substr($r['mot_de_passe'], 0, 40) . PHP_EOL;
}
