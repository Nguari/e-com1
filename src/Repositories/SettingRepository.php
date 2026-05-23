<?php

namespace App\Repositories;

use PDO;

/**
 * Gestion des paramètres de l'application
 * 
 * Stockage et récupération des paramètres en base de données
 */
class SettingRepository {
    
    private PDO $db;
    private array $cache = [];
    
    public function __construct(PDO $db) {
        $this->db = $db;
    }
    
    /**
     * Récupère tous les paramètres
     * 
     * @return array Tableau associatif [clé => valeur]
     */
    public function getAll(): array {
        $stmt = $this->db->query("SELECT setting_key, setting_value FROM settings ORDER BY setting_key");
        $settings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row['setting_value'];
            $this->cache[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }
    
    /**
     * Récupère un paramètre spécifique
     * 
     * @param string $key La clé du paramètre
     * @param mixed $default Valeur par défaut si non trouvé
     * @return mixed
     */
    public function get(string $key, $default = null) {
        // Vérifier dans le cache d'abord
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }
        
        $stmt = $this->db->prepare("SELECT setting_value FROM settings WHERE setting_key = :key");
        $stmt->execute([':key' => $key]);
        $value = $stmt->fetchColumn();
        
        // Mettre en cache
        $this->cache[$key] = $value !== false ? $value : $default;
        
        return $this->cache[$key];
    }
    
    /**
     * Met à jour un paramètre (ou le crée s'il n'existe pas)
     * 
     * @param string $key La clé du paramètre
     * @param string $value La valeur du paramètre
     * @return bool
     */
    public function set(string $key, string $value): bool {
        // Vérifier si la clé existe
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM settings WHERE setting_key = :key");
        $stmt->execute([':key' => $key]);
        $exists = $stmt->fetchColumn() > 0;
        
        if ($exists) {
            // Mise à jour
            $stmt = $this->db->prepare("UPDATE settings SET setting_value = :value WHERE setting_key = :key");
            $result = $stmt->execute([':key' => $key, ':value' => $value]);
        } else {
            // Insertion
            $stmt = $this->db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (:key, :value)");
            $result = $stmt->execute([':key' => $key, ':value' => $value]);
        }
        
        // Mettre à jour le cache
        if ($result) {
            $this->cache[$key] = $value;
        }
        
        return $result;
    }
    
    /**
     * Met à jour plusieurs paramètres en une seule fois
     * 
     * @param array $settings Tableau associatif [clé => valeur]
     * @return bool
     */
    public function setMultiple(array $settings): bool {
        $success = true;
        foreach ($settings as $key => $value) {
            if (!$this->set($key, (string)$value)) {
                $success = false;
            }
        }
        return $success;
    }
    
    /**
     * Supprime un paramètre
     * 
     * @param string $key La clé du paramètre
     * @return bool
     */
    public function delete(string $key): bool {
        $stmt = $this->db->prepare("DELETE FROM settings WHERE setting_key = :key");
        $result = $stmt->execute([':key' => $key]);
        
        // Supprimer du cache
        if ($result) {
            unset($this->cache[$key]);
        }
        
        return $result;
    }
    
    /**
     * Vide le cache (utile après des modifications directes en BDD)
     */
    public function clearCache(): void {
        $this->cache = [];
    }
    
    /**
     * Récupère la valeur d'un paramètre avec conversion automatique en boolean
     * 
     * @param string $key La clé du paramètre
     * @param bool $default Valeur par défaut
     * @return bool
     */
    public function getBool(string $key, bool $default = false): bool {
        $value = $this->get($key, $default ? '1' : '0');
        return $value == '1' || $value === true || $value === 'true';
    }
    
    /**
     * Récupère la valeur d'un paramètre avec conversion automatique en entier
     * 
     * @param string $key La clé du paramètre
     * @param int $default Valeur par défaut
     * @return int
     */
    public function getInt(string $key, int $default = 0): int {
        return (int)$this->get($key, $default);
    }
    
    /**
     * Récupère la valeur d'un paramètre avec conversion automatique en float
     * 
     * @param string $key La clé du paramètre
     * @param float $default Valeur par défaut
     * @return float
     */
    public function getFloat(string $key, float $default = 0): float {
        return (float)$this->get($key, $default);
    }
    
    /**
     * Récupère tous les paramètres d'un groupe (par préfixe)
     * 
     * @param string $prefix Préfixe des clés (ex: 'payment_')
     * @return array
     */
    public function getByPrefix(string $prefix): array {
        $all = $this->getAll();
        $result = [];
        foreach ($all as $key => $value) {
            if (strpos($key, $prefix) === 0) {
                $result[$key] = $value;
            }
        }
        return $result;
    }
    
    /**
     * Initialise les paramètres par défaut (si la table est vide)
     * 
     * @return bool
     */
    public function initializeDefaultSettings(): bool {
        $defaultSettings = [
            'site_name' => 'NGAARY SHOP',
            'site_email' => 'contact@ngaary.sn',
            'site_phone' => '+221 78 123 45 67',
            'site_address' => 'Dakar, Sénégal',
            'delivery_fee' => '2500',
            'free_delivery_min' => '15000',
            'min_password' => '8',
            'max_login_attempts' => '5',
            'session_lifetime' => '7200',
            'enable_wave' => '1',
            'enable_om' => '1',
            'enable_cash' => '1',
            'primary_color' => '#16a34a',
            'header_bg' => '#ffffff',
            'order_email' => '1',
            'newsletter_email' => '1'
        ];
        
        $success = true;
        foreach ($defaultSettings as $key => $value) {
            // Vérifier si le paramètre existe déjà
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM settings WHERE setting_key = :key");
            $stmt->execute([':key' => $key]);
            $exists = $stmt->fetchColumn() > 0;
            
            if (!$exists) {
                if (!$this->set($key, $value)) {
                    $success = false;
                }
            }
        }
        
        return $success;
    }
}