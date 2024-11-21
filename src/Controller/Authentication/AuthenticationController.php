<?php

namespace App\Controller\Authentication;

use App\Config\AppConfig;
use App\Entity\Authentication\User;
use App\Form\Authentication\LoginType;
use App\Form\Authentication\RegisterType;
use App\Form\Authentication\RegisterGoogleType;
use App\Form\Authentication\ResetPasswordTokenType;
use App\Form\Authentication\ResetPasswordType;
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
        if ($this->getUser() && $this->getUser()->isVerified()) {
            return $this->redirectToRoute('app_home');
        }

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
                if ($user->isVerified()) {
                    return $this->redirectToRoute('app_home');
                }

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

    #[Route('/reset/password', 'app_reset_password')]
    public function resetPassword(
        Request $request,
        TokenService $tokenService,
        SendMailService $mailService,
    ): Response
    {
        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        $invalidCredentials = false;
        $mailSent = false;
        if ($form->isSubmitted() && $form->isValid()) {
            $invalidCredentials = true;

            $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $form->get('email')->getData()]);
            if ($user) {
                $mailSent = true;
                $invalidCredentials = false;

                $token = $tokenService->generateToken();
                $user
                    ->setPasswordToken($token)
                    ->setPasswordTokenExpiration((new \DateTimeImmutable())->modify('+1 day'))
                ;
                $this->entityManager->flush();

                $mailService->send(
                    $this->appConfig->noReplyMail,
                    $user->getEmail(),
                    $this->appConfig->forgottenPasswordMailSubject,
                    'reset-password',
                    compact('user', 'token')
                );
            }
        }

        return $this->render('Page/Authentication/reset-password.html.twig', [
            'form' => $form,
            'invalidCredentials' => $invalidCredentials,
            'mailSent' => $mailSent
        ]);
    }

    #[Route('/reset/password/{token}', 'app_reset_password_token')]
    public function resetPasswordToken(
        Request $request,
        TokenService $tokenService,
        UserPasswordHasherInterface $hasher,
        string $token
    ): Response
    {
        $form = $this->createForm(ResetPasswordTokenType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $tokenService->getUserByToken($token);
            if ($tokenService->isTokenValid($token)) {
                $user->setPassword($hasher->hashPassword($user, $form->get('password')->getData()));
                $this->entityManager->flush();

                return $this->redirectToRoute('app_login');
            }

            return $this->redirectToRoute('app_reset_password');
        }

        return $this->render('PAge/Authentication/reset-password-token.html.twig', [
            'form' => $form
        ]);
    }
}