<?php

namespace App\Controller\Authentication;

use App\Entity\Authentication\User;
use App\Form\Authentication\LoginType;
use App\Form\Authentication\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class AuthenticationController extends AbstractController
{
    public function __construct(
        public EntityManagerInterface $entityManager
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
        UserPasswordHasherInterface $hasher
    ): Response
    {
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($hasher->hashPassword($user, $form->get('password')->getData()));
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_home');
        }

        return $this->render('Page/Authentication/register.html.twig', [
            'form' => $form
        ]);
    }
}