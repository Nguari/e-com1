<?php
require_once '../config/config.php';

use App\Utils\Session;
use App\Models\User;
use App\Repositories\UserRepository;

Session::start();

// 1. Vérification basique
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit();
}

// 2. Récupération et validation des données
$nom      = trim($_POST['nom'] ?? '');
$prenom   = trim($_POST['prenom'] ?? '');
$email    = filter_var($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!$email || empty($password) || empty($nom)) {
    die("Données invalides.");
}

// 3. Création de l'objet User (L'entité)
$user = new User();
$user->setNom($nom)
     ->setPrenom($prenom)
     ->setEmail($email)
     ->setRole('client') // Rôle par défaut
     ->setIsActive(true);

// Utilise password_hash pour le mot de passe avant de l'envoyer au Repository
$user->setPasswordHash(password_hash($password, PASSWORD_BCRYPT));

// 4. Utilisation du Repository pour l'insertion
$userRepo = new UserRepository();

// Vérifier si l'email existe déjà
if ($userRepo->findByEmail($email)) {
    die("Cet email est déjà utilisé.");
}

// L'insertion SQL se fait ici via ta méthode save()
if ($userRepo->save($user)) {
    // Succès ! Redirection vers la page de connexion
    header('Location: login.php?registered=1');
} else {
    // Si ça échoue, regarde ton fichier /logs/repository.log
    echo "Erreur lors de l'inscription. Vérifiez les logs.";
}
exit();
