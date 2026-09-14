<?php
require 'C:\laragon\www\e-com\config\config.php';
require 'C:\laragon\www\e-com\src\Models\BaseEntity.php';
require 'C:\laragon\www\e-com\src\Models\User.php';
require 'C:\laragon\www\e-com\src\Repositories\BaseRepository.php';
require 'C:\laragon\www\e-com\src\Repositories\UserRepository.php';
require 'C:\laragon\www\e-com\src\Utils\Session.php';
require 'C:\laragon\www\e-com\src\Utils\Auth.php';
$tests = ['123456', 'password', 'admin123', 'admin', 'secret'];
$hash = '$2y$10$FpsQn6.04ult6m5r6PJhNuhaRUoAVWzcK';
foreach ($tests as $pw) {
    echo $pw . ' => ' . (password_verify($pw, $hash) ? 'OK' : 'NO') . PHP_EOL;
}
$u = new App\Models\User();
$u->setRole('admin');
$u->setPasswordHash($hash);
var_dump($u->verifyPassword('123456'));
var_dump($u->verifyPassword('admin'));

session_start();
var_dump(App\Utils\Auth::attempt('aliounecissendiay@gmail.com', '123456'));
var_dump($_SESSION['user_id'] ?? null);
