<?php

namespace App\Models;

use DateTime;

class User extends BaseEntity {

    protected ?string $nom = null;
    protected ?string $prenom = null;
    protected ?string $email = null;
    protected ?string $passwordHash = null;
    protected ?string $tel = null;
    protected string $role = 'client';
    protected bool $isActive = true;

    public function __construct() {
        parent::__construct();
    }

    // Getters
    public function getNom(): ?string { return $this->nom; }
    public function getPrenom(): ?string { return $this->prenom; }
    public function getFullName(): string {
        return trim(($this->prenom ?? '') . ' ' . ($this->nom ?? ''));
    }
    public function getEmail(): ?string { return $this->email; }
    public function getPasswordHash(): ?string { return $this->passwordHash; }
    public function getTel(): ?string { return $this->tel; }
    public function getRole(): string { return $this->role; }
    public function isActive(): bool { return $this->isActive; }

    // Setters (API attendue par UserRepository / AuthController)
    public function setNom(?string $nom): self { $this->nom = $nom; return $this; }
    public function setPrenom(?string $prenom): self { $this->prenom = $prenom; return $this; }
    public function setEmail(?string $email): self { $this->email = $email; return $this; }

    // Utilisé par AuthController->register()
    public function setPassword(?string $password): self {
        $password = (string)($password ?? '');
        $this->passwordHash = password_hash($password, PASSWORD_BCRYPT);
        return $this;
    }

    // Utilisé par UserRepository->hydrate()
    public function setPasswordHash(?string $passwordHash): self { $this->passwordHash = $passwordHash; return $this; }

    public function setTel(?string $tel): self { $this->tel = $tel; return $this; }
    public function setRole(?string $role): self { $this->role = $role ?: 'client'; return $this; }
    public function setIsActive(bool $isActive): self { $this->isActive = $isActive; return $this; }


    public function setCreatedAt(DateTime $created_at): self {
        parent::setCreatedAt($created_at);
        return $this;
    }

    public function setUpdatedAt(DateTime $updated_at): self {
        parent::setUpdatedAt($updated_at);
        return $this;
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'email' => $this->email,
            'tel' => $this->tel,
            'role' => $this->role,
            'actif' => $this->isActive ? 1 : 0,
            'createdAt' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}

