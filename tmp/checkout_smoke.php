<?php
require 'C:\laragon\www\e-com\config\config.php';
require 'C:\laragon\www\e-com\src\Models\BaseEntity.php';
require 'C:\laragon\www\e-com\src\Models\Cart.php';
require 'C:\laragon\www\e-com\src\Models\CartItem.php';
require 'C:\laragon\www\e-com\src\Repositories\BaseRepository.php';
require 'C:\laragon\www\e-com\src\Repositories\CartRepository.php';
require 'C:\laragon\www\e-com\src\Config\Database.php';
require 'C:\laragon\www\e-com\src\Services\OrderService.php';
require 'C:\laragon\www\e-com\src\Services\PaymentService.php';

$db = App\Config\Database::getInstance()->getConnection();
$repo = new App\Repositories\CartRepository();
$db->exec("DELETE FROM panier WHERE id_utilisateur = 1");
$db->exec("INSERT INTO panier (id_utilisateur, id_produit, quantite, date_ajout) VALUES (1, 1, 1, NOW())");
$cart = $repo->getCartByUser(1);
$adresse = [
    'nom_complet' => 'Test User',
    'rue' => 'Rue de Test',
    'ville' => 'Dakar',
    'code_postal' => '00000',
    'telephone' => '770000000',
    'pays' => 'Sénégal'
];
$order = new App\Services\OrderService($db);
$cmd = $order->createFromCart($cart, 1, $adresse, 'especes', 'Smoke test');
$tx = 'SMOKE_' . time();
$db->prepare("INSERT INTO paiements (id_commande, montant, mode_paiement, statut, transaction_id, date_paiement) VALUES (:id, :montant, 'Especes', 'en_attente', :tx, NOW())")
   ->execute([':id' => $cmd['id_commande'], ':montant' => $cmd['montant_total'], ':tx' => $tx]);
$service = new App\Services\PaymentService($db);
var_dump($service->confirmPayment($tx));
var_dump($service->checkPaymentStatus($tx)['statut'] ?? null);
