<?php

namespace App\Security;

use App\Entity\Authentication\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use KnpU\OAuth2ClientBundle\Security\User\OAuthUser;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class GoogleAuthenticator extends OAuth2Authenticator implements UserProviderInterface
{
    private ClientRegistry $clientRegistry;
    private RouterInterface $router;
    private UserProviderInterface $userProvider;

    private EntityManagerInterface $entityManager;

    public function __construct(
        ClientRegistry        $clientRegistry,
        RouterInterface       $route,
        UserProviderInterface $userProvider,
        EntityManagerInterface $entityManager
    )
    {
        $this->clientRegistry = $clientRegistry;
        $this->router = $route;
        $this->userProvider = $userProvider;
        $this->entityManager = $entityManager;
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'app_login_google_check';
    }

    public function getCredentials(Request $request)
    {
        return $this->fetchAccessToken($this->getGoogleClient());
    }

    public function getUser($credentials, UserProviderInterface $userProvider): ?UserInterface
    {
        /** @var GoogleUser $googleUser */
        $googleUser = $this->getGoogleClient()->fetchUserFromToken($credentials);

        $email = $googleUser->getEmail();

        // Option 1: Charger l'utilisateur depuis la base de données
        $existingUser = $userProvider->loadUserByIdentifier($email);
        if ($existingUser) {
            return $existingUser;
        }

        // Option 2: Créer un nouvel utilisateur si nécessaire
        $user = new \App\Entity\Authentication\User();
        $user->setEmail($googleUser->getEmail());
        $user->setUsername($googleUser->getName());
        // Persiste l'utilisateur avec Doctrine si nécessaire
        return $user;
    }


    public function onAuthenticationSuccess(Request $request, $token, string $firewallName): RedirectResponse
    {
        return new RedirectResponse($this->router->generate('app_home'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): RedirectResponse
    {
        return new RedirectResponse($this->router->generate('app_login'));
    }

    private function getGoogleClient()
    {
        return $this->clientRegistry->getClient('google');
    }

    public function authenticate(Request $request): Passport
    {
        // Récupérer le token d'accès Google
        $credentials = $this->fetchAccessToken($this->getGoogleClient());

        // Récupérer les informations utilisateur depuis Google
        $googleUser = $this->getGoogleClient()->fetchUserFromToken($credentials);

        $email = $googleUser->getEmail();

        if (!$email) {
            throw new AuthenticationException('Email introuvable ou invalide.');
        }

        // Créer un Passport Self-Validating
        return new SelfValidatingPassport(
            new UserBadge($email, function ($email) {
                // Charger ou créer l'utilisateur correspondant
                return $this->userProvider->loadUserByIdentifier($email);
            })
        );
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return User::class === $class || OAuthUser::class === $class;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $identifier]);

        if (!$user) {
            throw new UserNotFoundException();
        }

        return $user;
    }
}
