<?php

namespace App\Utils;

use App\Models\User;
use App\Repositories\UserRepository;

/**
 * Classe Auth - Gestion de l'authentification utilisateur
 * 
 * Gere l'authentification des utilisateurs:
 * - Login / Logout
 * - Vérifier si un utilisateur est connecté
 * - Récupérer l'utilisateur connecté
 * - Limitation des tentatives de connexion
 * 
 * Utilisation:
 * - Auth::login($user)  → connecter un utilisateur
 * - Auth::logout()      → déconnecter l'utilisateur
 * - Auth::check()       → vérifier si un utilisateur est connecté
 * - Auth::user()        → récupérer l'objet User connecté
 * - Auth::isAdmin()     → vérifier si l'utilisateur est admin
 */
class Auth {

    /**
     * Nombre maximum de tentatives autorisées
     */
    private const MAX_LOGIN_ATTEMPTS = 5;
    
    /**
     * Temps de blocage en secondes (15 minutes)
     */
    private const LOCKOUT_TIME = 900;

    /**
     * Utilisateur en cache (pour éviter plusieurs requêtes BDD)
     */
    private static ?User $currentUser = null;

    // =========================================
    // VÉRIFICATIONS
    // =========================================

    /**
     * Vérifier si un utilisateur est connecté
     */
    public static function check(): bool {
        Session::start();
        return Session::has('user_id');
    }

    /**
     * Vérifier si aucun utilisateur n'est connecté (visiteur)
     */
    public static function guest(): bool {
        return !self::check();
    }

    /**
     * Vérifier si l'utilisateur connecté est admin
     */
    public static function isAdmin(): bool {
        $user = self::user();
        return $user !== null && $user->isAdmin();
    }

    // =========================================
    // LIMITATION DES TENTATIVES DE CONNEXION
    // =========================================

    /**
     * Vérifie si l'utilisateur a dépassé le nombre de tentatives
     *
     * @param string $email Email de l'utilisateur
     * @return bool True si l'utilisateur peut encore tenter, False si bloqué
     */
    public static function checkLoginAttempts(string $email): bool {
        $key = 'login_attempts_' . md5($email);
        $attempts = $_SESSION[$key] ?? null;
        
        // Aucune tentative enregistrée
        if ($attempts === null) {
            return true;
        }
        
        // Vérifier si le temps de blocage est dépassé
        if (time() - $attempts['time'] > self::LOCKOUT_TIME) {
            // Réinitialiser les tentatives
            unset($_SESSION[$key]);
            return true;
        }
        
        // Vérifier le nombre de tentatives
        return $attempts['count'] < self::MAX_LOGIN_ATTEMPTS;
    }

    /**
     * Enregistre une tentative de connexion
     *
     * @param string $email Email de l'utilisateur
     * @param bool $success True si la connexion a réussi, False sinon
     */
    public static function registerLoginAttempt(string $email, bool $success): void {
        $key = 'login_attempts_' . md5($email);
        
        // Connexion réussie - réinitialiser les tentatives
        if ($success) {
            unset($_SESSION[$key]);
            return;
        }
        
        // Connexion échouée - incrémenter le compteur
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'count' => 1,
                'time' => time(),
                'email' => $email
            ];
        } else {
            $_SESSION[$key]['count']++;
        }
    }

    /**
     * Récupère le temps restant avant déblocage (en minutes)
     *
     * @param string $email Email de l'utilisateur
     * @return int|null Temps restant en minutes, null si pas bloqué
     */
    public static function getLockoutTimeRemaining(string $email): ?int {
        $key = 'login_attempts_' . md5($email);
        $attempts = $_SESSION[$key] ?? null;
        
        if ($attempts === null || $attempts['count'] < self::MAX_LOGIN_ATTEMPTS) {
            return null;
        }
        
        $elapsed = time() - $attempts['time'];
        $remaining = self::LOCKOUT_TIME - $elapsed;
        
        return $remaining > 0 ? ceil($remaining / 60) : null;
    }

    // =========================================
    // LOGIN / LOGOUT
    // =========================================

    /**
     * Connecter un utilisateur
     *
     * @param User $user
     */
    public static function login(User $user): void {
        Session::start();
        Session::set('user_id', $user->getId());
        self::$currentUser = $user;
    }

    /**
     * Déconnecter l'utilisateur
     */
    public static function logout(): void {
        Session::destroy();
        self::$currentUser = null;
    }

    /**
     * Tenter de se connecter avec un email et un mot de passe
     *
     * @param string $email
     * @param string $password
     * @return bool True si la connexion est réussie, false sinon
     */
    public static function attempt(string $email, string $password): bool {
        // Vérifier les tentatives de connexion
        if (!self::checkLoginAttempts($email)) {
            $remaining = self::getLockoutTimeRemaining($email);
            $_SESSION['flash_error'] = "Trop de tentatives. Veuillez réessayer dans {$remaining} minute(s).";
            return false;
        }
        
        $userRepository = new UserRepository();
        $user = $userRepository->findByEmail($email);

        // Aucun utilisateur trouvé avec cet email
        if ($user === null) {
            self::registerLoginAttempt($email, false);
            return false;
        }

        // Mot de passe incorrect
        if (!$user->verifyPassword($password)) {
            self::registerLoginAttempt($email, false);
            return false;
        }

        // Compte désactivé
        if (!$user->isActive()) {
            self::registerLoginAttempt($email, false);
            return false;
        }

        // Connexion réussie - réinitialiser les tentatives
        self::registerLoginAttempt($email, true);
        
        // Connecter l'utilisateur
        self::login($user);

        // Mettre à jour la dernière connexion en BDD
        $userRepository->updateDerniereConnexion($user->getId());

        return true;
    }

    // =========================================
    // GETTERS
    // =========================================

    /**
     * Récupérer l'ID de l'utilisateur connecté
     */
    public static function id(): ?int {
        if (!self::check()) {
            return null;
        }
        return Session::get('user_id');
    }

    /**
     * Récupérer l'utilisateur connecté sous forme d'objet User
     */
    public static function user(): ?User {
        // Retourner le cache si disponible
        if (self::$currentUser !== null) {
            return self::$currentUser;
        }

        // Récupérer l'ID depuis la session
        $userId = self::id();
        if ($userId === null) {
            return null;
        }

        // Charger l'utilisateur depuis la BDD et le mettre en cache
        $userRepository = new UserRepository();
        self::$currentUser = $userRepository->findById($userId);

        return self::$currentUser;
    }
}