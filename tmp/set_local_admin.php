<?php
require 'C:\laragon\www\e-com\config\config.php';
require 'C:\laragon\www\e-com\src\Config\Database.php';

$db = App\Config\Database::getInstance()->getConnection();
$email = 'admin@ecommerce.sn';
$password = 'Admin123!';
$hash = password_hash($password, PASSWORD_BCRYPT);
$stmt = $db->prepare("SELECT id_utilisateur FROM utilisateurs WHERE email = :email LIMIT 1");
$stmt->execute([':email' => $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    $upd = $db->prepare("UPDATE utilisateurs SET role = 'admin', mot_de_passe = :hash, actif = 1 WHERE email = :email");
    $upd->execute([':hash' => $hash, ':email' => $email]);
    echo "UPDATED\n";
} else {
    $ins = $db->prepare("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, tel, role, actif) VALUES (:nom, :prenom, :email, :hash, :tel, 'admin', 1)");
    $ins->execute([
        ':nom' => 'Admin',
        ':prenom' => 'Test',
        ':email' => $email,
        ':hash' => $hash,
        ':tel' => '770000000'
    ]);
    echo "INSERTED\n";
}
