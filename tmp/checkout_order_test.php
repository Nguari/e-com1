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
require 'C:\laragon\www\e-com\src\Utils\Session.php';
require 'C:\laragon\www\e-com\src\Utils\Auth.php';

session_start();
App\Utils\Auth::login(new App\Models\User());

$db = App\Config\Database::getInstance()->getConnection();
$repo = new App\Repositories\CartRepository();
$cart = $repo->getCartByUser(1);
var_dump($cart->isEmpty());
var_dump($cart->getTotal());

$adresse = [
  'nom_complet' => 'Test User',
  'rue' => 'Rue de Test',
  'ville' => 'Dakar',
  'code_postal' => '00000',
  'telephone' => '770000000',
  'pays' => 'Sénégal'
];
$order = new App\Services\OrderService($db);
$cmd = $order->createFromCart($cart, 1, $adresse, 'especes', 'Test checkout');
var_dump($cmd);
