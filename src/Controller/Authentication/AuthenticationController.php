<?php

namespace App\Controller\Authentication;

use App\Config\AppConfig;
use App\Entity\Authentication\User;
use App\Form\Authentication\LoginType;
use App\Form\Authentication\RegisterType;
use App\Form\Authentication\RegisterGoogleType;
use App\Form\Authentication\TokenGenerateType;
use App\Service\SendMailService;
use App\Service\TokenService;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Security\User\OAuthUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class AuthenticationController extends AbstractController
{
    public function __construct(
        public EntityManagerInterface $entityManager,
        public AppConfig $appConfig
    ){}

    #[Route('/login', 'app_login')]
    public function login(): Response
    {
        $form = $this->createForm(LoginType::class);

        return $this->render("Page/Authentication/login.html.twig", [
            'form' => $form
        ]);
    }

    #[Route('/register', 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $hasher,
        TokenService $tokenService,
        SendMailService $mailService
    ): Response
    {
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($hasher->hashPassword($user, $form->get('password')->getData()));
            $token = $tokenService->generateToken();
            $user->setPasswordToken($token);
            $user->setPasswordTokenExpiration((new \DateTimeImmutable())->modify('+1 day'));

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $mailService->send(
                $this->appConfig->noReplyMail,
                $user->getEmail(),
                $this->appConfig->verifyMailSubject,
                'verify',
                compact('token')
            );

            return $this->redirectToRoute('app_wait');
        }

        return $this->render('Page/Authentication/register.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/register/google', 'app_register_google')]
    public function setPassword(
        Request $request,
        UserPasswordHasherInterface $hasher
    ): Response
    {
        /** @var OAuthUser $oauthUser */
        $oauthUser = $this->getUser();
        if (!$oauthUser) {
            throw new NotFoundHttpException();
        }

        $user = new User();
        $user
            ->setEmail($oauthUser->getUserIdentifier())
            ->setUsername($oauthUser->getUserIdentifier())
            ->setPassword("")
            ->setIsVerified(true);
        ;

        $form = $this->createForm(RegisterGoogleType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setUsername($form->get('username')->getData());
            $password = $form->get('password')->getData();
            $user->setPassword($hasher->hashPassword($user, $password));
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_home');
        }

        return $this->render('Page/Authentication/google-register.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/wait', 'app_wait')]
    public function wait(): Response
    {
        return $this->render("Page/Authentication/wait.html.twig");
    }

    #[Route('/verify/{token}', 'app_verify')]
    public function verify(
        string $token,
        TokenService $tokenService
    ): Response
    {
        $user = $tokenService->getUserByToken($token);
        if ($user && $tokenService->isTokenValid($token)) {
            $user->setIsVerified(true);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_home');
        }

        return $this->redirectToRoute('app_resend');
    }

    #[Route('/resend', 'app_resend')]
    public function resend(
        Request $request,
        UserPasswordHasherInterface $hasher,
        TokenService $tokenService,
        SendMailService $mailService
    ): Response
    {
        $form = $this->createForm(TokenGenerateType::class);
        $form->handleRequest($request);

        $invalidCredentials = false;
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $form->get('email')->getData()]);
            if ($user && $hasher->isPasswordValid($user, $form->get('password')->getData())) {
                $token = $tokenService->generateToken();
                $user->setPasswordToken($token);
                $user->setPasswordTokenExpiration((new \DateTimeImmutable())->modify('+1 day'));

                $mailService->send(
                    $this->appConfig->noReplyMail,
                    $user->getEmail(),
                    $this->appConfig->verifyMailSubject,
                    'verify',
                    compact('token')
                );

                $this->entityManager->flush();

                return $this->redirectToRoute('app_wait');
            }

            $invalidCredentials = true;
        }

        return $this->render('Page/Authentication/generate-token.html.twig', [
            'form' => $form,
            'invalidCredentials' => $invalidCredentials
        ]);
    }
}