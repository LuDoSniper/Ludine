<?php

namespace App\Security;

use App\Entity\Authentication\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Security\User\OAuthUserProvider;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class GoogleUserProvider extends OAuthUserProvider
{
    public function loadUserByOAuthUserResponse(
        GoogleUser $response,
        EntityManagerInterface $entityManager,
        RouterInterface $router
    ): UserInterface | RedirectResponse
    {
        // Ici, tu récupères ou crées l'utilisateur depuis la réponse de Google
        $user = $this->findUserByGoogleId($response->getId());

        if (!$user) {
            // Si l'utilisateur n'existe pas, tu peux en créer un
            $user = new User();
            $user->setUsername($response->getEmail());
            $user->setEmail($response->getEmail());
            $user->setPassword("");
            // Assure-toi de gérer la création et la persistance de l'utilisateur ici.
        }

        return $user;
    }

    private function findUserByGoogleId($googleId)
    {
        // Remplace par ta logique de récupération d'utilisateur via l'ID Google
        return null;
    }
}