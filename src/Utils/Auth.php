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
        $userRepository = new UserRepository();
        $user = $userRepository->findByEmail($email);

        // Aucun utilisateur trouvé avec cet email
        if ($user === null) {
            return false;
        }

        // Mot de passe incorrect
        if (!$user->verifyPassword($password)) {
            return false;
        }

        // Compte désactivé
        if (!$user->isActive()) {
            return false;
        }

        // Connexion réussie
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