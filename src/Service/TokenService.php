<?php

namespace App\Service;

use App\Entity\Authentication\User;
use Doctrine\ORM\EntityManagerInterface;

class TokenService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ){}

    public function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    public function getUserByToken(string $token): ?User
    {
        return $this->entityManager->getRepository(User::class)->findOneBy(['passwordToken' => $token]);
    }

    public function isTokenValid(string $token): bool
    {
        $user = $this->getUserByToken($token);
        if (is_null($user)) {
            return false;
        }
        return $user->getPasswordTokenExpiration() > new \DateTime();
    }
}