<?php
$files = [
  'C:/laragon/www/e-com/public/index.php',
  'C:/laragon/www/e-com/public/boutique.php',
  'C:/laragon/www/e-com/public/Promotions.php',
  'C:/laragon/www/e-com/public/login.php',
  'C:/laragon/www/e-com/public/checkout.php',
  'C:/laragon/www/e-com/public/admin/parametres.php'
];
foreach ($files as $file) {
    try {
        ob_start();
        include $file;
        ob_end_clean();
        echo "OK " . $file . PHP_EOL;
    } catch (Throwable $e) {
        echo "FAIL " . $file . ' => ' . $e->getMessage() . PHP_EOL;
    }
}
