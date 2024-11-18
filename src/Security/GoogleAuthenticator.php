<?php

namespace App\Security;

use App\Entity\Authentication\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use KnpU\OAuth2ClientBundle\Security\User\OAuthUser;
use League\OAuth2\Client\Provider\GoogleUser;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class GoogleAuthenticator extends OAuth2Authenticator implements UserProviderInterface
{
    public function __construct(
        private readonly ClientRegistry $clientRegistry,
        private readonly RouterInterface $router,
        private readonly UserProviderInterface $userProvider,
        private readonly EntityManagerInterface $entityManager
    ){}

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'app_login_google_check';
    }

    public function getCredentials(Request $request): AccessToken
    {
        return $this->fetchAccessToken($this->getGoogleClient());
    }

    public function getUser($credentials, UserProviderInterface $userProvider): ?UserInterface
    {
        /** @var GoogleUser $googleUser */
        $googleUser = $this->getGoogleClient()->fetchUserFromToken($credentials);
        $email = $googleUser->getEmail();

        // Si le user existe dans la bdd, alors il est sélectionné. Sinon, un nouveau user est créé
        return $userProvider->loadUserByIdentifier($email);
    }


    public function onAuthenticationSuccess(Request $request, $token, string $firewallName): RedirectResponse
    {
        /** @var OAuthUser $oauthUser */
        $oauthUser = $token->getUser();
        // Ici user peut valoir null car loadUserByIdentifierFromBDD renvoi User ou null
        $user = $this->loadUserByIdentifierFromBDD($oauthUser->getUserIdentifier());

        // Regarder si l'utilisateur n'existe pas dans la bdd auquel cas, redirection vers /register/google
        if (!$user) {
            return new RedirectResponse($this->router->generate('app_register_google'));
        }

        return new RedirectResponse($this->router->generate('app_home'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): RedirectResponse
    {
        return new RedirectResponse($this->router->generate('app_login'));
    }

    private function getGoogleClient(): OAuth2ClientInterface
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
        $badge = new UserBadge($email, function ($email) {
            return $this->userProvider->loadUserByIdentifier($email);
        });
        return new SelfValidatingPassport($badge);
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
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $identifier]);

        if (!$user) {
            $user = new OAuthUser($identifier, ['ROLE_USER']);
        }

        return $user;
    }

    public function loadUserByIdentifierFromBDD(string $identifier): ?UserInterface
    {
        return $this->entityManager->getRepository(User::class)->findOneBy(['email' => $identifier]);
    }
}
