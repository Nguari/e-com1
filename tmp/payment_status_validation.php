<?php
require 'C:\laragon\www\e-com\config\config.php';
require 'C:\laragon\www\e-com\src\Config\Database.php';
$db = App\Config\Database::getInstance()->getConnection();
$sql = "INSERT INTO paiements (id_commande, montant, mode_paiement, statut, transaction_id, date_paiement) VALUES (999999, 15000, 'Wave', 'valide', 'VALIDATION_CHECK_1', NOW()) ON DUPLICATE KEY UPDATE statut = VALUES(statut)";
try { $db->exec($sql); echo "INSERT_OK\n"; } catch (Throwable $e) { echo "ERR: " . $e->getMessage() . "\n"; }
$stmt = $db->query("SELECT statut FROM paiements WHERE transaction_id = 'VALIDATION_CHECK_1' LIMIT 1");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
var_dump($row['statut'] ?? 'NONE');
