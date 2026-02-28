<?php

namespace App\Utils;

/**
 * Classe Session - Gestion des sessions utilisateur
 * 
 * Concepts:
 * - Gestion des sessions PHP
 * - Méthodes statiques pour démarrer, détruire et manipuler les sessions
 * - Sécurité des sessions (regénération d'ID, gestion des cookies)
 * 
 * Utilisation:
 * - Session::start() pour démarrer une session
 * - Session::destroy() pour détruire une session
 * - Session::set($key, $value) pour définir une variable de session (ex: Session::set('user_id', 45))
 * - Session::get($key) pour obtenir une variable de session
 */
class Session {

    /**
     * Démarrer une session sécurisée
     * 
     * @return void
     */
    public static function start(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
            // Régénérer l'ID de session pour éviter les attaques de fixation de session
            session_regenerate_id(true);
        }
    }

    /**
     * Détruire la session en cours
     * 
     * @return void
     */
    public static function destroy(): void {
        self::start();
        // Supprimer toutes les variables de session
        $_SESSION = [];
        // Détruire le cookie de session  
        session_unset();
        session_destroy();
    }

    /**
     * Définir une variable de session
     * 
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function set(string $key, $value): void {
        self::start();
        $_SESSION[$key] = $value;
    }

    /**
     * Obtenir une variable de session
     * 
     * @param string $key
     * @return mixed|null
     */
    public static function get(string $key, $default = null) {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Supprimer une variable de session
     * 
     * @param string $key
     * @return void
     */
    public static function remove(string $key): void {
        self::start();
        unset($_SESSION[$key]);
    }

    /**
     * Vérifier si une variable de session existe
     * 
     * @param string $key
     * @return bool
     */
    public static function has(string $key): bool {
        self::start();
        return isset($_SESSION[$key]);
    }

    /**
     * Regénérer l'ID de session pour des raisons de sécurité
     * 
     * @return void
     */
    public static function regenerate(): void {
        self::start();
        session_regenerate_id(true);
    }

    /**
     * Ajouter un message flash à la session
     * 
     * @param string $type
     * @param string $message
     * @return void
     */
    public static function flash(string $type, string $message): void {
        self::start();
        $_SESSION['flash'][$type] = $message;
    }

    /**
     * Obtenir et supprimer un message flash de la session
     * 
     * @param string $type
     * @return string|null
     */
    public static function getFlash(string $type): ?string {
        self::start();
        if (isset($_SESSION['flash'][$type])) {
            $message = $_SESSION['flash'][$type];
            unset($_SESSION['flash'][$type]);
            return $message;
        }
        return null;
    }
}