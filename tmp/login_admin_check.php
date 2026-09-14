<?php
require 'C:\laragon\www\e-com\config\config.php';
require 'C:\laragon\www\e-com\src\Models\BaseEntity.php';
require 'C:\laragon\www\e-com\src\Models\User.php';
require 'C:\laragon\www\e-com\src\Repositories\BaseRepository.php';
require 'C:\laragon\www\e-com\src\Repositories\UserRepository.php';
require 'C:\laragon\www\e-com\src\Utils\Session.php';
require 'C:\laragon\www\e-com\src\Utils\Auth.php';

session_start();
var_dump(App\Utils\Auth::attempt('admin@ecommerce.sn', 'Admin123!'));
var_dump($_SESSION['user_id'] ?? null);
var_dump(App\Utils\Auth::isAdmin());
