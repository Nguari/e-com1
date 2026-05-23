<?php

namespace App\Controllers;

use App\Repositories\UserRepository;
use App\Models\User;
use App\Utils\Auth;
use App\Utils\Session;
use App\Services\MailService;

/**
 * Classe AuthController - Contrôleur pour l'authentification utilisateur
 * 
 * Gère:
 * - Affichage des formulaires de login et d'inscription
 * - Traitement des soumissions de formulaires
 * - Redirections après login/logout
 */
class AuthController {

    private UserRepository $userRepository;

    public function __construct() {
        $this->userRepository = new UserRepository();
    }

    // =========================================
    // AFFICHAGE DES FORMULAIRES
    // =========================================

    /**
     * Afficher le formulaire de connexion
     */
    public function showLoginForm(): void {
        if (Auth::check()) {
            // Redirection selon le rôle si déjà connecté
            if (Auth::isAdmin()) {
                $this->redirect(url('admin/index.php'));
            } else {
                $this->redirect(url('index.php'));
            }
        }
        require __DIR__ . '/../../views/auth/login.php';
    }

    /**
     * Afficher le formulaire d'inscription
     */
    public function showRegisterForm(): void {
        if (Auth::check()) {
            $this->redirect(url('index.php'));
        }
        require __DIR__ . '/../../views/auth/register.php';
    }

    // =========================================
    // TRAITEMENT DES FORMULAIRES
    // =========================================

    /**
     * Traitement du formulaire de connexion
     */
    public function login(): void {
        $email    = trim($_POST['email']    ?? '');
        $password = $_POST['password']      ?? '';

        // Validation : champs vides
        if (empty($email) || empty($password)) {
            Session::flash('error', 'Veuillez remplir tous les champs.');
            $this->redirect(url('login.php'));
            return;
        }

        // Validation : format email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Adresse email invalide.');
            $this->redirect(url('login.php'));
            return;
        }

        // Tentative de connexion
        if (Auth::attempt($email, $password)) {
            Session::flash('success', 'Bienvenue ' . Auth::user()->getFullName() . ' !');
            // ✅ Redirection selon le rôle
            if (Auth::isAdmin()) {
                $this->redirect(url('admin/index.php'));
            } else {
                $this->redirect(url('index.php'));
            }
        } else {
            Session::flash('error', 'Email ou mot de passe incorrect.');
            $this->redirect(url('login.php'));
        }
    }

    /**
     * Traitement du formulaire d'inscription
     */
    public function register(): void {
        $nom      = trim($_POST['nom']      ?? '');
        $prenom   = trim($_POST['prenom']   ?? '');
        $email    = trim($_POST['email']    ?? '');
        $password = $_POST['password']      ?? '';
        $password2= $_POST['password2']     ?? '';
        $tel      = trim($_POST['tel']      ?? '');

        // Sauvegarder les anciens champs pour les réafficher en cas d'erreur
        $_SESSION['old'] = compact('nom', 'prenom', 'email', 'tel');

        // Validation : champs obligatoires
        if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
            Session::flash('error', 'Veuillez remplir tous les champs obligatoires.');
            $this->redirect(url('register.php'));
            return;
        }

        // Validation : format email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Adresse email invalide.');
            $this->redirect(url('register.php'));
            return;
        }

        // Validation : longueur mot de passe
        if (strlen($password) < PASSWORD_MIN_LENGTH) {
            Session::flash('error', 'Le mot de passe doit contenir au moins ' . PASSWORD_MIN_LENGTH . ' caractères.');
            $this->redirect(url('register.php'));
            return;
        }

        // Validation : confirmation mot de passe
        if ($password !== $password2) {
            Session::flash('error', 'Les mots de passe ne correspondent pas.');
            $this->redirect(url('register.php'));
            return;
        }

        // Vérifier si l'email est déjà utilisé
        if ($this->userRepository->emailExiste($email)) {
            Session::flash('error', 'Cette adresse email est déjà utilisée.');
            $this->redirect(url('register.php'));
            return;
        }

        // Créer le nouvel utilisateur
        $user = new User();
        $user->setNom($nom)
             ->setPrenom($prenom)
             ->setEmail($email)
             ->setPassword($password)
             ->setTel(!empty($tel) ? $tel : null);

        // Sauvegarder en BDD
        if ($this->userRepository->save($user)) {
            unset($_SESSION['old']);
            Auth::login($user);
            // Email de bienvenue
            try {
                MailService::sendWelcome($user->getPrenom(), $user->getEmail());
            } catch (\Exception $e) {
                error_log("[AuthController] Email bienvenue : " . $e->getMessage());
            }
            Session::flash('success', 'Inscription réussie ! Bienvenue ' . $user->getFullName() . ' !');
            $this->redirect(url('index.php'));
        } else {
            Session::flash('error', 'Une erreur est survenue lors de l\'inscription.');
            $this->redirect(url('register.php'));
        }
    }

    // =========================================
    // DÉCONNEXION
    // =========================================

    /**
     * Déconnecter l'utilisateur
     */
    public function logout(): void {
        Auth::logout();
        Session::flash('success', 'Vous avez été déconnecté avec succès.');
        $this->redirect(url('login.php'));
    }

    // =========================================
    // UTILITAIRES
    // =========================================

    /**
     * Rediriger vers une URL
     *
     * @param string $path URL complète
     */
    private function redirect(string $path): void {
        header('Location: ' . $path);
        exit();
    }
}