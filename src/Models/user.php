<?php
namespace App\Models;

use DateTime;

/**
 * Classe User
 * 
 * Représente un utilisateur de la boutique NGAARY SHOP
 * Hérite de BaseEntity (id, createdAt, updatedAt)
 * 
 * Concepts enseignés :
 * - Héritage (extends BaseEntity)
 * - Encapsulation (private + getters/setters)
 * - Chaînage de méthodes (return $this)
 * - Propriétés statiques (compteurTotal)
 */
class User extends BaseEntity {


    private static int $compteurTotal = 0;
    private int $numeroInstance;

    private string $nom;
    private string $prenom;
    private string $email;
    private string $password;
    private ?string $tel      = null;
    private string $role      = 'client';
    private bool $isActive    = true;

    /**
     * @param string $nom
     * @param string $prenom
     * @param string $email
     * @param string $password  mot de passe en clair (sera hashé automatiquement)
     */
    public function __construct(
        string $nom      = '',
        string $prenom   = '',
        string $email    = '',
        string $password = ''
    ) {
        parent::__construct(); // initialise createdAt via BaseEntity

        // Compteur statique
        self::$compteurTotal++;
        $this->numeroInstance = self::$compteurTotal;

        $this->nom    = trim($nom);
        $this->prenom = trim($prenom);
        $this->email  = strtolower(trim($email));

        // On hashe le mot de passe uniquement s'il est fourni
        if (!empty($password)) {
            $this->password = password_hash($password, PASSWORD_BCRYPT);
        } else {
            $this->password = '';
        }
    }

    /**
     * Retourne le nombre total d'instances User créées
     */
    public static function getCompteurTotal(): int {
        return self::$compteurTotal;
    }


    public function getNumeroInstance(): int {
        return $this->numeroInstance;
    }

    public function getNom(): string {
        return $this->nom;
    }

    public function getPrenom(): string {
        return $this->prenom;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getTel(): ?string {
        return $this->tel;
    }

    public function getRole(): string {
        return $this->role;
    }

    public function isActive(): bool {
        return $this->isActive;
    }

    public function getPasswordHash(): string {
        return $this->password;
    }


    public function setNom(string $nom): self {
        $this->nom = trim($nom);
        return $this;
    }

    public function setPrenom(string $prenom): self {
        $this->prenom = trim($prenom);
        return $this;
    }

    public function setEmail(string $email): self {
        $this->email = strtolower(trim($email));
        return $this;
    }

    public function setTel(?string $tel): self {
        $this->tel = $tel;
        return $this;
    }

    public function setRole(string $role): self {
        if (in_array($role, ['admin', 'client'])) {
            $this->role = $role;
        }
        return $this;
    }

    public function setIsActive(bool $isActive): self {
        $this->isActive = $isActive;
        return $this;
    }

    /**
     * Définit le mot de passe en le hashant automatiquement
     * @param string $password mot de passe en clair
     */
    public function setPassword(string $password): self {
        $this->password = password_hash($password, PASSWORD_BCRYPT);
        return $this;
    }

    /**
     * Définit le mot de passe déjà hashé (utilisé lors d'une lecture BDD)
     * @param string $passwordHash hash déjà existant
     */
    public function setPasswordHash(string $passwordHash): self {
        $this->password = $passwordHash;
        return $this;
    }

    /**
     * Vérifie si le mot de passe en clair correspond au hash stocké
     */
    public function verifyPassword(string $password): bool {
        return password_verify($password, $this->password);
    }

    /**
     * Retourne le nom complet "Prénom Nom"
     */
    public function getFullName(): string {
        return $this->prenom . ' ' . $this->nom;
    }

    /**
     * Retourne les initiales ex: "FD" pour "Fatou Diallo"
     */
    public function getInitials(): string {
        $i1 = !empty($this->prenom) ? strtoupper($this->prenom[0]) : '';
        $i2 = !empty($this->nom)    ? strtoupper($this->nom[0])    : '';
        return $i1 . $i2;
    }

    /**
     * Vérifie si l'utilisateur est administrateur
     */
    public function isAdmin(): bool {
        return $this->role === 'admin';
    }

    /**
     * Convertit l'objet en tableau associatif
     * Implémentation obligatoire de BaseEntity::toArray()
     */
    public function toArray(): array {
        return [
            // Propriétés héritées de BaseEntity
            'id'         => $this->getId(),
            'created_at' => $this->getCreatedAt()?->format('Y-m-d H:i:s'),
            'updated_at' => $this->getUpdatedAt()?->format('Y-m-d H:i:s'),

            // Propriétés spécifiques à User
            'nom'        => $this->nom,
            'prenom'     => $this->prenom,
            'email'      => $this->email,
            'tel'        => $this->tel,
            'role'       => $this->role,
            'is_active'  => $this->isActive,

            // Propriétés calculées
            'full_name'  => $this->getFullName(),
            'initials'   => $this->getInitials(),
        ];
    }
}