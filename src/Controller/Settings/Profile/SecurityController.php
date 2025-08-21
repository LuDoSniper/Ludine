<?php

namespace App\Controller\Settings\Profile;

use App\Form\Settings\Profile\SecurityType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class SecurityController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $hasher,
    ){}

    #[Route('/settings/profile/security', name: 'settings_profile_security')]
    public function security(
        Request $request,
    ): Response
    {
        $user = $this->getUser();

        $form = $this->createForm(SecurityType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->hasher->isPasswordValid($user, $form->get('actualPassword')->getData())) {
                $user->setPassword($this->hasher->hashPassword($user, $form->get('newPassword')->getData()));

                $this->entityManager->flush();
            }
        }

        return $this->render('Page/Settings/Profile/security.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/settings/profile/security/save', name: 'settings_profile_security_save', methods: ['POST'])]
    public function save(
        Request $request,
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (empty($data)) {
            return new JsonResponse(['error' => 'empty data'], Response::HTTP_BAD_REQUEST);
        }

        $missing_fields = [];
        if (empty($data['actualPassword'])) {
            $missing_fields[] = 'actualPassword';
        }
        if (empty($data['newPassword_first'])) {
            $missing_fields[] = 'newPassword_first';
        }
        if (empty($data['newPassword_second'])) {
            $missing_fields[] = 'newPassword_second';
        }

        if (!empty($missing_fields)) {
            return new JsonResponse(['missing_fields' => $missing_fields], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->getUser();
        if ($this->hasher->isPasswordValid($user, $data['actualPassword']) && $data['newPassword_first'] === $data['newPassword_second']) {
            $user->setPassword($this->hasher->hashPassword($user, $data['newPassword_first']));

            $this->entityManager->flush();
        }

        $this->entityManager->flush();

        return new JsonResponse(['general' => [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'username' => $user->getUsername(),
            'displayName' => $user->getDisplayName(),
        ]]);
    }
}