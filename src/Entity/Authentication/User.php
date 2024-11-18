<?php

namespace App\Entity\Authentication;

use App\Repository\Authentication\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username', 'email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?string $email = null;

    #[ORM\Column(length: 180)]
    private ?string $username = null;

    #[ORM\Column(length: 16, nullable: true)]
    private ?string $displayName = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    private ?string $plainPassword = null;

    #[ORM\Column]
    private bool $isVerified = false;

    #[ORM\Column(nullable: true)]
    private ?string $passwordToken = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $passwordTokenExpiration = null;

    // Getter - Setter

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }
    public function setEmail(?string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }
    public function setUsername(string $username): static
    {
        $this->username = $username;
        return $this;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }
    public function setDisplayName(?string $displayName): static
    {
        $this->displayName = $displayName;
        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }
    public function addRole(string $role): static
    {
        if (!in_array($role, $this->getRoles())) {
            $this->roles[] = $role;
        }
        return $this;
    }
    public function removeRole(string $role): static
    {
        if (in_array($role, $this->getRoles())) {
            $this->roles[] = $role;
        }
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }
    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }
    public function setPlainPassword(?string $plainPassword): static
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }
    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;
        return $this;
    }

    public function getPasswordToken(): ?string
    {
        return $this->passwordToken;
    }
    public function setPasswordToken(?string $passwordToken): static
    {
        $this->passwordToken = $passwordToken;
        return $this;
    }

    public function getPasswordTokenExpiration(): ?\DateTimeImmutable
    {
        return $this->passwordTokenExpiration;
    }
    public function setPasswordTokenExpiration(?\DateTimeImmutable $passwordTokenExpiration): static
    {
        $this->passwordTokenExpiration = $passwordTokenExpiration;
        return $this;
    }

    // Methods

    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }
}
