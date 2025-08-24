<?php

namespace App\Security;

use App\Entity\Authentication\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Security\User\OAuthUserProvider;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

class GoogleUserProvider extends OAuthUserProvider
{
    public function loadUserByOAuthUserResponse(
        GoogleUser $response,
        EntityManagerInterface $entityManager,
    ): UserInterface
    {
        // Ici, tu récupères ou crées l'utilisateur depuis la réponse de Google
        $user = $this->findUserByGoogleEmail($response->getEmail(), $entityManager);
        dd($user);
        die();
        //? Tout cela est assez étrange, il semblerais que nous ne passons jamais ici...
        //? Le code reste comme ça avec un die au cas où ce bout de code serais appelé.
        //? Necessaire pour le debug

        if (!$user) {
//            // Si l'utilisateur n'existe pas, tu peux en créer un
//            $user = new User();
//            $user->setUsername($response->getEmail());
//            $user->setEmail($response->getEmail());
//            $user->setPassword("");
//            // Assure-toi de gérer la création et la persistance de l'utilisateur ici.
            throw new UserNotFoundException();
        }

        return $user;
    }

    private function findUserByGoogleEmail(
        string $googleEmail,
        EntityManagerInterface $entityManager
    ): ?User
    {
        return $entityManager->getRepository(User::class)->findOneBy(['email' => $googleEmail]);
    }
}