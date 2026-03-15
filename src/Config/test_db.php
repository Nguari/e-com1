<?php
// public/test_db.php
require_once '../config/config.php';
use App\Repositories\UserRepository;
use App\Models\User;

$user = new User();
$user->setNom('Test')->setPrenom('User')->setEmail('test'.time().'@mail.com');
$user->setPasswordHash(password_hash('123456', PASSWORD_BCRYPT));
$user->setRole('client')->setIsActive(true);

$repo = new UserRepository();
if ($repo->save($user)) {
    echo "Succès ! ID généré : " . $user->getId();
} else {
    echo "Échec. Regarde le fichier : " . ROOT_PATH . "/logs/repository.log";
}
