<?php
require 'C:\laragon\www\e-com\config\config.php';
require 'C:\laragon\www\e-com\src\Models\BaseEntity.php';
require 'C:\laragon\www\e-com\src\Models\Cart.php';
require 'C:\laragon\www\e-com\src\Models\CartItem.php';
require 'C:\laragon\www\e-com\src\Repositories\BaseRepository.php';
require 'C:\laragon\www\e-com\src\Repositories\CartRepository.php';
require 'C:\laragon\www\e-com\src\Config\Database.php';
require 'C:\laragon\www\e-com\src\Services\OrderService.php';
$db = App\Config\Database::getInstance()->getConnection();
$repo = new App\Repositories\CartRepository();
$cart = $repo->getCartByUser(1);
var_dump($cart->isEmpty());
var_dump($cart->getTotal());
foreach ($cart->getItems() as $item) { echo $item->getNomProduit() . ' x' . $item->getQuantite() . ' - ' . $item->getPrixUnitaire() . PHP_EOL; }
