<?php
require 'C:\laragon\www\e-com\config\config.php';
require 'C:\laragon\www\e-com\src\Models\BaseEntity.php';
require 'C:\laragon\www\e-com\src\Models\User.php';

$u = new App\Models\User();
$u->setRole('admin');
$u->setPasswordHash(password_hash('secret', PASSWORD_BCRYPT));
var_dump($u->isAdmin());
var_dump($u->verifyPassword('secret'));
var_dump($u->verifyPassword('bad'));
