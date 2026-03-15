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
 * - Session::start()              → démarrer une session
 * - Session::destroy()            → détruire une session
 * - Session::set($key, $value)    → définir une variable de session
 * - Session::get($key)            → obtenir une variable de session
 * - Session::flash($type, $msg)   → message temporaire
 * - Session::getFlash($type)      → lire et supprimer un message flash
 */
class Session {

    // =========================================
    // GESTION DE LA SESSION
    // =========================================

    /**
     * Démarrer une session sécurisée
     * Note : session_regenerate_id() est retiré d'ici
     * et appelé uniquement lors du login dans Auth::login()
     */
    public static function start(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Détruire la session en cours
     */
    public static function destroy(): void {
        self::start();
        $_SESSION = [];
        session_unset();
        session_destroy();
    }

    /**
     * Régénérer l'ID de session pour des raisons de sécurité
     * À appeler uniquement lors du login pour éviter la fixation de session
     */
    public static function regenerate(): void {
        self::start();
        session_regenerate_id(true);
    }

    // =========================================
    // MANIPULATION DES VARIABLES
    // =========================================

    /**
     * Définir une variable de session
     *
     * @param string $key
     * @param mixed  $value
     */
    public static function set(string $key, $value): void {
        self::start();
        $_SESSION[$key] = $value;
    }

    /**
     * Obtenir une variable de session
     *
     * @param string $key
     * @param mixed  $default Valeur par défaut si la clé n'existe pas
     * @return mixed
     */
    public static function get(string $key, $default = null) {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Supprimer une variable de session
     *
     * @param string $key
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

    // =========================================
    // MESSAGES FLASH
    // =========================================

    /**
     * Ajouter un message flash à la session
     * Le message sera supprimé après la première lecture
     *
     * Exemple : Session::flash('success', 'Connexion réussie !')
     *           Session::flash('error', 'Email invalide.')
     *
     * @param string $type    Type du message (success, error, warning, info)
     * @param string $message Contenu du message
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

    /**
     * Vérifier si un message flash existe
     *
     * @param string $type
     * @return bool
     */
    public static function hasFlash(string $type): bool {
        self::start();
        return isset($_SESSION['flash'][$type]);
    }
}